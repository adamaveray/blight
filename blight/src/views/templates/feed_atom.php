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
		$callback($node, $document);
	}
	if(isset($content)){
		$node->appendChild($document->createTextNode($content));
	}
	$parent->appendChild($node);
};

$now	= new \DateTime();
$blogAuthor	= $blog->getAuthor();


// Create XML file
$dom	= new \DOMDocument('1.0', 'UTF-8');

$root	= $dom->createElement('feed');
$root->setAttribute('xmlns', 'http://www.w3.org/2005/Atom');
$root->setAttribute('xml:lang', 'en-US');

	$createNode($dom, $root, 'title', $blog->getName());
	$createNode($dom, $root, 'subtitle', $blog->getDescription());
	$createNode($dom, $root, 'link', null, array(
		'rel'	=> 'self',
		'type'	=> 'application/atom+xml',
		'href'	=> $blog->getFeedURL()
	));
	// Link to homepage
	$createNode($dom, $root, 'link', null, array(
		'rel'	=> 'alternate',
		'type'	=> 'text/html',
		'href'	=> $blog->getURL()
	));
	$createNode($dom, $root, 'id', $blog->getFeedURL());
	$createNode($dom, $root, 'updated', $now->format('c'));
	if(isset($blogAuthor)){
		$createNode($dom, $root, 'rights', 'Copyright © '.$now->format('Y').' '.$blogAuthor->getName());
	}

	foreach($posts as $post){
		/** @var \Blight\Interfaces\Models\Post $post */
		$entry	= $dom->createElement('entry');

			$title	= $post->getTitle();
			$link	= $post->getLink();
			$date	= $post->getDate();
			$dateUpdated	= $post->getDateUpdated();
			$guid	= $post->getPermalink();
			$guidIsPermalink	= true;
			$author	= $blog->getAuthor();

			// Build post content
			$summary	= $post->getSummary();
			$content	= $post->getContent();
			$processContent	= true;
			$append		= '';
			if($post->isLinked()){
				// Append permalink link
				$append	= "\n\n".'[∞ Permalink]('.$post->getPermalink().')';
			}

			$blog->doHook('feed_post', array(
				'feed_type'	=> 'atom',
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
				'append'	=> &$append,
				'summary'	=> &$summary
			));

			$createNode($dom, $entry, 'title', $post->getTitle());
			$createNode($dom, $entry, 'link', null, array(
				'rel'	=> 'alternate',
				'type'	=> 'text/html',
				'href'	=> $post->getLink()
			));
			$createNode($dom, $entry, 'id', $guid);
			$createNode($dom, $entry, 'published', $post->getDate()->format('c'));
			$createNode($dom, $entry, 'updated', $dateUpdated->format('c'));
			if(isset($author)){
				$createNode($dom, $entry, 'author', null, null, function(\DOMElement $authorElement, \DOMDocument $dom) use($createNode, $post, $author){
					$createNode($dom, $authorElement, 'name', $author->getName());
					if($author->hasEmail()){
						$createNode($dom, $authorElement, 'email', $author->hasEmail());
					}
					if($author->hasURL()){
						$createNode($dom, $authorElement, 'uri', $author->hasURL());
					}
				});
			}

			if(isset($summary)){
				$createNode($dom, $entry, 'summary', $summary, array(
					'xml:lang'	=> 'en-US'
				));
			}

			$base_url	= $post->getPermalink();

			$node	= $dom->createElement('content');
				$node->setAttribute('type', 'html');
				$node->setAttribute('xml:base', $base_url);
				$node->setAttribute('xml:lang', 'en-US');

				$node->appendChild($dom->createCDATASection(($processContent ? $text->minifyHTML($text->processMarkdown($content)) : $content).$append));
			$entry->appendChild($node);

		$root->appendChild($entry);
	}

$dom->appendChild($root);


// Output XML
echo $dom->saveXML();
