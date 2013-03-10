<?php
namespace Blight\Interfaces;

interface Renderer {
	public function __construct(\Blight\Interfaces\Blog $blog, \Blight\Interfaces\Manager $manager);

	public function render_post(Post $post);

	public function render_drafts();

	public function render_archives($options = null);

	public function render_year(\Blight\Interfaces\Collection $year, $options = null);

	public function render_tags($options = null);

	public function render_categories($options = null);

	public function render_home($options = null);

	public function render_feed($options = null);
};
