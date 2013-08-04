<?php
namespace Blight;

class UpdateManager {
	const CACHE_KEY_PREFIX	= 'files-modified.';
	const CACHE_KEY_DRAFTS	= 'drafts';
	const CACHE_KEY_POSTS	= 'posts';
	const CACHE_KEY_PAGES	= 'pages';
	const CACHE_KEY_ASSETS	= 'assets';
	const CACHE_KEY_SYSTEM	= 'system';

	/** @var \Blight\Interfaces\Blog */
	protected $blog;

	/** @var \Blight\Interfaces\Manager */
	protected $manager;

	protected $changedDraftsFiles;
	protected $changedPostsFiles;
	protected $changedPagesFiles;
	protected $changedAssetFiles;
	protected $deletedDraftsFiles;
	protected $deletedPostsFiles;
	protected $deletedPagesFiles;
	protected $deletedAssetFiles;

	protected $changedSystemFiles;


	/**
	 * @param \Blight\Interfaces\Blog $blog
	 */
	public function __construct(\Blight\Interfaces\Blog $blog){
		$this->blog	= $blog;
	}

	/**
	 * @param \Blight\Interfaces\Manager $manager
	 */
	public function setManager(\Blight\Interfaces\Manager $manager){
		$this->manager	= $manager;
	}

	/**
	 * @return \Blight\Interfaces\Manager
	 * @throws \RuntimeException
	 */
	protected function getManager(){
		if(!isset($this->manager)){
			throw new \RuntimeException('Manager not set');
		}

		return $this->manager;
	}


	/**
	 * @param string|null $type	The specific type of resource to check, or any if null
	 */
	public function needsUpdate($type = null){
		$fullSiteUpdate	= $this->doesNeedFullUpdate();

		if($fullSiteUpdate){
			// All resources need updating
			return true;
		}

		$changedTypes	= array(
			'drafts'	=> (bool)(count($this->getChangedDraftFiles($deletedFiles)) + count($deletedFiles)),
			'posts'		=> (bool)(count($this->getChangedPostFiles($deletedFiles)) + count($deletedFiles) + count($this->getManager()->getDraftsToPublish())),
			'pages'		=> (bool)(count($this->getChangedPageFiles($deletedFiles)) + count($deletedFiles)),
			'assets'	=> (bool)(count($this->getChangedAssetFiles($deletedFiles)) + count($deletedFiles)),
			'theme'		=> $fullSiteUpdate,
			'supplementary'	=> $fullSiteUpdate
		);

		if(isset($type)){
			if(!isset($changedTypes[$type])){
				// Unknown type - assume update
				return true;
			}

			return $changedTypes[$type];

		} else {
			// Any type
			return in_array(true, $changedTypes);
		}
	}

	public function saveState(){
		$this->blog->getCache()->set(array(
			self::CACHE_KEY_PREFIX.self::CACHE_KEY_DRAFTS	=> $this->getDraftFiles(true),
			self::CACHE_KEY_PREFIX.self::CACHE_KEY_POSTS	=> $this->getPostFiles(true),
			self::CACHE_KEY_PREFIX.self::CACHE_KEY_PAGES	=> $this->getPageFiles(true),
			self::CACHE_KEY_PREFIX.self::CACHE_KEY_ASSETS	=> $this->getAssetFiles(true),
			self::CACHE_KEY_PREFIX.self::CACHE_KEY_SYSTEM	=> $this->getMonitoredSystemFiles(true)
		));
	}


	/**
	 * @return bool
	 */
	protected function doesNeedFullUpdate(){
		return (bool)count($this->getChangedSystemFiles());
	}


	public function getChangedDraftPosts(){
		return $this->manager->getDraftPosts($this->getChangedDraftFiles());
	}


	public function getChangedDraftFiles(&$deletedFiles = null){
		if(!isset($this->changedDraftsFiles)){
			$this->changedDraftsFiles	= $this->getChangedFiles($this->getDraftFiles(), self::CACHE_KEY_DRAFTS, $deletedFiles);
			$this->deletedDraftsFiles	= $deletedFiles;
		}

		$deletedFiles	= $this->deletedDraftsFiles;
		return $this->changedDraftsFiles;
	}

