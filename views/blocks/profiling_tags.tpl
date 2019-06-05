[{*
* Shop System Plugins:
* - Terms of Use can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/_TERMS_OF_USE
* - License can be found under:
* https://github.com/wirecard/oxid-ee/blob/master/LICENSE
*
*}]

[{assign var="oPayment" value=$oView->getPayment()}]

[{if $oPayment->isCustomPaymentMethod()}]

  [{if $oPayment->oxpayments__oxid->value === 'wdratepay-invoice'}]

  [{assign var="sDeviceIdentToken" value=$oViewConf->getRatepayUniqueToken()}]
  <script>
    var di = {t:"[{$sDeviceIdentToken}]",v:"WDWL",l:"Checkout"};
  </script>
  <script type="text/javascript" src="//d.ratepay.com/[{$sDeviceIdentToken}]/di.js">
  </script>
  <noscript>
    <link rel="stylesheet" type="text/css"
          href="//d.ratepay.com/di.css?t=[{$sDeviceIdentToken}]&v=WDWL&l=Checkout">
  </noscript>
  <object type="application/x-shockwave-flash" data="//d.ratepay.com/[{$sDeviceIdentToken}]/c.swf" width="0"
          height="0">
    <param name="movie" value="//d.ratepay.com/WDWL/c.swf"/>
    <param name="flashvars" value="t=[{$sDeviceIdentToken}]>&v=WDWL"/>
    <param name="AllowScriptAccess" value="always"/>
  </object>

  [{else}]
  [{assign var="sDeviceId" value=$oViewConf->getModuleDeviceId($oPayment->oxpayments__wdoxidee_maid)}]

  <script type="text/javascript"
          src="https://h.wirecard.com/fp/tags.js?org_id=6xxznhva&session_id=[{$sDeviceId}]">
  </script>
  <noscript>
    <iframe style="width: 100px; height: 100px; border: 0; position: absolute; top: -5000px;"
            src="https://h.wirecard.com/tags?org_id=6xxznhva&session_id=[{$sDeviceId}]"></iframe>
  </noscript>
  [{/if}]
  [{/if}]
[{$smarty.block.parent}]
