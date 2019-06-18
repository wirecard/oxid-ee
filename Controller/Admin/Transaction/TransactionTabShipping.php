<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Controller\Admin\Transaction;

/**
 * Controls the view for the shipping transaction tab.
 *
 * @since 1.0.0
 */
class TransactionTabShipping extends TransactionTab
{
    /**
     * @inheritdoc
     *
     * @return array
     *
     * @since 1.0.0
     */
    protected function _getData()
    {
        /**
         * Possible translation keys for the PhraseApp parsing script to pick up:
         *
         * translate('wd_last-name')
         * translate('wd_first-name')
         * translate('wd_email')
         * translate('wd_date-of-birth')
         * translate('wd_phone')
         * translate('wd_merchant-crm-id')
         * translate('wd_gender')
         * translate('wd_social-security-number')
         * translate('wd_shipping-method')
         * translate('wd_street1')
         * translate('wd_street2')
         * translate('wd_city')
         * translate('wd_country')
         * translate('wd_postal-code')
         * translate('wd_house-extension')
         */
        return $this->_getListDataFromArray($this->_oResponseMapper->getShipping());
    }
}
