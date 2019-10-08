<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Core\AccountHolderHelper;

class AccountHolderHelperTest extends OxidEsales\TestingLibrary\UnitTestCase
{
    public function testCreateAccountHolderWithAllDataSet()
    {
        $aArgs = [
            'crmId' => '1234',
            'firstName' => 'FirstName',
            'lastName' => 'LastName',
            'email' => 'test@test.com',
            'phone' => '+123456789',
            'gender' => 'f',
            'dateOfBirth' => new DateTime('1979-09-02'),
            'countryCode' => 'CC',
            'city' => 'City',
            'street' => 'Street 1',
            'postalCode' => '1234',
            'state' => 'State'
        ];

        $oAccountHolder = AccountHolderHelper::createAccountHolder($aArgs);
        $aResult = $oAccountHolder->mappedProperties();

        $aExpected = [
            'last-name' => 'LastName',
            'first-name' => 'FirstName',
            'email' => 'test@test.com',
            'date-of-birth' => '02-09-1979',
            'phone' => '+123456789',
            'address' => [
                'street1' => 'Street 1',
                'city' => 'City',
                'country' => 'CC',
                'state' => 'State',
                'postal-code' => '1234',
            ],
            'gender' => 'f',
            'merchant-crm-id' => '1234'
        ];

        $this->assertEquals($aExpected, $aResult);
    }

    public function testCreateAccountHolderWithMandatoryDataSet()
    {
        $aArgs = [
            'firstName' => 'FirstName',
            'lastName' => 'LastName',
            'email' => 'test@test.com',
        ];

        $oAccountHolder = AccountHolderHelper::createAccountHolder($aArgs);
        $aResult = $oAccountHolder->mappedProperties();

        $aExpected = [
            'last-name' => 'LastName',
            'first-name' => 'FirstName',
            'email' => 'test@test.com',
            'address' => [
                'street1' => '',
                'city' => '',
                'country' => '',
            ],
        ];

        $this->assertEquals($aExpected, $aResult);
    }
}
