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

/**
 * Controls the view for a single transaction tab.
 */
class ListTab extends AdminDetailsController
{
    const NOTHING_SELECTED = '-1';

    /**
     * @var string
     */
    protected $sListObjectId;

    /**
     * @inheritdoc
     */
    protected $_sThisTemplate = 'list_tab.tpl';

    /**
     * ListTab constructor.
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
     */
    public function render(): string
    {
        $this->_aViewData += [
            'listData' => $this->_isListObjectIdSet() ? $this->_getListData() : [],
            'controller' => $this->classKey,
        ];

        return parent::render();
    }

    /**
     * Check if $sListObjectId is set
     *
     * @return bool
     */
    protected function _isListObjectIdSet(): bool
    {
        return isset($this->sListObjectId) && $this->sListObjectId !== self::NOTHING_SELECTED;
    }

    /**
     * Returns an array of data used to populate the view.
     *
     * @return array
     */
    protected function _getListData(): array
    {
        return [];
    }
}
