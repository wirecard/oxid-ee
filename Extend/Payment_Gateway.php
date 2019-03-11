<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

namespace Wirecard\Oxid\Extend;

use \Wirecard\Oxid\Core\Payment_Method_Factory;
use \Wirecard\PaymentSdk\Entity\Amount;
use \Wirecard\PaymentSdk\TransactionService;
use \Wirecard\PaymentSdk\Response\FailureResponse;
use \Wirecard\PaymentSdk\Response\InteractionResponse;
use \Wirecard\PaymentSdk\Entity\Redirect;

use \OxidEsales\Eshop\Core\Registry;

/**
 * Class BasePaymentGateway
 *
 * Base class for all payment methods
 *
 * @mixin  \OxidEsales\Eshop\Application\Model\PaymentGateway
 *
 */
class Payment_Gateway extends Payment_Gateway_parent
{
    const NAME = 'wdpaypal';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $oLogger;

    /**
     * @var \OxidEsales\Eshop\Core\Language
     */
    private $oLang;

    /**
     * BasePaymentGateway constructor.
     */
    public function __construct()
    {
        $this->oLogger = Registry::getLogger();
        $this->oLang = Registry::getLang();
    }

    /**
     * Executes payment, returns true on success.
     *
     * @param double $dAmount Goods amount
     * @param \Wirecard\Oxid\Extend\Order $oOrder User ordering object
     *
     * @return bool
     *
     * @override
     */
    public function executePayment($dAmount, &$oOrder)
    {
        if (!$oOrder->isWirecardPaymentType()) {
            return parent::executePayment($dAmount, $oOrder);
        }

        $oResponse = null;

        try {
            $oResponse = self::makeTransaction($dAmount, $oOrder);
        } catch (\Exception $exc) {
            $this->oLogger->error("Error processing transaction", [$exc]);
            return false;
        }

        if ($oResponse instanceof FailureResponse) {
            $this->oLogger->error('Error processing transaction:');

            foreach ($oResponse->getStatusCollection() as $oStatus) {
                /**
                 * @var $oStatus \Wirecard\PaymentSdk\Entity\Status
                 */
                $sSeverity = ucfirst($oStatus->getSeverity());
                $sCode = $oStatus->getCode();
                $sDescription = $oStatus->getDescription();
                $this->oLogger->error("\t$sSeverity with code $sCode and message '$sDescription' occurred.");
            }
            return false;
        }
        $sPageUrl = null;
        if ($oResponse instanceof InteractionResponse) {
            $sPageUrl = $oResponse->getRedirectUrl();
        }
        Registry::getUtils()->redirect($sPageUrl);
        return true;
    }

    /**
     * Returns country code
     *
     * @param string $countryId
     *
     * @return string
     *
     */
    public function getCountryCode($countryId)
    {
        $country = oxNew(\OxidEsales\Eshop\Application\Model\Country::class);
        $country->load($countryId);
        return $country->oxcountry__oxisoalpha2->value;
    }

    /**
     * Returns a descriptor
     *
     * @param string $countryId
     *
     * @return string
     *
     */
    public function getDescriptor($orderId)
    {
        $shopId = \OxidEsales\Eshop\Core\Registry::getConfig()->getShopId();
        $shop = oxNew(\OxidEsales\Eshop\Application\Model\Shop::class);
        $shop->load($shopId);
        return $shop->oxshops__oxname->value . " " . $orderId;
    }

    /**
     * Returns a redirect object
     *
     * @param string $oSession
     *
     * @param string $sShopUrl
     *
     * @return string
     *
     */
    public function getRedirectUrls($oSession, $sShopUrl)
    {
        $sSid = $oSession->sid(true);
        if ($sSid != '') {
            $sSid = '&' . $sSid;
        }

        $sErrorText = $this->oLang->translateString('order_error');
        $oRedirect = new Redirect(
            $sShopUrl . 'index.php?cl=thankyou' . $sSid,
            $sShopUrl . 'index.php?type=cancel&cl=payment',
            $sShopUrl . 'index.php?type=error&cl=payment&errortext=' . urlencode($sErrorText)
        );
        return $oRedirect;
    }

