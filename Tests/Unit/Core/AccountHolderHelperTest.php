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

        $this->assertEquals(count($aResult), 7);
        $this->assertEquals(count($aResult['address']), 5);
        $this->assertEquals($aResult['last-name'], 'LastName');
        $this->assertEquals($aResult['first-name'], 'FirstName');
        $this->assertEquals($aResult['email'], 'test@test.com');
        $this->assertEquals($aResult['date-of-birth'], '02-09-1979');
        $this->assertEquals($aResult['phone'], '+123456789');
        $this->assertEquals($aResult['address']['street1'], 'Street 1');
        $this->assertEquals($aResult['address']['city'], 'City');
        $this->assertEquals($aResult['address']['country'], 'CC');
        $this->assertEquals($aResult['address']['state'], 'State');
        $this->assertEquals($aResult['address']['postal-code'], '1234');
        $this->assertEquals($aResult['gender'], 'f');
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

        $this->assertEquals(count($aResult), 4);
        $this->assertEquals(count($aResult['address']), 3);
        $this->assertEquals($aResult['last-name'], 'LastName');
        $this->assertEquals($aResult['first-name'], 'FirstName');
        $this->assertEquals($aResult['email'], 'test@test.com');
        $this->assertEmpty($aResult['address']['street1']);
        $this->assertEmpty($aResult['address']['city']);
        $this->assertEmpty($aResult['address']['country']);
    }
}
