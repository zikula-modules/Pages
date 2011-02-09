{if $enablecategorization and $shorturls and $shorturlstype eq 0 and $addcategorytitletopermalink}
{assign var='prop' value=$properties.0}
<a href="{modurl modname='Pages' func='display' pageid=$pageid cat=$__CATEGORIES__.$prop.path_relative}">{$title|safehtml}</a>
{else}
<a href="{modurl modname='Pages' func='display' pageid=$pageid}">{$title|safehtml}</a>
{/if}