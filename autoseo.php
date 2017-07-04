<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;

/**
 * Class AutoSeoPlugin
 * @package Grav\Plugin
 */
class AutoSeoPlugin extends Plugin
{
     /**
     * Initialize plugin and subsequent events
     * @return array
     */
    public static function getSubscribedEvents() {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
        ];
    }
    /**
     * Register events with Grav
     * @return void
     */
    public function onPluginsInitialized()
    {
        // deactivate plugin in admin 
        if ($this->isAdmin()) {
            $this->active = false;
        } else {
            $this->enable([
                'onPageContentRaw' => ['onPageContentRaw', 0]
            ]);
        }
    }

    /**
     * Add content after page content was read into the system.
     *
     */
    public function onPageContentRaw()
    {
        $page = $this->grav['page'];
    	$config = $this->mergeConfig($page);
    	if ( !$config['enabled']) return;

        $updateDescription =  $config['description.enabled'];
        $updateKeyword =  $config['keyword.enabled'];
        
        if (!$updateDescription && !$updateKeyword) return

        $available = [
            'header' => (array) $page->header(),
            'site' => $this->config->get('site')
        ];
        $metadata = $page->metadata();

        // PROCESS for the description metadata
        if ($updateDescription ) {
            if (array_key_exists('description', $metadata)) { $siteMetadataContent = $metadata['description']; } else { $siteMetadataContent = ''; }

            if (isset($available['header']['metadata']['description'])) {
                $pageMetadataContent=$available['header']['metadata']['description'];
            } else {
                $length = $config['description.length'];       
                if ($length <=1 ) $length=20; 

                // we create a description using the page content
                $autoContent = $page->rawMarkdown();
                // limit the content size to reduce the performance impact
                $autoContent = substr($autoContent,0, 1000 );
                // remove some annoying characters
                $autoContent = str_replace("\n",' ',$autoContent);
                $autoContent = str_replace("&nbsp;",' ',$autoContent);
                $autoContent = str_replace('"',"'",$autoContent);
                $autoContent = trim($autoContent);
                // Removes special chars.
                $autoContent = transliterator_transliterate('Any-Latin; Latin-ASCII; [\u0080-\u7fff] remove', $autoContent);
                // remove headers
                $autoContent = preg_replace('/[#]+ /', '', $autoContent); 
                // truncate the content to the number of words set in config
                $autoContent = preg_replace('/((\w+\W*){'.$length.'}(\w+))(.*)/', '${1}', $autoContent);    

                $pageMetadataContent = $autoContent;
            }
            if (!empty($pageMetadataContent)) {
                $metadata['description'] = [ 'property' => 'description', 'content' => $pageMetadataContent];
            }
        }
        
        if ($updateKeyword) {
            // PROCESS for the keyword metadata
            if (array_key_exists('keyword', $metadata)) { $siteMetadataContent = $metadata['keyword']; } else { $siteMetadataContent = ''; }

            if (isset($available['header']['metadata']['keyword'])) {
                $pageMetadataContent=$available['header']['metadata']['keyword'];
            } else {
                $length = $config['keyword.length'];
                if ($length <=1 ) $length=20; 

                // we create a keyword list using the page tags and categories
                if (array_key_exists( 'category', $this->grav['page']->taxonomy() )) { $categories = $this->grav['page']->taxonomy()['category']; } else { $categories = []; }
                if (array_key_exists( 'tag', $this->grav['page']->taxonomy() )) { $tags = $this->grav['page']->taxonomy()['tag']; } else { $tags = []; }

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
            if (!empty($pageMetadataContent)) {
                $metadata['keyword'] = [ 'property' => 'keyword', 'content' => $pageMetadataContent];
            }
        }

        // Update page metadata
        $page->metadata($metadata);
    }
}