<?php
namespace Blight\Interfaces;

interface Blog {
	/**
	 * @param $config* @param array $config	An associative array of config settings
	 * @throws \InvalidArgumentException	The config settings provided are incomplete
	 */
	public function __construct($config);

	/**
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The root path, with the provided string appended
	 */
	public function getPathRoot($append = '');

	/**
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The cache directory path, with the provided string appended
	 */
	public function getPathCache($append = '');

	/**
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The path, with the provided string appended
	 * @see getRootPath()
	 */
	public function getPathApp($append = '');

	/**
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The path, with the provided string appended
	 * @see getRootPath()
	 */
	public function getPathThemes($append = '');

	/**
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The path, with the provided string appended
	 * @see getRootPath()
	 */
	public function getPathPlugins($append = '');

	/**
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The path, with the provided string appended
	 * @see getRootPath()
	 */
	public function getPathAssets($append = '');

	/**
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The path, with the provided string appended
	 * @see getRootPath()
	 */
	public function getPathPages($append = '');

	/**
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The path, with the provided string appended
	 * @see getRootPath()
	 */
	public function getPathPosts($append = '');

	/**
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The path, with the provided string appended
	 * @see getRootPath()
	 */
	public function getPathDrafts($append = '');

	/**
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The path, with the provided string appended
	 * @see getRootPath()
	 */
	public function getPathDraftsWeb($append = '');

	/**
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The path, with the provided string appended
	 * @see getRootPath()
	 */
	public function getPathWWW($append = '');

	/**
	 * @param string $append	An additonal URL fragment to append to the path
	 * @return string			The URL, with the provided string appended
	 */
	public function getURL($append = '');

	/**
	 * @return string	The blog name
	 */
	public function getName();

	/**
	 * @return string|null	The blog description if set, or null
	 */
	public function getDescription();

	/**
	 * @return string	The URL to the site feed
	 */
	public function getFeedURL();

	/**
	 * @return \Blight\Interfaces\FileSystem	The common FileSystem object
	 */
	public function getFileSystem();

	/**
	 * @return \Blight\Interfaces\PackageManager
	 */
	public function getPackageManager();

	/**
	 * @return \Blight\Interfaces\Packages\Theme
	 */
	public function getTheme();

	/**
	 * @return bool	Whether the blog is a linkblog
	 */
	public function isLinkblog();

	/**
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
	public function doHook($hook, $params = null);

	/**
	 * @param string $parameter		The name of the parameter to retrieve
	 * @param string|null $group	The settings group the parameter exists in
	 * @param mixed $default		The value to be returned if the requested parameter is not set
	 * @return mixed		The requested parameter's value or $default
	 */
	public function get($parameter, $group = null, $default = null);
};
