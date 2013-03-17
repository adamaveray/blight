<?php
namespace Blight\Interfaces\Packages;

interface Theme extends \Blight\Interfaces\Packages\Package {
	public function render_template($name, $params = null);

	public function get_path_templates();

	public function get_path_assets();
};
