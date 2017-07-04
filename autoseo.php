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
		// deactivate plugin in admin 
		if ($this->isAdmin()) {
			$this->active = false;
			return;
		}

		$autoload = __DIR__ . '/vendor/autoload.php';
		
		if (!is_file($autoload)) {
			$this->grav['logger']->error(PLUGINS.AUTO_SEO.PLUGIN_DEPENDENCY_FAILED);
		}

		require_once $autoload;

		$autoseo = new AutoSeo("AutoSeo",$this->grav, $this->config);
		$autoseo->init();
	}
}