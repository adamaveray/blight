<?php
namespace Blight\Interfaces;

interface Post extends \Blight\Interfaces\Page {
	public function __construct(\Blight\Interfaces\Blog $blog, $content, $slug, $is_draft = false);

	public function get_title($raw = false);

	public function get_year();

	public function get_tags();

	public function get_category();

	public function is_draft();

	public function is_linked();
};
