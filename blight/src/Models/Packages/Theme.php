<?php
namespace Blight\Models\Packages;

abstract class Theme extends Package implements \Blight\Interfaces\Models\Packages\Theme {
	protected $templates	= array();

	/**
	 * Builds a template file with the provided variables, and returns the generated HTML
	 *
	 * @param string|array $names	The template or templates to use
	 * @param array|null $params	An array of variables to be assigned to the local scope of the template
	 * @return string	The rendered content from the template
	 * @throws \RuntimeException	Template cannot be found
	 */
	public function renderTemplate($names, $params = null){
		// Check if template cached
		if(!is_array($names)){
			$names	= array($names);
		}

		foreach($names as $templateName){
			if(!isset($this->templates[$templateName])){
				// Create template
				try {
					$template	= new \Blight\Models\Template($this->blog, $this, $templateName);
				} catch(\Exception $e){
					// Skip template
					continue;
				}

				$this->templates[$templateName]	= $template;
			}

			return $this->templates[$templateName]->render($params);
		}

		// Template not found
		throw new \RuntimeException('No templates could be found');
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
