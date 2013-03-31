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
	 * @param bool $matchDirectoryOwnership	Whether to make the file match the ownership attributes of the parent directory
	 * @throws \RuntimeException	The file cannot be written or the containing directory cannot be made
	 */
	public function createFile($path, $content, $matchDirectoryOwnership = true);

	/**
	 * @param string $path		The file to be read
	 * @param bool $normaliseLineEndings	Whether to normalise line endings to the value set in the provided Blog instance
	 * @return string	The content from the file
	 * @throws \RuntimeException	The file cannot be read
	 */
	public function loadFile($path, $normaliseFileEndings = true);

	/**
	 * @param string $oldPath	The current location of the file
	 * @param string $newPath	The location to move the file to
	 * @param bool $cleanup		Whether to delete the containing directory if empty
	 * @param bool $maintainAttributes	Whether to maintain the file's modification date and ownership
	 * @see deleteFile()
	 */
	public function moveFile($oldPath, $newPath, $cleanup = false, $matchParentOwnership = true);

	/**
	 * @param string $sourcePath	The path to the original file
	 * @param string $targetPath	The path to the file to create
	 * @param bool $maintainAttributes	Whether to set the new file's ownership to match the original
	 */
	public function copyFile($sourcePath, $targetPath, $maintainAttributes = true);

	/**
	 * @param string $path	The file location
	 * @param bool $cleanup	Whether to delete the containing directory if empty
	 * @throws \RuntimeException	Cannot delete the file
	 */
	public function deleteFile($path, $cleanup = false);

	/**
	 * @param string $path		The path to the directory to be made
	 * @param int $mode			The directory permissions mode to use
	 * @param bool $recursive	Whether to create parent directories as needed
	 * @throws \RuntimeException	Cannot create the directory
	 * @see mkdir()
	 */
	public function createDir($path, $mode = 0755, $recursive = true, $matchParentOwnership = true);

	/**
	 * @param string $sourceDir	The directory to copy files from
	 * @param string $targetDir	The directory to copy files to
	 * @param int $mode			The directory permissions mode to use
	 * @param bool $recursive	Whether to copy and child directories
	 * @param bool $maintainAttributes	Whether to set the new directory's modification date and permissions to match the original
	 * @param bool $checkDifference		Whether to overwrite files with the same or newer modification date
	 *
	 * @throws \RuntimeException	Source directory does not exist
	 * @throws \RuntimeException	Cannot create target directory
	 */
	public function copyDir($sourceDir, $targetDir, $mode = 0755, $recursive = true, $maintainAttributes = true, $checkDifference = false);
};
