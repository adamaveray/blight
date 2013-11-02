<?php
$createNode	= function(\DOMDocument $document, \DOMElement $parent, $nodeName, $content, $attributes = null, $callback = null){
	$node	= $document->createElement($nodeName);
	if(is_array($attributes)){
		foreach($attributes as $key => $value){
			$node->setAttribute($key, $value);
		}
	}
	if(is_callable($callback)){
		$callback($node, $document);
	}
	if(isset($content)){
		$node->appendChild($document->createTextNode($content));
	}
	$parent->appendChild($node);
};

$filterHTML	= function($html){
	$tags	= array('script','link','meta');

	$document	= new \DOMDocument();
	$document->loadHTML($html);

	// Remove DOCTYPE
	$document->removeChild($document->firstChild);

	// Move elements out from body tag
	$parent	= $document->firstChild->firstChild;
	$nodes	= array();
	foreach($parent->childNodes as $node){
		/** @var \DOMNode $node */
		$nodes[]	= $node;
	}
	foreach($nodes as $node){
		$node	= $parent->removeChild($node);
		$document->appendChild($node);
	}
	$document->removeChild($document->firstChild);

	// Remove tags
	foreach($tags as $tag){
		$nodes	= $document->getElementsByTagName($tag);

		$removingElements	= array();
		foreach($nodes as $node){
			$removingElements[]	= $node;
		}

		foreach($removingElements as $node){
			$node->parentNode->removeChild($node);
		}
	}

	$html	= $document->saveHTML();
	return trim($html);
};
