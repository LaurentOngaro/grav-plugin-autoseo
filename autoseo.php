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
        $updateFacebook =  $config['facebook.enabled'];
        $updateTwitter =  $config['twitter.enabled'];
        if (!$updateDescription && !$updateKeyword && !$updateFacebook && !$updateTwitter) return

        $page = $this->grav['page'];
        $meta = $page->metadata(null);
        $available = [
            'header' => (array) $page->header(),
            'site' => $this->config->get('site')
        ];

        $cleanSummary = $this->cleanContent ($page, $config); 
        $cleanTitle = $this->cleanString ($page->title());

        if ($updateDescription) $meta = $this->getMetaDescription($meta, $available, $config, $cleanSummary);
        if ($updateKeyword) $meta = $this->getMetaKeywork($meta, $available, $config);
        if ($updateFacebook) $meta = $this->getFacebookMetatags ( $meta, $config, $cleanSummary, $cleanTitle);
        if ($updateTwitter) $meta = $this->getTwitterCardMetatags ( $meta, $config, $cleanSummary, $cleanTitle);

        $page->metadata ( $meta ) ;
    }
    
    // PROCESS for the description metadata
    private function getMetaDescription ( $meta, $available, $config, $cleanSummary ) {
        $page = $this->grav['page'];
        if (array_key_exists('description', $meta)) { $siteMetadataContent = $meta['description']; } else { $siteMetadataContent = ''; }

        if (isset($available['header']['metadata']['description'])) {
            $pageMetadataContent=$available['header']['metadata']['description'];
        } else {

            $pageMetadataContent = $cleanSummary;
        }
        if (!empty($pageMetadataContent)) {
            $meta['description'] = [ 'property' => 'description', 'content' => $pageMetadataContent];
        }
        return $meta;
    }
    
    // PROCESS for the keyword metadata
    private function getMetaKeywork ( $meta, $available, $config ) {
        $page = $this->grav['page'];
        if (array_key_exists('keyword', $meta)) { $siteMetadataContent = $meta['keyword']; } else { $siteMetadataContent = ''; }

        if (isset($available['header']['metadata']['keyword'])) {
            $pageMetadataContent=$available['header']['metadata']['keyword'];
        } else {
            $length = $config['keyword.length'];
            if ($length <=1 ) $length=20; 

            // we create a keyword list using the page tags and categories
            if (array_key_exists( 'category', $this->grav['page']->taxonomy() )) { $categories = $this->grav['page']->taxonomy()['category']; } else { $categories = []; }
            if (array_key_exists( 'tag', $this->grav['page']->taxonomy() )) { $tags = $this->grav['page']->taxonomy()['tag']; } else { $tags = []; }

            $content = array_merge ($categories, $tags) ;
            $content = array_unique ($content);
            $content = array_slice($content, 0, $length);
            $content = join(',',$content);
            $content = $this->cleanString($content);
            $pageMetadataContent = $content;
        }
        if (!empty($pageMetadataContent)) {
            $meta['keyword'] = [ 'property' => 'keyword', 'content' => $pageMetadataContent];
        }
        return $meta;
    }

    // PROCESS for the twitter metadata
    private function getTwitterCardMetatags ( $meta, $config, $cleanSummary, $cleanTitle) {
        $page = $this->grav['page'];
        if (!isset($meta['twitter:card'])) {
            $meta['twitter:card']['name']      = 'twitter:card';
            $meta['twitter:card']['property']  = 'twitter:card';
            //$meta['twitter:card']['content']   = $this->grav['config']->get('plugins.social-meta-tags.social_pages.pages.twitter.type');
            //$meta['twitter:card']['content']  = 'summary';
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
            $meta['twitter:description']['content']  = substr($cleanSummary,0,140);
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
/*
        if (!isset($meta['twitter:site'])) {
            //Use AboutMe plugin configuration
            if ($this->grav['config']->get('plugins.social-meta-tags.social_pages.pages.twitter.aboutme'))
            {
                if ($this->grav['config']->get('plugins.aboutme.social_pages.enabled')
                     and $this->grav['config']->get('plugins.aboutme.social_pages.pages.twitter.url'))
                {
                    $user = preg_replace('((http|https)://twitter.com/)', '@', $this->grav['config']->get('plugins.aboutme.social_pages.pages.twitter.url'));
                }
                else
                {
                    $user = "";
                }
            }
            //Use plugin self-configuration
            else
            {
                $user = "@".$this->grav['config']->get('plugins.social-meta-tags.social_pages.pages.twitter.username');
            }
            //Update data
            $meta['twitter:site']['name']     = 'twitter:site';
            $meta['twitter:site']['property'] = 'twitter:site';
            $meta['twitter:site']['content']  = $user;
        }
*/
        return $meta;
    }

    // PROCESS for the facebook metadata
    private function getFacebookMetatags ( $meta, $config, $cleanSummary, $cleanTitle) {
        $page = $this->grav['page'];
        $meta['og:sitename']['name']        = 'og:sitename';
        $meta['og:sitename']['property']    = 'og:sitename';
        $meta['og:sitename']['content']     = $this->config->get('site.title');
        $meta['og:title']['name']           = 'og:title';
        $meta['og:title']['property']       = 'og:title';
        $meta['og:title']['content']        = $cleanTitle;
        $meta['og:description']['name']     = 'og:description';
        $meta['og:description']['property'] = 'og:description';
        $meta['og:description']['content']  = $cleanSummary; 
        $meta['og:type']['name']            = 'og:type';
        $meta['og:type']['property']        = 'og:type';
        $meta['og:type']['content']         = 'article';
        $meta['og:url']['name']             = 'og:url';
        $meta['og:url']['property']         = 'og:url';
        $meta['og:url']['content']          = $this->grav['uri']->url(true);

        if (!empty($page->value('media.image'))) {
            $images = $page->media()->images();
            $image  = array_shift($images);
            $meta['og:image']['name']      = 'og:image';
            $meta['og:image']['property']  = 'og:image';
            $meta['og:image']['content']   = $this->grav['uri']->base() . $image->url();
        }
/*
        $meta['fb:app_id']['name']         = 'fb:app_id';
        $meta['fb:app_id']['property']     = 'fb:app_id';
        $meta['fb:app_id']['content']      = $this->grav['config']->get('plugins.social-meta-tags.social_pages.pages.facebook.appid');
*/
        return $meta;
    }
    
    private function sanitizeMarkdowns($text){
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
        $text=str_replace("\n", '', $text);
        $text=str_replace('"', '', $text);

        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    private function cleanContent ( $page, $config) {
        $page = $this->grav['page'];
        $length = $config['description.length'];       
        if ($length <=1 ) $length=20; 
        // limit the content size to reduce the performance impact
        $content = substr(strip_tags($page->summary()),0, 1000 );
        $content = $this->sanitizeMarkdowns($content);
        // truncate the content to the number of words set in config
        $content = preg_replace('/((\w+\W*){'.$length.'}(\w+))(.*)/', '${1}', $content);    
        return $content;
    }

    private function cleanString ( $str) {
        // remove some annoying characters
        $str = str_replace("&nbsp;",' ',$str);
        $str = str_replace('"',"'",$str);
        $str = trim($str);
        // Removes special chars.
        $str = transliterator_transliterate('Any-Latin; Latin-ASCII; [\u0080-\u7fff] remove', $str);
        return $str;
    }
}