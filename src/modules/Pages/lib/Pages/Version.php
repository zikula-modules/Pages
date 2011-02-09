<?php
class Pages_Version extends Zikula_Version
{
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname'] = $this->__('Static pages');
        $meta['description'] = $this->__('Manager of the static pages of the site.');
        //! this defines the module's url
        $meta['url'] = $this->__('pages');
        $meta['version'] = '2.5.0';

        $meta['securityschema'] = array('Pages::' => 'Page name::Page ID',
                'Pages:category:' => 'Category ID::');
        return $meta;
    }
}