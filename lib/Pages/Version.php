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
        $meta['displayname']    = __('Static pages', $dom);
        $meta['description']    = __('Manager of the static pages of the site.', $dom);
        //! this defines the module's url
        $meta['url']            = __('pages', $dom);
        $meta['version']        = '2.4.2';
        $meta['contact']        = 'http://zikula.org/';

        $meta['securityschema'] = array('Pages::'         => 'Page name::Page ID',
                'Pages:category:' => 'Category ID::');
        return $meta;
    }
}