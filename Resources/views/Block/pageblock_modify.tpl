{pageaddvar name='javascript' value='@ZikulaPagesModule/Resources/public/js/select2/select2.min.js'}
{pageaddvar name='stylesheet' value='@ZikulaPagesModule/Resources/public/js/select2/select2.css'}
{pageaddvar name='stylesheet' value='@ZikulaPagesModule/Resources/public/js/select2/select2-bootstrap.css'}

<div class="form-group">
    <label for="htmlpages_pid" class="col-lg-3 control-label">
        {gt text='Page' domain='module_pages'}
    </label>

    <div class="col-lg-9">
        <select id="htmlpages_pid" name='pid' class="form-control">
            {foreach from=$pages item="page"}
            <option value="{$page.pageid|safehtml}" {if isset($pid) && $page.pageid == $pid}selected="selected"{/if} >
                {$page.title|safehtml}
            </option>
            {/foreach}
        </select>
    </div>
</div>

<script type="text/javascript">
    jQuery('#htmlpages_pid').select2({
        "formatNoMatches": function () {
            return "{{gt text="No match found"}}";
        }
    });
</script>