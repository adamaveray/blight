<?php
namespace Blight\Models\Packages;

abstract class Theme extends Package implements \Blight\Interfaces\Models\Packages\Theme {
	protected $templates	= array();

	/**
	 * Builds a template file with the provided variables, and returns the generated HTML
	 *
	 * @param string $name			The template to use
	 * @param array|null $params	An array of variables to be assigned to the local scope of the template
	 * @return string	The rendered content from the template
	 * @throws \RuntimeException	Template cannot be found
	 */
	public function renderTemplate($name, $params = null){
		// Check if template cached
		if(!isset($this->templates[$name])){
			// Create template
			$this->templates[$name]	= new \Blight\Models\Template($this->blog, $this, $name);
		}

		return $this->templates[$name]->render($params);
	}

	/**
	 * @return string	The path to the templates directory
	 */
	public function getPathTemplates(){
		return $this->path.'templates/';
	}

	/**
	 * @return string	The path to the assets directory
	 */
	public function getPathAssets(){
		return $this->path.'assets/';
	}
};
