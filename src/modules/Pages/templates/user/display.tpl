{pagesetvar name='title' value=$title}
{insert name='getstatusmsg'}

<div class="pages_page_container">
    {if $displaytitle}
    <h2>{$title|safehtml}</h2>
    {/if}

    {if $displaywrapper or $displaycreated or $displayupdated}
    <div class="pages_page_header">
        <ul>
            {if $displaycreated and $cr_uid}
            {usergetvar name='uname' uid=$cr_uid assign='cr_uname'}
            <li>{gt text='Created by %1$s on %2$s' tag1=$cr_uname|userprofilelink tag2=$cr_date|dateformat}</li>
            {/if}
            {if $displayupdated and $lu_uid}
            {usergetvar name='uname' uid=$lu_uid assign='lu_uname'}
            <li>{gt text='Last update by %1$s on %2$s' tag1=$lu_uname|userprofilelink tag2=$lu_date|dateformat}</li>
            {/if}
            {if $__CATEGORIES__}
            <li>{gt text='Categories'}:
                {foreach from=$__CATEGORIES__ key='property' item='category'}
                {if $category.accessible}
                {if $shorturls and $shorturlstype eq 0}
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
        {$content|safehtml}{*WAS$content|safehtml|modcallhooks:'Pages'*}
    </div>

    {if $displayprint or $displaytextinfo or $displayeditlink}
    <div class="pages_page_footer">
        {if $displayeditlink}
        <a href="{modurl modname='Pages' type='admin' func='modify' pageid=$pageid}">{gt text='Edit'}</a>
        <span class="text_separator">|</span>
        {/if}
        {if $displaytextinfo}
        {gt text='%s total words in this text' tag1=$content|count_words}
        <span class="text_separator">|</span>
        {gt text='%s reads' tag1=$counter}
        {/if}
        {if $displayprint}
        <span class="pages_page_printerlink">
            <a href="{modurl modname='Pages' func='display' pageid=$pageid theme='Printer'}">{img modname='core' src='printer1.gif' set='icons/small' __alt='Print page'}</a>
        </span>
        {/if}
    </div>
    {/if}

    {pager rowcount=$pager.numitems limit=$pager.itemsperpage posvar='page'}

    {modurl modname='Pages' func='display' pageid=$pageid assign='returnurl'}
    {*modcallhooks hookobject='item' hookaction='display' hookid=$pageid module='Pages' returnurl=$returnurl*}
</div>