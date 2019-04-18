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
use Wirecard\Oxid\Model\Transaction;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\TableViewNameGenerator;
use OxidEsales\Eshop\Application\Controller\Admin\AdminListController;

/**
 * Controls the transaction list view.
 */
class TransactionList extends AdminListController
{
    /**
     * @inheritdoc
     */
    protected $_sListClass = Transaction::class;

    /**
     * @inheritdoc
     */
    protected $_sThisTemplate = 'transaction_list.tpl';

    /**
     * @inheritdoc
     */
    protected $_sDefSortField = 'oxid';

    /**
     * @inheritdoc
     *
     * @return string
     */
    public function render(): string
    {
        $this->setViewData($this->getViewData() + [
            'payments' => Helper::getModulePayments(),
            'actions' => Transaction::getActions(),
            'states' => Transaction::getStates(),
        ]);

        return parent::render();
    }

    /**
     * @inheritdoc
     *
     * @param object $oListObject
     * @return string
     */
    protected function _buildSelectString($oListObject = null): string
    {
        $sQuery = parent::_buildSelectString($oListObject);

        $oViewNameGenerator = Registry::get(TableViewNameGenerator::class);
        $sLocalizedViewName = $oViewNameGenerator->getViewName('oxpayments');

        // str_replace is used to add the JOIN statement to the initial SQL query and consider the localized view.
        // The view needs to display data from OXID's order table, which is fetched through the JOIN statement.
        // Because OXID uses different views per language, it is necessary to dynamically alter the base query
        // at this point.
        $sQuery = str_replace(
            'from wdoxidee_ordertransactions',
            ", `{$sLocalizedViewName}`.`oxdesc` as `paymentname` from `wdoxidee_ordertransactions` LEFT JOIN `oxorder`
                ON `wdoxidee_ordertransactions`.`orderid` = `oxorder`.`oxid` LEFT JOIN `{$sLocalizedViewName}`
                ON `oxorder`.`oxpaymenttype` = `{$sLocalizedViewName}`.`oxid` ",
            $sQuery
        );

        return $sQuery;
    }
}
