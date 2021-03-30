<?php


namespace Plugin\ws5_mollie\lib;


use Exception;
use Generator;
use JTL\Checkout\Bestellung;
use JTL\Exceptions\CircularReferenceException;
use JTL\Exceptions\ServiceNotFoundException;
use JTL\Plugin\Payment\Method;
use JTL\Plugin\Payment\MethodInterface;
use JTL\Shop;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Exceptions\IncompatiblePlatform;
use Plugin\ws5_mollie\lib\Model\OrderModel;
use Plugin\ws5_mollie\lib\Model\QueueModel;
use Plugin\ws5_mollie\lib\Traits\Plugin;
use RuntimeException;

class Queue
{

    use Plugin;


    /**
     * @param int $limit
     * @throws CircularReferenceException
     * @throws ServiceNotFoundException
     */
    public static function run($limit = 10): void
    {

        /** @var QueueModel $todo */
        foreach (self::getOpen($limit) as $todo) {

            if ((list($type, $id) = explode(':', $todo->getType()))) {
                try {
                    switch ($type) {
                        case 'webhook':
                            self::handleWebhook($id, $todo);
                            break;

                        case 'hook':
                            self::handleHook((int)$id, $todo);
                            break;
                    }

                } catch (Exception $e) {
                    Shop::Container()->getLogService()->error($e->getMessage() . " ({$type}, {$id})");
                    $todo->setResult($e->getMessage());
                    $todo->setDone(date('Y-m-d H:i:s'));
                    $todo->save();
                }
            }
        }
    }

    /**
     * @param $limit
     * @return Generator|null
     */
    private static function getOpen($limit): ?Generator
    {
        $open = Shop::Container()->getDB()->executeQueryPrepared("SELECT * FROM xplugin_ws5_mollie_queue WHERE cResult IS NULL AND dDone IS NULL ORDER BY dCreated DESC LIMIT 0, :LIMIT;", [
            ':LIMIT' => $limit
        ], 2);

        foreach ($open as $_raw) {
            $queueModel = QueueModel::newInstance(Shop::Container()->getDB());
            $queueModel->fill($_raw);
            $queueModel->setWasLoaded(true);
            yield $queueModel;
        }
    }

    /**
     * @param string $id
     * @param QueueModel $todo
     * @return bool
     * @throws Exception
     */
    protected static function handleWebhook(string $id, QueueModel $todo): bool
    {
        $order = OrderModel::loadByAttributes(['orderId' => $id], Shop::Container()->getDB(), OrderModel::ON_NOTEXISTS_FAIL);
        $oBestellung = new Bestellung($order->getBestellung());
        if ($oBestellung->kBestellung && $method = self::paymentMethod((int)$oBestellung->kZahlungsart)) {
            $method->handleNotification($oBestellung, $order->getHash(), ['id' => $order->getOrderId()]);
            $todo->setDone(date('Y-m-d H:i:s'));
            return $todo->save();
        }
        throw new RuntimeException(`Bestellung oder Zahlungsart konnte nicht geladen werden: ${id}`);
    }

    /**
     * @param int $kZahlungsart
     * @return MethodInterface
     */
    public static function paymentMethod(int $kZahlungsart): MethodInterface
    {
        if ($za = Shop::Container()->getDB()->executeQueryPrepared('SELECT cModulId from tzahlungsart WHERE kZahlungsart = :kZahlungsart AND cModulId LIKE \'kPlugin_%\'', [':kZahlungsart' => $kZahlungsart], 1)) {
            return Method::create($za->cModulId);
        }
        $fallback = sprintf("kPlugin_%s_mollie", self::Plugin()->getID());
        if ($fallbackZA = Method::create($fallback)) {
            return $fallbackZA;
        }
        throw new RuntimeException('PaymentMethod not found');
    }

