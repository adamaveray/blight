<?php
namespace Blight\Interfaces\Models\Packages;

interface Theme extends \Blight\Interfaces\Models\Packages\Package {
	/**
	 * @param string|array $names	The template or templates to use
	 * @param array|null $params	An array of variables to be assigned to the local scope of the template
	 * @return string	The rendered content from the template
	 * @throws \RuntimeException	Template cannot be found
	 */
	public function renderTemplate($names, $params = null);

	/**
	 * @return string	The path to the templates directory
	 */
	public function getPathTemplates();

	/**
	 * @return string	The path to the assets directory
	 */
	public function getPathAssets();
};
