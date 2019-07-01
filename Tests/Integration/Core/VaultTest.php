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
            ->willReturn("User ID");
        $oUser->method('getSelectedAddressId')
            ->willReturn('Selected Address ID');

        $this->getSession()->setUser($oUser);

        Vault::saveCard($oSuccessResponse, $aCard);

        $aResult = $this->getDb(DatabaseInterface::FETCH_MODE_ASSOC)->getAll(
            "SELECT * FROM " . OxidEeEvents::VAULT_TABLE
        );

        $this->assertArraySubset([
            'USERID' => 'User ID',
            'ADDRESSID' => 'Selected Address ID',
            'TOKEN' => 'Card Token ID',
            'MASKEDPAN' => 'Masked Account Number',
            'EXPIRATIONMONTH' => 9,
            'EXPIRATIONYEAR' => 21,
        ], $aResult[0]);
    }
}
