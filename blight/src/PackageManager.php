<?php
namespace Blight;

class PackageManager implements \Blight\Interfaces\PackageManager {
	const MANIFEST_FILE	= 'package.json';
	const HOOK_FUNCTION_PREFIX	= 'hook_';

	/** @var \Blight\Interfaces\Blog */
	protected $blog;

	protected $plugins;

	/**
	 * @param \Blight\Interfaces\Blog $blog
	 */
	public function __construct(\Blight\Interfaces\Blog $blog){
		$this->blog	= $blog;

		$this->plugins	= $this->load_packages($blog->get_path_plugins());
	}

	/**
	 * @param string $dir	The directory to load plugins from
	 * @return array		An array of
	 */
	protected function load_packages($dir){
		$packages		= array();
		$packge_dirs	= glob(rtrim($dir,'/').'/*');
		foreach($packge_dirs as $dir){
			if(!is_dir($dir)){
				// Not a valid plugin
				continue;
			}

			$package_name	= basename($dir);

			try {
				$plugin	= $this->initialise_package($package_name, $dir);
			} catch(\Exception $e){
				continue;
			}

			$packages[$package_name]	= $plugin;
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