    /**
     * Returns a redirect object
     *
     * @param string $dAmount
     *
     * @param string $oOrder
     *
     * @return object
     *
     */
    public function makeTransaction($dAmount, $oOrder)
    {
        $sShopUrl = $this->getConfig()->getCurrentShopUrl();
        $oSession = $this->getSession();

        $oRedirect = self::getRedirectUrls($oSession, $sShopUrl);
        $oPaymentMethod = Payment_Method_Factory::create(self::NAME);
        $oTransactionService = new TransactionService($oPaymentMethod->getConfig(), $this->oLogger);

        $oTransaction = $oPaymentMethod->getTransaction();
        $oTransaction->setRedirect($oRedirect);

        $config = \OxidEsales\Eshop\Core\Registry::getConfig();
        $currency = $config->getActShopCurrencyObject();
        $oTransaction->setAmount(new Amount($dAmount, $currency->name));

        $basket = $oSession->getBasket();
        $user = $basket->getBasketUser();

        $oTransaction->setOrderDetail(sprintf(
            '%s %s %s',
            $oOrder->oxorder__oxbillemail->value,
            $user->oxuser__oxfname->value,
            $user->oxuser__oxlname->value
        ));

        $payment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
        $payment->load(self::NAME);

        if ($payment->oxpayments__wdoxidee_additional_info->value) {
            $oViewConf = oxNew('oxViewConfig');
            $ip = $oViewConf->getRemoteAddress();
            $oTransaction->setIpAddress($ip);
            $oTransaction->setConsumerId($user->oxuser__oxid->value);
            $oTransaction->setOrderNumber($oOrder->oxorder__oxid->value);
        }

        if ($payment->oxpayments__wdoxidee_descriptor->value) {
            $descriptor = self::getDescriptor($oOrder->oxorder__oxid->value);
            $oTransaction->setDescriptor($descriptor);
        }

        $address = new \Wirecard\PaymentSdk\Entity\Address(
            self::getCountryCode($user->oxuser__oxcountryid),
            $user->oxuser__oxcity->value,
            $user->oxuser__oxstreet->value
        );
        $address->setPostalCode($user->oxuser__oxzip->value);

        $accountHolder = new \Wirecard\PaymentSdk\Entity\AccountHolder();
        $accountHolder->setAddress($address);
        $accountHolder->setFirstName($user->oxuser__oxfname->value);
        $accountHolder->setLastName($user->oxuser__oxlname->value);
        $accountHolder->setPhone($user->oxuser__oxfon->value);
        $accountHolder->setEmail($oOrder->oxorder__oxbillemail->value);

        $oTransaction->setAccountHolder($accountHolder);

        if ($oOrder->oxorder__oxdelfname->value) { //shipping address exists
            $addressShipping = new \Wirecard\PaymentSdk\Entity\Address(
                self::getCountryCode($oOrder->oxorder__oxdelcountryid),
                $oOrder->oxorder__oxdelcity->value,
                $oOrder->oxorder__oxdelstreet->value
            );
            $addressShipping->setPostalCode($oOrder->oxorder__oxdelzip->value);
            $accountHolderShipping = new \Wirecard\PaymentSdk\Entity\AccountHolder();
            $accountHolderShipping->setAddress($addressShipping);
            $accountHolderShipping->setFirstName($oOrder->oxorder__oxdelfname->value);
            $accountHolderShipping->setLastName($oOrder->oxorder__oxdellname->value);
            $accountHolderShipping->setPhone($oOrder->oxorder__oxdelfon->value);
            $oTransaction->setShipping($accountHolderShipping);
        } else {
            $oTransaction->setShipping($accountHolder);
        }

        if ($payment->oxpayments__wdoxidee_basket->value) { //add basket data
            $finalPrice = array();
            $contents = $basket->getContents();
            foreach ($contents as $content) {
                $finalPrice[$content->getProductId()] = $content->getFUnitPrice();
            }

            $wdBasket = new \Wirecard\PaymentSdk\Entity\Basket();
            $articles = $basket->getBasketSummary()->aArticles;
            foreach ($articles as $key => $value) {
                $oArticle = oxNew(\OxidEsales\Eshop\Application\Model\Article::class);
                $oArticle->load($key);
                $item = new \Wirecard\PaymentSdk\Entity\Item(
                    $oArticle->oxarticles__oxtitle->value,
                    new Amount($finalPrice[$key], $currency->name),
                    $value
                );
                $wdBasket->add($item);
            }
            $oTransaction->setBasket($wdBasket);
        }
        $oTransaction->setNotificationUrl($sShopUrl . 'notify.php');
        $oResponse = $oTransactionService->pay($oTransaction);
        return $oResponse;
    }
}
