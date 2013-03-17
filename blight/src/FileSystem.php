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
	protected $junk_files	= array('.', '..');

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
	 * @param bool $match_directory_ownership	Whether to make the file match the ownership attributes of the parent directory
	 * @throws \RuntimeException	The file cannot be written or the containing directory cannot be made
	 */
	public function create_file($path, $content, $match_directory_ownership = true){
		$dir	= pathinfo($path, \PATHINFO_DIRNAME);
		if(!is_dir($dir)){
			$this->create_dir($dir);
		}

		$result	= file_put_contents($path, $content);
		if($result === false){
			throw new \RuntimeException('Cannot create '.$path);
		}

		if($match_directory_ownership){
			$this->match_file_ownership($dir, $path);
		}
	}

	/**
	 * Retrieves a file's contents
	 *
	 * @param string $path		The file to be read
	 * @param bool $normalise_line_endings	Whether to normalise line endings to the value set in the provided Blog instance
	 * @return string	The content from the file
	 * @throws \RuntimeException	The file cannot be read
	 */
	public function load_file($path, $normalise_line_endings = true){
		if(file_exists($path)){
			$content	= file_get_contents($path);
		}
		if(!isset($content) || $content === false){
			throw new \RuntimeException('Cannot read '.$path);
		}
		if($normalise_line_endings){
			$content	= preg_replace('/\R/', "\n", $content);
		}

		return $content;
	}

	/**
	 * Copies a file's contents to a new location, and removes the old file.
	 *
	 * @param string $old_path	The current location of the file
	 * @param string $new_path	The location to move the file to
	 * @param bool $cleanup		Whether to delete the containing directory if empty
	 * @param bool $maintain_attributes	Whether to maintain the file's modification date and ownership
	 * @see delete_file()
	 */
	public function move_file($old_path, $new_path, $cleanup = false, $maintain_attributes = true){
		$this->copy_file($old_path, $new_path, $maintain_attributes);
		$this->delete_file($old_path, $cleanup);
	}

	/**
	 * Copies a file's contents to a second location
	 *
	 * @param string $source_path	The path to the original file
	 * @param string $target_path	The path to the file to create
	 * @param bool $maintain_attributes	Whether to set the new file's modification date and ownership to match the original
	 */
	public function copy_file($source_path, $target_path, $maintain_attributes = true){
		$content	= $this->load_file($source_path);
		$this->create_file($target_path, $content);

		if($maintain_attributes){
			// Update modification date
			touch($target_path, filemtime($source_path));

			// Attempt to update owner
			$this->match_file_ownership($source_path, $target_path);
		}
	}

	/**
	 * Attempts to match the file owner and group of the source file on the target file
	 *
	 * @param string $source	The file to copy ownership from
	 * @param string $target	The file to copy ownership to
	 * @return bool	Whether the copying was successful
	 */
	protected function match_file_ownership($source, $target){
		try {
			// Update group
			if(($source_groupid = filegroup($source)) === false) throw new \Exception('filegroup failed on source');
			if(($target_groupid = filegroup($target)) === false) throw new \Exception('filegroup failed on target');
			if($source_groupid != $target_groupid){
				// Group is different - update
				if(!@chgrp($target, $source_groupid)) throw new \Exception('chgrp failed on target');
			}

			// Update user
			if(($source_userid = fileowner($source)) === false) throw new \Exception('fileowner failed on source');
			if(($target_userid = fileowner($target)) === false) throw new \Exception('fileowner failed on target');
			if($source_userid != $target_userid){
				// User is different - update
				if(!@chown($target, $source_userid)) throw new \Exception('chown failed on target');
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
	public function delete_file($path, $cleanup = false){
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
			$files	= array_diff(scandir($dir), $this->junk_files);
			if(count($files) === 0){
				// Delete directory
				$this->delete_file($dir);
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
	public function create_dir($path, $mode = 0755, $recursive = true, $match_parent_ownership = true){
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
			$this->create_dir($parent, $mode, $recursive, $match_parent_ownership);
		}

		$result	= mkdir($path, $mode);
		if($result === false){
			throw new \RuntimeException('Cannot create '.$path);
		}

		if($match_parent_ownership){
			$this->match_file_ownership($parent, $path);
		}
	}

	/**
	 * Copies files from the source directory to a second location. If the target directory does not exist,
	 * it will be created.
	 *
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
	public function copy_dir($source_dir, $target_dir, $mode = 0755, $recursive = true, $maintain_attributes = true, $check_difference = false){
		$source_dir	= rtrim($source_dir, '/');
		$target_dir	= rtrim($target_dir, '/');

		if(!is_dir($source_dir)){
			throw new \RuntimeException('Source dir does not exist');
		}

		$this->create_dir($target_dir, $mode, true, $maintain_attributes);
		if($maintain_attributes){
			touch($target_dir, filemtime($source_dir));
		}

		$source	= new \FilesystemIterator($source_dir);
		foreach($source as $file){
			$target_file	= str_replace($source_dir, $target_dir, $file);

			if(is_dir($file)){
				if(!$recursive){
					continue;
				}

				$this->copy_dir($file, $target_file, $mode, $recursive, $maintain_attributes, $check_difference);

			} elseif(!$check_difference || !file_exists($target_file) || filemtime($file) > filemtime($target_file)){
				// Nonexistent or allowed to overwrite
				$this->copy_file($file, $target_file, $maintain_attributes);
			}
		}
	}
};
