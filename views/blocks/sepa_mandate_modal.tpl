[{*
* Shop System Plugins:
* - Terms of Use can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
* - License can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/LICENSE
*
*}]

<div id="mandateModal" class="modal">

  <div class="modal-content">
    <span class="close-modal">&times;</span>

    [{$sepamandate}]

    <input type="checkbox" id="sepadd-checkbox" style="margin-right: 5px"/><label for="sepadd-checkbox">[{oxmultilang ident="wd_sepa_text_6"}]</label>

    <hr>

    <form action="[{$oViewConf->getSslSelfLink()}]" method="post" id="orderConfirmAgbBottom" class="form-horizontal">
        <div class="hidden">
            [{$oViewConf->getHiddenSid()}]
            [{$oViewConf->getNavFormParams()}]
            <input type="hidden" name="cl" value="order">
            <input type="hidden" name="fnc" value="[{$oView->getExecuteFnc()}]">
            <input type="hidden" name="challenge" value="[{$challenge}]">
            <input type="hidden" name="sDeliveryAddressMD5" value="[{$oView->getDeliveryAddressMD5()}]">

            [{if $oView->isActive('PsLogin') || !$oView->isConfirmAGBActive()}]
                <input type="hidden" name="ord_agb" value="1">
            [{else}]
                <input type="hidden" name="ord_agb" value="0">
            [{/if}]
            <input type="hidden" name="oxdownloadableproductsagreement" value="0">
            <input type="hidden" name="oxserviceproductsagreement" value="0">
        </div>

        [{block name="checkout_order_btn_submit_bottom"}]
          <button type="submit" disabled class="btn btn-primary submitButton nextStep">
              <i class="fa fa-check"></i> [{oxmultilang ident="wd_accept"}]
          </button>

          <div class="clearfix"></div>
        [{/block}]
    </form>
  </div>

</div>
