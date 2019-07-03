<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Core\OxidEeEvents;
use Wirecard\Oxid\Core\Vault;
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
                    [1, 'User ID 1', 'Address ID 1', 'Token 1', 'Masked****Pan', 2, (int) $oFutureDate->format('y')],
                    [2, 'User ID 1', 'Address ID 1', 'Token 2', 'Masked****Pan', 2, (int) $oPastDate->format('y')],
                    [3, 'User ID 2', 'Address ID 2', 'Token 3', 'Masked****Pan', 2, (int) $oFutureDate->format('y')],
                    [4, 'User ID 2', 'Address ID 2', 'Token 4', 'Masked****Pan', 3, (int) $oFutureDate->format('y')],
                    [5, 'User ID 3', 'Address ID 3', 'Token 5', 'Masked****Pan', 2, (int) $oPastDate->format('y')],
                    [6, 'User ID 4', 'Address ID 4', 'Token 6', 'Masked****Pan', 2, (int) $oFutureDate->format('y')],
                ]
            ],
        ];
    }

    /**
     * @dataProvider saveCardProvider
     */
    public function testSaveCard($sToken, $sAddressId, $sUserId, $aExpected)
    {
        $oSuccessResponse = $this->getMockBuilder(SuccessResponse::class)
            ->disableOriginalConstructor()
            ->getMock();
        $oSuccessResponse->method('getCardTokenId')
            ->willReturn($sToken);
        $oSuccessResponse->method('getMaskedAccountNumber')
            ->willReturn('Masked Account Number');

        $aCard = [
            'expiration-month' => 9,
            'expiration-year' => 21,
        ];

        $oUser = $this->getMockBuilder(\OxidEsales\EshopCommunity\Application\Model\User::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getSelectedAddressId'])
            ->getMock();

        $oUser->method('getId')
            ->willReturn($sUserId);
        $oUser->method('getSelectedAddressId')
            ->willReturn($sAddressId);

        $this->getSession()->setUser($oUser);

        Vault::saveCard($oSuccessResponse, $aCard);

        $aResult = $this->getDb(DatabaseInterface::FETCH_MODE_ASSOC)->getAll(
            "SELECT * FROM " . OxidEeEvents::VAULT_TABLE . " WHERE `USERID` = '{$sUserId}'"
        );

        $this->assertEquals($aExpected, $aResult);
    }

    public function saveCardProvider()
    {
        $oFutureDate = new DateTime();
        $oFutureDate->add(new DateInterval('P1Y'));

        return [
            'new card' => ['New token', 'Selected Address ID', 'User ID save card',
                [
                    [
                        'OXID' => 7,
                        'USERID' => 'User ID save card',
                        'ADDRESSID' => 'Selected Address ID',
                        'TOKEN' => 'New token',
                        'MASKEDPAN' => 'Masked Account Number',
                        'EXPIRATIONMONTH' => 9,
                        'EXPIRATIONYEAR' => 21,
                    ],
                ],
            ],
            'exiting token' => ['Token 6', 'Address ID 4', 'User ID 4',
                [
                    [
                        'OXID' => 6,
                        'USERID' => 'User ID 4',
                        'ADDRESSID' => 'Address ID 4',
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
    public function testGetCards($sUserId, $sAddressId, $aExpected)
    {
        $oUser = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getSelectedAddressId'])
            ->getMock();
        $oUser->method('getId')
            ->willReturn($sUserId);
        $oUser->method('getSelectedAddressId')
            ->willReturn($sAddressId);

        $this->getSession()->setUser($oUser);

        $aCards = Vault::getCards();
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
                'Address ID 1',
                [
                    [
                        'OXID' => 1,
                        'USERID' => 'User ID 1',
                        'ADDRESSID' => 'Address ID 1',
                        'TOKEN' => 'Token 1',
                        'MASKEDPAN' => 'Masked****Pan',
                        'EXPIRATIONMONTH' => 2,
                        'EXPIRATIONYEAR' => $iYear,
                    ],
                ],
            ],
            'User 2 with 2 valid cards' => [
                'User ID 2',
                'Address ID 2',
                [
                    [
                        'OXID' => 3,
                        'USERID' => 'User ID 2',
                        'ADDRESSID' => 'Address ID 2',
                        'TOKEN' => 'Token 3',
                        'MASKEDPAN' => 'Masked****Pan',
                        'EXPIRATIONMONTH' => 2,
                        'EXPIRATIONYEAR' => $iYear,
                    ],
                    [
                        'OXID' => 4,
                        'USERID' => 'User ID 2',
                        'ADDRESSID' => 'Address ID 2',
                        'TOKEN' => 'Token 4',
                        'MASKEDPAN' => 'Masked****Pan',
                        'EXPIRATIONMONTH' => 3,
                        'EXPIRATIONYEAR' => $iYear,
                    ],
                ],
            ],
            'User 3 without valid card' => [
                'User ID 3',
                'Address ID 3',
                [],
            ],
            'User 5 without saved card' => [
                'User ID 5',
                'Address ID 5',
                [],
            ],
        ];
    }

    public function testDeleteCard()
    {
        $oUser = $this->getMockBuilder(\OxidEsales\EshopCommunity\Application\Model\User::class)
            ->disableOriginalConstructor()
            ->getMock();

        $oUser->method('getId')
            ->willReturn('User ID 2');

        $this->getSession()->setUser($oUser);
        Vault::deleteCard(4);
        $aResult = $this->getDb(DatabaseInterface::FETCH_MODE_ASSOC)->getAll(
            "SELECT * FROM " . OxidEeEvents::VAULT_TABLE . " WHERE `OXID` = 4");

        $this->assertEmpty($aResult);

    }
}
