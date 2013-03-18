<?php
namespace Blight\Interfaces;

interface FileSystem {
	/**
	 * @param \Blight\Interfaces\Blog $blog
	 */
	public function __construct(\Blight\Interfaces\Blog $blog);

	/**
	 * @param string $path		The file to be written
	 * @param string $content	The content to write to the file
	 * @param bool $match_directory_ownership	Whether to make the file match the ownership attributes of the parent directory
	 * @throws \RuntimeException	The file cannot be written or the containing directory cannot be made
	 */
	public function create_file($path, $content, $match_parent_ownership = true);

	/**
	 * @param string $path		The file to be read
	 * @param bool $normalise_line_endings	Whether to normalise line endings to the value set in the provided Blog instance
	 * @return string	The content from the file
	 * @throws \RuntimeException	The file cannot be read
	 */
	public function load_file($path, $normalise_file_endings = true);

	/**
	 * @param string $old_path	The current location of the file
	 * @param string $new_path	The location to move the file to
	 * @param bool $cleanup		Whether to delete the containing directory if empty
	 * @param bool $maintain_attributes	Whether to maintain the file's modification date and ownership
	 * @see delete_file()
	 */
	public function move_file($old_path, $new_path, $cleanup = false, $match_parent_ownership = true);

	/**
	 * @param string $source_path	The path to the original file
	 * @param string $target_path	The path to the file to create
	 * @param bool $maintain_attributes	Whether to set the new file's ownership to match the original
	 */
	public function copy_file($source_path, $target_path, $maintain_attributes = true);

	/**
	 * @param string $path	The file location
	 * @param bool $cleanup	Whether to delete the containing directory if empty
	 * @throws \RuntimeException	Cannot delete the file
	 */
	public function delete_file($path, $cleanup = false);

	/**
	 * @param string $path		The path to the directory to be made
	 * @param int $mode			The directory permissions mode to use
	 * @param bool $recursive	Whether to create parent directories as needed
	 * @throws \RuntimeException	Cannot create the directory
	 * @see mkdir()
	 */
	public function create_dir($path, $mode = 0755, $recursive = true, $match_parent_ownership = true);

	/**
	 * @param string $source_dir	The directory to copy files from
	 * @param string $target_dir	The directory to copy files to
	 * @param int $mode				The directory permissions mode to use
	 * @param bool $recursive		Whether to copy and child directories
	 * @param bool $maintain_attributes	Whether to set the new directory's modification date and permissions to match the original
	 * @param bool $check_difference	Whether to overwrite files with the same or newer modification date
	 *
	 * @throws \RuntimeException	Source directory does not exist
	 * @throws \RuntimeException	Cannot create target directory
	 */
	public function copy_dir($source_dir, $target_dir, $mode = 0755, $recursive = true, $maintain_attributes = true, $check_difference = false);
};