    /**
     * @param int $hook
     * @param QueueModel $todo
     * @return bool
     * @throws CircularReferenceException
     * @throws ServiceNotFoundException
     * @throws ApiException
     * @throws IncompatiblePlatform
     * @throws Exception
     */
    protected static function handleHook(int $hook, QueueModel $todo): bool
    {
        $data = unserialize($todo->getData()); //, [stdClass::class, Bestellung::class, \JTL\Customer\Customer::class]);
        if (array_key_exists('kBestellung', $data)) {
            switch ($hook) {
                case HOOK_BESTELLUNGEN_XML_BESTELLSTATUS:
                    if ((int)$data['kBestellung']) {
                        $oBestellung = new Bestellung($data['kBestellung'], true);

                        /** @var $method PaymentMethod */
                        if ($oBestellung->kBestellung
                            && array_key_exists('status', $data)
                            && (int)$data['status']
                            && ($method = self::paymentMethod((int)$oBestellung->kZahlungsart))
                            && ($order = OrderModel::loadByAttributes(['bestellung' => $oBestellung->kBestellung], Shop::Container()->getDB(), OrderModel::ON_NOTEXISTS_FAIL))
                            && (strpos($order->orderId, 'tr_') === false)
                            && ($mollie = (new MollieAPI($order->getTest()))->getClient()->orders->get($order->getOrderId(), ['embed' => 'payments']))) {

                            $method->handleNotification($oBestellung, $order->getHash(), ['id' => $order->getOrderId()]);

                            if ($mollie->isCreated() || $mollie->isPaid() || $mollie->isAuthorized() || $mollie->isShipping() || $mollie->isPending()) {
                                try {
                                    if ($shipments = Shipment::syncBestellung($oBestellung->kBestellung)) {
                                        foreach ($shipments as $shipment) {
                                            self::paymentMethod($oBestellung->kZahlungsart)->doLog("Order shipped: \n" . print_r($shipment, 1));
                                        }
                                        $todo->setResult("Shipped " . count($shipments) . " shipments");
                                    }
                                } catch (Exception $e) {
                                    $todo->setResult($e->getMessage() . "\n" . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString());
                                }
                            }

                        } else {
                            $todo->setResult('Nothing to do.');
                        }

                        $todo->setDone(date('Y-m-d H:i:s'));
                        return $todo->save();

                    }

                    $todo->setResult("kBestellung missing");
                    $todo->setDone(date('Y-m-d H:i:s'));
                    return $todo->save();

                case HOOK_BESTELLUNGEN_XML_BEARBEITESTORNO:

                    /** @var Bestellung $oBestellung */
                    $oBestellung = new Bestellung($data['kBestellung']);
                    $order = OrderModel::loadByAttributes(['bestellung' => $oBestellung->kBestellung], Shop::Container()->getDB(), OrderModel::ON_NOTEXISTS_FAIL);

                    /** @var \Mollie\Api\Resources\Order|Payment $mollie */
                    $mollie = null;
                    if (strpos($order->orderId, 'tr_') === 0) {
                        /** @var Payment $mollie */
                        $mollie = MollieAPI::API($order->getTest())->payments->get($order->getOrderId());
                    } else {
                        /** @var \Mollie\Api\Resources\Order $mollie */
                        $mollie = MollieAPI::API($order->getTest())->orders->get($order->getOrderId(), ['embed' => 'payments']);
                    }


                    if (self::Plugin()->getConfig()->getValue('autoRefund') !== 'on') {
                        throw new RuntimeException('Auto-Refund disabled');
                    }

                    if ((int)$oBestellung->cStatus === BESTELLUNG_STATUS_STORNO) {
                        if ($mollie->isCancelable) {
                            if ($mollie->resource === 'payment') {
                                MollieAPI::API($order->getTest())->payments->cancel($mollie->id);
                                $todo->setResult('Payment cancelled.');
                            } else {
                                $res = $mollie->cancel();
                                $todo->setResult('Order cancelled.');
                            }
                            self::paymentMethod($oBestellung->kZahlungsart)->doLog("Order cancelled: \n" . print_r($res, 1));
                        } else {

                            if ($mollie->resource === 'payment') {
                                /** @noinspection PhpParamsInspection */
                                MollieAPI::API($order->getTest())->payments->refund($mollie, ["amount" => $mollie->amount]);
                                $todo->setResult('Payment refunded.');
                            } else {
                                $res = $mollie->refundAll();
                                $todo->setResult('Order refunded.');
                            }


                            self::paymentMethod($oBestellung->kZahlungsart)->doLog("Order refunded: \n" . print_r($res, 1));
                        }
                        $todo->setDone(date('Y-m-d H:i:s'));
                        return $todo->save();
                    }
                    break;
            }
        }
        return false;
    }
}