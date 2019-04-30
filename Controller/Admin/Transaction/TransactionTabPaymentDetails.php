<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Controller\Admin\Transaction;

use Wirecard\Oxid\Core\Helper;

/**
 * Controls the view for the payment details transaction tab.
 *
 * @since 1.0.0
 */
class TransactionTabPaymentDetails extends TransactionTab
{
    /**
     * @inheritdoc
     *
     * @return array
     *
     * @since 1.0.0
     */
    protected function _getData(): array
    {
        $aPaymentDetails = $this->oResponseMapper->getPaymentDetails();
        $this->_unsetOrderNumber($aPaymentDetails);
        $this->_setPanelOrderId($aPaymentDetails, $this->oOrder->oxorder__oxid->value);
        $this->_setOrderNumber($aPaymentDetails, $this->oOrder->oxorder__oxordernr->value);

        $aListData = $this->_getListDataFromArray($aPaymentDetails);
        $aListData[] = [
            'title' => Helper::translate('wd_panel_transaction_copy'),
            'value' => $this->oTransaction->getResponseXML(),
            'action' => 'copyToClipboard',
            'action_title' => Helper::translate('wd_copy_xml_text'),
        ];

        return $aListData;
    }

    /**
     * Unsets the order number originally set from the response mapper.
     *
     * @param array $aPaymentDetails
     *
     * @since 1.0.0
     */
    private function _unsetOrderNumber($aPaymentDetails)
    {
        unset($aPaymentDetails['orderNumber']);
    }

    /**
     * Sets the panel order ID property on the payment details array.
     *
     * @param array  $aPaymentDetails
     * @param string $sPanelOrderId
     *
     * @since 1.0.0
     */
    private function _setPanelOrderId($aPaymentDetails, $sPanelOrderId)
    {
        $aPaymentDetails['panel_order_id'] = $sPanelOrderId;
    }

    /**
     * Sets the order number on the payment details array.
     *
     * @param array  $aPaymentDetails
     * @param string $sOrderNumber
     *
     * @since 1.0.0
     */
    private function _setOrderNumber($aPaymentDetails, $sOrderNumber)
    {
        $aPaymentDetails['orderNumber'] = $sOrderNumber;
    }
}
