<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use AutoSeo\AutoSeo;

class AutoSeoPlugin extends Plugin
{
	public static function getSubscribedEvents() {
		return [
			'onPluginsInitialized' => ['onPluginsInitialized', 0],
		];
	}

	public function onPluginsInitialized()
	{
		$autoload = __DIR__ . '/vendor/autoload.php';
		
		if (!is_file($autoload)) {
			$this->grav['logger']->error('Auto SEO Plugin failed to load. Composer dependencies not met.');
		}

		require_once $autoload;

		$autoseo = new AutoSeo("AutoSeo",$this->grav, $this->config);
		$autoseo->init();
	}
}