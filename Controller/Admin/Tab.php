<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;
use OxidEsales\Eshop\Application\Model\Order;

use Wirecard\Oxid\Core\Helper;
use Wirecard\Oxid\Model\Transaction;

/**
 * Controls the view for a single tab in the admin details.
 *
 * @since 1.0.0
 */
class Tab extends AdminDetailsController
{
    const NOTHING_SELECTED = '-1';

    /**
     * @var Transaction
     *
     * @since 1.1.0
     */
    protected $_oTransaction;

    /**
     * @var Order
     *
     * @since 1.1.0
     */
    protected $_oOrder;

    /**
     * @var string
     *
     * @since 1.0.0
     */
    protected $_sListObjectId;

    /**
     * @inheritdoc
     *
     * @since 1.0.0
     */
    protected $_sThisTemplate = 'tab_simple.tpl';

    /**
     * ListTab constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        parent::__construct();

        $this->_oOrder = oxNew(Order::class);
        $this->_oTransaction = oxNew(Transaction::class);
        $this->_sListObjectId = $this->getEditObjectId();
    }

    /**
     * @inheritdoc
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function render()
    {
        $sTemplate = parent::render();

        Helper::addToViewData($this, [
            'data' => $this->_isListObjectIdSet() ? $this->_getData() : [],
            'emptyText' => $this->_isListObjectIdSet() ? Helper::translate('wd_text_no_data_available') : null,
            'controller' => $this->classKey,
        ]);

        return $sTemplate;
    }

    /**
     * Determines whether the live chat should be displayed in the tab.
     *
     * @return boolean
     *
     * @since 1.1.0
     */
    public function shouldDisplayLiveChat()
    {
        return $this->_oOrder->isCustomPaymentMethod();
    }

    /**
     * Check if $_sListObjectId is set
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function _isListObjectIdSet()
    {
        return isset($this->_sListObjectId) && $this->_sListObjectId !== self::NOTHING_SELECTED;
    }

    /**
     * Returns an array of arbitrary data used to populate the view.
     *
     * @return array
     *
     * @since 1.0.0
     */
    protected function _getData()
    {
        return [];
    }
}
