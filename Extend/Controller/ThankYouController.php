<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Extend\Controller;

use OxidEsales\Eshop\Core\Registry;

/**
 * Class ThankYouController
 *
 * @mixin \OxidEsales\Eshop\Application\Controller\ThankYouController
 *
 * @since 1.0.0
 */
class ThankYouController extends ThankYouController_parent
{
    /**
     * Extends the parent init method
     * deletes a wdtoken and updates the order number in the transaction table
     *
     * @since 1.0.0
     */
    public function init()
    {
        Registry::getSession()->deleteVariable("wdtoken");

        parent::init();
    }
}
