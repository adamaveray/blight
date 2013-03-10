<?php
namespace Blight\Interfaces;

interface Blog {
	public function __construct($config);

	public function get_path_root($append = '');

	public function get_path_cache($append = '');

	public function get_path_app($append = '');

	public function get_path_templates($append = '');

	public function get_path_posts($append = '');

	public function get_path_drafts($append = '');

	public function get_path_drafts_web($append = '');

	public function get_path_www($append = '');

	public function get_url($append = '');

	public function get_name();

	public function get_description();

	public function get_feed_url();

	public function get_file_system();

	public function get_eol();

	public function is_linkblog();

	public function get($parameter, $group = null, $default = null);
};