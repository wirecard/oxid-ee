<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use OxidEsales\EshopCommunity\Application\Model\User;
use OxidEsales\Eshop\Core\Field;

use Wirecard\Oxid\Core\OxidEeEvents;
use Wirecard\Oxid\Core\Vault;
use Wirecard\Oxid\Extend\Model\Order;
use Wirecard\PaymentSdk\Response\SuccessResponse;

class VaultTest extends \Wirecard\Test\WdUnitTestCase
{
    protected function dbData()
    {
        $oFutureDate = new DateTime();
        $oFutureDate->add(new DateInterval('P1Y'));

        $oPastDate = new DateTime();
        $oPastDate->sub(new DateInterval('P1Y'));

        return [
            [
                'table' => OxidEeEvents::VAULT_TABLE,
                'columns' => ['OXID', 'USERID', 'ADDRESSID', 'TOKEN', 'MASKEDPAN', 'EXPIRATIONMONTH', 'EXPIRATIONYEAR'],
                'rows' => [
                    [1, 'User ID 1', 'caf0983c2368ab82adebd542e99f2c8f363a0e56', 'Token 1', 'Masked****Pan', 2, (int) $oFutureDate->format('y')],
                    [2, 'User ID 1', 'caf0983c2368ab82adebd542e99f2c8f363a0e56', 'Token 2', 'Masked****Pan', 2, (int) $oPastDate->format('y')],
                    [3, 'User ID 2', 'caf0983c2368ab82adebd542e99f2c8f363a0e56', 'Token 3', 'Masked****Pan', 2, (int) $oFutureDate->format('y')],
                    [4, 'User ID 2', 'caf0983c2368ab82adebd542e99f2c8f363a0e56', 'Token 4', 'Masked****Pan', 3, (int) $oFutureDate->format('y')],
                    [5, 'User ID 3', 'caf0983c2368ab82adebd542e99f2c8f363a0e56', 'Token 5', 'Masked****Pan', 2, (int) $oPastDate->format('y')],
                    [6, 'User ID 4', 'caf0983c2368ab82adebd542e99f2c8f363a0e56', 'Token 6', 'Masked****Pan', 2, (int) $oFutureDate->format('y')],
                ]
            ],
        ];
    }

    /**
     * @dataProvider saveCardProvider
     */
    public function testSaveCard($sToken, $sUserId, $aExpected)
    {
        $oSuccessResponse = $this->getMockBuilder(SuccessResponse::class)
            ->disableOriginalConstructor()
            ->getMock();
        $oSuccessResponse->method('getCardTokenId')
            ->willReturn($sToken);
        $oSuccessResponse->method('getMaskedAccountNumber')
            ->willReturn('Masked Account Number');

        $aCard = [
            'expiration-month' => '9',
            'expiration-year' => '21',
        ];

        $oUser = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', '__get'])
            ->getMock();

        $oUser->method('getId')
            ->willReturn($sUserId);
        $oUser->method('__get')
            ->with('oxuser__oxid')
            ->willReturn(new Field($sUserId));

        $oOrder = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $oOrder->method('getShippingAccountHolder')
            ->willReturn(\Wirecard\Oxid\Core\AccountHolderHelper::createAccountHolder(
                [
                    'firstName' => 'First Name',
                    'lastName' => 'Last Name',
                    'countryCode' => 'AT',
                    'street' => 'Street 1',
                    'city' => 'Graz',
                ]
            ));
        $oOrder->method('getOrderUser')
            ->willReturn($oUser);

        Vault::saveCard($oSuccessResponse, $aCard, $oOrder);

        $aResult = $this->_getCardsForUser($sUserId);

        $this->assertEquals($aExpected, $aResult);
    }

    private function _getCardsForUser($sUserId)
    {
        return $this->getDb(DatabaseInterface::FETCH_MODE_ASSOC)->getAll(
            "SELECT * FROM " . OxidEeEvents::VAULT_TABLE . " WHERE `USERID` = '{$sUserId}'"
        );
    }

