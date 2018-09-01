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
         if (    !$this->isAdmin()
            and $this->config->get('plugins.autoseo.enabled')
        ) {
            $this->enable([
                'onPageInitialized' => ['onPageInitialized', 0]
            ]);
        }
    }

    /**
     * Add content after page content was read into the system.
     *
     */
    public function onPageInitialized()
    {
        $page = $this->grav['page'];
    	$config = $this->mergeConfig($page);
    	if ( !$config['enabled']) return;

        $updateDescription =  $config['description.enabled'];
        $updatekeywords =  $config['keywords.enabled'];
        $updateFacebook =  $config['facebook.enabled'];
        $updateTwitter =  $config['twitter.enabled'];
        if (!$updateDescription && !$updatekeywords && !$updateFacebook && !$updateTwitter) return;

        $meta = $page->metadata();

        $metaSite = $this->config->get('site')['metadata'];
        
        // limit the content size to reduce the performance impact
        $content = mb_substr(strip_tags($page->content()),0, 1000 );

        $cleanContent = $this->cleanText ($content, $config); // here because we don't want to make this call several times
        $cleanTitle = $this->cleanString ($page->title()); // here because we don't want to make this call several times

        if ($updateDescription) $meta = $this->getMetaDescription ($meta, $metaSite, $config, $cleanContent);
        if ($updatekeywords) $meta = $this->getMetaKeywords ($meta, $metaSite, $config);
        if ($updateFacebook) $meta = $this->getMetaOpenGraph ($meta, $metaSite, $config, $cleanContent, $cleanTitle);
        if ($updateTwitter) $meta = $this->getMetaTwitter ($meta, $metaSite, $config, $cleanContent, $cleanTitle);
        $page->metadata ($meta);
    }
    
    // PROCESS for the description metadata
    private function getMetaDescription ($meta, $metaSite, $config, $cleanContent) {

        if (array_key_exists('description', $metaSite)) { $metaSiteContent =  htmlspecialchars($metaSite['description'], ENT_QUOTES, 'UTF-8'); } else { $metaSiteContent = ''; }
        // if the page has a meta that is different from the default one, we return its value
        if (!empty($meta['description']['content']) && $meta['description']['content'] != $metaSiteContent) return $meta;

        $metaPageContent = $cleanContent;

        if (empty($metaPageContent)) $metaPageContent = $metaSiteContent;

        $meta['description'] = [ 'name' => 'description', 'content' => $metaPageContent];
        return $meta;
    }
    
    // PROCESS for the keywords metadata
    private function getMetaKeywords ($meta, $metaSite, $config) {
        $page = $this->grav['page'];

        if (array_key_exists('keywords', $metaSite)) { $metaSiteContent =  htmlspecialchars($metaSite['keywords'], ENT_QUOTES, 'UTF-8'); } else { $metaSiteContent = ''; }

        // if the page has a meta that is different from the default one, we return its value
        if (!empty($meta['keywords']['content']) && $meta['keywords']['content'] != $metaSiteContent) return $meta;
        $length = $config['keywords.length'];
        if ($length <=1 ) $length=20; 

        // we create a keywords list using the page tags and categories
        if (array_key_exists( 'category', $page->taxonomy() )) { $categories = $page->taxonomy()['category']; } else { $categories = []; }
        if (array_key_exists( 'tag', $page->taxonomy() )) { $tags = $page->taxonomy()['tag']; } else { $tags = []; }

        $content = array_merge ($categories, $tags) ;
        $content = array_unique ($content);
        $content = array_slice($content, 0, $length);
        $content = join(',',$content);
        $content = $this->cleanString($content);
        $metaPageContent = $content;
        if (empty($metaPageContent)) $metaPageContent = $metaSiteContent;

        $meta['keywords'] = [ 'name' => 'keywords', 'content' => $metaPageContent];
        return $meta;
    }

    // PROCESS for the OpenGraph metadata
    private function getMetaOpenGraph ($meta, $metaSite, $config, $cleanContent, $cleanTitle) {
        $page = $this->grav['page'];        

        $meta['og:sitename']['name']        = 'og:sitename';
        $meta['og:sitename']['property']    = 'og:sitename';
        $meta['og:sitename']['content']     = $this->config->get('site.title');
        $meta['og:title']['name']           = 'og:title';
        $meta['og:title']['property']       = 'og:title';
        $meta['og:title']['content']        = $cleanTitle;
        $meta['og:type']['name']            = 'og:type';
        $meta['og:type']['property']        = 'og:type';
        $meta['og:type']['content']         = 'article';
        $meta['og:url']['name']             = 'og:url';
        $meta['og:url']['property']         = 'og:url';
        $meta['og:url']['content']          = $this->grav['uri']->url(true);
        $meta['og:description']['name']     = 'og:description';
        $meta['og:description']['property'] = 'og:description';
        if (empty($cleanContent)) $cleanContent = $meta['description']['content'];
        else {
            if (array_key_exists('description', $metaSite)) { $metaSiteContent =  htmlspecialchars($metaSite['description'], ENT_QUOTES, 'UTF-8'); } else { $metaSiteContent = ''; }
            if ($meta['description']['content'] != $metaSiteContent) $cleanContent = $meta['description']['content'];
        }

        $meta['og:description']['content']  = $cleanContent; 

        if (!empty($page->value('media.image'))) {
            $images = $page->media()->images();
            $image  = array_shift($images);
            $meta['og:image']['name']      = 'og:image';
            $meta['og:image']['property']  = 'og:image';
            $meta['og:image']['content']   = $this->grav['uri']->base() . $image->url();
        }
        return $meta;
    }
    
    // PROCESS for the twitter metadata
    private function getMetaTwitter ($meta, $metaSite, $config, $cleanContent, $cleanTitle) {
        $page = $this->grav['page'];   

        if (!isset($meta['twitter:card'])) {
            $meta['twitter:card']['name']      = 'twitter:card';
            $meta['twitter:card']['property']  = 'twitter:card';
            $meta['twitter:card']['content']  = 'summary_large_image';
        }

        if (!isset($meta['twitter:title'])) {
            $meta['twitter:title']['name']     = 'twitter:title';
            $meta['twitter:title']['property'] = 'twitter:title';
            $meta['twitter:title']['content']  = $cleanTitle;
        }

        if (!isset($meta['twitter:description'])) {
            $meta['twitter:description']['name']     = 'twitter:description';
            $meta['twitter:description']['property'] = 'twitter:description';
            if (empty($cleanContent)) 
                $cleanContent = $meta['description']['content'];
            else {
                if (array_key_exists('description', $metaSite)) { $metaSiteContent =  htmlspecialchars($metaSite['description'], ENT_QUOTES, 'UTF-8'); } else { $metaSiteContent = ''; }
                if ($meta['description']['content'] != $metaSiteContent) $cleanContent = $meta['description']['content'];
            }
            $meta['twitter:description']['content']  = mb_substr($cleanContent,0,140); 
        }

        if (!isset($meta['twitter:image'])) {
            if (!empty($page->value('media.image'))) {
                $images = $page->media()->images();
                $image  = array_shift($images);
                $meta['twitter:image']['name']     = 'twitter:image';
                $meta['twitter:image']['property'] = 'twitter:image';
                $meta['twitter:image']['content']  = $this->grav['uri']->base() . $image->url();
            }
        }
        return $meta;
    }

    private function cleanMarkdown($text){
        $rules = array (
            '/(#+)(.*)/'                             => '\2',  // headers
            '/(&lt;|<)!--\n((.*|\n)*)\n--(&gt;|\>)/' => '',    // comments
            '/(\*|-|_){3}/'                          => '',    // hr
            '/!\[([^\[]+)\]\(([^\)]+)\)/'            => '',    // images
            '/\[([^\[]+)\]\(([^\)]+)\)/'             => '\1',  // links
            '/(\*\*|__)(.*?)\1/'                     => '\2',  // bold
            '/(\*|_)(.*?)\1/'                        => '\2',  // emphasis
            '/\~\~(.*?)\~\~/'                        => '\1',  // del
            '/\:\"(.*?)\"\:/'                        => '\1',  // quote
            '/```(.*)\n((.*|\n)+)\n```/'             => '\2',  // fence code
            '/`(.*?)`/'                              => '\1',  // inline code
            '/(\*|\+|-)(.*)/'                        => '\2',  // ul lists
            '/\n[0-9]+\.(.*)/'                       => '\2',  // ol lists
            '/(&gt;|\>)+(.*)/'                       => '\2',  // blockquotes
        );
        foreach ($rules as $regex => $replacement) {
            if (is_callable ( $replacement)) {
                $text = preg_replace_callback ($regex, $replacement, $text);
            } else {
                $text = preg_replace ($regex, $replacement, $text);
            }
        }
        $text=str_replace(".\n", '.', $text);
        $text=str_replace("\n", '.', $text);
        $text=str_replace('"', '', $text);

        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    private function cleanText ($content, $config) {
        $length = $config['description.length'];       
        if ($length <=1 ) $length=20; 
 
        $content = $this->cleanMarkdown($content);
        // truncate the content to the number of words set in config
        $contentSmall = mb_ereg_replace('((\w+\W*){'.$length.'}(\w+))(.*)', '${1}', $content); // beware if content is less than length words, it will be nulled    
        if ($contentSmall == '' ) $contentSmall = $content;
 
        return $contentSmall;
    }

    private function cleanString ($content) {
        // remove some annoying characters
        $content = str_replace("&nbsp;",' ',$content);
        $content = str_replace('"',"'",$content);
        $content = trim($content);
        // Removes special chars.
        // $content = \Grav\Plugin\Admin\Utils::slug($content);
        return $content;
    }
}
