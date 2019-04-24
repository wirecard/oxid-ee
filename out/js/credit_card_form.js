/*
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */
/* global WirecardPaymentPage */
var ModuleCreditCardForm = (function($) {
  var debug = false;

  var requestData = null;

  function getOrderButton() {
    return $("#orderConfirmAgbBottom button[type = 'submit']");
  }

  function callback(response) {
    $('#cc-spinner').fadeOut();
    $('#creditcard-form-div')
      .height(350)
      .fadeIn();
    getOrderButton().prop('disabled', false);
  }

  function logError(where, error) {
    if (error.status_code_1) {
      $('#wirecard-cc-error')
        .addClass('alert alert-danger')
        .html(error.status_code_1 + ' ' + error.status_description_1);
    }

    if (debug) {
      // eslint-disable-next-line no-console
      console.error('Error on ' + where + ':', error);
    }
  }

  function setParentTransactionId(response) {
    var form = $('#wirecard-cc-form');
    $.each(response, function(key, value) {
      form.append("<input type='hidden' name='" + key + "' value='" + value + "'>");
    });
    form.append("<input type='hidden' id='jsresponse' name='jsresponse' value='true'>");
    form.submit();
  }

  function submitPaymentForm(event) {
    if (!$('#wirecard-cc-form input#jsresponse').length) {
      event.preventDefault();
      WirecardPaymentPage.seamlessSubmitForm({
        onSuccess: setParentTransactionId,
        onError: function(error) {
          logError('seamlessSubmitForm', error);

          // if it was not just a local form validation error, reload the seamless credit card form to create a new transaction
          if (!error['form_validation_result']) {
            createNewTransaction();
          }
        },
      });
    }
  }

  function setRequestData(_requestData) {
    requestData = _requestData;
  }

  function parseCreditCardFormDataRespone(responseString) {
    var parsedObj = null;

    try {
      var dataObj = JSON.parse(responseString);

      var requestDataObj = JSON.parse(dataObj['requestData']);

      parsedObj = requestDataObj;
    } catch (ex) {}

    return parsedObj;
  }

  function createNewTransaction() {
    var ccRequestDataAjaxUrl = $('#ccRequestDataAjaxUrl').val();

    $.get(ccRequestDataAjaxUrl, function(data) {
      var _requestData = parseCreditCardFormDataRespone(data);

      setRequestData(_requestData);

      initSeamlessRenderForm();
    });
  }

  function initSeamlessRenderForm() {
    WirecardPaymentPage.seamlessRenderForm({
      requestData: requestData,
      wrappingDivId: 'creditcard-form-div',
      onSuccess: callback,
      onError: function(error) {
        logError('seamlessRenderForm', error);
      },
    });
  }

  return {
    init: function(_requestData) {
      setRequestData(_requestData);

      initSeamlessRenderForm();

      var orderButton = getOrderButton();
      orderButton.prop('disabled', true);
      orderButton.on('click', function(event) {
        event.preventDefault();
        submitPaymentForm(event);
      });

      $('#wirecard-cc-form').submit(submitPaymentForm);
    },
  };
})(jQuery);
