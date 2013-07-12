<?php
namespace Blight\Models\Collections;

class Sequential extends Collection implements \Blight\Interfaces\Models\Collection {
	public function getURL($relative = false){
		$url	= $this->getSlug();
		if(!$relative){
			$url	= $this->blog->getURL($url);
		}
		return $url;
	}
};
