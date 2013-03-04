<?php
namespace Blight;

/**
 * Stores configuration data for the blog
 */
class Blog {
	protected $config;

	/** @var \Blight\FileSystem */
	protected $file_system;

	protected $root_path;
	protected $url;
	protected $name;
	protected $paths;

	/**
	 * Processes all configuration settings for the blog
	 *
	 * @param array $config	An associative array of config settings
	 */
	public function __construct($config){
		$this->root_path	= rtrim($config['root_path'], '/').'/';

		$fields	= array(
			'url',
			'name'
		);
		foreach($fields as $field){
			$this->{$field}	= $config[$field];
		}

		foreach($config['paths'] as $key => $path){
			if($path[0] != '/'){
				$config['paths'][$key]	= $this->get_path_root($path);
			}
			$config['paths'][$key]	= rtrim($config['paths'][$key], '/').'/';
		}
		$this->paths	= array(
			'www'		=> $config['paths']['web'],
			'posts'		=> $config['paths']['posts'],
			'templates'	=> $config['paths']['templates'],
			'cache'		=> $config['paths']['cache']
		);

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
		return $this->get_path_root('blight/'.$append);
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
		return isset($this->config['description']) ? $this->config['description'] : null;
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
	 * @return \Blight\FileSystem	The common FileSystem object
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