{adminheader}
<h3>
    <span class="fa fa-trash-o"></span>
    {gt text='Delete page'}
</h3>

<p class="alert alert-warning">{gt text='Do you really want to delete this page?'}</p>

{form cssClass="form"}
    {formvalidationsummary}
    <div class="">
        {formbutton class="btn btn-danger" commandName="save" __text="Delete"}
        {formbutton class="btn btn-default" commandName="cancel" __text="Cancel"}
    </div>
{/form}
{adminfooter}