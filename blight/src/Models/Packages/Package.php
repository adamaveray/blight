<?php
namespace Blight\Models\Packages;

abstract class Package implements \Blight\Interfaces\Models\Packages\Package {
	const TWITTER_URL	= 'https://twitter.com/%s';

	/** @var \Blight\Interfaces\Blog $blog */
	protected $blog;
	/** @var array $config */
	protected $config;
	/** @var array $data */
	protected $data;

	private $dataHash;

	protected $path;

	/**
	 * @param \Blight\Interfaces\Blog $blog
	 * @param array|null $config	The configuration data for the package
	 * @param array|null $data		The data for the package
	 */
	final public function __construct(\Blight\Interfaces\Blog $blog, $config = null){
		$this->blog	= $blog;

		$this->config	= $this->loadConfig($config);

		if(isset($this->config['path'])){
			$this->path	= $this->config['path'];
		}
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

	/**
	 * Loads the package's local data
	 *
	 * @param string $path	The path to the package's data file
	 * @return array		The data
	 */
	final protected function loadDataFromFile($path){
		try {
			return json_decode($this->blog->getFileSystem()->loadFile($path), true);

		} catch(\RuntimeException $e){
			// File invalid
			return array();
		}
	}

	/**
	 * Saves the package's local data to file
	 *
	 * @param array $data	The data to save
	 * @param string $path	The path to the package's data file
	 */
	final protected function saveDataToFile(array $data, $path){
		$this->blog->getFileSystem()->createFile($path, json_encode($data));
	}

	/**
	 * @return array	The data for the package
	 */
	final public function getRawData(){
		return $this->data;
	}

	/**
	 * @param array $data	The data for the package
	 * @return $this
	 */
	final public function setRawData(array $data){
		$this->data	= $data;
		return $this;
	}

	public function setup(){
		// To be implemented by packages
	}
};
