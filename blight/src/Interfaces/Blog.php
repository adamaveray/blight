<?php
namespace Blight\Interfaces;

interface Blog {
	/**
	 * @param $config* @param array $config	An associative array of config settings
	 * @throws \InvalidArgumentException	The config settings provided are incomplete
	 */
	public function __construct($config);

	/**
	 * @return bool	Whether the site is in debug mode
	 */
	public function isDebug();

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
	public function getPathData($append = '');

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

	/** @return \DateTimezone	The blog publishing timezone */
	public function getTimezone();

	/**
	 * @return string	The URL to the site feed
	 */
	public function getFeedURL();

	/**
	 * @return \Blight\Interfaces\FileSystem	The common FileSystem object
	 */
	public function getFileSystem();

	/**
	 * @param \Blight\Interfaces\FileSystem $fileSystem
	 */
	public function setFileSystem(\Blight\Interfaces\FileSystem $fileSystem);

	/**
	 * @return \Blight\Interfaces\PackageManager
	 */
	public function getPackageManager();

	/**
	 * @param \Blight\Interfaces\PackageManager $packageManager
	 */
	public function setPackageManager(\Blight\Interfaces\PackageManager $packageManager);

	/**
	 * @return \Psr\Log\LoggerInterface	The logger instance
	 */
	public function getLogger();

	/**
	 * @param \Psr\Log\LoggerInterface $logger
	 */
	public function setLogger(\Psr\Log\LoggerInterface $logger);

	/**
	 * @return \Blight\Interfaces\Cache
	 * @throws \RuntimeException
	 */
	public function getCache();

	/**
	 * @param Interfaces\Cache $cache
	 */
	public function setCache(\Blight\Interfaces\Cache $cache);

	/**
	 * @return \Blight\Interfaces\Models\Packages\Theme
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
	 * @param string|null $name	The name of the author to retrieve, or null for the site owner
	 * @return \Blight\Interfaces\Models\Author|null	The author, or null if not found
	 */
	public function getAuthor($name = null);

	/**
	 * @return array	An associative array of all authors defined in the site, with author names as keys
	 */
	public function getAuthors();

	/**
	 * @param array $authors	An array of \Blight\Interfaces\Models\Author instances
	 * @throws \InvalidArgumentException	An author object does not implement \Blight\Interfaces\Models\Author
	 */
	public function setAuthors(array $authors);


	/**
	 * @param string $parameter		The name of the parameter to retrieve, dot-separated through the hierarchy
	 * @param mixed $default		The value to be returned if the requested parameter is not set
	 * @return mixed		The requested parameter's value or $default
	 */
	public function get($parameter, $default = null);
};
