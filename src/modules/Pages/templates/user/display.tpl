{pagesetvar name='title' value=$item.title}
{insert name='getstatusmsg'}

<div class="pages_page_container">
    {if $item.displaytitle}
    <h2>{$item.title|safehtml}</h2>
    {/if}

    {if $item.displaywrapper or $item.displaycreated or $item.displayupdated}
    <div class="pages_page_header">
        <ul>
            {if $item.displaycreated and $item.cr_uid}
            {usergetvar name='uname' uid=$item.cr_uid assign='cr_uname'}
            <li>{gt text='Created by %1$s on %2$s' tag1=$cr_uname|userprofilelink tag2=$item.cr_date|dateformat}</li>
            {/if}
            {if $item.displayupdated and $item.lu_uid}
            {usergetvar name='uname' uid=$item.lu_uid assign='lu_uname'}
            <li>{gt text='Last update by %1$s on %2$s' tag1=$lu_uname|userprofilelink tag2=$item.lu_date|dateformat}</li>
            {/if}
            {if $item.__CATEGORIES__}
            <li>{gt text='Categories'}:
                {foreach from=$item.__CATEGORIES__ key='property' item='category'}
                {if $category.accessible}
                {if $modvars.ZConfig.shorturls and $modvars.ZConfig.shorturlstype eq 0}
                <a href="{modurl modname='Pages' func='view' prop=$property cat=$category.path_relative}" title="{$category.display_desc.$lang}">{$category.display_name.$lang}</a>
                {else}
                <a href="{modurl modname='Pages' func='view' prop=$property cat=$category.id}" title="{$category.display_desc.$lang}">{$category.display_name.$lang}</a>
                {/if}
                {/if}
                {/foreach}
            </li>
            {/if}
        </ul>
    </div>
    {/if}

    <div class="pages_page_body">
        {$item.content|notifyfilters:'pages.hook.pagesfilter.ui.filter'|safehtml}
    </div>

    {if $item.displayprint or $item.displaytextinfo or $item.displayeditlink}
    <div class="pages_page_footer">
        {if $item.displayeditlink}
        <a href="{modurl modname='Pages' type='admin' func='modify' pageid=$item.pageid}">{gt text='Edit'}</a>
        <span class="text_separator">|</span>
        {/if}
        {if $item.displaytextinfo}
        {gt text='%s total words in this text' tag1=$item.content|count_words}
        <span class="text_separator">|</span>
        {gt text='%s reads' tag1=$item.counter}
        {/if}
        {if $item.displayprint}
        <span class="pages_page_printerlink">
            <a href="{modurl modname='Pages' func='display' pageid=$item.pageid theme='Printer'}">{img modname='core' src='printer1.gif' set='icons/small' __alt='Print page'}</a>
        </span>
        {/if}
    </div>
    {/if}

    {pager rowcount=$pager.numitems limit=$pager.itemsperpage posvar='page'}

    {modurl modname='Pages' func='display' pageid=$item.pageid assign='returnurl'}
    {notifydisplayhooks eventname='pages.hook.pages.ui.view' area='modulehook_area.pages.pages' subject=$item id=$item.pageid}
</div>