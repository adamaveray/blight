<?php
namespace Blight\Interfaces;

interface PackageManager {
	/**
	 * @param \Blight\Interfaces\Blog $blog
	 */
	public function __construct(\Blight\Interfaces\Blog $blog);

	/**
	 * @param string $theme_name	The name of the theme to retrieve
	 * @return \Blight\Interfaces\Packages\Theme
	 * @throws \RuntimeException	Theme not found
	 * @throws \RuntimeException	Invalid theme package
	 */
	public function get_theme($theme_name);

	/**
	 * @param string $hook	The name of the hook to run
	 * @param array|null $params	An array of parameters to pass to plugins. Parameters must be passed by reference:
	 *
	 * 		$value	= 1;
	 * 		do_hook('hook_name', array(
	 * 			'param'	=> &$value
	 *  	));
	 */
	public function do_hook($hook, $params = null);
};
