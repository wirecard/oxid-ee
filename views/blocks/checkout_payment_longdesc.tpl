[{if $paymentmethod->oxpayments__oxid == "wdcreditcard"}]
  <p>API URL: [{$paymentmethod->oxpayments__wdoxidee_apiurl}]</p>
  <p>Maid: [{$paymentmethod->oxpayments__wdoxidee_maid}]</p>
  <p>Secret: [{$paymentmethod->oxpayments__wdoxidee_secret}]</p>
  <p>3D Maid: [{$paymentmethod->oxpayments__wdoxidee_three_d_maid}]</p>
  <p>3D Secret: [{$paymentmethod->oxpayments__wdoxidee_three_d_secret}]</p>
[{/if}]

[{$smarty.block.parent}]