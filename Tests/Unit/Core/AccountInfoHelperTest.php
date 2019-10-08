<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Core\AccountInfoHelper;

class AccountInfoHelperTest extends OxidEsales\TestingLibrary\UnitTestCase
{

    public function testCreateGuest()
    {
        $oAccountInfo = AccountInfoHelper::create(false, '01', null, false);

        $this->assertEquals(
            [
                'authentication-method' => '01',
                'challenge-indicator'   => '01',
            ], $oAccountInfo->mappedProperties()
        );
    }

    public function testCreateWithLoggedInUser()
    {
        $oAccountInfo = AccountInfoHelper::create(true, '01', false);

        $this->assertEquals(
            [
                'authentication-method' => '02',
                'challenge-indicator'   => '01',
            ], $oAccountInfo->mappedProperties()
        );
    }

    public function testCreateNewToken()
    {
        $oAccountInfo = AccountInfoHelper::create(true, '01', true);

        $this->assertEquals(
            [
                'authentication-method' => '02',
            ], $oAccountInfo->mappedProperties()
        );
    }

    public function testAddAuthenticatedUserDataNotLoggedIn()
    {
        $oAccountInfo = AccountInfoHelper::create(false, '01', false);

        AccountInfoHelper::addAuthenticatedUserData($oAccountInfo, false, null, null, null);
        $this->assertEquals(
            [
                'authentication-method' => '01',
                'challenge-indicator'   => '01'
            ], $oAccountInfo->mappedProperties()
        );
    }

    public function testAddAuthenticatedUserData()
    {
        $oAccountInfo = AccountInfoHelper::create(false, '01', false);

        $oCardCreationDate = new DateTime;
        AccountInfoHelper::addAuthenticatedUserData($oAccountInfo, true, null, null, $oCardCreationDate);
        $this->assertEquals(
            [
                'authentication-method' => '01',
                'challenge-indicator'   => '01',
                'card-creation-date'    => $oCardCreationDate->format('Y-m-d')
            ], $oAccountInfo->mappedProperties()
        );
    }

    public function testAddAuthenticatedUserDataFull()
    {
        $oAccountInfo = AccountInfoHelper::create(false, '01', false);

        $oDate = new DateTime;
        AccountInfoHelper::addAuthenticatedUserData($oAccountInfo, true, '2019-10-05', $oDate, $oDate);
        $this->assertEquals(
            [
                'authentication-method'      => '01',
                'challenge-indicator'        => '01',
                'card-creation-date'         => $oDate->format('Y-m-d'),
                'creation-date'              => '2019-10-05',
                'shipping-address-first-use' => $oDate->format('Y-m-d'),
            ], $oAccountInfo->mappedProperties()
        );
    }
}
