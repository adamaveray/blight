<?php
/** @var \Blight\Interfaces\Blog $blog */
/** @var \Blight\TextProcessor $text */
$createNode	= function(\DOMDocument $document, \DOMElement $parent, $nodeName, $content, $attributes = null, $callback = null){
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
	$parent->appendChild($node);
};

$now	= new \DateTime();

// Create XML file
$dom	= new \DOMDocument('1.0', 'UTF-8');

$root	= $dom->createElement('rss');
$root->setAttribute('version', '2.0');
$root->setAttribute('xmlns:atom', 'http://www.w3.org/2005/Atom');

$channel	= $dom->createElement('channel');

	$createNode($dom, $channel, 'title', $blog->getName());
	$createNode($dom, $channel, 'link', $blog->getURL());
	$createNode($dom, $channel, 'description', $blog->getDescription());
	$createNode($dom, $channel, 'lastBuildDate', $now->format('r'));
	$createNode($dom, $channel, 'atom:link', null, array(
		'href'	=> $blog->getFeedURL(),
		'rel'	=> 'self',
		'type'	=> 'application/rss+xml'
	));

	foreach($posts as $post){
		/** @var \Blight\Interfaces\Models\Post $post */
		$item	= $dom->createElement('item');

			$title	= $post->getTitle();
			$link	= $post->getLink();
			$date	= $post->getDate();
			$dateUpdated	= $post->getDateUpdated();
			$guid	= $post->getPermalink();
			$guidIsPermalink	= true;
			$author	= $blog->getAuthor();

			// Build post content
			$content	= $post->getContent();
			$processContent	= true;
			$append		= '';
			if($post->isLinked()){
				// Append permalink link
				$append	= "\n\n".'[∞ Permalink]('.$post->getPermalink().')';
			}

			$blog->doHook('feedPost', array(
				'feed_type'	=> 'rss',
				'post'		=> $post,
				'title'		=> &$title,
				'link'		=> &$link,
				'author'	=> &$author,
				'date_published'	=> &$date,
				'date_updated'		=> &$dateUpdated,
				'guid'		=> &$guid,
				'guid_is_permalink'	=> &$guidIsPermalink,
				'content'	=> &$content,
				'process_content'	=> &$processContent,
				'append'	=> &$append
			));

			$createNode($dom, $item, 'title', $title);
			$createNode($dom, $item, 'link', $link);
			$createNode($dom, $item, 'guid', $guid, array(
				'isPermaLink'	=> ($guidIsPermalink ? 'true' : 'false')
			));
			$createNode($dom, $item, 'pubDate', $date->format('r'));
			$createNode($dom, $item, 'description', ($processContent ? $text->minifyHTML($text->processMarkdown($content)) : $content).$append));
			if(isset($author) && $author->hasEmail()){
				$createNode($dom, $item, 'author', $author->getEmail().' ('.$author->getName().')');
			}

		$channel->appendChild($item);
	}

$root->appendChild($channel);
$dom->appendChild($root);

// Output XML
echo $dom->saveXML();
