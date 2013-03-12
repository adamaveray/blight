<?php
namespace Blight\Interfaces;

interface Page {
	public function __construct(\Blight\Interfaces\Blog $blog, $content, $slug);

	public function get_title();

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
};
