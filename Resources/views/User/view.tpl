{gt text='Pages list' assign=templatetitle}
{pagesetvar name='title' value=$templatetitle}
{insert name='getstatusmsg'}

<h2>{gt text='Category: %s' tag1=$categoryname} </h2>
<p>{gt text='Pages published under this category:'}</p>
<ul>
    {foreach item='page' from=$pages}
    {include file='User/pagelink.tpl'}
    {/foreach}
</ul>

{*pager show='page' rowcount=$pager.numitems limit=$pager.itemsperpage posvar='startnum' route='zikulapagesmodule_user_view'*}