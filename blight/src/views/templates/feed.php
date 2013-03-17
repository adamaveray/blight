<?php
/** @var \Blight\Interfaces\Blog $blog */
$create_node	= function(\DOMDocument $document, \DOMElement $parent, $node_name, $content, $attributes = null, $callback = null){
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
};

$now	= new \DateTime();

// Create XML file
$dom	= new \DOMDocument('1.0', 'UTF-8');

$root	= $dom->createElement('rss');
$root->setAttribute('version', '2.0');
$root->setAttribute('xmlns:atom', 'http://www.w3.org/2005/Atom');

$channel	= $dom->createElement('channel');

	$create_node($dom, $channel, 'title', $blog->get_name());
	$create_node($dom, $channel, 'link', $blog->get_url());
	$create_node($dom, $channel, 'description', $blog->get_description());
	$create_node($dom, $channel, 'lastBuildDate', $now->format('r'));
	$create_node($dom, $channel, 'atom:link', null, array(
		'href'	=> $blog->get_feed_url(),
		'rel'	=> 'self',
		'type'	=> 'application/rss+xml'
	));

	foreach($posts as $post){
		/** @var \Blight\Interfaces\Post $post */
		$item	= $dom->createElement('item');

			$title	= $post->get_title();
			$link	= $post->get_link();
			$guid	= $post->get_permalink();
			$guid_is_permalink	= true;
			$date	= $post->get_date();

			// Build post content
			$content	= $post->get_content();
			$process_content	= true;
			$append		= '';
			if($post->is_linked()){
				// Append permalink link
				$append	= "\n\n".'[∞ Permalink]('.$post->get_permalink().')';
			}

			$blog->do_hook('feed_post', array(
				'post'		=> $post,
				'title'		=> &$title,
				'link'		=> &$link,
				'date_published'	=> &$date,
				'guid'		=> &$guid,
				'guid_is_permalink'	=> &$guid_is_permalink,
				'content'	=> &$content,
				'process_content'	=> &$process_content,
				'append'	=> &$append
			));

			$create_node($dom, $item, 'title', $title);
			$create_node($dom, $item, 'link', $link);
			$create_node($dom, $item, 'guid', $guid, array(
				'isPermaLink'	=> $guid_is_permalink
			));
			$create_node($dom, $item, 'pubDate', $date->format('r'));
			$create_node($dom, $item, 'description', ($process_content ? $text->process_markdown($content) : $content));

		$channel->appendChild($item);
	}

$root->appendChild($channel);
$dom->appendChild($root);

// Output XML
echo $dom->saveXML();
