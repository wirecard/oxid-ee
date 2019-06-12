<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Controller\Admin\Order;

use Wirecard\Oxid\Core\Helper;
use Wirecard\Oxid\Model\RatepayInvoicePaymentMethod;

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
        if ($this->_oOrder->isCustomPaymentMethod()) {
            $sOrderPaymentName = $this->_oOrder->oxorder__oxpaymenttype->value;
            if ($sOrderPaymentName === RatepayInvoicePaymentMethod::getName(true)) {
                $oXml = simplexml_load_string($this->_oTransaction->getResponseXML());
                if (isset($oXml->descriptor)) {
                    return [$oXml->descriptor];
                }
            }
        }
        return [];
    }
}
