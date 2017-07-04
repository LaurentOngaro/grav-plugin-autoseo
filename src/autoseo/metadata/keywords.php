<?php

namespace AutoSeo\Metadata;

use Grav\Common\Plugin;
use RocketTheme\Toolbox\Event\Event;

class Keywords extends Plugin
{
	public static function getSubscribedEvents() {
		return [
			'onPageProcessed' => ['onPageProcessed', 0],
		];
	}

	public function onPageProcessed(Event $e)
	{
		$page = $e['page'];

		$available = [
			'page' => (array) $page->header(),
			'site' => $this->config->get('site')
		];

		$metadata = $page->metadata();
		$length = $this->config->get('plugins.autoseo.keywords.length');

		if (empty($metadata['keywords']))
			$metadata['keywords'] = [ 'property' => 'keywords', 'content' => $available['page']['metadata']['keywords'] ];

		$page->metadata($metadata);
	}


}