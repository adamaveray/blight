<?php
namespace Blight;

class Cache implements \Blight\Interfaces\Cache {
	const CACHE_SUBDIR	= 'blight';
	const FILE_EXT	= '.var';

	/** @var \Blight\Interfaces\Blog */
	protected $blog;

	protected $dir;

	/**
	 * @param Interfaces\Blog $blog
	 */
	public function __construct(\Blight\Interfaces\Blog $blog){
		$this->blog	= $blog;

		$this->dir	= $this->blog->getPathCache(self::CACHE_SUBDIR.'/');
	}

	/**
	 * @param string $key	The key the item was cached with
	 * @return mixed		The cached item's value
	 * @throws \RuntimeException	The cache item cannot be loaded
	 */
	public function get($key){
		$path	= $this->keyToPath($key);

		try {
			$value	= $this->blog->getFileSystem()->loadFile($path);
			$value	= unserialize($value);
		} catch(\Exception $e){
			throw new \RuntimeException('Cannot load cache item');
		}

		return $value;
	}

	/**
	 * @param string $key	The key to cache the value ender
	 * @param mixed $value	The value to be cached
	 * @throws \RuntimeException	The item cannot be cached
	 */
	public function set($key, $value){
		$path	= $this->keyToPath($key);
		$value	= serialize($value);

		try {
			$this->blog->getFileSystem()->createFile($path, $value);
		} catch(\Exception $e){
			echo $e->getMessage().PHP_EOL;
			throw new \RuntimeException('Cannot save cache item');
		}
	}

	/**
	 * @param string $key	The key to build a path from
	 * @return string		The path for the key
	 */
	protected function keyToPath($key){
		preg_match('/^(\w*?)\\|\\|(.*?)$/i', $key, $matches);

		$prefix	= '';
		if(isset($matches[1])){
			$prefix	= $matches[1].'-';
			$key	= $matches[2];
		}

		return $this->dir.$prefix.sha1($key).self::FILE_EXT;
	}
};
