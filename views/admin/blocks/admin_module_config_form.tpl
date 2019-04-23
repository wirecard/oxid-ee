[{$smarty.block.parent}]

[{if $oViewConf->isOurModule($oModule->getId())}]
  [{include file="live_chat.tpl"}]
[{/if}]
