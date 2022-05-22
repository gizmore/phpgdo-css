<?php
namespace GDO\CSS;

use GDO\Core\GDO_Module;
use GDO\Core\GDT_Checkbox;

/**
 * CSS related settings and toolchain utilities.
 * 
 * @author gizmore
 * @version 7.0.0
 * @since 6.10.5
 */
final class Module_CSS extends GDO_Module
{
    public function onLoadLanguage() : void { $this->loadLanguage('lang/css'); }
    
    public function getConfig() : array
    {
        return [
            GDT_Checkbox::make('minify_css')->initial('0'),
        ];
    }
    public function cfgMinify() : string { return $this->getConfigVar('minify_css'); }
    
    public function includeMinifier() : void
    {
        spl_autoload_register([$this, 'psr']);
    }

    /**
     * Not psr but gizmore bullshit autoloader.
     * 
     * @param string $classname
     */
    public function psr(string $classname)
    {
        $prefix = 'MatthiasMullie\\Minify\\';
        if (str_starts_with($classname, $prefix))
        {
            $classname = substr($classname, strlen($prefix));
            $path = str_replace('\\', '/', $classname);
            $path = GDO_PATH . 'GDO/CSS/css-minify/src/' . $path . '.php';
            require $path;
        }
        
        $prefix = 'MatthiasMullie\\PathConverter\\';
        if (str_starts_with($classname, $prefix))
        {
            $classname = substr($classname, strlen($prefix));
            $path = str_replace('\\', '/', $classname);
            $path = GDO_PATH . 'GDO/CSS/path-converter/src/' . $path . '.php';
            require $path;
        }
    }
    
}
