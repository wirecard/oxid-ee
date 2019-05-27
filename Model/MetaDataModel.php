<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Model;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;

use OxidEsales\EshopCommunity\Core\Model\BaseModel;

/**
 * Adds meta data functionality to a model.
 *
 * @since 1.2.0
 */
trait MetaDataModel
{
    /**
     * Returns the name of the meta data table for this model.
     * @return string
     *
     * @since 1.2.0
     */
    abstract public function getTableName();

    /**
     * Returns the short field name for a given field (e.g. oxpayments__foo → foo).
     *
     * @param string $sLongFieldName
     * @return string
     *
     * @since 1.2.0
     */
    protected function _getFieldShortName($sLongFieldName)
    {
        return str_replace("{$this->_sCoreTable}__", '', $sLongFieldName);
    }

    /**
     * Extends the `load` method to also load meta fields from the database.
     *
     * @inheritdoc
     * @param string $sOxid
     * @return bool
     *
     * @since 1.2.0
     */
    public function load($sOxid)
    {
        $bIsLoaded = is_subclass_of($this, BaseModel::class) ? parent::load($sOxid) : true;

        foreach ($this->loadMetaData() as $sFieldShortName => $mFieldValue) {
            $sFieldLongName = $this->_getFieldLongName($sFieldShortName);

            $this->$sFieldLongName = new Field($mFieldValue);
        }

        return $bIsLoaded;
    }

    /**
     * Extends the `save` method to also save meta fields to the database.
     *
     * @inheritdoc
     * @return string|bool
     *
     * @since 1.2.0
     */
    public function save()
    {
        $aMetaData = [];

        foreach ($this->getMetaFields() as $sFieldLongName => $oField) {
            $sFieldShortName = $this->_getFieldShortName($sFieldLongName);

            if ($this->$sFieldLongName instanceof Field) {
                $aMetaData[$sFieldShortName] = $this->$sFieldLongName->getRawValue();
            }
        }

        $this->deleteMetaData();
        $this->saveMetaData($aMetaData);

        return is_subclass_of($this, BaseModel::class) ? parent::save() : true;
    }

    /**
     * Returns all meta fields attached to the object.
     *
     * @return array
     *
     * @since 1.2.0
     */
    public function getMetaFields()
    {
        return array_filter(get_object_vars($this), function ($sKey) {
            return strpos($sKey, "{$this->_sCoreTable}__") === 0 &&
                !isset($this->_aFieldNames[$this->_getFieldShortName($sKey)]);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Loads meta data from the database.
     *
     * @return array
     *
     * @since 1.2.0
     */
    public function loadMetaData()
    {
        $aMetaData = [];
        $aRows = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->select(
            "SELECT `KEY`, `VALUE` FROM `{$this->getTableName()}` WHERE `OXOBJECTID` = '{$this->getId()}'"
        )->fetchAll();

        foreach ($aRows as $aRow) {
            $aMetaData[$aRow['KEY']] = unserialize($aRow['VALUE']);
        }

        return $aMetaData;
    }

    /**
     * Saves meta data to the database.
     *
     * @param array $aMetaData Associative array of meta data to save.
     * @return integer
     *
     * @since 1.2.0
     */
    public function saveMetaData($aMetaData = [])
    {
        if (!$aMetaData) {
            return 0;
        }

        // delete all duplicate keys for this object
        $this->deleteMetaData(array_keys($aMetaData));

        $aValuesQuery = [];

        foreach ($aMetaData as $sKey => $mValue) {
            $aValues = [
                Registry::getUtilsObject()->generateUID(),
                $this->getId(),
                $sKey,
                serialize($mValue),
            ];

            $aValuesQuery[] = '(' . implode(',', DatabaseProvider::getDb()->quoteArray($aValues)) . ')';
        }

        return DatabaseProvider::getDb()->execute(
            "INSERT INTO `{$this->getTableName()}` (`OXID`, `OXOBJECTID`, `KEY`, `VALUE`) VALUES " .
            implode(', ', $aValuesQuery)
        );
    }

    /**
     * Deletes meta data from the database.
     *
     * @param array $aKeys Meta data keys to delete. If no keys are passed, all meta data for this payment is deleted.
     * @return integer
     *
     * @since 1.2.0
     */
    public function deleteMetaData($aKeys = [])
    {
        $sQuery = "DELETE FROM `{$this->getTableName()}` WHERE `OXOBJECTID` = '{$this->getId()}'";

        if ($aKeys) {
            $sQuery .= ' AND `KEY` IN (' . implode(', ', DatabaseProvider::getDb()->quoteArray($aKeys)) . ')';
        }

        return DatabaseProvider::getDb()->execute($sQuery);
    }
}
