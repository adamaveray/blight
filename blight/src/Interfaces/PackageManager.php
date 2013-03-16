<?php
namespace Blight\Interfaces;

interface PackageManager {
	public function __construct(\Blight\Interfaces\Blog $blog);

	public function do_hook($hook, $params = null);
};
