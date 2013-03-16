<?php
namespace Blight\Packages;

abstract class Package implements \Blight\Interfaces\Packages\Package {
	const TWITTER_URL	= 'https://twitter.com/%s';

	/** @var \Blight\Interfaces\Blog $blog */
	protected $blog;
	/** @var array $config */
	protected $config;

	protected $path;

	final public function __construct(\Blight\Interfaces\Blog $blog, $config = null){
		$this->blog	= $blog;

		$this->config	= $this->load_config($config);

		if(isset($this->config['path'])){
			$this->path	= $this->config['path'];
		}

		$this->setup();
	}

	final protected function load_config($config){
		$config	= (array)$config;

		// Parse authors
		$normalise_author	= function($author){
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
			$config['author']	= $normalise_author($config['author']);
		}
		if(isset($config['contributors']) && is_array($config['contributors'])){
			$config['contributors']	= array_map($normalise_author, $config['contributors']);
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
