<?php
namespace Blight\Models\Packages;

abstract class Package implements \Blight\Interfaces\Models\Packages\Package {
	const TWITTER_URL	= 'https://twitter.com/%s';

	/** @var \Blight\Interfaces\Blog $blog */
	protected $blog;
	/** @var array $config */
	protected $config;

	protected $path;

	/**
	 * @param \Blight\Interfaces\Blog $blog
	 * @param array|null $config	The configuration data for the package
	 */
	final public function __construct(\Blight\Interfaces\Blog $blog, $config = null){
		$this->blog	= $blog;

		$this->config	= $this->loadConfig($config);

		if(isset($this->config['path'])){
			$this->path	= $this->config['path'];
		}

		$this->setup();
	}

	/**
	 * Processes and standardises the package configuration data
	 *
	 * @param array|null $config	The configuration data for the package
	 * @return array				The processed configuration data
	 */
	final protected function loadConfig($config){
		$config	= (array)$config;

		// Parse authors
		$normaliseAuthor	= function($author){
			if(is_array($author)){
				return $author;
			}

			// String only
			$url	= null;
			if($author[0] == '@'){
				// Twitter handle - build url
				$url	= sprintf(self::TWITTER_URL, substr($author, 1));
			}

			return array(
				'name'	=> $author,
				'url'	=> $url
			);
		};

		if(isset($config['author'])){
			$config['author']	= $normaliseAuthor($config['author']);
		}
		if(isset($config['contributors']) && is_array($config['contributors'])){
			$config['contributors']	= array_map($normaliseAuthor, $config['contributors']);
		}


		// Process version information
		if(!isset($config['compatibility']['max-tested'])){
			$config['compatibility']['max-tested']	= $config['compatibility']['minimum'];
		}

		return $config;
	}

	public function setup(){
		// To be implemented by packages
	}
};
