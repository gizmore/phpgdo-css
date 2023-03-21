<?php
namespace GDO\CSS;

use GDO\Core\CSS;
use GDO\Core\GDO_Module;
use GDO\Core\GDT_Checkbox;
use GDO\UI\GDT_Textarea;

/**
 * CSS related settings and toolchain utilities.
 *
 * @version 7.0.1
 * @since 6.10.5
 * @author gizmore
 */
final class Module_CSS extends GDO_Module
{

	public int $priority = 100;
	public string $license = 'MIT';

	public function onLoadLanguage(): void
	{
		$this->loadLanguage('lang/css');
	}

	public function onIncludeScripts(): void
	{
		CSS::addInline($this->cfgCustomCSS());
	}

	# #############
	# ## Config ###
	# #############

	public function cfgCustomCSS(): string
	{
		return (string)$this->getConfigVar('custom_css');
	}

	public function getConfig(): array
	{
		return [
			GDT_Checkbox::make('minify_css')->initial('0'),
			GDT_Textarea::make('custom_css'),
		];
	}

	public function cfgMinify(): string
	{
		return $this->getConfigVar('minify_css');
	}

	# #############
	# ## Loader ###
	# #############

	public function includeMinifier(): void
	{
		spl_autoload_register([
			$this,
			'psr',
		]);
	}

	/**
	 * Not psr but gizmore bullshit autoloader.
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
