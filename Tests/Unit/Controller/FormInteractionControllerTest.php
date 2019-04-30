<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use OxidEsales\Eshop\Core\Registry;
use Wirecard\Oxid\Controller\FormInteractionController;
use Wirecard\Oxid\Model\FormInteractionResponseFields;

class FormInteractionControllerTest extends \Wirecard\Test\WdUnitTestCase
{
    /**
     * @var FormInteractionController
     */
    private $_controller;

    protected function setUp()
    {
        $this->_controller = oxNew(FormInteractionController::class);

        parent::setUp();
    }

    /**
     * @dataProvider testResponseFieldsProvider
     */
    public function testInit($responseFields)
    {
        Registry::getSession()->setVariable('wdFormInteractionResponse', $responseFields);

        $this->_controller->init();
        $this->assertNotNull($this->_controller->getUrl());
        $this->assertNotNull($this->_controller->getMethod());
        $this->assertNotNull($this->_controller->getFormFields());
    }

    public function testInitWithoutResponse()
    {
        oxTestModules::addFunction(
            'oxUtils',
            'redirect',
            '{ return; }');

        $this->_controller->init();
        $this->assertNull($this->_controller->getUrl());
        $this->assertNull($this->_controller->getMethod());
        $this->assertNull($this->_controller->getFormFields());
    }

    /**
     * @dataProvider testResponseFieldsProvider
     */
    public function testGetUrl($responseFields)
    {
        Registry::getSession()->setVariable('wdFormInteractionResponse', $responseFields);

        $this->_controller->init();
        $this->assertEquals('my url', $this->_controller->getUrl());
    }

    /**
     * @dataProvider testResponseFieldsProvider
     */
    public function testGetMethod($responseFields)
    {
        Registry::getSession()->setVariable('wdFormInteractionResponse', $responseFields);

        $this->_controller->init();
        $this->assertEquals('test method', $this->_controller->getMethod());
    }

    /**
     * @dataProvider testResponseFieldsProvider
     */
    public function testGetForm($responseFields)
    {

        Registry::getSession()->setVariable('wdFormInteractionResponse', $responseFields);

        $this->_controller->init();

        $oFormFieldMap = $this->_controller->getFormFields();
        $this->assertNotNull($oFormFieldMap);
        $aFormFields = $oFormFieldMap->getIterator()->getArrayCopy();
        $this->assertArrayHasKey('KEY1', $aFormFields);
        $this->assertArrayHasKey('KEY2', $aFormFields);
    }

    public function testResponseFieldsProvider()
    {
        $oFormFields = new \Wirecard\PaymentSdk\Entity\FormFieldMap();
        $oFormFields->add("KEY1", "VALUE1");
        $oFormFields->add("KEY2", "VALUE2");
        $oResponseFields = new FormInteractionResponseFields(
            'my url',
            'test method',
            $oFormFields);

        return [
            'response fields' => [$oResponseFields],
        ];
    }

}
