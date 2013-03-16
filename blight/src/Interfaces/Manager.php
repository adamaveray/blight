<?php
namespace Blight\Interfaces;

interface Manager {
	public function __construct(\Blight\Interfaces\Blog $blog);

	public function get_pages();
	
	public function get_draft_posts();

	public function get_posts($filters = null);

	public function get_posts_by_year();

	public function get_posts_by_tag();

	public function get_posts_by_category();

	public function cleanup_drafts();
};
