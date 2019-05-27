<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

use Wirecard\Oxid\Model\MetaDataModel;

use OxidEsales\Eshop\Core\Field;

use Wirecard\Test\WdUnitTestCase;

class MetaDataModelTest extends WdUnitTestCase
{
    /**
     * @var MetaDataModel
     */
    private $_oMetaDataModelStub;

    protected function setUp()
    {
        $this->_oMetaDataModelStub = $this->getMockForTrait(
            MetaDataModel::class,
            [],
            '',
            true,
            true,
            true,
            [
                'getId',
                '_getFieldLongName',
            ]
        );

        $this->_oMetaDataModelStub
            ->method('getTableName')
            ->willReturn('wdoxidee_oxpayments_metadata');
        $this->_oMetaDataModelStub
            ->method('getId')
            ->willReturn('wdpaypal');

        parent::setUp();
    }

    public function testGetMetaFields()
    {
        $this->_oMetaDataModelStub->oxdesc = new Field('Description');
        $this->_oMetaDataModelStub->__foo = new Field('foo');

        $this->assertEquals([
            '__foo' => new Field('foo'),
        ], $this->_oMetaDataModelStub->getMetaFields());
    }

    public function testSave()
    {
        $this->_oMetaDataModelStub->__foo = new Field('Lorem');
        $this->_oMetaDataModelStub->save();

        $this->assertArrayHasKey('foo', $this->_oMetaDataModelStub->loadMetaData());
    }

    public function testLoad()
    {
        $this->_oMetaDataModelStub
            ->method('_getFieldLongName')
            ->willReturn('oxpayments__foo');

        $this->_oMetaDataModelStub->saveMetaData([
            'foo' => 'Lorem',
        ]);
        $this->_oMetaDataModelStub->load('wdpaypal');

        $this->assertObjectHasAttribute('oxpayments__foo', $this->_oMetaDataModelStub);
    }

    public function testSaveMetaData()
    {
        $aInput = [
            'foo' => 'Lorem',
            'bar' => 1337,
            'baz' => [1, 2, 3],
        ];

        $this->assertEquals(count($aInput), $this->_oMetaDataModelStub->saveMetaData($aInput));
        $this->assertEquals(0, $this->_oMetaDataModelStub->saveMetaData([]));

        return $aInput;
    }

    /**
     * @depends testSaveMetaData
     */
    public function testLoadMetaData($aInput)
    {
        $this->assertEquals($aInput, $this->_oMetaDataModelStub->loadMetaData());

        return $aInput;
    }

    /**
     * @depends testLoadMetaData
     */
    public function testDeleteMetaData($aInput)
    {
        $this->_oMetaDataModelStub->deleteMetaData(['foo']);
        $this->assertCount(2, $this->_oMetaDataModelStub->loadMetaData());

        $this->_oMetaDataModelStub->deleteMetaData();
        $this->assertEmpty($this->_oMetaDataModelStub->loadMetaData());
    }
}
