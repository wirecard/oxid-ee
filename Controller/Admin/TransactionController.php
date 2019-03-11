<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminController;

/**
 * Controls the main transaction view (combines list and tab views).
 */
class TransactionController extends AdminController
{
    /**
     * @inheritdoc
     */
    protected $_sThisTemplate = 'transaction.tpl';
}
