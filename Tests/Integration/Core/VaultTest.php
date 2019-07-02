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
                ]
            ],
        ];
    }

    public function testSaveCard()
    {
        $oSuccessResponse = $this->getMockBuilder(SuccessResponse::class)
            ->disableOriginalConstructor()
            ->getMock();
        $oSuccessResponse->method('getCardTokenId')
            ->willReturn("Card Token ID");
        $oSuccessResponse->method('getMaskedAccountNumber')
            ->willReturn('Masked Account Number');

        $aCard = [
            'expiration-month' => 9,
            'expiration-year' => 21,
        ];

        $oUser = $this->getMockBuilder(\OxidEsales\EshopCommunity\Application\Model\User::class)
            ->disableOriginalConstructor()
            ->getMock();

        $oUser->method('getId')
            ->willReturn("User ID save card");
        $oUser->method('getSelectedAddressId')
            ->willReturn('Selected Address ID');

        $this->getSession()->setUser($oUser);

        Vault::saveCard($oSuccessResponse, $aCard);

        $aResult = $this->getDb(DatabaseInterface::FETCH_MODE_ASSOC)->getAll(
            "SELECT * FROM " . OxidEeEvents::VAULT_TABLE . " WHERE `USERID` = 'User ID save card'"
        );

        $this->assertEquals([[
            'OXID' => 6,
            'USERID' => 'User ID save card',
            'ADDRESSID' => 'Selected Address ID',
            'TOKEN' => 'Card Token ID',
            'MASKEDPAN' => 'Masked Account Number',
            'EXPIRATIONMONTH' => 9,
            'EXPIRATIONYEAR' => 21,
        ]], $aResult);
    }

    /**
     *
     * @dataProvider getCardsProvider
     */
    public function testGetCards($sUserId, $sAddressId, $aExpected)
    {
        $aCards = Vault::getCards($sUserId, $sAddressId);
        $this->assertEquals($aExpected, $aCards);
    }

    public function getCardsProvider()
    {
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
                        'EXPIRATIONYEAR' => 20,
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
                        'EXPIRATIONYEAR' => 20,
                    ],
                    [
                        'OXID' => 4,
                        'USERID' => 'User ID 2',
                        'ADDRESSID' => 'Address ID 2',
                        'TOKEN' => 'Token 4',
                        'MASKEDPAN' => 'Masked****Pan',
                        'EXPIRATIONMONTH' => 3,
                        'EXPIRATIONYEAR' => 20,
                    ],
                ],
            ],
            'User 3 without valid card' => [
                'User ID 3',
                'Address ID 3',
                [],
            ],
            'User 4 without saved card' => [
                'User ID 4',
                'Address ID 4',
                [],
            ],
        ];
    }
}
