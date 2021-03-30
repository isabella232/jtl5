<?php

/**
 * @copyright 2020 WebStollen GmbH
 */

namespace Plugin\ws5_mollie\lib;

use Exception;
use JTL\Alert\Alert;
use JTL\Checkout\Bestellung;
use JTL\Exceptions\CircularReferenceException;
use JTL\Exceptions\ServiceNotFoundException;
use JTL\Plugin\Helper as PluginHelper;
use JTL\Plugin\Payment\Method;
use JTL\Plugin\Payment\MethodInterface;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Exceptions\IncompatiblePlatform;
use Plugin\ws5_mollie\lib\Checkout\OrderCheckout;
use Plugin\ws5_mollie\lib\Checkout\PaymentCheckout;
use Plugin\ws5_mollie\lib\Traits\Plugin;
use Session;
use Shop;

abstract class PaymentMethod extends Method
{

    public const ALLOW_PAYMENT_BEFORE_ORDER = false;

    public const METHOD = '';

    /**
     * @var string
     */
    protected $pluginID;

    use Plugin;

    /**
     * @param int $nAgainCheckout
     * @return $this|Method|MethodInterface|PaymentMethod
     */
    public function init($nAgainCheckout = 0)
    {
        parent::init($nAgainCheckout);

        $this->pluginID = PluginHelper::getIDByModuleID($this->moduleID);

        return $this;
    }

    public function canPayAgain(): bool
    {
        return true;
    }

    /**
     * @param array $args_arr
     * @return bool
     */
    public function isValidIntern(array $args_arr = []): bool
    {
        return $this->duringCheckout
            ? static::ALLOW_PAYMENT_BEFORE_ORDER && parent::isValidIntern($args_arr)
            : parent::isValidIntern($args_arr);
    }

    /**
     * @return bool
     */
    public function isSelectable(): bool
    {
        if (MollieAPI::getMode()) {
            $selectable = trim(self::Plugin()->getConfig()->getValue('test_apiKey')) !== '';
        } else {
            $selectable = trim(self::Plugin()->getConfig()->getValue('apiKey')) !== '';
            if (!$selectable) {
                $this->doLog("Live API Key missing!", LOGLEVEL_ERROR);
            }
        }
        if ($selectable) {
            try {
                $locale = self::getLocale($_SESSION['cISOSprache'], $_SESSION['Kunde']->cLand);
                $amount = Session::getCart()->gibGesamtsummeWaren(true) * Session::getCurrency()->getConversionFactor();
                if ($amount <= 0) {
                    $amount = 0.01;
                }
                $selectable = self::isMethodPossible(
                    static::METHOD,
                    $locale,
                    Session::getCustomer()->cLand,
                    Session::getCurrency()->getCode(),
                    $amount
                );
            } catch (Exception $e) {
                $selectable = false;
            }
        }
        return $selectable && parent::isSelectable();
    }

    /**
     * @param string $cISOSprache
     * @param string|null $country
     * @return string
     */
    public static function getLocale(string $cISOSprache, string $country = null): string
    {
        switch ($cISOSprache) {
            case "ger":
                if ($country === "AT") {
                    return "de_AT";
                }
                if ($country === "CH") {
                    return "de_CH";
                }
                return "de_DE";
            case "fre":
                if ($country === "BE") {
                    return "fr_BE";
                }
                return "fr_FR";
            case "dut":
                if ($country === "BE") {
                    return "nl_BE";
                }
                return "nl_NL";
            case "spa":
                return "es_ES";
            case "ita":
                return "it_IT";
            case "pol":
                return "pl_PL";
            case "hun":
                return "hu_HU";
            case "por":
                return "pt_PT";
            case "nor":
                return "nb_NO";
            case "swe":
                return "sv_SE";
            case "fin":
                return "fi_FI";
            case "dan":
                return "da_DK";
            case "ice":
                return "is_IS";
            default:
                return "en_US";
        }
    }

