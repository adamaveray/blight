<?php
namespace Blight;

/**
 * Stores configuration data for the blog
 */
class Blog implements \Blight\Interfaces\Blog {
	const VERSION	= '0.6.0';

	protected $config;

	/** @var \Blight\Interfaces\FileSystem */
	protected $fileSystem;

	/** @var \Blight\Interfaces\PackageManager */
	protected $packageManager;

	/** @var \Blight\Interfaces\Packages\Theme */
	protected $theme;


	protected $rootPath;
	protected $appPath;
	protected $url;
	protected $name;
	protected $paths;

	/**
	 * Processes all configuration settings for the blog
	 *
	 * @param array $config	An associative array of config settings
	 * @throws \InvalidArgumentException	The config settings provided are incomplete
	 */
	public function __construct($config){
		if(!is_array($config)){
			throw new \InvalidArgumentException('Config must be provided as an array');
		}

		$this->rootPath	= rtrim($config['root_path'], '/').'/';

		if(!isset($config['site'])){
			throw new \InvalidArgumentException('Config is missing `site`');
		}
		$fields	= array(
			'url',
			'name'
		);
		foreach($fields as $field){
			if(!isset($config['site'][$field])){
				throw new \InvalidArgumentException('Config is missing site setting `'.$field.'`');
			}
			$this->{$field}	= $config['site'][$field];
		}

		if(!isset($config['paths'])){
			throw new \InvalidArgumentException('Config is missing `paths`');
		}

		$this->paths	= array(
			'www'			=> 'web',
			'drafts-web'	=> 'drafts-web',
			'pages'			=> 'pages',
			'posts'			=> 'posts',
			'drafts'		=> 'drafts',
			'themes'		=> 'themes',
			'plugins'		=> 'plugins',
			'assets'		=> 'assets',
			'cache'			=> 'cache'
		);
		foreach($this->paths as $key => $configKey){
			if(!isset($config['paths'][$configKey])){
				throw new \InvalidArgumentException('Config is missing path `'.$configKey.'`');
			}

			$path	= $config['paths'][$configKey];

			if($path[0] != '/'){
				$path	= $this->getPathRoot($path);
			}
			$this->paths[$key]	= rtrim($path, '/').'/';
		}

		$this->config	= $config;
	}

	/**
	 * Returns the path to the application root
	 *
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The root path, with the provided string appended
	 */
	public function getPathRoot($append = ''){
		return $this->rootPath.$append;
	}

	/**
	 * Returns the path to the cache directory
	 *
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The cache directory path, with the provided string appended
	 */
	public function getPathCache($append = ''){
		return $this->paths['cache'].$append;
	}

	/**
	 * Returns the path to the application files directory
	 *
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The path, with the provided string appended
	 * @see get_root_path()
	 */
	public function getPathApp($append = ''){
		if(!isset($this->appPath)){
			$dir	= __DIR__;
			$path	= $this->getPathRoot();
			if(class_exists('\Phar') && \Phar::running()){
				// Phar
				$path	= \Phar::running();
			} else {
				// Directory
				$stub	= explode('/', trim(str_replace($dir, '', $dir), '/'));
				$path	.= current($stub);
			}
			$this->appPath	= $path.'/';
		}
		return $this->appPath.$append;
	}

	/**
	 * Returns the path to the themes directory
	 *
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The path, with the provided string appended
	 * @see get_root_path()
	 */
	public function getPathThemes($append = ''){
		return $this->paths['themes'].$append;
	}

	/**
	 * Returns the path to the plugins directory
	 *
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The path, with the provided string appended
	 * @see get_root_path()
	 */
	public function getPathPlugins($append = ''){
		return $this->paths['plugins'].$append;
	}

	/**
	 * Returns the path to the pages directory
	 *
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The path, with the provided string appended
	 * @see get_root_path()
	 */
	public function getPathPages($append = ''){
		return $this->paths['pages'].$append;
	}

