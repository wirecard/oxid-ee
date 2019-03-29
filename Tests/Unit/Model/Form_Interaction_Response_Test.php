<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

class Form_Interaction_Response_Test extends OxidEsales\TestingLibrary\UnitTestCase
{
    const URL = "myCustomURL";
    const METHOD = "POST";

    private $_oFormFields;

    protected function setUp()
    {
        parent::setUp();
        $this->_oFormFields = new \Wirecard\PaymentSdk\Entity\FormFieldMap();
        $this->_oFormFields->add("KEY1", "VALUE1");
        $this->_oFormFields->add("KEY2", "VALUE2");
    }

    public function testCorrectConstructor()
    {
        $response = new \Wirecard\Oxid\Model\Form_Interaction_Response(self::URL, self::METHOD, $this->_oFormFields);
        $this->assertTrue($response->sUrl === self::URL);
        $this->assertTrue($response->sMethod === self::METHOD);

        $actualFormFields = $response->aFormFields->getIterator()->getArrayCopy();
        $this->assertTrue($actualFormFields["KEY1"] === "VALUE1");
        $this->assertTrue($actualFormFields["KEY2"] === "VALUE2");
    }

}
