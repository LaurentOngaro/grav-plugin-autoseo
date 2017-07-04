<?php

namespace AutoSeo\Metadata;

use Grav\Common\Plugin;
use RocketTheme\Toolbox\Event\Event;

class Keyword extends Plugin
{
	public static function getSubscribedEvents() {
		return [
			'onPageContentProcessed' => ['onPageContentProcessed', 0],
		];
	}

	public function onPageContentProcessed(Event $e)
	{
		$page = $e['page'];

		$available = [
			'header' => (array) $page->header(),
			'site' => $this->config->get('site')
		];

		$metadata = $page->metadata();
		if (array_key_exists('keyword', $metadata)) { $siteMetadataContent = $metadata['keyword']; } else { $siteMetadataContent = ''; }

		if (isset($available['header']['metadata']['keyword'])) {
			$pageMetadataContent=$available['header']['metadata']['keyword'];
		} else {
			$length = $this->config->get('plugins.autoseo.keyword.length');
			if ($length <=1 ) $length=20; 

			// we create a keyword list using the page tags and categories
			if (array_key_exists( 'category', $this->grav['page']->taxonomy() )) { $categories = $this->grav['page']->taxonomy()['category']; } else { $categories = ''; }
			if (array_key_exists( 'tag', $this->grav['page']->taxonomy() )) { $tags = $this->grav['page']->taxonomy()['tag']; } else { $tags = ''; }

			$autoContent = array_merge ($categories, $tags) ;
			$autoContent = array_unique ($autoContent);
			$autoContent = array_slice($autoContent, 0, $length);
			$autoContent = join(',',$autoContent);
			// remove some annoying characters
			$autoContent = str_replace("&nbsp;",' ',$autoContent);
			$autoContent = str_replace('"',"'",$autoContent);
			$autoContent = trim($autoContent);
			// Removes special chars.
			$autoContent = transliterator_transliterate('Any-Latin; Latin-ASCII; [\u0080-\u7fff] remove', $autoContent);
			$pageMetadataContent = $autoContent;
		}

		if (!empty($pageMetadataContent))
			$metadata['keyword'] = [ 'property' => 'keyword', 'content' => $pageMetadataContent];

		$page->metadata($metadata);
	}


}