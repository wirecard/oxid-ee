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
    /**
     * @var AccountHolderHelper
     */
    private $_oAccountHolderHelper;

    protected function setUp()
    {
        parent::setUp();
        $this->_oAccountHolderHelper = new AccountHolderHelper();

    }

    /**
     * @throws Exception
     */
    public function testCreateAccountHolderWithAllDataSet()
    {
        $aArgs = [
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

        $oAccountHolder = $this->_oAccountHolderHelper->createAccountHolder($aArgs);
        $aResult = $oAccountHolder->mappedProperties();

        $this->assertCount(7, $aResult);
        $this->assertCount(5, $aResult['address']);
        $this->assertEquals('LastName', $aResult['last-name']);
        $this->assertEquals('FirstName', $aResult['first-name']);
        $this->assertEquals('test@test.com', $aResult['email']);
        $this->assertEquals('02-09-1979', $aResult['date-of-birth']);
        $this->assertEquals('+123456789', $aResult['phone']);
        $this->assertEquals('Street 1', $aResult['address']['street1']);
        $this->assertEquals('City', $aResult['address']['city']);
        $this->assertEquals('CC', $aResult['address']['country']);
        $this->assertEquals('State', $aResult['address']['state']);
        $this->assertEquals('1234', $aResult['address']['postal-code']);
        $this->assertEquals('f', $aResult['gender']);
    }

    public function testCreateAccountHolderWithMandatoryDataSet()
    {
        $aArgs = [
            'firstName' => 'FirstName',
            'lastName' => 'LastName',
            'email' => 'test@test.com',
        ];

        $oAccountHolder = $this->_oAccountHolderHelper->createAccountHolder($aArgs);
        $aResult = $oAccountHolder->mappedProperties();

        $this->assertCount(4, $aResult);
        $this->assertCount(3, $aResult['address']);
        $this->assertEquals('LastName', $aResult['last-name']);
        $this->assertEquals('FirstName', $aResult['first-name']);
        $this->assertEquals('test@test.com', $aResult['email']);
        $this->assertEmpty($aResult['address']['street1']);
        $this->assertEmpty($aResult['address']['city']);
        $this->assertEmpty($aResult['address']['country']);
    }
}
