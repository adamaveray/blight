<?php
namespace Blight\Interfaces;

interface Cache {
	/**
	 * @param Interfaces\Blog $blog
	 */
	public function __construct(\Blight\Interfaces\Blog $blog);

	/**
	 * @param string $key	The key the item was cached with
	 * @return mixed		The cached item's value
	 * @throws \RuntimeException	The cache item cannot be loaded
	 */
	public function get($key);

	/**
	 * @param string $key	The key to cache the value ender
	 * @param mixed $value	The value to be cached
	 * @throws \RuntimeException	The item cannot be cached
	 */
	public function set($key, $value);
};
