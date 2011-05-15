<?php
class Pages_Version extends Zikula_AbstractVersion
{
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname'] = $this->__('Static pages');
        $meta['description'] = $this->__('Manager of the static pages of the site.');
        $meta['version'] = '2.5.0';
        //! this defines the module's url
        $meta['url'] = $this->__('pages');
        $meta['core_min'] = '1.3.0'; // requires minimum 1.3.0 or later
        $meta['capabilities'] = array(HookUtil::SUBSCRIBER_CAPABLE => array('enabled' => true));
        $meta['securityschema'] = array('Pages::' => 'Page name::Page ID',
                'Pages:category:' => 'Category ID::');
        return $meta;
    }

    protected function setupHookBundles()
    {
        $bundle = new Zikula_HookManager_SubscriberBundle($this->name, 'subscriber_area.ui.pages.pages', 'ui', $this->__('Pages Hooks'));
        $bundle->addType('ui.view', 'pages.hook.pages.ui.view');
        $bundle->addType('ui.edit', 'pages.hook.pages.ui.edit');
        $bundle->addType('ui.delete', 'pages.hook.pages.ui.delete');
        $bundle->addType('validate.edit', 'pages.hook.pages.validate.edit');
        $bundle->addType('validate.delete', 'pages.hook.pages.validate.delete');
        $bundle->addType('process.edit', 'pages.hook.pages.process.edit');
        $bundle->addType('process.delete', 'pages.hook.pages.process.delete');
        $this->registerHookSubscriberBundle($bundle);

        $bundle = new Zikula_HookManager_SubscriberBundle($this->name, 'subscriber_area.filter.pages.pagesfilter', 'filter', $this->__('Pages Filter Hooks'));
        $bundle->addType('ui.filter', 'pages.hook.pagesfilter.ui.filter');
        $this->registerHookSubscriberBundle($bundle);
    }
}