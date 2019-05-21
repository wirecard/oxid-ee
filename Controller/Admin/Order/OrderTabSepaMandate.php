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

/**
 * Controls the view for the order SEPA mandate tab.
 *
 * @since 1.0.1
 */
class OrderTabSepaMandate extends OrderTab
{
    /**
     * @inheritdoc
     *
     * @since 1.0.1
     */
    protected $_sThisTemplate = 'tab_sepa_mandate.tpl';

    /**
     * @inheritdoc
     *
     * @return string
     *
     * @since 1.0.1
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
     * @since 1.0.1
     */
    protected function _getData()
    {
        if ($this->oOrder->oxorder__wdoxidee_sepamandate->value) {
            return [$this->oOrder->oxorder__wdoxidee_sepamandate->value];
        }
        return [];
    }
}
