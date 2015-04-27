{adminheader}
<h3>
    <span class="fa fa-wrench"></span>
    {gt text='Settings'}
</h3>


{form cssClass="form-horizontal"}
{formvalidationsummary}
    <fieldset>
        <legend>{gt text='General settings'}</legend>
        <div class="form-group">
            {formlabel cssClass="col-lg-3 control-label" for="enablecategorization" __text='Enable categorization'}
            <div class="col-lg-9">
                {formcheckbox id="enablecategorization"}
            </div>
        </div>
        <div class="form-group">
            {formlabel cssClass="col-lg-3 control-label" for="itemsperpage" __text='Items per page'}
            <div class="col-lg-9">
                {formintinput cssClass="form-control" id="itemsperpage" size="3"}
            </div>
        </div>
    </fieldset>
    <fieldset>
        <legend>{gt text='New page defaults'}</legend>
        <div class="form-group">
            {formlabel cssClass="col-lg-3 control-label" for="displaywrapper" __text='Display additional information'}
            <div class="col-lg-9">
                {formcheckbox id="displaywrapper"}
            </div>
        </div>
        <div class="form-group">
            {formlabel cssClass="col-lg-3 control-label" for="displaytitle" __text='Display page title'}
            <div class="col-lg-9">
                {formcheckbox id="displaytitle"}
            </div>
        </div>
        <div class="form-group">
            {formlabel cssClass="col-lg-3 control-label" for="displaycreated" __text='Display page creation date'}
            <div class="col-lg-9">
                {formcheckbox id="displaycreated"}
            </div>
        </div>
        <div class="form-group">
            {formlabel cssClass="col-lg-3 control-label" for="displayupdated" __text='Display page update date'}
            <div class="col-lg-9">
                {formcheckbox id="displayupdated"}
            </div>
        </div>
        <div class="form-group">
            {formlabel cssClass="col-lg-3 control-label" for="displaytextinfo" __text='Display page text statistics'}
            <div class="col-lg-9">
                {formcheckbox id="displaytextinfo"}
            </div>
        </div>
        <div class="form-group">
            {formlabel cssClass="col-lg-3 control-label" for="displayprint" __text='Display page print link'}
            <div class="col-lg-9">
                {formcheckbox id="displayprint"}
            </div>
        </div>
    </fieldset>
    <fieldset>
        <legend>{gt text='Permalinks settings'}</legend>
        <div class="form-group">
            {formlabel cssClass="col-lg-3 control-label" for="addcategorytitletopermalink" __text='Add category title to permalink'}
            <div class="col-lg-9">
                {formcheckbox id="addcategorytitletopermalink"}
            </div>
        </div>
        <div class="form-group">
            {formlabel cssClass="col-lg-3 control-label" for="showpermalinkinput" __text='Show permalink input field'}
            <div class="col-lg-9">
                {formcheckbox id="showpermalinkinput"}
            </div>
        </div>
    </fieldset>
    <div class="form-group">
        <div class="col-lg-offset-3 col-lg-9">
            {formbutton class="btn btn-success" commandName="save" __text="Save"}
            {formbutton class="btn btn-default" commandName="cancel" __text="Cancel"}
        </div>
    </div>
{/form}
{adminfooter}