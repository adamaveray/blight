<?php
namespace Blight\Interfaces;

interface FileSystem {
	public function __construct(\Blight\Interfaces\Blog $blog);

	public function create_file($path, $content);

	public function load_file($path, $normalise_file_endings = true);

	public function move_file($old_path, $new_path, $cleanup = false);

	public function copy_file($source_path, $target_path);

	public function delete_file($path, $cleanup = false);

	public function create_dir($path, $mode = 0755, $recursive = true);

	public function copy_dir($source_dir, $target_dir, $mode = 0755, $recursive = true);
};
