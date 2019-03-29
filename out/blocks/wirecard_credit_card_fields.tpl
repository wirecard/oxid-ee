[{$smarty.block.parent}]
[{*[{assign var="payment" value=$oView->getPayment()}]*}]
[{*{if $payment->oxpayments__oxid->value eq "wdcreditcard"} *}]
<script src="https://code.jquery.com/jquery-3.1.1.min.js" type="application/javascript"></script>
<script src="https://api-test.wirecard.com/engine/hpp/paymentPageLoader.js" type="text/javascript"></script>

<div id="wirecard-cc-error"></div>
<div>
  <form id="wirecard-cc-form" method="post" action="[{$oViewConf->getSslSelfLink()}]">
    [{* Fields from the actual submit form for execute function *}]
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
    <div id="creditcard-form-div"></div>
  </form>
</div>
<script type="application/javascript">
  var debug = true;

  WirecardPaymentPage.seamlessRenderForm({

    requestData: [{$oView->getCreditCardUiWithData()}],
    wrappingDivId: "creditcard-form-div",
    onSuccess: callback,
    onError: function (error) {
      logError('seamlessRenderForm', error);
    }
  });

  function callback(response) {
    $("#creditcard-form-div").height(300).fadeIn();
    getOrderButton().prop("disabled", false);

    if (debug) {
      console.log(response);
    }
  }

  function logError(where, error) {
    if (typeof error == "string") {
      $("#wirecard-cc-error").html(error)
    }

    if (debug) {
      console.error("Error on " + where + ":", error);
    }
  }

  function getOrderButton() {
    return $('#orderConfirmAgbBottom button[type = "submit"]');
  }

  $(document).ready(function () {
    var orderButton = getOrderButton();
    orderButton.prop("disabled", true);
    orderButton.on("click", function (event) {
      event.preventDefault();
      submitPaymentForm(event)
    });
  });

  $('#wirecard-cc-form').submit(submitPaymentForm);

  function submitPaymentForm(event) {
    if (!$("input#jsresponse").length) {
      event.preventDefault();

      WirecardPaymentPage.seamlessSubmitForm({
        onSuccess: setParentTransactionId,
        onError: function (error) {
          logError('seamlessSubmitForm', error);
        }
      })
    }
  }

  function setParentTransactionId(response) {
    console.log("setParentTransactionId", response);
    var form = $('#wirecard-cc-form');
    $.each(response, function (key, value) {
      form.append("<input type='hidden' name='" + key + "' value='" + value + "'>");
    });
    form.append("<input type='hidden' id='jsresponse' name='jsresponse' value='true'>");
    form.submit();
  }

</script>
[{*[{/if}] *}]