    public function saveCardProvider()
    {
        $oFutureDate = new DateTime();
        $oFutureDate->add(new DateInterval('P1Y'));

        return [
            'new card' => ['New token', 'User ID save card',
                [
                    [
                        'OXID' => '7',
                        'USERID' => 'User ID save card',
                        'ADDRESSID' => 'caf0983c2368ab82adebd542e99f2c8f363a0e56',
                        'TOKEN' => 'New token',
                        'MASKEDPAN' => 'Masked Account Number',
                        'EXPIRATIONMONTH' => 9,
                        'EXPIRATIONYEAR' => 21,
                    ],
                ],
            ],
            'existing token' => ['Token 6', 'User ID 4',
                [
                    [
                        'OXID' => '6',
                        'USERID' => 'User ID 4',
                        'ADDRESSID' => 'caf0983c2368ab82adebd542e99f2c8f363a0e56',
                        'TOKEN' => 'Token 6',
                        'MASKEDPAN' => 'Masked****Pan',
                        'EXPIRATIONMONTH' => 2,
                        'EXPIRATIONYEAR' => (int) $oFutureDate->format('y'),
                    ],
                ],
            ],
        ];
    }

    /**
     *
     * @dataProvider getCardsProvider
     */
    public function testGetCards($sUserId, $aExpected)
    {
        $oUser = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', '__get'])
            ->getMock();

        $oUser->method('getId')
            ->willReturn($sUserId);
        $oUser->method('__get')
            ->with('oxuser__oxid')
            ->willReturn(new Field($sUserId));

        $oOrder = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $oOrder->method('getShippingAccountHolder')
            ->willReturn(\Wirecard\Oxid\Core\AccountHolderHelper::createAccountHolder(
                [
                    'firstName' => 'First Name',
                    'lastName' => 'Last Name',
                    'countryCode' => 'AT',
                    'street' => 'Street 1',
                    'city' => 'Graz',
                ]
            ));
        $oOrder->method('getOrderUser')
            ->willReturn($oUser);

        $aCards = Vault::getCards($oOrder);
        $this->assertEquals($aExpected, $aCards);
    }

    public function getCardsProvider()
    {
        $oFutureDate = new DateTime();
        $oFutureDate->add(new DateInterval('P1Y'));
        $iYear = (int) $oFutureDate->format('y');
        return [
            'User 1 only one valid card' => [
                'User ID 1',
                [
                    '1' => [
                        'OXID' => 1,
                        'USERID' => 'User ID 1',
                        'ADDRESSID' => 'caf0983c2368ab82adebd542e99f2c8f363a0e56',
                        'TOKEN' => 'Token 1',
                        'MASKEDPAN' => 'Masked****Pan',
                        'EXPIRATIONMONTH' => 2,
                        'EXPIRATIONYEAR' => $iYear,
                    ],
                ],
            ],
            'User 2 with 2 valid cards' => [
                'User ID 2',
                [
                    [
                        'OXID' => 4,
                        'USERID' => 'User ID 2',
                        'ADDRESSID' => 'caf0983c2368ab82adebd542e99f2c8f363a0e56',
                        'TOKEN' => 'Token 4',
                        'MASKEDPAN' => 'Masked****Pan',
                        'EXPIRATIONMONTH' => 3,
                        'EXPIRATIONYEAR' => $iYear,
                    ],
                    [
                        'OXID' => 3,
                        'USERID' => 'User ID 2',
                        'ADDRESSID' => 'caf0983c2368ab82adebd542e99f2c8f363a0e56',
                        'TOKEN' => 'Token 3',
                        'MASKEDPAN' => 'Masked****Pan',
                        'EXPIRATIONMONTH' => 2,
                        'EXPIRATIONYEAR' => $iYear,
                    ],
                ],
            ],
            'User 3 without valid card' => [
                'User ID 3',
                [],
            ],
            'User 5 without saved card' => [
                'User ID 5',
                [],
            ],
        ];
    }

    public function testDeleteCard()
    {
        $sUserId = 'User ID 2';
        $iVaultId = 4;
        Vault::deleteCard($sUserId, $iVaultId);
        $aResult = $this->_getCardsWithId($iVaultId);

        $this->assertEmpty($aResult);
    }

    private function _getCardsWithId($iOxid)
    {
        return $this->getDb(DatabaseInterface::FETCH_MODE_ASSOC)->getAll(
            "SELECT * FROM " . OxidEeEvents::VAULT_TABLE . " WHERE `OXID` = {$iOxid}");
    }
}
