[{$smarty.block.parent}]
[{assign var="payment" value=$oView->getPayment()}]*
[{if $payment->oxpayments__oxid->value eq "wdcreditcard"}]
  <script src="https://code.jquery.com/jquery-3.1.1.min.js" type="application/javascript"></script>
  <script src="https://api-test.wirecard.com/engine/hpp/paymentPageLoader.js" type="text/javascript"></script>

  <style>
    loader {
      color: #000;
      font-size: 15px;
      margin: 100px auto;
      width: 1em;
      height: 1em;
      border-radius: 50%;
      position: relative;
      text-indent: -9999em;
      -webkit-animation: load4 1.3s infinite linear;
      animation: load4 1.3s infinite linear;
      -webkit-transform: translateZ(0);
      -ms-transform: translateZ(0);
      transform: translateZ(0);
    }
    @-webkit-keyframes load4 {
      0%,
      100% {
        box-shadow: 0 -3em 0 0.2em, 2em -2em 0 0em, 3em 0 0 -1em, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 -1em, -3em 0 0 -1em, -2em -2em 0 0;
      }
      12.5% {
        box-shadow: 0 -3em 0 0, 2em -2em 0 0.2em, 3em 0 0 0, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 -1em, -3em 0 0 -1em, -2em -2em 0 -1em;
      }
      25% {
        box-shadow: 0 -3em 0 -0.5em, 2em -2em 0 0, 3em 0 0 0.2em, 2em 2em 0 0, 0 3em 0 -1em, -2em 2em 0 -1em, -3em 0 0 -1em, -2em -2em 0 -1em;
      }
      37.5% {
        box-shadow: 0 -3em 0 -1em, 2em -2em 0 -1em, 3em 0em 0 0, 2em 2em 0 0.2em, 0 3em 0 0em, -2em 2em 0 -1em, -3em 0em 0 -1em, -2em -2em 0 -1em;
      }
      50% {
        box-shadow: 0 -3em 0 -1em, 2em -2em 0 -1em, 3em 0 0 -1em, 2em 2em 0 0em, 0 3em 0 0.2em, -2em 2em 0 0, -3em 0em 0 -1em, -2em -2em 0 -1em;
      }
      62.5% {
        box-shadow: 0 -3em 0 -1em, 2em -2em 0 -1em, 3em 0 0 -1em, 2em 2em 0 -1em, 0 3em 0 0, -2em 2em 0 0.2em, -3em 0 0 0, -2em -2em 0 -1em;
      }
      75% {
        box-shadow: 0em -3em 0 -1em, 2em -2em 0 -1em, 3em 0em 0 -1em, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 0, -3em 0em 0 0.2em, -2em -2em 0 0;
      }
      87.5% {
        box-shadow: 0em -3em 0 0, 2em -2em 0 -1em, 3em 0 0 -1em, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 0, -3em 0em 0 0, -2em -2em 0 0.2em;
      }
    }
    @keyframes load4 {
      0%,
      100% {
        box-shadow: 0 -3em 0 0.2em, 2em -2em 0 0em, 3em 0 0 -1em, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 -1em, -3em 0 0 -1em, -2em -2em 0 0;
      }
      12.5% {
        box-shadow: 0 -3em 0 0, 2em -2em 0 0.2em, 3em 0 0 0, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 -1em, -3em 0 0 -1em, -2em -2em 0 -1em;
      }
      25% {
        box-shadow: 0 -3em 0 -0.5em, 2em -2em 0 0, 3em 0 0 0.2em, 2em 2em 0 0, 0 3em 0 -1em, -2em 2em 0 -1em, -3em 0 0 -1em, -2em -2em 0 -1em;
      }
      37.5% {
        box-shadow: 0 -3em 0 -1em, 2em -2em 0 -1em, 3em 0em 0 0, 2em 2em 0 0.2em, 0 3em 0 0em, -2em 2em 0 -1em, -3em 0em 0 -1em, -2em -2em 0 -1em;
      }
      50% {
        box-shadow: 0 -3em 0 -1em, 2em -2em 0 -1em, 3em 0 0 -1em, 2em 2em 0 0em, 0 3em 0 0.2em, -2em 2em 0 0, -3em 0em 0 -1em, -2em -2em 0 -1em;
      }
      62.5% {
        box-shadow: 0 -3em 0 -1em, 2em -2em 0 -1em, 3em 0 0 -1em, 2em 2em 0 -1em, 0 3em 0 0, -2em 2em 0 0.2em, -3em 0 0 0, -2em -2em 0 -1em;
      }
      75% {
        box-shadow: 0em -3em 0 -1em, 2em -2em 0 -1em, 3em 0em 0 -1em, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 0, -3em 0em 0 0.2em, -2em -2em 0 0;
      }
      87.5% {
        box-shadow: 0em -3em 0 0, 2em -2em 0 -1em, 3em 0 0 -1em, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 0, -3em 0em 0 0, -2em -2em 0 0.2em;
      }
    }
  </style>

  <div id="pg-spinner" class="loader"></div>
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
      onError: logError
    });

    function callback(response) {
      $("#pg-spinner").fadeOut();
      $("#creditcard-form-div").height(500).fadeIn();
      getOrderButton().prop("disabled", false);

      if (debug) {
        console.log(response);
      }
    }

    function logError(error) {
      if (typeof error === "string") {
        $("#wirecard-cc-error").html(error)
      }

      if (debug) {
        console.log(error);
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
          onError: logError
        })
      }
    }

    function setParentTransactionId(response) {
      var form = $('#wirecard-cc-form');
      $.each(response, function (key, value) {
        form.append("<input type='hidden' name='" + key + "' value='" + value + "'>");
      });
      form.append("<input id='jsresponse' type='hidden' name='jsresponse' value='true'>");
      form.submit();
    }

  </script>
  [{/if}]
