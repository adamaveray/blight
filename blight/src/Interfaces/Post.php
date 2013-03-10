<?php
namespace Blight\Interfaces;

interface Post {
	public function __construct(\Blight\Interfaces\Blog $blog, $content, $slug, $is_draft = false);

	public function get_title($raw = false);

	public function get_slug();

	public function get_date();

	public function set_date(\DateTime $date);

	public function get_content();

	public function get_metadata();

	public function get_meta($name);

	public function has_meta($name);

	public function get_link();

	public function get_permalink();

	public function get_relative_permalink();

	public function get_year();

	public function get_tags();

	public function get_category();

	public function is_draft();

	public function is_linked();
};
