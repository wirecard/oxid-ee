[{*
* Shop System Plugins:
* - Terms of Use can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
* - License can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/LICENSE
*
*}]

[{assign var="paymentMethod" value=$paymentmethod->getPaymentMethod()}]

[{if $paymentMethod}]
    [{assign var="checkoutFields" value=$paymentMethod->getCheckoutFields()}]
[{/if}]

[{if $checkoutFields}]
    [{assign var="dynvalue" value=$oView->getDynValue()}]
    <dl>
        <dt>
            <input id="payment_[{$sPaymentID}]" type="radio" name="paymentid" value="[{$sPaymentID}]" [{if $oView->getCheckedPaymentId() == $paymentmethod->oxpayments__oxid->value}]checked[{/if}]>
            <label for="payment_[{$sPaymentID}]"><b>[{$paymentmethod->oxpayments__oxdesc->value}]</b></label>
        </dt>
        <dd class="[{if $oView->getCheckedPaymentId() == $paymentmethod->oxpayments__oxid->value}]activePayment[{/if}]">
            [{if $paymentmethod->getPrice()}]
                [{assign var="oPaymentPrice" value=$paymentmethod->getPrice()}]
                    [{if $oViewConf->isFunctionalityEnabled('blShowVATForPayCharge')}]
                        ( [{oxprice price=$oPaymentPrice->getNettoPrice() currency=$currency}]
                        [{if $oPaymentPrice->getVatValue() > 0}]
                            [{oxmultilang ident="PLUS_VAT"}] [{oxprice price=$oPaymentPrice->getVatValue() currency=$currency}]
                        [{/if}])
                    [{else}]
                        ([{oxprice price=$oPaymentPrice->getBruttoPrice() currency=$currency}])
                    [{/if}]
            [{/if}]
            [{foreach from=$checkoutFields key=fieldKey item=checkoutField}]
                <div class="form-group">
                    [{if $checkoutField.type !== 'hidden'}]
                        <label class="req control-label col-lg-3">[{$checkoutField.title}] [{if $checkoutField.required}]*[{/if}]</label>
                    [{/if}]
                    <div class="col-lg-9">
                        [{if $checkoutField.type === 'text' || $checkoutField.type === 'hidden'}]
                            <input id="[{$fieldKey}]" type="[{$checkoutField.type}]" class="form-control" name="dynvalue[[{$fieldKey}]]" value="[{$dynvalue.$fieldKey}]" [{if $checkoutField.required}]required[{/if}]/>
                        [{/if}]

                        [{if $checkoutField.type === 'info' }]
                          [{$checkoutField.text}]
                        [{/if}]

                        [{if $checkoutField.type === 'select'}]
                            <select class="form-control" name="dynvalue[[{$fieldKey}]]" [{if $checkoutField.required}]required[{/if}]>
                                [{foreach from=$checkoutField.options key=optionKey item=optionValue}]
                                    <option value="[{$optionKey}]" [{if $dynvalue.$fieldKey == $optionKey}]selected[{/if}]>[{$optionValue}]</option>
                                [{/foreach}]
                            </select>
                        [{/if}]

                        [{if $checkoutField.type !== 'hidden' && $checkoutField.description}]
                            <div class="help-block">[{$checkoutField.description}]</div>
                        [{/if}]

                        [{if $checkoutField.type === 'list'}]
                          [{assign var='data' value=$checkoutField.data}]
                          [{if $data.body|@count > 0}]
                            <style>
                              .cards .wd-table td {
                                border:0
                              }
                            </style>
                            <div class="cards">
                              [{include file='table.tpl'}]
                            </div>
                          [{/if}]
                        [{/if}]
                    </div>
                </div>
            [{/foreach}]
            [{block name="checkout_payment_longdesc"}]
                [{if $paymentmethod->oxpayments__oxlongdesc->value}]
                    <div class="row">
                        <div class="col-xs-12 col-lg-9 col-lg-offset-3">
                            <div class="alert alert-info desc">
                                [{$paymentmethod->oxpayments__oxlongdesc->getRawValue()}]
                            </div>
                        </div>
                    </div>
                [{/if}]
            [{/block}]
        </dd>
    </dl>
[{else}]
    [{$smarty.block.parent}]
[{/if}]
