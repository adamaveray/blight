<?php
/** @var \Blight\Interfaces\Blog $blog */
/** @var array $pages */

$createURL	= function(\DOMDocument $document, $url, $lastModified = null, $params = null){
	$createNode	= function(\DOMDocument $document, $nodeName, $content, $attributes = null, $callback = null){
		$node	= $document->createElement($nodeName);
		if(is_array($attributes)){
			foreach($attributes as $key => $value){
				$node->setAttribute($key, $value);
			}
		}
		if(is_callable($callback)){
			$callback($node);
		}
		if(isset($content)){
			$node->appendChild($document->createTextNode($content));
		}
		return $node;
	};


	$params	= array_merge(array(

	), (array)$params);

	$urlNode	= $document->createElement('url');

		$node	= $createNode($document, 'loc', $url);
		$urlNode->appendChild($node);

		if(isset($lastModified) && $lastModified instanceof \DateTime){
			$node	= $createNode($document, 'lastmod', $lastModified->format('c'));
			$urlNode->appendChild($node);
		}

		foreach($params as $param => $value){
			$node	= $createNode($document, $param, $value);
			$urlNode->appendChild($node);
		}

	return $urlNode;
};

$now	= new \DateTime();

// Create XML file
$dom	= new \DOMDocument('1.0', 'UTF-8');

$root	= $dom->createElement('urlset');
$root->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

// Create URLs
foreach($pages as $page){
	/** @var \Blight\Interfaces\Models\Page $page */
	$node	= $createURL($dom, $page->getPermalink(), $page->getDate());
	$root->appendChild($node);
}

$dom->appendChild($root);

// Output XML
echo $dom->saveXML();
