<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Controller\Admin\Order;

use Wirecard\Oxid\Model\RatepayInvoicePaymentMethod;
use Wirecard\Oxid\Core\Helper;

/**
 * Controls the view for the order descriptor tab.
 *
 * @since 1.2.0
 */
class OrderTabDescriptor extends OrderTab
{
    /**
     * @inheritdoc
     *
     * @since 1.2.0
     */
    protected $_sThisTemplate = 'tab_descriptor.tpl';

    /**
     * @inheritdoc
     *
     * @return string
     *
     * @since 1.2.0
     */
    public function render()
    {
        $sTemplate = parent::render();

        Helper::addToViewData($this, [
            'emptyText' => Helper::translate('wd_text_no_data_available'),
        ]);

        return $sTemplate;
    }

    /**
     * @inheritdoc
     *
     * @return array
     *
     * @since 1.2.0
     */
    protected function _getData()
    {
        $sOrderPaymentName = $this->_oOrder->getOrderPayment()->getPaymentMethod()->getName();

        if ($sOrderPaymentName === RatepayInvoicePaymentMethod::getName()) {
            $oTransactionDetails = $this->_oResponseMapper->getDataReadable();
            $iIndex = array_search('descriptor', array_column($oTransactionDetails, 'title'));
            if ($iIndex) {
                return [$oTransactionDetails[$iIndex]['value']];
            }
        }
        return [];
    }
}
