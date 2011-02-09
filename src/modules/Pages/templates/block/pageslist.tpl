<ul class="pages-list">
    {section name='items' loop=$items}
    <li>
        {if $items[items].url neq ''}
        <a href="{$items[items].url|safetext}">{$items[items].title|safehtml}{*WAS$items[items].title|modcallhooks:'Pages'|safehtml*}</a>
        {else}
        {$items[items].title|safehtml}{*WAS$items[items].title|safehtml|modcallhooks:'Pages'*}
        {/if}
    </li>
    {/section}
</ul>