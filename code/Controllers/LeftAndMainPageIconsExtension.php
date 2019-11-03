<?php

namespace SilverStripe\CMS\Controllers;

use Psr\SimpleCache\CacheInterface;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Convert;
use SilverStripe\Core\Extension;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\View\Requirements;

/**
 * Extension to include custom page icons
 */
class LeftAndMainPageIconsExtension extends Extension
{

    public function init()
    {
        Requirements::customCSS($this->generatePageIconsCss(), CMSMain::PAGE_ICONS_ID);
    }

    /**
     * Include CSS for page icons. We're not using the JSTree 'types' option
     * because it causes too much performance overhead just to add some icons.
     *
     * @return string CSS
     */
    public function generatePageIconsCss()
    {
        /** @var CacheInterface $cache */
        $cache = Injector::inst()->get(CacheInterface::class . '.SiteTree_PageIcons');

        if ($cache->has('css')) {
            return $cache->get('css');
        }

        $css = '';
        $classes = ClassInfo::subclassesFor(SiteTree::class);
        foreach ($classes as $class) {
            if (!empty(Config::inst()->get($class, 'icon_class', Config::UNINHERITED))) {
                continue;
            }
            $iconURL = SiteTree::singleton($class)->getPageIconURL();
            if ($iconURL) {
                $cssClass = Convert::raw2htmlid($class);
                $selector = sprintf('.page-icon.class-%1$s, li.class-%1$s > a .jstree-pageicon', $cssClass);
                $css .= sprintf('%s { background: transparent url(\'%s\') 0 0 no-repeat; }', $selector, $iconURL);
            }
        }

        $cache->set('css', $css);

        return $css;
    }
}
