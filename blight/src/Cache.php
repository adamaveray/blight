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
			return null;
		}

		return $value;
	}

	/**
	 * @param string|array $key	The key to cache the value ender
	 * @param mixed|null $value	The value to be cached
	 * @throws \RuntimeException	The item cannot be cached
	 */
	public function set($key, $value = null){
		if(is_array($key)){
			foreach($key as $k => $v){
				$this->set($k, $v);
			}

			return;
		}

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
		return $this->dir.$prefix.sha1($key).self::FILE_EXT;
	}
};
