<?php
namespace Blight\Tests\Mock;

class Theme implements \Blight\Interfaces\Packages\Theme {
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

	public function renderTemplate($name, $params = null){
		$template	= new \Blight\Template($this->blog, $this, $name);
		return $template->render($params);
	}

	public function getPathTemplates(){
		return $this->path.'templates/';
	}

	public function getPathAssets(){
		return $this->path.'assets/';
	}
};
