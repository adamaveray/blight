<?php
namespace Blight\Models\Collections;

class Tag extends Collection implements \Blight\Interfaces\Models\Collection {
	public function getURL(){
		return $this->blog->getURL('tag/'.$this->getSlug());
	}
};