<?php
namespace Blight\Models\Collections;

class Category extends Collection implements \Blight\Interfaces\Models\Collection {
	public function getURL(){
		return $this->blog->getURL('category/'.$this->getSlug());
	}
};