<?php
namespace Blight\Interfaces;

interface Template {
	public function __construct(\Blight\Interfaces\Blog $blog, $name);

	public function render($params = null);
};
