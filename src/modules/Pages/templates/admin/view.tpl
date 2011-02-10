{ajaxheader modname='Pages' filename='pages.js'}
{gt text='View pages list' assign='templatetitle'}

{include file='admin/menu.tpl'}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname='core' src='windowlist.gif' set='icons/large' alt=$templatetitle}</div>

    <h2>{$templatetitle}</h2>

    {if ($modvars.ZConfig.multilingual OR ($modvars.Pages.enablecategorization AND $numproperties > 0))}
    <form class="z-form" action="{modurl modname='Pages' type='admin' func='view'}" method="post" enctype="application/x-www-form-urlencoded">
        <fieldset{if $filter_active} class='filteractive'{/if}>
            {if $filter_active}{gt text='active' assign=filteractive}{else}{gt text='inactive" assign=filteractive}{/if}
            <legend>{gt text='Filter %1$s, %2$s page listed' plural='Filter %1$s, %2$s pages listed' count=$pager.numitems tag1=$filteractive tag2=$pager.numitems}</legend>
            <div id="pages_multicategory_filter">
                {if ($modvars.Pages.enablecategorization && $numproperties > 0)}
                <label for="pages_property">{gt text='Category'}</label>
                {gt text='Choose a category' assign='lblDef'}
                {nocache}
                {if $numproperties gt 1}
                {html_options id='pages_property' name='pages_property' options=$properties selected=$property}
                {else}
                <input type="hidden" id="pages_property" name="pages_property" value="{$property}" />
                {/if}
                <div id="pages_category_selectors">
                    {foreach from=$catregistry key='prop' item='cat'}
                    {assign var='propref' value=$prop|string_format:'pages_%s_category'}
                    {if $property eq $prop}
                    {assign var='selectedValue' value=$category}
                    {else}
                    {assign var='selectedValue' value=0}
                    {/if}
                    <noscript>
                        <div class="property_selector_noscript"><label for="{$propref}">{$prop}</label>:</div>
                    </noscript>
                    {selector_category category=$cat name=$propref selectedValue=$selectedValue allValue=0 allText=$lblDef editLink=false}
                    {/foreach}
                </div>
                {/nocache}
                {/if}
                {if $modvars.ZConfig.multilingual}
                &nbsp;&nbsp;&nbsp;&nbsp;
                <label for="pages_language">{gt text='Language'}</label>
                {nocache}
                {languagelist id='pages_language' name='language' all=true installed=true selected=$language}
                {/nocache}
                {/if}
                &nbsp;&nbsp;
                <span class="z-nowrap z-buttons">
                    <input class='z-bt-filter' name="submit" type="submit" value="{gt text='Filter'}" />
                    <a href="{modurl modname="Pages" type='admin' func='view'}" title="{gt text="Clear"}">{img modname=core src="button_cancel.gif" set="icons/extrasmall" __alt="Clear" __title="Clear"} {gt text="Clear"}</a>
                </span>
            </div>
        </fieldset>
    </form>
    {/if}

    <table class="z-datatable">
        <thead>
            <tr>
                <th>{gt text='ID'}</th>
                <th>{gt text='Title'}</th>
                <th>{gt text='Creator'}</th>
                {if $modvars.Pages.enablecategorization}
                <th>{gt text='Category'}</th>
                {/if}
                {if $modvars.ZConfig.multilingual}
                <th>{gt text='Language'}</th>
                {/if}
                <th>{gt text='Created'}</th>
                <th>{gt text='Actions'}</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$pages item='page'}
            <tr class="{cycle values='z-odd,z-even'}">
                <td>{$page.pageid|safehtml}</td>
                <td>{$page.title|safehtml}</td>
                {usergetvar uid=$page.cr_uid name='uname' assign='uname'}
                <td>{$uname|safehtml}</td>
                {if $modvars.Pages.enablecategorization}
                <td>{assignedcategorieslist item=$page}</td>
                {/if}
                {if $modvars.ZConfig.multilingual}
                <td>{$page.language|getlanguagename|safehtml}</td>
                {/if}
                <td>{$page.cr_date|dateformat|safehtml}</td>
                <td>
                    {assign var='options' value=$page.options}
                    {section name='options' loop=$options}
                    <a href="{$options[options].url|safetext}">{img modname='core' set='icons/extrasmall' src=$options[options].image title=$options[options].title alt=$options[options].title}</a>
                    {/section}
                </td>
            </tr>
            {foreachelse}
            {assign var='colspan' value=4}
            {if $modvars.Pages.enablecategorization}
            {assign var='colspan' value=$colspan+1}
            {/if}
            {if $modvars.ZConfig.multilingual}
            {assign var='colspan' value=$colspan+1}
            {/if}
            <tr class="z-datatableempty"><td colspan="{$colspan}">{gt text='No pages found.'}</td></tr>
            {/foreach}
        </tbody>
    </table>

    {pager rowcount=$pager.numitems limit=$pager.itemsperpage posvar='startnum' shift=1}
</div>