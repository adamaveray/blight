<?php
namespace Blight\Interfaces;

interface PackageManager {
	/**
	 * @param \Blight\Interfaces\Blog $blog
	 */
	public function __construct(\Blight\Interfaces\Blog $blog);

	/**
	 * @param string $themeName	The name of the theme to retrieve
	 * @return \Blight\Interfaces\Models\Packages\Theme
	 * @throws \RuntimeException	Theme not found
	 * @throws \RuntimeException	Invalid theme package
	 */
	public function getTheme($themeName);

	/**
	 * @param string $hook	The name of the hook to run
	 * @param array|null $params	An array of parameters to pass to plugins. Parameters must be passed by reference:
	 *
	 * 		$value	= 1;
	 * 		doHook('hook_name', array(
	 * 			'param'	=> &$value
	 *  	));
	 *
	 * @param \Blight\Interfaces\Models\Packages\Plugin|null $theme	An optional theme to also apply hooks to
	 */
	public function doHook($hook, $params = null, \Blight\Interfaces\Models\Packages\Plugin $theme = null);
};
