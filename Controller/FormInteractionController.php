<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Registry;

use Wirecard\Oxid\Model\FormInteractionResponseFields;
use Wirecard\PaymentSdk\Entity\FormFieldMap;

/**
 * Class Form_Interaction_Controller
 *
 * @since 1.0.0
 */
class FormInteractionController extends FrontendController
{

    const WD_RESPONSE_VARIABLE = 'wdFormInteractionResponse';
    /**
     * @inheritdoc
     *
     * @since 1.0.0
     */
    protected $_sThisTemplate = 'form_interaction.tpl';

    /**
     * @var FormInteractionResponseFields
     *
     * @since 1.0.0
     */
    private $_oResponse = null;

    /**
     * @inheritdoc
     *
     * @since 1.0.0
     */
    public function init()
    {
        parent::init();

        $oSession = Registry::getSession();
        $this->_oResponse = $oSession->getVariable(self::WD_RESPONSE_VARIABLE);
        $oSession->deleteVariable(self::WD_RESPONSE_VARIABLE);
        if (empty($this->_oResponse)) {
            //redirect home
            Registry::getUtils()->redirect(Registry::getConfig()->getShopHomeUrl());
        }
    }

    /**
     * @return string
     *
     * @since 1.0.0
     */
    public function getUrl()
    {
        return $this->_oResponse->sUrl;
    }

    /**
     * @return string
     *
     * @since 1.0.0
     */
    public function getMethod()
    {
        return $this->_oResponse->sMethod;
    }

    /**
     * @return FormFieldMap
     *
     * @since 1.0.0
     */
    public function getFormFields()
    {
        return $this->_oResponse->aFormFields;
    }
}
