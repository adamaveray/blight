<?php
namespace Blight\Interfaces;

interface Blog {
	public function __construct($config);

	public function get_path_root($append = '');

	public function get_path_cache($append = '');

	public function get_path_app($append = '');

	public function get_path_themes($append = '');

	public function get_path_plugins($append = '');

	public function get_path_assets($append = '');

	public function get_path_pages($append = '');

	public function get_path_posts($append = '');

	public function get_path_drafts($append = '');

	public function get_path_drafts_web($append = '');

	public function get_path_www($append = '');

	public function get_url($append = '');

	public function get_name();

	public function get_description();

	public function get_feed_url();

	public function get_file_system();

	public function get_package_manager();

	public function get_theme();

	public function is_linkblog();

	public function do_hook($hook, $params = null);

	public function get($parameter, $group = null, $default = null);
};
