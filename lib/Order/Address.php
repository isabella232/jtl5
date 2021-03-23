<?php


namespace Plugin\ws5_mollie\lib\Order;

/**
 * Class Address
 * @package Mollie\Order
 */
class Address extends \Plugin\ws5_mollie\lib\Payment\Address
{


    /**
     * @var string|null
     */
    public $organizationName;

    /**
     * @var string|null
     */
    public $title;

    /**
     * @var string
     */
    public $givenName;

    /**
     * @var string
     */
    public $familyName;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string|null
     */
    public $phone;

    /**
     * @param \Adresse|\stdClass $adresse
     * @return Address
     */
    public static function factory($adresse): Address
    {
        $address = new self();

        $address->title = trim(($adresse->cAnrede === 'm' ? \Shop::Lang()->get('mr') : \Shop::Lang()->get('mrs')) . ' ' . $adresse->cTitel) ?? null;
        $address->givenName = $adresse->cVorname;
        $address->familyName = $adresse->cNachname;
        $address->email = $adresse->cMail ?? null;
        $address->streetAndNumber = $adresse->cStrasse . ' ' . $adresse->cHausnummer;
        $address->postalCode = $adresse->cPLZ;
        $address->city = $adresse->cOrt;
        $address->country = $adresse->cLand;

        if (
            isset($adresse->cAdressZusatz)
            && trim($adresse->cAdressZusatz) !== ''
        ) {
            $address->streetAdditional = trim($adresse->cAdressZusatz);
        }

        if ($organizationName = trim($adresse->cFirma)) {
            $address->organizationName = $organizationName;
        }

        return $address;

    }


}