	public function getChangedPostFiles(&$deletedFiles = null){
		if(!isset($this->changedPostsFiles)){
			$this->changedPostsFiles	= $this->getChangedFiles($this->getPostFiles(), self::CACHE_KEY_POSTS, $deletedFiles);
			$this->deletedPostsFiles	= $deletedFiles;
		}

		$deletedFiles	= $this->deletedPostsFiles;
		return $this->changedPostsFiles;
	}

	public function getChangedPageFiles(&$deletedFiles = null){
		if(!isset($this->changedPagesFiles)){
			$this->changedPagesFiles	= $this->getChangedFiles($this->getPageFiles(), self::CACHE_KEY_PAGES, $deletedFiles);
			$this->deletedPagesFiles	= $deletedFiles;
		}

		$deletedFiles	= $this->deletedPagesFiles;
		return $this->changedPagesFiles;
	}

	public function getChangedAssetFiles(&$deletedFiles = null){
		if(!isset($this->changedAssetFiles)){
			$this->changedAssetFiles	= $this->getChangedFiles($this->getAssetFiles(), self::CACHE_KEY_ASSETS, $deletedFiles);
			$this->deletedAssetFiles	= $deletedFiles;
		}

		$deletedFiles	= $this->deletedAssetFiles;
		return $this->changedAssetFiles;
	}


	protected function getDraftFiles($withModification = false){
		$files	= $this->getManager()->getRawPosts(true);

		if($withModification){
			$files	= $this->blog->getFileSystem()->getModifiedTimesForFiles($files);
		}

		return $files;
	}

	protected function getPostFiles($withModification = false){
		$files	= $this->getManager()->getRawPosts();

		if($withModification){
			$files	= $this->blog->getFileSystem()->getModifiedTimesForFiles($files);
		}

		return $files;
	}

	protected function getPageFiles($withModification = false){
		$files	= $this->getManager()->getRawPages();

		if($withModification){
			$files	= $this->blog->getFileSystem()->getModifiedTimesForFiles($files);
		}

		return $files;
	}

	protected function getAssetFiles($withModification = false){
		$files	= $this->blog->getFileSystem()->getDirectoryListing($this->blog->getPathAssets());

		if($withModification){
			$files	= $this->blog->getFileSystem()->getModifiedTimesForFiles($files);
		}

		return $files;
	}


	protected function getChangedSystemFiles(){
		if(!isset($this->changedSystemFiles)){
			$this->changedSystemFiles	= $this->getChangedFiles($this->getMonitoredSystemFiles(), self::CACHE_KEY_SYSTEM);
		}

		return $this->changedSystemFiles;
	}

	protected function getMonitoredSystemFiles($withModification = false){
		$files	= array(
			$this->blog->getPathRoot('config.json'),
			$this->blog->getPathRoot('authors.json'),
			$this->blog->getPathThemes($this->blog->get('theme.name').'.phar')
		);

		if(substr($this->blog->getPathApp(), 0, 7) === 'phar://'){
			$files[]	= rtrim(substr($this->blog->getPathApp(), 7), '/');
		}

		if($withModification){
			$files	= $this->blog->getFileSystem()->getModifiedTimesForFiles($files);
		}

		return $files;
	}


	/**
	 * @param array $newFilesListing	An array of filepaths
	 * @param string $cacheKey			The cache key for the directory listing
	 * @param array|null &$deletedFiles	Any files that were deleted (by reference)
	 * @return array
	 */
	protected function getChangedFiles($newFilesListing, $cacheKey, &$deletedFiles = null){
		$files			= $this->blog->getFileSystem()->getModifiedTimesForFiles($newFilesListing);
		$cachedFiles	= $this->blog->getCache()->get(self::CACHE_KEY_PREFIX.$cacheKey);

		$changedFiles	= array();
		$remainingFiles	= $cachedFiles;

		foreach($files as $file => $mtime){
			unset($remainingFiles[$file]);
			if(!isset($cachedFiles[$file]) || $cachedFiles[$file] < $mtime){
				// Changed
				$changedFiles[]	= $file;
			}
		}

		if(count($remainingFiles) > 0){
			$deletedFiles	= array_keys($remainingFiles);
		}

		return $changedFiles;
	}

};
