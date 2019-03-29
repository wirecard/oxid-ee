<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Controller;

use \OxidEsales\Eshop\Application\Controller\FrontendController;
use \OxidEsales\Eshop\Core\Registry;

use \Wirecard\Oxid\Model\Form_Interaction_Response;
use \Wirecard\PaymentSdk\Entity\FormFieldMap;

/**
 * Class Form_Interaction_Controller
 */
class Form_Interaction_Controller extends FrontendController
{
    /**
     * @inheridoc
     */
    protected $_sThisTemplate = 'wd_form_interaction.tpl';

    /**
     * @var Form_Interaction_Response
     */
    private $_oResponse = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->_oResponse = Registry::getSession()->getVariable('wdFormInteractionResponse');
        Registry::getLogger()->debug(print_r($this->_oResponse, true));
        if (is_null($this->_oResponse)) {
            //redirect home
            Registry::getUtils()->redirect(Registry::getConfig()->getShopUrl() . 'index.php');
        }
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->_oResponse->sUrl;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->_oResponse->sMethod;
    }

    /**
     * @return FormFieldMap
     */
    public function getFormFields(): FormFieldMap
    {
        return $this->_oResponse->aFormFields;
    }
}
