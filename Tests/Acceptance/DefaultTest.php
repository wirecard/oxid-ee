<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Tests\Acceptance;

/**
 * This test class contains basic acceptance tests.
 *
 * @package Wirecard\Oxid\Tests\Acceptance
 */
class DefaultTest extends BaseAcceptanceTestCase
{
    public function testOpenShop()
    {
        $this->openShop();
        $this->assertTextPresent('The shopping cart is empty');
        $this->assertTextNotPresent('3e917032675ee7fce601ad52144c4704');
    }
}
