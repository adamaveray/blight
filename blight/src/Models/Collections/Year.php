<?php
namespace Blight\Models\Collections;

class Year extends Collection implements \Blight\Interfaces\Models\Collection {
	public function getURL($relative = false){
		$url	= 'archive/'.$this->getSlug();
		if(!$relative){
			$url	= $this->blog->getURL($url);
		}
		return $url;
	}
};
