<?php
namespace Blight\Tests\Mock;

class Theme implements \Blight\Interfaces\Models\Packages\Theme {
	protected $blog;
	protected $config;
	protected $path;

	public function __construct(\Blight\Interfaces\Blog $blog, $config = null){
		$this->blog		= $blog;
		$this->config	= $config;
		if(isset($this->config['path'])){
			$this->path	= $this->config['path'];
		}
	}

	public function setup(){
	}

	public function renderTemplate($names, $params = null){
		if(!is_array($names)){
			$names	= array($names);
		}

		foreach($names as $templateName){
			try {
				$template	= new \Blight\Models\Template($this->blog, $this, $templateName);
				return $template->render($params);
			} catch(\Exception $e){
				// Skip template
				continue;
			}
		}

		// Template not found
		throw new \RuntimeException('No templates could be found');
	}

	public function getPathTemplates(){
		return $this->path.'templates/';
	}

	public function getPathAssets(){
		return $this->path.'assets/';
	}
};
