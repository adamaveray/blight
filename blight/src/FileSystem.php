<?php
namespace Blight;

/**
 * Provides utility helper methods for interacting with the local filesystem
 */
class FileSystem implements \Blight\Interfaces\FileSystem {
	/** @var \Blight\Interfaces\Blog */
	protected $blog;
	/**
	 * @var array	Files to be ignored while checking for empty directories
	 */
	protected $ignoredFiles	= array('.', '..');

	/**
	 * Initialises the FileSystem manager
	 *
	 * @param \Blight\Interfaces\Blog $blog
	 */
	public function __construct(\Blight\Interfaces\Blog $blog){
		$this->blog	= $blog;
	}

	/**
	 * Creates a file and writes content to it
	 *
	 * @param string $path		The file to be written
	 * @param string $content	The content to write to the file
	 * @param bool $matchDirectoryOwnership	Whether to make the file match the ownership attributes of the parent directory
	 * @throws \RuntimeException	The file cannot be written or the containing directory cannot be made
	 */
	public function createFile($path, $content, $matchDirectoryOwnership = true){
		$dir	= dirname($path);
		$this->createDir($dir);

		$result	= file_put_contents($path, $content);
		if($result === false){
			throw new \RuntimeException('Cannot create '.$path);
		}

		if($matchDirectoryOwnership){
			$this->matchFileOwnership($dir, $path);
		}
	}

	/**
	 * Retrieves a file's contents
	 *
	 * @param string $path		The file to be read
	 * @param bool $normaliseLineEndings	Whether to normalise line endings to the value set in the provided Blog instance
	 * @return string	The content from the file
	 * @throws \RuntimeException	The file cannot be read
	 */
	public function loadFile($path, $normaliseLineEndings = true){
		if(file_exists($path)){
			$content	= file_get_contents($path);
		}
		if(!isset($content) || $content === false){
			throw new \RuntimeException('Cannot read '.$path);
		}
		if($normaliseLineEndings){
			$content	= preg_replace('/\R/', "\n", $content);
		}

		return $content;
	}

	/**
	 * Copies a file's contents to a new location, and removes the old file.
	 *
	 * @param string $oldPath	The current location of the file
	 * @param string $newPath	The location to move the file to
	 * @param bool $cleanup		Whether to delete the containing directory if empty
	 * @param bool $maintainAttributes	Whether to maintain the file's modification date and ownership
	 * @see deleteFile()
	 */
	public function moveFile($oldPath, $newPath, $cleanup = false, $maintainAttributes = true){
		$this->createDir(dirname($newPath));

		rename($oldPath, $newPath);

		if($maintainAttributes){
			// Attempt to update owner
			$this->matchFileOwnership($oldPath, $newPath);
		}
	}

	/**
	 * Copies a file's contents to a second location
	 *
	 * @param string $sourcePath	The path to the original file
	 * @param string $targetPath	The path to the file to create
	 * @param bool $maintainAttributes	Whether to set the new file's ownership to match the original
	 */
	public function copyFile($sourcePath, $targetPath, $maintainAttributes = true){
		$this->createDir(dirname($targetPath));

		copy($sourcePath, $targetPath);

		if($maintainAttributes){
			// Attempt to update owner
			$this->matchFileOwnership($sourcePath, $targetPath);
		}
	}

	/**
	 * Attempts to match the file owner and group of the source file on the target file
	 *
	 * @param string $source	The file to copy ownership from
	 * @param string $target	The file to copy ownership to
	 * @return bool	Whether the copying was successful
	 */
	protected function matchFileOwnership($source, $target){
		try {
			// Update group
			if(($sourceGroupID = filegroup($source)) === false) throw new \Exception('filegroup failed on source');
			if(($targetGroupID = filegroup($target)) === false) throw new \Exception('filegroup failed on target');
			if($sourceGroupID != $targetGroupID){
				// Group is different - update
				if(!@chgrp($target, $sourceGroupID)) throw new \Exception('chgrp failed on target');
			}

			// Update user
			if(($sourceUserID = fileowner($source)) === false) throw new \Exception('fileowner failed on source');
			if(($targetUserID = fileowner($target)) === false) throw new \Exception('fileowner failed on target');
			if($sourceUserID != $targetUserID){
				// User is different - update
				if(!@chown($target, $sourceUserID)) throw new \Exception('chown failed on target');
			}
		} catch(\Exception $e){
			return false;
		}

		return true;
	}

