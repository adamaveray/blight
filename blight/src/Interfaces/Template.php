<?php
namespace Blight\Interfaces;

interface Template {
	public function __construct(\Blight\Interfaces\Blog $blog, \Blight\Interfaces\Packages\Theme $theme, $name);

	public function render($params = null);
};
