<?php
namespace Blight;

class PackageManager implements \Blight\Interfaces\PackageManager {
	const MANIFEST_FILE	= 'package.json';
	const HOOK_FUNCTION_PREFIX	= 'hook_';

	/** @var \Blight\Interfaces\Blog */
	protected $blog;

	protected $packages;
	protected $plugins;

	/**
	 * @param \Blight\Interfaces\Blog $blog
	 */
	public function __construct(\Blight\Interfaces\Blog $blog){
		$this->blog	= $blog;

		$packages	= $this->load_packages($blog->get_path_plugins());
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
	protected function load_packages($dir){
		$packages	= array();

		$package_types	= array(
			'package'	=> '\Blight\Interfaces\Packages\Package',
			'plugin'	=> '\Blight\Interfaces\Packages\Plugin'
		);

		foreach($package_types as $type => $interface){
			$packages[$type]	= array();
		}

		$packge_dirs	= glob(rtrim($dir,'/').'/*');
		foreach($packge_dirs as $dir){
			$is_phar	= (pathinfo($dir, \PATHINFO_EXTENSION) == 'phar');
			if(!$is_phar && !is_dir($dir)){
				// Not a valid plugin
				continue;
			}

			$package_name	= basename($dir);

			if($is_phar){
				$package_name	= substr($package_name, 0, -1*strlen('.phar'));
				$dir	= 'phar://'.$dir;
			}

			try {
				$package	= $this->initialise_package($package_name, $dir);
			} catch(\Exception $e){
				continue;
			}

			// Group package
			foreach($package_types as $type => $interface){
				if(!($package instanceof $interface)){
					continue;
				}

				$packages[$type][$package_name]	= $package;
			}
		}

		return $packages;
	}

	/**
	 * Loads a package manifest file and instantiates the main class for the package
	 *
	 * @param string $name		The name of the package
	 * @param string $directory	The directory of the package files
	 * @return \Blight\Interfaces\Packages\Package
	 * @throws \RuntimeException	Package is missing required files
	 * @throws \RuntimeException	Package does not implement \Blight\Interfaces\Packages\Package
	 */
	protected function initialise_package($name, $directory){
		$directory	= rtrim($directory, '/');
		$package_initialiser	= $directory.'/'.$name.'.php';
		$package_manifest		= $directory.'/'.self::MANIFEST_FILE;

		if(!file_exists($package_initialiser) || !file_exists($package_manifest)){
			// Plugin missing initialisation file
			throw new \RuntimeException('Package files missing');
		}

		// Parse manifest
		$config	= \Blight\Utilities::array_multi_merge(array(
			'namespace'	=> '\\'
		), $this->parse_manifest($this->blog->get_file_system()->load_file($package_manifest)));

		$class	= rtrim($config['namespace'], '\\').'\\'.$name;

		$config['path']	= $directory.'/';

		// Initialise plugin
		include($package_initialiser);
		$instance	= new $class($this->blog, $config);

		if(!($instance instanceof \Blight\Interfaces\Packages\Package)){
			// Invalid class
			throw new \RuntimeException('Invalid package class type');
		}

		return $instance;
	}

	/**
	 * @param string $content	The raw content from the manifest file
	 * @return array			The processed manifest data
	 */
	protected function parse_manifest($content){
		$parser	= new \Blight\Config();
		return $parser->unserialize($content);
	}

	/**
	 * @param string $theme_name	The name of the theme to retrieve
	 * @return \Blight\Interfaces\Packages\Theme
	 * @throws \RuntimeException	Theme not found
	 * @throws \RuntimeException	Invalid theme package
	 */
	public function get_theme($theme_name){
		$path	= $this->blog->get_path_themes($theme_name.'.phar');
		if(!file_exists($path)){
			throw new \RuntimeException('Theme `'.$theme_name.'` not found');
		}

		$is_phar	= (pathinfo($path, \PATHINFO_EXTENSION) == 'phar');
		if($is_phar){
			$path	= 'phar://'.$path;
		}
		$theme	= $this->initialise_package($theme_name, $path);
		if(!($theme instanceof \Blight\Interfaces\Packages\Theme)){
			throw new \RuntimeException('Theme does not implement \Blight\Interfaces\Packages\Theme');
		}

		return $theme;
	}

	/**
	 * Runs a hook through plugins
	 *
	 * @param string $hook	The name of the hook to run
	 * @param array|null $params	An array of parameters to pass to plugins. Parameters must be passed by reference:
	 *
	 * 		$value	= 1;
	 * 		do_hook('hook_name', array(
	 * 			'param'	=> &$value
	 *  	));
	 */
	public function do_hook($hook, $params = null){
		$callback_name	= static::HOOK_FUNCTION_PREFIX.$hook;

		foreach($this->plugins as $plugin){
			// Run hook
			$callback	= array($plugin, $callback_name);
			if(is_callable($callback)){
				call_user_func($callback, $params);
			}
		}
	}
};