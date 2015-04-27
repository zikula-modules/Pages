{assign var="pageid" value=$page.pageid}
{assign var="title" value=$page.title}
{checkpermission component="Pages::Page" instance="$title::$pageid" level="ACCESS_READ" assign="auth"}
{if $auth}
    <li><a href="{route name='zikulapagesmodule_user_display' pageid=$page.pageid}">{$page.title}</a></li>
{/if}