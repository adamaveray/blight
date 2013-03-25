<?php
namespace Blight\Collections;

class Category extends Collection implements \Blight\Interfaces\Collection {
	public function getURL(){
		return $this->blog->getURL('category/'.$this->getSlug());
	}
};