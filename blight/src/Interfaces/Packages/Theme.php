<?php
namespace Blight\Interfaces\Packages;

interface Theme extends \Blight\Interfaces\Packages\Package {
	/**
	 * @param string $name			The template to use
	 * @param array|null $params	An array of variables to be assigned to the local scope of the template
	 * @return string	The rendered content from the template
	 * @throws \RuntimeException	Template cannot be found
	 */
	public function renderTemplate($name, $params = null);

	/**
	 * @return string	The path to the templates directory
	 */
	public function getPathTemplates();

	/**
	 * @return string	The path to the assets directory
	 */
	public function getPathAssets();
};
