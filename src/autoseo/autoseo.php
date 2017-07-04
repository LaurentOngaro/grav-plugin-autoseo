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

	    if ($this->config->get('plugins.autoseo.keyword.enabled')) {
				$this->grav['events']->addSubscriber(new Metadata\Keyword("AutoSeoKeyword",$this->grav, $this->config));
	    }


	}
}

