<?php
namespace Blight\Models\Collections;

class Year extends Collection implements \Blight\Interfaces\Models\Collection {
	public function getURL(){
		return $this->blog->getURL('archive/'.$this->getSlug());
	}
};