	/**
	 * Deletes a file
	 *
	 * @param string $path	The file location
	 * @param bool $cleanup	Whether to delete the containing directory if empty
	 * @throws \RuntimeException	Cannot delete the file
	 */
	public function deleteFile($path, $cleanup = false){
		if(is_dir($path)){
			$result	= rmdir($path);
		} else {
			$result	= unlink($path);
		}
		if($result === false){
			throw new \RuntimeException('Cannot delete '.$path);
		}

		if($cleanup){
			$dir	= pathinfo($path, \PATHINFO_DIRNAME);
			$files	= array_diff(scandir($dir), $this->ignoredFiles);
			if(count($files) === 0){
				// Delete directory
				$this->deleteFile($dir);
			}
		}
	}

	/**
	 * Creates a directory
	 *
	 * @param string $path		The path to the directory to be made
	 * @param int $mode			The directory permissions mode to use
	 * @param bool $recursive	Whether to create parent directories as needed
	 * @throws \RuntimeException	Cannot create the directory
	 * @see mkdir()
	 */
	public function createDir($path, $mode = 0755, $recursive = true, $matchParentOwnership = true){
		$path	= rtrim($path, '/');
		if(is_dir($path)){
			// Already exists
			return;
		}

		$parent	= pathinfo($path, \PATHINFO_DIRNAME);
		if(!is_dir($parent)){
			if(!$recursive){
				throw new \RuntimeException('Parent directory for '.$path.' does not exist');
			}

			// Create parent
			$this->createDir($parent, $mode, $recursive, $matchParentOwnership);
		}

		$result	= mkdir($path, $mode);
		if($result === false){
			throw new \RuntimeException('Cannot create '.$path);
		}

		if($matchParentOwnership){
			$this->matchFileOwnership($parent, $path);
		}
	}

	/**
	 * Copies files from the source directory to a second location. If the target directory does not exist,
	 * it will be created.
	 *
	 * @param string $sourceDir	The directory to copy files from
	 * @param string $targetDir	The directory to copy files to
	 * @param int $mode				The directory permissions mode to use
	 * @param bool $recursive		Whether to copy and child directories
	 * @param bool $maintainAttributes	Whether to set the new directory's modification date and permissions to match the original
	 * @param bool $checkDifference	Whether to overwrite files with the same or newer modification date
	 *
	 * @throws \RuntimeException	Source directory does not exist
	 * @throws \RuntimeException	Cannot create target directory
	 */
	public function copyDir($sourceDir, $targetDir, $mode = 0755, $recursive = true, $maintainAttributes = true, $checkDifference = false){
		$sourceDir	= rtrim($sourceDir, '/');
		$targetDir	= rtrim($targetDir, '/');

		if(!is_dir($sourceDir)){
			throw new \RuntimeException('Source dir does not exist');
		}

		$this->createDir($targetDir, $mode, true, $maintainAttributes);
		if($maintainAttributes){
			touch($targetDir, filemtime($sourceDir));
		}

		$source	= new \FilesystemIterator($sourceDir);
		foreach($source as $file){
			$targetFile	= str_replace($sourceDir, $targetDir, $file);

			if(is_dir($file)){
				if(!$recursive){
					continue;
				}

				$this->copyDir($file, $targetFile, $mode, $recursive, $maintainAttributes, $checkDifference);

			} elseif(!$checkDifference || !file_exists($targetFile) || filemtime($file) > filemtime($targetFile)){
				// Nonexistent or allowed to overwrite
				$this->copyFile($file, $targetFile, $maintainAttributes);
			}
		}
	}
};
