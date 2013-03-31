<?php
namespace Blight\Interfaces\Models\Packages;

interface Package {
	/**
	 * @param \Blight\Interfaces\Blog $blog
	 * @param array|null $config	The configuration data for the package
	 */
	public function __construct(\Blight\Interfaces\Blog $blog, $config = null);

	public function setup();
};
