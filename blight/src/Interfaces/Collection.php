<?php
namespace Blight\Interfaces;

interface Collection {
	public function get_name();

	public function get_slug();

	public function get_url();

	public function set_posts($posts);

	public function add_post(\Blight\Post $post);

	public function get_posts();
};