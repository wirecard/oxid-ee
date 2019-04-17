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
 *
 * @since 1.0.0
 */
class FormInteractionResponseFields
{

    /**
     * @var string
     *
     * @since 1.0.0
     */
    public $sUrl;

    /**
     * @var string
     *
     * @since 1.0.0
     */
    public $sMethod;

    /**
     * @var FormFieldMap
     *
     * @since 1.0.0
     */
    public $aFormFields;

    /**
     * Form_Interaction_Response constructor.
     *
     * @param string       $sUrl
     * @param string       $sMethod
     * @param FormFieldMap $aFormFields
     *
     * @since 1.0.0
     */
    public function __construct(string $sUrl, string $sMethod, FormFieldMap $aFormFields)
    {
        $this->sUrl = $sUrl;
        $this->sMethod = $sMethod;
        $this->aFormFields = $aFormFields;
    }
}
