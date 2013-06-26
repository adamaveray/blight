<?php
namespace Blight\Models\Collections;

class Tag extends Collection implements \Blight\Interfaces\Models\Collection {
	public function getURL($relative = false){
		$url	= 'tag/'.$this->getSlug();
		if(!$relative){
			$url	= $this->blog->getURL($url);
		}
		return $url;
	}
};