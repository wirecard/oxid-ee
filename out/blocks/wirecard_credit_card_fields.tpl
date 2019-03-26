[{$smarty.block.parent}]
<script src="https://code.jquery.com/jquery-3.1.1.min.js" type="application/javascript"></script>
<script src="https://api-test.wirecard.com/engine/hpp/paymentPageLoader.js" type="text/javascript"></script>

<div>
  <form id="payment-form" method="post" action="[{$oViewConf->getSslSelfLink()}]">
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

  WirecardPaymentPage.seamlessRenderForm({

    requestData: [{$oView->getCreditCardUiWithData()}],
    wrappingDivId: "creditcard-form-div",
    onSuccess: logCallback,
    onError: logCallback
  });

  function logCallback(response) {
    console.log(response);
  }


  $(document).ready(function () {
    $('#orderConfirmAgbBottom button[type = "submit"]').on("click", function (event) {
      event.preventDefault();

      console.log("order button click", event);

      submitPaymentForm(event)
    });
  });

  $('#payment-form').submit(submitPaymentForm);

  function submitPaymentForm(event) {
    console.log("submit event:", event)
    console.log("submit");

    if (!$("input#jsresponse").length) {
      console.log("submit prevent default");

      event.preventDefault();

      WirecardPaymentPage.seamlessSubmitForm({
        onSuccess: setParentTransactionId,
        onError: logCallback
      })
    }
  }

  function setParentTransactionId(response) {
    console.log("setParentTransactionId", response);
    var form = $('#payment-form');
    for (var key in response) {
      if (response.hasOwnProperty(key)) {
        form.append("<input type='hidden' name='" + key + "' value='" + response[key] + "'>");
      }
    }
    form.append("<input id='jsresponse' type='hidden' name='jsresponse' value='true'>");
    form.submit();
  }

</script>
</body>
</html>