	/**
	 * Returns the path to the posts directory
	 *
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The path, with the provided string appended
	 * @see get_root_path()
	 */
	public function getPathPosts($append = ''){
		return $this->paths['posts'].$append;
	}

	/**
	 * Returns the path to the assets directory
	 *
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The path, with the provided string appended
	 * @see get_root_path()
	 */
	public function getPathAssets($append = ''){
		return $this->paths['assets'].$append;
	}

	/**
	 * Returns the path to the draft posts directory
	 *
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The path, with the provided string appended
	 * @see get_root_path()
	 */
	public function getPathDrafts($append = ''){
		return $this->paths['drafts'].$append;
	}

	/**
	 * Returns the path to the rendered drafts HTML directory
	 *
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The path, with the provided string appended
	 * @see get_root_path()
	 */
	public function getPathDraftsWeb($append = ''){
		return $this->paths['drafts-web'].$append;
	}

	/**
	 * Returns the path to the web directory
	 *
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The path, with the provided string appended
	 * @see get_root_path()
	 */
	public function getPathWWW($append = ''){
		return $this->paths['www'].$append;
	}

	/**
	 * Returns the URL to the root of the site
	 *
	 * @param string $append	An additonal URL fragment to append to the path
	 * @return string			The URL, with the provided string appended
	 */
	public function getURL($append = ''){
		return $this->url.$append;
	}

	/**
	 * @return string	The blog name
	 */
	public function getName(){
		return $this->name;
	}

	/**
	 * @return string|null	The blog description if set, or null
	 */
	public function getDescription(){
		return isset($this->config['site']['description']) ? $this->config['site']['description'] : null;
	}

	/**
	 * @return string	The URL to the site feed
	 */
	public function getFeedURL(){
		return $this->getURL().'feed';
	}

	/**
	 * Provides access throughout the application to a common instance of the FileSystem utility class
	 *
	 * @return \Blight\Interfaces\FileSystem	The common FileSystem object
	 */
	public function getFileSystem(){
		if(!isset($this->fileSystem)){
			$this->fileSystem	= new FileSystem($this);
		}

		return $this->fileSystem;
	}

	/**
	 * @return \Blight\Interfaces\PackageManager
	 */
	public function getPackageManager(){
		if(!isset($this->packageManager)){
			$this->packageManager	= new \Blight\PackageManager($this);
		}

		return $this->packageManager;
	}

	/**
	 * @return \Blight\Interfaces\Packages\Theme
	 */
	public function getTheme(){
		if(!isset($this->theme)){
			$this->theme	= $this->getPackageManager()->getTheme($this->get('name', 'theme'));
		}

		return $this->theme;
	}

	/**
	 * @return bool	Whether the blog is a linkblog
	 */
	public function isLinkblog(){
		return (bool)$this->get('linkblog', 'linkblog', false);
	}

	/**
	 * Runs a hook through plugins
	 *
	 * @param string $hook	The name of the hook to run
	 * @param array|null $params	An array of parameters to pass to plugins. Parameters must be passed by reference:
	 *
	 * 		$value	= 1;
	 * 		doHook('hook_name', array(
	 * 			'param'	=> &$value
	 *  	));
	 *
	 * @see \Blight\PackageManager::doHook
	 */
	public function doHook($hook, $params = null){
		$this->getPackageManager()->doHook($hook, $params);
	}

	/**
	 * Retrieves settings from the blog configation
	 *
	 * @param string $parameter		The name of the parameter to retrieve
	 * @param string|null $group	The settings group the parameter exists in
	 * @param mixed $default		The value to be returned if the requested parameter is not set
	 * @return mixed		The requested parameter's value or $default
	 */
	public function get($parameter, $group = null, $default = null){
		$config	= $this->config;
		if(isset($group)){
			if(!isset($config[$group])){
				return $default;
			}
			$config	= $config[$group];
		}

		if(!isset($config[$parameter])){
			return $default;
		}

		return $config[$parameter];
	}
};