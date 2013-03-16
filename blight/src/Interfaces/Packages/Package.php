<?php
namespace Blight\Interfaces\Packages;

interface Package {
	public function __construct(\Blight\Interfaces\Blog $blog, $config = null);

	public function setup();
};
