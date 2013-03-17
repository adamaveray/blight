<?php
/** @var \Blight\Interfaces\Blog $blog */
/** @var array $pages */

$sitemap_create_url	= function(\DOMDocument $document, $url, $last_modified = null, $params = null){
	$sitemap_create_node	= function(\DOMDocument $document, $node_name, $content, $attributes = null, $callback = null){
		$node	= $document->createElement($node_name);
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

	$url_node	= $document->createElement('url');

		$node	= $sitemap_create_node($document, 'loc', $url);
		$url_node->appendChild($node);

		if(isset($last_modified) && $last_modified instanceof \DateTime){
			$node	= $sitemap_create_node($document, 'lastmod', $last_modified->format('c'));
			$url_node->appendChild($node);
		}

		foreach($params as $param => $value){
			$node	= $sitemap_create_node($document, $param, $value);
			$url_node->appendChild($node);
		}

	return $url_node;
};

$now	= new \DateTime();

// Create XML file
$dom	= new \DOMDocument('1.0', 'UTF-8');

$root	= $dom->createElement('urlset');
$root->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

// Create URLs
foreach($pages as $page){
	/** @var \Blight\Interfaces\Page $page */
	$node	= $sitemap_create_url($dom, $page->get_permalink(), $page->get_date());
	$root->appendChild($node);
}

$dom->appendChild($root);

// Output XML
echo $dom->saveXML();
