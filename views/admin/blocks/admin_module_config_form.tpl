[{$smarty.block.parent}]

[{if $oViewConf->isThisModule($oModule->getId())}]
  [{include file="live_chat.tpl"}]
[{/if}]
