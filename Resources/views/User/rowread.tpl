{if $modvars.$module.enablecategorization and $modvars.ZConfig.shorturls and $modvars.$module.addcategorytitletopermalink}
    {assign var='prop' value=$properties.0}
    <a href="{route name='zikulapagesmodule_user_display' pageid=$item.pageid cat=$item.__CATEGORIES__.$prop.path_relative}">{$item.title|safehtml}</a>
{else}
    <a href="{route name='zikulapagesmodule_user_display' pageid=$item.pageid}">{$item.title|safehtml}</a>
{/if}