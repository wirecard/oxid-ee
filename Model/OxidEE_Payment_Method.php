<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

/**
 * Payment method
 *
 * @SuppressWarnings(PHPMD.Coverage)
 */
class OxidEE_Payment_Method
{
    private $sConfigId;
    private $sDescription;
    private $sLogoPath;
    private $sTransactionType;
    private $sApiUrl;
    private $sMaid;
    private $sSecret;
    private $sHttpUser;
    private $sHttpPass;

    /**
     * Payment method constructor
     */
    public function __construct()
    {
    }

    /**
     * Sets the config id.
     *
     * @param string $sConfigId config ID
     */
    public function setConfigId($sConfigId)
    {
        $this->sConfigId = $sConfigId;
    }

    /**
     * Returns the config id.
     *
     * @return string config ID
     */
    public function getConfigId()
    {
        return $this->sConfigId;
    }

    /**
     * Sets the description.
     *
     * @param string $sDescription description
     */
    public function setDescription($sDescription)
    {
        $this->sDescription = $sDescription;
    }

    /**
     * Returns the description.
     *
     * @return string description
     */
    public function getDescription()
    {
        return $this->sDescription;
    }

    /**
     * Sets the logo path.
     *
     * @param string $sLogoPath logo
     */
    public function setLogoPath($sLogoPath)
    {
        $this->sLogoPath = $sLogoPath;
    }

    /**
     * Returns the logo path.
     *
     * @return string logo
     */
    public function getLogoPath()
    {
        return $this->sLogoPath;
    }

    /**
     * Sets the transaction type.
     *
     * @param string $sTransactionType transaction type
     */
    public function setTransactionType($sTransactionType)
    {
        $this->sTransactionType = $sTransactionType;
    }

    /**
     * Returns the transaction type.
     *
     * @return string transaction type
     */
    public function getTransactionType()
    {
        return $this->sTransactionType;
    }

    /**
     * Sets the API URL.
     *
     * @param string $sApiUrl API URL
     */
    public function setApiUrl($sApiUrl)
    {
        $this->sApiUrl = $sApiUrl;
    }

    /**
     * Returns the API URL.
     *
     * @return string API URL
     */
    public function getApiUrl()
    {
        return $this->sApiUrl;
    }

    /**
     * Sets the MAID.
     *
     * @param string $sMaid MAID
     */
    public function setMaid($sMaid)
    {
        $this->sMaid = $sMaid;
    }

    /**
     * Returns the MAID.
     *
     * @return string MAID
     */
    public function getMaid()
    {
        return $this->sMaid;
    }

    /**
     * Sets the secret.
     *
     * @param string $sSecret secret
     */
    public function setSecret($sSecret)
    {
        $this->sSecret = $sSecret;
    }

    /**
     * Returns the secret.
     *
     * @return string secret
     */
    public function getSecret()
    {
        return $this->sSecret;
    }

    /**
     * Sets the HTTP user.
     *
     * @param string $sHttpUser HTTP user
     */
    public function setHttpUser($sHttpUser)
    {
        $this->sHttpUser = $sHttpUser;
    }

    /**
     * Returns the HTTP user.
     *
     * @return string HTTP user
     */
    public function getHttpUser()
    {
        return $this->sHttpUser;
    }

    /**
     * Sets the HTTP pass.
     *
     * @param string $sHttpPass HTTP pass
     */
    public function setHttpPass($sHttpPass)
    {
        $this->sHttpPass = $sHttpPass;
    }

    /**
     * Returns the HTTP pass.
     *
     * @return string HTTP pass
     */
    public function getHttpPass()
    {
        return $this->sHttpPass;
    }
}
