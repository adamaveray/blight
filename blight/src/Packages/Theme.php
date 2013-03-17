<?php
namespace Blight\Packages;

abstract class Theme extends Package implements \Blight\Interfaces\Packages\Theme {
	protected $templates	= array();

	/**
	 * Builds a template file with the provided variables, and returns the generated HTML
	 *
	 * @param string $name			The template to use
	 * @param array|null $params	An array of variables to be assigned to the local scope of the template
	 * @return string	The rendered content from the template
	 * @throws \RuntimeException	Template cannot be found
	 */
	public function render_template($name, $params = null){
		// Check if template cached
		if(!isset($this->templates[$name])){
			// Create template
			$this->templates[$name]	= new \Blight\Template($this->blog, $this, $name);
		}

		return $this->templates[$name]->render($params);
	}

	/**
	 * @return string	The path to the templates directory
	 */
	public function get_path_templates(){
		return $this->path.'templates/';
	}
};
