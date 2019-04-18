<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

namespace Wirecard\Oxid\Model;

use Wirecard\PaymentSdk\Entity\FormFieldMap;

/**
 * Wrapper class to allow serialization
 */
class FormInteractionResponseFields
{

    /**
     * @var string
     */
    public $sUrl;

    /**
     * @var string
     */
    public $sMethod;

    /**
     * @var FormFieldMap
     */
    public $aFormFields;

    /**
     * Form_Interaction_Response constructor.
     *
     * @param string       $sUrl
     * @param string       $sMethod
     * @param FormFieldMap $aFormFields
     */
    public function __construct(string $sUrl, string $sMethod, FormFieldMap $aFormFields)
    {
        $this->sUrl = $sUrl;
        $this->sMethod = $sMethod;
        $this->aFormFields = $aFormFields;
    }
}
