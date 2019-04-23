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

use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;

/**
 * Controls the view for a single tab in the admin details.
 *
 * @since 1.0.0
 */
class Tab extends AdminDetailsController
{
    const NOTHING_SELECTED = '-1';

    /**
     * @var string
     *
     * @since 1.0.0
     */
    protected $sListObjectId;

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

        $this->sListObjectId = $this->getEditObjectId();
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
        $this->setViewData($this->getViewData() + [
            'data' => $this->_isListObjectIdSet() ? $this->_getData() : [],
            'emptyText' => $this->_isListObjectIdSet() ? Helper::translate('wd_text_no_data_available') : null,
            'controller' => $this->classKey,
        ]);

        return parent::render();
    }

    /**
     * Check if $sListObjectId is set
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected function _isListObjectIdSet()
    {
        return isset($this->sListObjectId) && $this->sListObjectId !== self::NOTHING_SELECTED;
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
