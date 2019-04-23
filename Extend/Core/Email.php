<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Extend\Core;

use OxidEsales\Eshop\Core\Registry;

/**
 * Email extension
 *
 * @mixin \OxidEsales\Eshop\Core\Email
 *
 * @since 1.0.0
 */
class Email extends Email_parent
{
    /**
     * {@inheritdoc }
     * For custom payment method send the email in order language
     *
     * @param \OxidEsales\Eshop\Application\Model\Order $order   Order object
     * @param string                                    $subject user defined subject [optional]
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function sendOrderEmailToUser($order, $subject = null)
    {
        return $this->_sendEmailWithOrderLanguage($order, $subject, array(parent, 'sendOrderEmailToUser'));
    }

    /**
     * {@inheritdoc }
     * For custom payment method send the email in order language
     *
     * @param \OxidEsales\Eshop\Application\Model\Order $order   Order object
     * @param string                                    $subject user defined subject [optional]
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function sendOrderEmailToOwner($order, $subject = null)
    {
        return $this->_sendEmailWithOrderLanguage($order, $subject, array(parent, 'sendOrderEmailToOwner'));
    }

    /**
     * Wrapper for parent methods.
     * If custom payment is used language will be switched to order language
     *
     * @param object   $order
     * @param string   $subject
     * @param callable $function
     *
     * @return bool
     *
     * @since 1.0.0
     */
    private function _sendEmailWithOrderLanguage($order, $subject, $function)
    {
        if (!$order->isCustomPaymentMethod()) {
            return call_user_func($function, $order, $subject);
        }

        $oConfig = Registry::getConfig();
        $bWasAdminMode = $oConfig->isAdmin();
        $oConfig->setAdminMode(false);

        $oLang = Registry::getLang();
        $oOldShop = $this->_getShop();
        $iOldTplLang = $oLang->getTplLanguage();
        $iOldBaseLang = $oLang->getTplLanguage();
        $iOrderLanguage = $order->oxorder__oxlang->value;

        // set new language settings before calling parent method
        $oLang->setTplLanguage($iOrderLanguage);
        $oLang->setBaseLanguage($iOrderLanguage);

        // set shop language if different then order language
        if ($oOldShop->getLanguage() !== $iOrderLanguage) {
            $this->_oShop = $this->_getShop($iOrderLanguage);
        }

        // send emails
        $iReturn = call_user_func($function, $order, $subject);

        // reset language settings to the initial state
        $oLang->setTplLanguage($iOldTplLang);
        $oLang->setBaseLanguage($iOldBaseLang);
        $this->_oShop = $oOldShop;

        // reset admin mode settings
        if ($bWasAdminMode) {
            $oConfig->setAdminMode(true);
        }

        return $iReturn;
    }
}
