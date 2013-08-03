<?php
namespace Blight;

class PackageManager implements \Blight\Interfaces\PackageManager {
	const MANIFEST_FILE	= 'package.json';
	const HOOK_FUNCTION_PREFIX	= 'hook';

	/** @var \Blight\Interfaces\Blog */
	protected $blog;

	protected $packages;
	protected $plugins;

	/**
	 * @param \Blight\Interfaces\Blog $blog
	 */
	public function __construct(\Blight\Interfaces\Blog $blog){
		$this->blog	= $blog;

		$packages	= $this->loadPackages($blog->getPathPlugins());
		$this->packages	= $packages['package'];
		$this->plugins	= $packages['plugin'];
	}

	/**
	 * @param string $dir	The directory to load plugins from
	 * @return array		An multidimensional array of packages
	 *
	 * 		array(
	 * 			'packages'	=> array(Package, ...)	// All packages
	 * 			'plugins'	=> ...,	// Plugins only
	 * 		)
	 */
	protected function loadPackages($dir){
		$packages	= array();

		$packageTypes	= array(
			'package'	=> '\Blight\Interfaces\Models\Packages\Package',
			'plugin'	=> '\Blight\Interfaces\Models\Packages\Plugin'
		);

		foreach($packageTypes as $type => $interface){
			$packages[$type]	= array();
		}

		$packgeDirs	= glob(rtrim($dir,'/').'/*');
		foreach($packgeDirs as $dir){
			$isPhar	= (pathinfo($dir, \PATHINFO_EXTENSION) == 'phar');
			if(!$isPhar && !is_dir($dir)){
				// Not a valid plugin
				continue;
			}

			$packageName	= basename($dir);

			if($isPhar){
				$packageName	= substr($packageName, 0, -1*strlen('.phar'));
				$dir	= 'phar://'.$dir;
			}

			try {
				$package	= $this->initialisePackage($packageName, $dir);
			} catch(\Exception $e){
				continue;
			}

			// Group package
			foreach($packageTypes as $type => $interface){
				if(!($package instanceof $interface)){
					continue;
				}

				$packages[$type][$packageName]	= $package;
			}
		}

		return $packages;
	}

	/**
	 * Loads a package manifest file and instantiates the main class for the package
	 *
	 * @param string $name		The name of the package
	 * @param string $directory	The directory of the package files
	 * @return \Blight\Interfaces\Models\Packages\Package
	 * @throws \RuntimeException	Package is missing required files
	 * @throws \RuntimeException	Package does not implement \Blight\Interfaces\Models\Packages\Package
	 */
	protected function initialisePackage($name, $directory){
		$directory	= rtrim($directory, '/');
		$packageInitialiser	= $directory.'/'.$name.'.php';
		$packageManifest	= $directory.'/'.self::MANIFEST_FILE;

		if(!file_exists($packageInitialiser) || !file_exists($packageManifest)){
			// Plugin missing initialisation file
			throw new \RuntimeException('Package files missing ('.$packageInitialiser.': '.(file_exists($packageInitialiser) ? 1 : 0).', '.$packageManifest.': '.(file_exists($packageManifest) ? 1 : 0).')');
		}

		// Parse manifest
		$config	= \Blight\Utilities::arrayMultiMerge(array(
			'package'	=> array(
				'namespace'	=> '\\'
			)
		), $this->parseManifest($this->blog->getFileSystem()->loadFile($packageManifest)));

		$minVersion	= $config['compatibility']['minimum'];
		if(version_compare($minVersion, \Blight\Blog::VERSION, '>')){
			// Needs newer version
			throw new \RuntimeException('Package requires version '.$config['package']['version']);
		}

		$class	= rtrim($config['package']['namespace'], '\\').'\\'.$name;

		$config['path']	= $directory.'/';

		// Initialise plugin
		include($packageInitialiser);
		$instance	= new $class($this->blog, $config);

		if(!($instance instanceof \Blight\Interfaces\Models\Packages\Package)){
			// Invalid class
			throw new \RuntimeException('Invalid package class type');
		}

		return $instance;
	}

	/**
	 * @param string $content	The raw content from the manifest file
	 * @return array			The processed manifest data
	 */
	protected function parseManifest($content){
		$parser	= new \Blight\Config();
		return $parser->unserialize($content);
	}

	/**
	 * @param string $themeName	The name of the theme to retrieve
	 * @return \Blight\Interfaces\Models\Packages\Theme
	 * @throws \RuntimeException	Theme not found
	 * @throws \RuntimeException	Invalid theme package
	 */
	public function getTheme($themeName){
		$path	= $this->blog->getPathThemes($themeName.'.phar');
		if(!file_exists($path)){
			throw new \RuntimeException('Theme `'.$themeName.'` not found');
		}

		$isPhar	= (pathinfo($path, \PATHINFO_EXTENSION) == 'phar');
		if($isPhar){
			$path	= 'phar://'.$path;
		}
		$theme	= $this->initialisePackage($themeName, $path);
		if(!($theme instanceof \Blight\Interfaces\Models\Packages\Theme)){
			throw new \RuntimeException('Theme does not implement \Blight\Interfaces\Models\Packages\Theme');
		}

		return $theme;
	}

	/**
	 * Runs a hook through plugins
	 *
	 * @param string $hook	The name of the hook to run
	 * @param array|null $params	An array of parameters to pass to plugins. Editable parameters must be passed by reference:
	 *
	 * 		$value	= 1;
	 * 		doHook('hook_name', array(
	 * 			'param'	=> &$value
	 *  	));
	 *
	 * @param \Blight\Interfaces\Models\Packages\Plugin|null $theme	An optional theme to also apply hooks to
	 */
	public function doHook($hook, $params = null, \Blight\Interfaces\Models\Packages\Plugin $theme = null){
		$callbackName	= static::HOOK_FUNCTION_PREFIX.$hook;

		$plugins	= $this->plugins;
		if(isset($theme)){
			$plugins[]	= $theme;
		}

		foreach($plugins as $plugin){
			// Run hook
			$callback	= array($plugin, $callbackName);
			if(is_callable($callback)){
				call_user_func($callback, $params);
			}
		}
	}
};