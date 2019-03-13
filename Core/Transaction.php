<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

 namespace Wirecard\Oxid\Core;

/**
 * Transaction manager
 *
 */
class Transaction extends \OxidEsales\Eshop\Core\Model\MultiLanguageModel
{
    protected $_sClassName = 'Transaction';

    /**
     * Transaction constructor
     */
    public function __construct()
    {
        parent::__construct();
        // allow Oxid's magic getters for database table
        $this->init('wdoxidee_ordertransactions');
    }
}
