<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

class Pages_Version extends Zikula_Version
{
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname']    = $this->__('Static pages');
        $meta['description']    = $this->__('Manager of the static pages of the site.');
        //! this defines the module's url
        $meta['url']            = $this->__('pages');
        $meta['version']        = '2.4.2';

        $meta['securityschema'] = array('Pages::'         => 'Page name::Page ID',
                'Pages:category:' => 'Category ID::');
        return $meta;
    }
}