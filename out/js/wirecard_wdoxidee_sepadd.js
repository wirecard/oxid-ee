/*
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/oxid-ee/blob/master/LICENSE
 */

var $ = jQuery;
var modal = document.getElementById("mandateModal");

function uncheckMandateTerms() {
  if ($("#sepadd-checkbox").prop("checked")) {
    $("#sepadd-checkbox").click();
  }
}

$("#openMandateModal").click(function() {
  $("#mandateModal").css("display", "block");
});

$(".close-modal").click(function() {
  $("#mandateModal").css("display", "none");
  uncheckMandateTerms();
});

$(window).click(function(event) {
  if (event.target === modal) {
    $("#mandateModal").css("display", "none");
    uncheckMandateTerms();
  }
});

$("#sepadd-checkbox").click(function() {
  $("#mandateModal button").attr("disabled", !this.checked);
});
