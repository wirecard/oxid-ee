<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;

use Wirecard\Oxid\Core\OrderHelper;
use Wirecard\Oxid\Tests\resources\OrderHelperData;

class OrderHelperTest extends \Wirecard\Test\WdUnitTestCase
{
    protected function dbData()
    {
        return OrderHelperData::DB_DATA;
    }

    /**
     * @dataProvider getLastOrderShippingAddressProvider
     */
    public function testGetLastOrderShippingAddress($sUserId, $aExpected)
    {
        $this->assertEquals($aExpected, OrderHelper::getLastOrderShippingAddress($sUserId));
    }

    public function getLastOrderShippingAddressProvider()
    {
        return [
            'unknown user' => [
                'invalid user',
                null
            ],
            'order with delivery country set' => [
                'User ID 1',
                OrderHelperData::TEST_USER_1,
            ],
            'order without delivery country set' => [
                'User ID 2',
                OrderHelperData::TEST_USER_2,
            ],
        ];
    }

    /**
     * @dataProvider getSelectedShippingAddressProvider
     */
    public function testGetSelectedShippingAddress($sUserId, $aExpected)
    {
        $oUser = oxNew(User::class);

        if ($sUserId !== null) {
            $oUser->load($sUserId);
            Registry::getSession()->setUser($oUser);
        } else {
            Registry::getSession()->setVariable('deladrid', '1');
        }

        $this->assertEquals($aExpected, OrderHelper::getSelectedShippingAddress());
    }

    public function getSelectedShippingAddressProvider()
    {
        return [
            'invalid user' => [
                null,
                OrderHelperData::TEST_USER_3,
            ],
            'valid user' => [
                'User ID 1',
                OrderHelperData::TEST_USER_4,
            ],
        ];
    }
}