    /**
     * @param $method
     * @param $locale
     * @param $billingCountry
     * @param $currency
     * @param $amount
     * @return bool
     * @throws ApiException
     * @throws IncompatiblePlatform
     */
    protected static function isMethodPossible($method, $locale, $billingCountry, $currency, $amount): bool
    {

        $api = new MollieAPI(MollieAPI::getMode());

        if (!array_key_exists('mollie_possibleMethods', $_SESSION)) {
            $_SESSION['mollie_possibleMethods'] = [];
        }

        $key = md5(serialize([$locale, $billingCountry, $currency, $amount]));
        if (!array_key_exists($key, $_SESSION['mollie_possibleMethods'])) {
            $_SESSION['mollie_possibleMethods'][$key] = $api->getClient()->methods->allActive([
                'locale' => $locale,
                'amount' => [
                    'currency' => $currency,
                    'value' => number_format($amount, 2, ".", "")
                ],
                'billingCountry' => $billingCountry,
                'resource' => 'orders',
                'includeWallets' => 'applepay',
            ]);
        }

        if ($method !== '') {
            foreach ($_SESSION['mollie_possibleMethods'][$key] as $m) {
                if ($m->id === $method) {
                    return true;
                }
            }
        } else {
            return true;
        }

        return false;

    }

    /**
     * @param Bestellung $order
     */
    public function preparePaymentProcess(Bestellung $order): void
    {

        parent::preparePaymentProcess($order);

        try {

            $payable = (float)$order->fGesamtsumme > 0;
            if (!$payable) {
                $this->doLog("Gesamtsumme 0, keine Zahlung notwendig!", LOGLEVEL_NOTICE);
                return;
            }
            if ($this->duringCheckout) {
                $this->doLog("Zahlung vor Bestellabschluss nicht unterstützt!", LOGLEVEL_ERROR);
                return;
            }

            $paymentOptions = [];

            if ((int)Session::getCustomer()->nRegistriert && ($customerID = Customer::createOrUpdate(Session::getCustomer()))) {
                $paymentOptions['customerId'] = $customerID;
            }

            $api = self::Plugin()->getConfig()->getValue($this->moduleID . '_api');

            $paymentOptions = array_merge($paymentOptions, $this->getPaymentOptions($order, $api));

            if ($api === 'payment') {
                $checkout = PaymentCheckout::factory($order);
                $payment = $checkout->create($paymentOptions);
                $url = $payment->getCheckoutUrl();
            } else {
                $checkout = OrderCheckout::factory($order);
                $mOrder = $checkout->create($paymentOptions);
                $url = $mOrder->getCheckoutUrl();
            }

            Shop::Smarty()->assign('redirect', $url);
            if (!headers_sent()) {
                header('Location: ' . $url);
            }


        } catch (Exception $e) {

            $this->doLog('mollie::preparePaymentProcess: ' . $e->getMessage() . ' - ' . print_r(['cBestellNr' => $order->cBestellNr], 1), LOGLEVEL_ERROR);

            Shop::Container()->getAlertService()->addAlert(
                Alert::TYPE_ERROR,
                self::Plugin()->getLocalization()->getTranslation("error_create"),
                'paymentFailed'
            );
        }
    }

    abstract public function getPaymentOptions(Bestellung $order, $apiType): array;

    /**
     * @param Bestellung $order
     * @param string $hash
     * @param array $args
     * @throws CircularReferenceException
     * @throws ServiceNotFoundException
     */
    public function handleNotification(Bestellung $order, string $hash, array $args): void
    {
        parent::handleNotification($order, $hash, $args);

        try {

            $orderId = $args['id'];
            $checkout = null;
            if (strpos($orderId, 'tr_') === 0) {
                $checkout = PaymentCheckout::factory($order);
            } else {
                $checkout = OrderCheckout::factory($order);
            }
            $checkout->handleNotification($hash);

        } catch (Exception $e) {
            $this->doLog("mollie::handleNotification: Bestellung '{$order->cBestellNr}': {$e->getMessage()}", LOGLEVEL_ERROR);
            Shop::Container()->getBackendLogService()->addCritical($e->getMessage(), $_REQUEST);
        }
    }

}
