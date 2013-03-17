<?php
namespace Blight\Interfaces;

interface PackageManager {
	public function __construct(\Blight\Interfaces\Blog $blog);

	public function get_theme($theme_name);

	public function do_hook($hook, $params = null);
};
