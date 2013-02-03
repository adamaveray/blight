<?php
namespace Blight;

/**
 * Provides utility helper methods for interacting with the local filesystem
 */
class FileSystem {
	/** @var \Blight\Blog */
	protected $blog;
	/**
	 * @var array	Files to be ignored while checking for empty directories
	 */
	protected $junk_files	= array('.', '..');

	/**
	 * Initialises the FileSystem manager
	 *
	 * @param Blog $blog
	 */
	public function __construct(Blog $blog){
		$this->blog	= $blog;
	}

	/**
	 * Creates a file and writes content to it
	 *
	 * @param string $path		The file to be written
	 * @param string $content	The content to write to the file
	 * @throws \RuntimeException	The file cannot be written or the containing directory cannot be made
	 */
	public function create_file($path, $content){
		$dir	= pathinfo($path, \PATHINFO_DIRNAME);
		if(!is_dir($dir)){
			$result	= mkdir($dir, 0777, true);

			if($result === false){
				throw new \RuntimeException('Cannot create directory for '.$path);
			}
		}

		$result	= file_put_contents($path, $content);
		if($result === false){
			throw new \RuntimeException('Cannot create '.$path);
		}
	}

	/**
	 * Retrieves a file's contents
	 *
	 * @param string $path		The file to be read
	 * @return string	The content from the file
	 * @throws \RuntimeException	The file cannot be read
	 */
	public function load_file($path){
		$content	= file_get_contents($path);
		if($content === false){
			throw new \RuntimeException('Cannot read '.$path);
		}

		return $content;
	}

	/**
	 * Copies a file's contents to a new location, and removes the old file.
	 *
	 * @param string $old_path	The current location of the file
	 * @param string $new_path	The location to move the file to
	 * @param bool $cleanup		Whether to delete the containing directory if empty
	 * @see delete_file()
	 */
	public function move_file($old_path, $new_path, $cleanup = false){
		$content	= $this->load_file($old_path);
		$this->create_file($new_path, $content);
		$this->delete_file($old_path, $cleanup);
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
	public function create_dir($path, $mode = 0777, $recursive = true){
		$result	= mkdir($path, $mode, $recursive);
		if($result === false){
			throw new \RuntimeException('Cannot create '.$path);
		}
	}
};