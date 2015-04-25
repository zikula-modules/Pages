{pageaddvar name='javascript' value='modules/Pages/javascript/Zikula.Pages.Admin.Modify.js'}

{adminheader}
<h3>
    <span class="fa fa-edit"></span>
    {gt text='Update page'}
</h3>

{form cssClass="form-horizontal"}
{formvalidationsummary}
    <fieldset>
        <legend>{gt text='Content'}</legend>
        <div class="form-group">
            {formlabel cssClass="col-lg-3" for="title" __text='Title'}
            <div class="col-lg-9">
                {formtextinput id="title" cssClass="form-control" maxLength="255" mandatory=true}
            </div>
        </div>
        {if $modvars.Pages.showpermalinkinput}
        <div class="form-group">
            {formlabel cssClass="col-lg-3" for="urltitle" __text='PermaLink URL title'}
            <div class="col-lg-9">
                {formtextinput id="urltitle" cssClass="form-control" maxLength="255"}
                <span class="help-block">{gt text='(Blank = auto-generate)'}</span>
            </div>
        </div>
        {/if}
        {if $modvars.Pages.enablecategorization}
        {foreach from=$registries item="registryCid" key="registryId"}
            <div class="form-group">
                {formlabel cssClass="col-lg-3" for="category_`$registryId`" __text="Category"}
                <div class="col-lg-9">
                    {formcategoryselector id="category_`$registryId`" category=$registryCid dataField="categories" group='page' registryId=$registryId doctrine2=true includeEmptyElement=true cssClass='form-control' editLink=false}
                </div>
            </div>
        {/foreach}
        {/if}
        {if $modvars.ZConfig.multilingual}
        <div class="form-group">
            {formlabel cssClass="col-lg-3" for="language" __text='Language'}
            <div class="col-lg-9">
                {formlanguageselector id="language" cssClass='form-control'}
            </div>
        </div>
        {/if}
        <div class="form-group">
            {formlabel cssClass="col-lg-3" for="content" __text='Content'}
            <div class="col-lg-9">
                {formtextinput textMode="multiline" id="content"  cssClass="form-control"}
                <em class="help-block">{gt text='If you want multiple pages you can write &lt;!--pagebreak--&gt; where you want to cut.'}</em>
            </div>
        </div>
    </fieldset>
    <fieldset>
        <legend>{gt text='Meta tags'}</legend>
        <div class="form-group">
            {formlabel cssClass="col-lg-3" for="metadescription" __text='Description'}
            <div class="col-lg-9">
                {formtextinput textMode="multiline" id="metadescription" rows="2" cols="50" cssClass="form-control noeditor"}
            </div>
        </div>
        <div class="form-group">
            {formlabel cssClass="col-lg-3" for="metakeywords" __text='Keywords'}
            <div class="col-lg-9">
                {formtextinput textMode="multiline" id="metakeywords" rows="2" cols="50" cssClass="form-control noeditor"}
            </div>
        </div>
    </fieldset>
    <fieldset>
        <legend><a id="pages_settings_collapse" href="#"><i class="fa fa-plus hide"></i> {gt text='Specific page settings'}</a></legend>
        <div id="pages_settings_details">
            <div class="form-group">
                {formlabel cssClass="col-lg-3" for="displaywrapper" __text='Display additional information'}
                <div class="col-lg-9">
                    {formcheckbox id="displaywrapper"}
                </div>
            </div>
            <div class="form-group">
                {formlabel cssClass="col-lg-3" for="displaytitle" __text='Display page title'}
                <div class="col-lg-9">
                    {formcheckbox id="displaytitle"}
                </div>
            </div>
            <div class="form-group">
                {formlabel cssClass="col-lg-3" for="displaycreated" __text='Display page creation date'}
                <div class="col-lg-9">
                    {formcheckbox id="displaycreated"}
                </div>
            </div>
            <div class="form-group">
                {formlabel cssClass="col-lg-3" for="displayupdated" __text='Display page update date'}
                <div class="col-lg-9">
                    {formcheckbox id="displayupdated"}
                </div>
            </div>
            <div class="form-group">
                {formlabel cssClass="col-lg-3" for="displaytextinfo" __text='Display page text statistics'}
                <div class="col-lg-9">
                    {formcheckbox id="displaytextinfo"}
                </div>
            </div>
            <div class="form-group">
                {formlabel cssClass="col-lg-3" for="displayprint" __text='Display page print link'}
                <div class="col-lg-9">
                    {formcheckbox id="displayprint"}
                </div>
            </div>
        </div>
    </fieldset>
    <fieldset>
        <legend><a id="pages_meta_collapse" href="#"><i class="fa fa-plus hide"></i> {gt text='Meta data'}</a></legend>
        <div id="pages_meta_details">
            <ul>
                {usergetvar name='uname' uid=$cr_uid assign='username'}
                <li>{gt text='Created by %s' tag1=$username}</li>
                <li>{gt text='Created on %s' tag1=$cr_date|dateformat}</li>
                {usergetvar name='uname' uid=$lu_uid assign='username'}
                <li>{gt text='Last update by %s' tag1=$username}</li>
                <li>{gt text='Updated on %s' tag1=$lu_date|dateformat}</li>
            </ul>
        </div>
    </fieldset>

    {if !empty($pageid)}
    {notifydisplayhooks eventname='pages.ui_hooks.pages.form_edit' id=$pageid}
    {else}
    {notifydisplayhooks eventname='pages.ui_hooks.pages.form_edit' id=null}
    {/if}

    <div class="form-group">
        <div class="col-lg-offset-3 col-lg-9">
            {formbutton class="btn btn-success" commandName="save" __text="Save"}
            {formbutton class="btn btn-danger" commandName="remove" __text="Remove"}
            {formbutton class="btn btn-default" commandName="cancel" __text="Cancel"}
        </div>
    </div>
{/form}
{adminfooter}