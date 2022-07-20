<?php
namespace GDO\CSS;

use GDO\Core\CSS;
use GDO\Core\Module_Core;
use GDO\Util\FileUtil;
use GDO\Util\Strings;
use GDO\DB\Database;
use MatthiasMullie\Minify\CSS as Mullifier;

final class Minifier
{
    private static string $HASH;
    
    public static function assetPath($append='')
    {
        return GDO_PATH . 'assets/' . self::getHash() . $append;
    }
    
    public static function assetHref($append='')
    {
        return GDO_WEB_ROOT . 'assets/' . self::getHash() . '/' . $append;
    }
    
    public static function renderMinified()
    {
        if (!is_file(self::assetPath('/css.css')))
        {
            try
            {
                Database::instance()->lock('MINIFY_CSS');
                if (!is_file(self::assetPath('/css.css')))
                {
                    self::minify();
                }
            }
            catch (\Throwable $e)
            {
                throw $e;
            }
            finally
            {
                Database::instance()->unlock('MINIFY_CSS');
            }
        }
        
        $back = '';

        foreach (CSS::$EXTERNAL as $path)
        {
            $back .= sprintf("\t<link rel=\"stylesheet\" href=\"%s\" /> \n", $path);
        }
        
        $v = Module_Core::instance()->nocacheVersion();
        $href = self::assetHref('css.css?'.$v);
        $back .= '<link rel="stylesheet" href="'.$href.'" />' . "\n";
        return $back;
    }
    
    public static function renderOriginal()
    {
        $back = '';
        
        foreach (CSS::$EXTERNAL as $path)
        {
            $back .= sprintf("\t<link rel=\"stylesheet\" href=\"%s\" /> \n", $path);
        }
        
        foreach (CSS::$FILES as $path)
        {
            $back .= sprintf("\t<link rel=\"stylesheet\" href=\"%s\" /> \n", $path);
        }
        
        if (CSS::$INLINE)
        {
            $back .= sprintf("\t<style><!--\n\t%s\n\t--></style>\n",
                self::$INLINE);
        }
        
        return $back;
    }
    
    private static function minify()
    {
        Module_CSS::instance()->includeMinifier();
        
        $dir = self::assetPath();
        FileUtil::createDir($dir);
        
        $minifier = new Mullifier();
        foreach (CSS::$FILES as $file)
        {
            $file = self::hrefToPath($file);
            $minifier->addFile($file);
        }
        $minifier->add(CSS::$INLINE);
        $minifier->minify(self::assetPath('/css.css'));
    }
    
    private static function hrefToPath($href)
    {
        $path = Strings::substrFrom($href, GDO_WEB_ROOT);
        $path = Strings::substrTo($path, '?', $path);
        return GDO_PATH . $path;
    }
    
    private static function getHash()
    {
    	if (!isset(self::$HASH))
        {
            $data = '';
            foreach (CSS::$FILES as $path)
            {
                $data .= $path;
            }
            $data .= CSS::$INLINE;
            $data .= Module_Core::instance()->nocacheVersion();
            self::$HASH = md5($data);
        }
        return self::$HASH;
    }
    
}
