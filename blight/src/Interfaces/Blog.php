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
	public function get_path_root($append = '');

	/**
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The cache directory path, with the provided string appended
	 */
	public function get_path_cache($append = '');

	/**
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The path, with the provided string appended
	 * @see get_root_path()
	 */
	public function get_path_app($append = '');

	/**
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The path, with the provided string appended
	 * @see get_root_path()
	 */
	public function get_path_themes($append = '');

	/**
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The path, with the provided string appended
	 * @see get_root_path()
	 */
	public function get_path_plugins($append = '');

	/**
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The path, with the provided string appended
	 * @see get_root_path()
	 */
	public function get_path_assets($append = '');

	/**
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The path, with the provided string appended
	 * @see get_root_path()
	 */
	public function get_path_pages($append = '');

	/**
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The path, with the provided string appended
	 * @see get_root_path()
	 */
	public function get_path_posts($append = '');

	/**
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The path, with the provided string appended
	 * @see get_root_path()
	 */
	public function get_path_drafts($append = '');

	/**
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The path, with the provided string appended
	 * @see get_root_path()
	 */
	public function get_path_drafts_web($append = '');

	/**
	 * @param string $append	An additonal path fragment to append to the path
	 * @return string			The path, with the provided string appended
	 * @see get_root_path()
	 */
	public function get_path_www($append = '');

	/**
	 * @param string $append	An additonal URL fragment to append to the path
	 * @return string			The URL, with the provided string appended
	 */
	public function get_url($append = '');

	/**
	 * @return string	The blog name
	 */
	public function get_name();

	/**
	 * @return string|null	The blog description if set, or null
	 */
	public function get_description();

	/**
	 * @return string	The URL to the site feed
	 */
	public function get_feed_url();

	/**
	 * @return \Blight\Interfaces\FileSystem	The common FileSystem object
	 */
	public function get_file_system();

	/**
	 * @return \Blight\Interfaces\PackageManager
	 */
	public function get_package_manager();

	/**
	 * @return \Blight\Interfaces\Packages\Theme
	 */
	public function get_theme();

	/**
	 * @return bool	Whether the blog is a linkblog
	 */
	public function is_linkblog();

	/**
	 * @param string $hook	The name of the hook to run
	 * @param array|null $params	An array of parameters to pass to plugins. Parameters must be passed by reference:
	 *
	 * 		$value	= 1;
	 * 		do_hook('hook_name', array(
	 * 			'param'	=> &$value
	 *  	));
	 *
	 * @see \Blight\PackageManager::do_hook
	 */
	public function do_hook($hook, $params = null);

	/**
	 * @param string $parameter		The name of the parameter to retrieve
	 * @param string|null $group	The settings group the parameter exists in
	 * @param mixed $default		The value to be returned if the requested parameter is not set
	 * @return mixed		The requested parameter's value or $default
	 */
	public function get($parameter, $group = null, $default = null);
};
