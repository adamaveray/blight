<?php
namespace Blight\Interfaces;

interface Template {
	/**
	 * @param \Blight\Interfaces\Blog $blog
	 * @param string $name			The name of the template to use
	 * @param string $dir			The directory to look for templates in
	 * @throws \RuntimeException	Template cannot be found
	 */
	public function __construct(\Blight\Interfaces\Blog $blog, \Blight\Interfaces\Packages\Theme $theme, $name);

	/**
	 * @param array|null $params	An array of variables to be assigned to the local scope of the template
	 * @return string				The rendered content from the template
	 * @throws \InvalidArgumentException	Invalid params provided
	 * @throws \RuntimeException			Template cannot be found
	 */
	public function render($params = null);

	/**
	 * @return string	The HTML for the styles
	 */
	public function getStyles();

	/**
	 * @return string	The HTML for the scripts
	 */
	public function getScripts();
};
