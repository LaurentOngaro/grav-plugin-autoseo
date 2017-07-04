<?php

namespace AutoSeo;

use Grav\Common\Plugin;

class AutoSeo extends Plugin
{
	public function init()
	{
	    if ($this->config->get('plugins.autoseo.description.enabled')) {
				$this->grav['events']->addSubscriber(new Metadata\Description("AutoSeoDescription",$this->grav, $this->config));
	    }

	    if ($this->config->get('plugins.autoseo.keywords.enabled')) {
				$this->grav['events']->addSubscriber(new Metadata\Keywords("AutoSeoKeywords",$this->grav, $this->config));
	    }


	}
}

