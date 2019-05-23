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
    private $_formInteractionController;

    protected function setUp()
    {
        $this->_formInteractionController = oxNew(FormInteractionController::class);

        parent::setUp();
    }

    /**
     * @dataProvider responseFieldsProvider
     */
    public function testInit($responseFields)
    {
        Registry::getSession()->setVariable('wdFormInteractionResponse', $responseFields);

        $this->_formInteractionController->init();
        $this->assertNotNull($this->_formInteractionController->getUrl());
        $this->assertNotNull($this->_formInteractionController->getMethod());
        $this->assertNotNull($this->_formInteractionController->getFormFields());
    }

    public function testInitWithoutResponse()
    {
        oxTestModules::addFunction(
            'oxUtils',
            'redirect',
            '{ return; }');

        $this->_formInteractionController->init();
        $this->assertNull($this->_formInteractionController->getUrl());
        $this->assertNull($this->_formInteractionController->getMethod());
        $this->assertNull($this->_formInteractionController->getFormFields());
    }

    /**
     * @dataProvider responseFieldsProvider
     */
    public function testGetUrl($responseFields)
    {
        Registry::getSession()->setVariable('wdFormInteractionResponse', $responseFields);

        $this->_formInteractionController->init();
        $this->assertEquals('my url', $this->_formInteractionController->getUrl());
    }

    /**
     * @dataProvider responseFieldsProvider
     */
    public function testGetMethod($responseFields)
    {
        Registry::getSession()->setVariable('wdFormInteractionResponse', $responseFields);

        $this->_formInteractionController->init();
        $this->assertEquals('test method', $this->_formInteractionController->getMethod());
    }

    /**
     * @dataProvider responseFieldsProvider
     */
    public function testGetForm($responseFields)
    {

        Registry::getSession()->setVariable('wdFormInteractionResponse', $responseFields);

        $this->_formInteractionController->init();

        $oFormFieldMap = $this->_formInteractionController->getFormFields();
        $aFormFields = $oFormFieldMap->getIterator()->getArrayCopy();
        $this->assertArraySubset(["KEY1" => "VALUE1","KEY2" => "VALUE2" ], $aFormFields);
    }

    public function responseFieldsProvider()
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
