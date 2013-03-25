<?php
namespace Blight\Collections;

class Tag extends Collection implements \Blight\Interfaces\Collection {
	public function getURL(){
		return $this->blog->getURL('tag/'.$this->getSlug());
	}
};