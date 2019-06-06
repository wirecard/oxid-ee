<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 *
 */

use Wirecard\Oxid\Core\SessionHelper;

class SessionHelperTest extends OxidEsales\TestingLibrary\UnitTestCase
{
    public function testGetAccountHolder()
    {
        $this->setSessionParam('dynvalue', ['accountHolder' => 'foo']);

        $this->assertEquals('foo', SessionHelper::getAccountHolder());
    }

    public function testGetIban()
    {
        $this->setSessionParam('dynvalue', ['iban' => 'foo']);

        $this->assertEquals('foo', SessionHelper::getIban());
    }

    public function testGetBic()
    {
        $this->setSessionParam('dynvalue', ['bic' => 'foo']);

        $this->assertEquals('foo', SessionHelper::getBic());
    }

    /**
     * @dataProvider getDbDateOfBirthProvider
     */
    public function testGetDbDateOfBirth($sExpected, $sInput)
    {
        $this->setSessionParam('dynvalue', ['dateOfBirth' => $sInput]);

        $this->assertEquals($sExpected, SessionHelper::getDbDateOfBirth());
    }

    public function getDbDateOfBirthProvider()
    {
        return [
            'valid date of birth' => ['2000-01-10', '10.01.2000'],
            'malformatted date of birth' => ['0000-00-00', '2000/01/10'],
            'invalid date of birth' => ['0000-00-00', ''],
        ];
    }

    /**
     * @dataProvider setDbDateOfBirthProvider
     */
    public function testSetDbDateOfBirth($sExpected, $sInput)
    {
        SessionHelper::setDbDateOfBirth($sInput);

        $this->assertEquals($sExpected, $this->getSessionParam('dynvalue')['dateOfBirth']);
    }

    public function setDbDateOfBirthProvider()
    {
        return [
            'valid date of birth' => ['10.01.2000', '2000-01-10'],
            'malformatted date of birth' => ['', '0000-00-00'],
            'invalid date of birth' => ['', '0000-00-00'],
        ];
    }

    /**
     * @dataProvider testIsDateOfBirthSetProvider
     */
    public function testIsDateOfBirthSet($blExpected, $sInput)
    {
        SessionHelper::setDbDateOfBirth($sInput);

        $this->assertEquals($blExpected, SessionHelper::isDateOfBirthSet());
    }

    public function testIsDateOfBirthSetProvider()
    {
        return [
            'valid date of birth' => [true, '2000-01-10'],
            'malformatted date of birth' => [false, '0000-00-00'],
            'invalid date of birth' => [false, '0000-00-00'],
        ];
    }

    /**
     * @dataProvider isUserOlderThanProvider
     */
    public function testIsUserOlderThan($blExpected, $sInput, $iAge)
    {
        $this->setSessionParam('dynvalue', ['dateOfBirth' => $sInput]);

        $this->assertEquals($blExpected, SessionHelper::isUserOlderThan($iAge));
    }

    public function isUserOlderThanProvider()
    {
        return [
            'older than 18' => [true, date('d.m.Y', strtotime('-20 years')), 18],
            'younger than 18' => [false, date('d.m.Y', strtotime('-10 years')), 18],
            'invalid date of birth' => [false, '', 18],
        ];
    }

    public function testGetPhone()
    {
        $this->setSessionParam('dynvalue', ['phone' => 'foo']);

        $this->assertEquals('foo', SessionHelper::getPhone());
    }

    public function testSetPhone()
    {
        SessionHelper::setPhone('foo');

        $this->assertEquals('foo', $this->getSessionParam('dynvalue')['phone']);
    }

    /**
     * @dataProvider isPhoneValidProvider
     */
    public function testIsPhoneValid($blExpected, $sInput)
    {
        $this->setSessionParam('dynvalue', ['phone' => $sInput]);

        $this->assertEquals($blExpected, SessionHelper::isPhoneValid());
    }

    public function isPhoneValidProvider()
    {
        return [
            'valid phone number' => [true, 'foo'],
            'invalid phone number' => [false, ''],
        ];
    }

    public function testGetSaveCheckoutFields()
    {
        $this->setSessionParam('dynvalue', ['saveCheckoutFields' => 'foo']);

        $this->assertEquals('foo', SessionHelper::getSaveCheckoutFields());
    }

    public function testSetSaveCheckoutFields()
    {
        SessionHelper::setSaveCheckoutFields('foo');

        $this->assertEquals('foo', $this->getSessionParam('dynvalue')['saveCheckoutFields']);
    }
}
