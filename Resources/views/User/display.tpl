{pagesetvar name='title' value=$item.title}
{insert name='getstatusmsg'}

{if $item.metadescription ne ''}{setmetatag name='description' value=$item.metadescription|safehtml}{/if}
{if $item.metakeywords ne ''}{setmetatag name='keywords' value=$item.metakeywords|safehtml}{/if}

{modapifunc modname='ISHAcore' type='user' func='breadcrumbs' title=$item.title}


{if $item.displaytitle}<h2>{$item.title|safehtml}</h2>{/if}

{checkpermission component="ISHA::" instance=".*" level="ACCESS_ADMIN" assign="auth"}
{if $auth}
    <a class="btn btn-default btn-small" href="{route name='zikulapagesmodule_admin_modify' pageid=$item.pageid}" title="Edit page">
        <i class="fa fa-edit"></i> {gt text="Edit page"}
    </a>
    <br/>
{/if}

{if $item.displaywrapper or $item.displaycreated or $item.displayupdated}
    <div class="pages_page_header">
        <ul>
            {if $item.displaycreated && isset($item.cr_uid)}
                {usergetvar name='uname' uid=$item.cr_uid assign='cr_uname'}
                <li>{gt text='Created by %1$s on %2$s' tag1=$cr_uname|profilelinkbyuname tag2=$item.cr_date|dateformat}</li>
            {/if}
            {if $item.displayupdated && isset($item.lu_uid)}
                {usergetvar name='uname' uid=$item.lu_uid assign='lu_uname'}
                <li>{gt text='Last update by %1$s on %2$s' tag1=$lu_uname|profilelinkbyuname tag2=$item.lu_date|dateformat}</li>
            {/if}

            {if $modvars.$module.enablecategorization && isset($item.categories)}
                <li>{gt text='Categories'}:
                    {foreach from=$item.categories key='property' item='c' name='cats'}
                        {if isset($c.category.displayName.$lang)}
                            {assign var='name' value=$c.category.displayName.$lang}
                        {else}
                            {assign var='name' value=$c.category.name}
                        {/if}
                        {if $modvars.ZConfig.shorturls}
                            <a href="{route name='zikulapagesmodule_user_view' prop='Main' cat=$c.category.name}"
                               title="{$c.category.name}">{$c.category.name}</a>
                        {else}
                            <a href="{route name='zikulapagesmodule_user_view' cat=$c.category.Id}"
                               title="{$name}">{$name}</a>
                        {/if}
                        {if $smarty.foreach.cats.last}{else}, {/if}
                    {/foreach}
                </li>
            {/if}
        </ul>
    </div>
{/if}

{$item.content|notifyfilters:'pages.filter_hooks.pages.filter'|safehtml}

{if $item.displayprint or $item.displaytextinfo or $displayeditlink}
    <div class="pages_page_footer">
        {if $displayeditlink}
            <a href="{route name='zikulapagesmodule_admin_modify' pageid=$item.pageid}">{gt text='Edit'}</a>
            <span class="text_separator">|</span>
        {/if}
        {if $item.displaytextinfo}
            {gt text='%s total words in this text' tag1=$item.content|count_words}
            <span class="text_separator">|</span>
            {gt text='%s reads' tag1=$item.counter}
        {/if}
        {if $item.displayprint}
            <span class="pages_page_printerlink">
        <a href="{route name='zikulapagesmodule_user_display' pageid=$item.pageid theme='Printer'}">{img modname='core' src='printer.png' set='icons/small' __alt='Print page'}</a>
    </span>
        {/if}
    </div>
{/if}

{pager rowcount=$pager.numitems limit=$pager.itemsperpage posvar='page' route='zikulapagesmodule_user_display'}
{notifydisplayhooks eventname='pages.ui_hooks.pages.display_view' id=$item.pageid}