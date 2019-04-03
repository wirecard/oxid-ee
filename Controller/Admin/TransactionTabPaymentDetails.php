<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Controller\Admin;

use Wirecard\Oxid\Core\Helper;

/**
 * Controls the view for the payment details transaction tab.
 */
class TransactionTabPaymentDetails extends TransactionTab
{
    /**
     * @inheritdoc
     *
     * @return array
     */
    protected function _getListData(): array
    {
        $aListData = $this->_getListDataFromArray($this->oResponseMapper->getPaymentDetails());
        $aListData[] = [
            'title' => Helper::translate('panel_transaction_copy'),
            'value' => $this->oTransaction->getResponseXML(),
            'action' => 'copyToClipboard',
            'action_title' => Helper::translate('copy_xml_text'),
        ];

        return $aListData;
    }
}
