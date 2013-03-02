<?php
/** @var \Blight\Blog $blog */

function post_title(\Blight\Blog $blog, \Blight\Post $post, $title){
	return $title;
}

function post_url(\Blight\Blog $blog, \Blight\Post $post, $url){
	return $url;
}

function post_content(\Blight\Blog $blog, \Blight\Post $post, $content){
	if($post->is_linked()){
		// Append permalink link
		$content	.= "\n\n".'<p><a href="'.$post->get_permalink().'">∞ Permalink</a></p>';
	}

	return $content;
}



function create_node(\DOMDocument $document, \DOMElement $parent, $node_name, $content, $attributes = null, $callback = null){
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
	$parent->appendChild($node);
}

$now	= new \DateTime();

// Create XML file
$dom	= new \DOMDocument('1.0', 'UTF-8');

$root	= $dom->createElement('rss');
$root->setAttribute('version', '2.0');
$root->setAttribute('xmlns:atom', 'http://www.w3.org/2005/Atom');
//$root->setAttributeNS('xmlns:atom', 'http://www.w3.org/2005/Atom');

$channel	= $dom->createElement('channel');

	create_node($dom, $channel, 'title', $blog->get_name());
	create_node($dom, $channel, 'link', $blog->get_url());
	create_node($dom, $channel, 'description', $blog->get_description());
	create_node($dom, $channel, 'lastBuildDate', $now->format('r'));
	create_node($dom, $channel, 'atom:link', null, array(
		'href'	=> $blog->get_feed_url(),
		'rel'	=> 'self',
		'type'	=> 'application/rss+xml'
	));

	foreach($posts as $post){
		/** @var \Blight\Post $post */
		$item	= $dom->createElement('item');

			create_node($dom, $item, 'title', post_title($blog, $post, $post->get_title()));
			create_node($dom, $item, 'link', post_url($blog, $post, $post->get_link()));
			create_node($dom, $item, 'guid', $post->get_link(), array(
				'isPermaLink'	=> 'false'
			));
			create_node($dom, $item, 'pubDate', $post->get_date()->format('r'));
			create_node($dom, $item, 'description', $text->process_markdown(post_content($blog, $post, $post->get_content())));

		$channel->appendChild($item);
	}

$root->appendChild($channel);
$dom->appendChild($root);

// Output XML
echo $dom->saveXML();
