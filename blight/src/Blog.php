<?php
namespace Blight;

/**
 * Stores configuration data for the blog
 */
class Blog implements \Blight\Interfaces\Blog {
	protected $config;

	/** @var \Blight\Interfaces\FileSystem */
	protected $file_system;

	protected $root_path;
	protected $app_path;
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

		$this->root_path	= rtrim($config['root_path'], '/').'/';

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
			'pagen'			=> 'pages',
			'posts'			=> 'posts',
			'drafts'		=> 'drafts',
			'templates'		=> 'templates',
			'cache'			=> 'cache'
		);
		foreach($this->paths as $key => $config_key){
			if(!isset($config['paths'][$config_key])){
				throw new \InvalidArgumentException('Config is missing path `'.$config_key.'`');
			}

			$path	= $config['paths'][$config_key];

			if($path[0] != '/'){
				$path	= $this->get_path_root($path);
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
	public function get_path_root($append = ''){
		return $this->root_path.$append;
	}

	/**
	 * Returns the path to the cache directory
	 *
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The cache directory path, with the provided string appended
	 */
	public function get_path_cache($append = ''){
		return $this->paths['cache'].$append;
	}

	/**
	 * Returns the path to the application files directory
	 *
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The path, with the provided string appended
	 * @see get_root_path()
	 */
	public function get_path_app($append = ''){
		if(!isset($this->app_path)){
			$dir	= __DIR__;
			$path	= $this->get_path_root();
			if(class_exists('\Phar') && \Phar::running()){
				// Phar
				$path	= \Phar::running();
			} else {
				// Directory
				$stub	= explode('/', trim(str_replace($dir, '', $dir), '/'));
				$path	.= current($stub);
			}
			$this->app_path	= $path.'/';
		}
		return $this->app_path.$append;
	}

	/**
	 * Returns the path to the templates directory
	 *
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The path, with the provided string appended
	 * @see get_root_path()
	 */
	public function get_path_templates($append = ''){
		return $this->paths['templates'].$append;
	}

	/**
	 * Returns the path to the pagen directory
	 *
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The path, with the provided string appended
	 * @see get_root_path()
	 */
	public function get_path_pages($append = ''){
		return $this->paths['pagen'].$append;
	}

	/**
	 * Returns the path to the posts directory
	 *
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The path, with the provided string appended
	 * @see get_root_path()
	 */
	public function get_path_posts($append = ''){
		return $this->paths['posts'].$append;
	}

	/**
	 * Returns the path to the draft posts directory
	 *
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The path, with the provided string appended
	 * @see get_root_path()
	 */
	public function get_path_drafts($append = ''){
		return $this->paths['drafts'].$append;
	}

	/**
	 * Returns the path to the rendered drafts HTML directory
	 *
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The path, with the provided string appended
	 * @see get_root_path()
	 */
	public function get_path_drafts_web($append = ''){
		return $this->paths['drafts-web'].$append;
	}

	/**
	 * Returns the path to the web directory
	 *
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The path, with the provided string appended
	 * @see get_root_path()
	 */
	public function get_path_www($append = ''){
		return $this->paths['www'].$append;
	}

	/**
	 * Returns the URL to the root of the site
	 *
	 * @param string $append	An additonal URL fragment to append to the path
	 * @return string			The URL, with the provided string appended
	 */
	public function get_url($append = ''){
		return $this->url.$append;
	}

	/**
	 * @return string	The blog name
	 */
	public function get_name(){
		return $this->name;
	}

	/**
	 * @return string|null	The blog description if set, or null
	 */
	public function get_description(){
		return isset($this->config['site']['description']) ? $this->config['site']['description'] : null;
	}

	/**
	 * @return string	The URL to the site feed
	 */
	public function get_feed_url(){
		return $this->get_url().'feed';
	}

	/**
	 * Provides access throughout the application to a common instance of the FileSystem utility class
	 *
	 * @return \Blight\Interfaces\FileSystem	The common FileSystem object
	 */
	public function get_file_system(){
		if(!isset($this->file_system)){
			$this->file_system	= new FileSystem($this);
		}

		return $this->file_system;
	}

	/**
	 * @return string	The line ending
	 */
	public function get_eol(){
		return "\n";
	}

	/**
	 * @return bool	Whether the blog is a linkblog
	 */
	public function is_linkblog(){
		return (bool)$this->get('linkblog', 'linkblog', false);
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