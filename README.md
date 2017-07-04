# [Grav](http://getgrav.org) AutoSEO

AutoSEO is a plugin for Grav with which you can fill automatically the description and keyword metadata of a page using its content.

If the plugin is enabled and **the description and keyword metadata of the page are not manually filled** (i.e. the page headers contains some metadata fields),
the plugin will use the first words of the page content to fill the description metadata and its categories and tags to fill the keyword metadata.

## Installation

### Manual Installation

To install this plugin, just download the zip version of this repository and unzip it under `/your/site/grav/user/plugins`. Then rename the folder to `autoseo`.

You should now have all the plugin files under

`/your/site/grav/user/plugins/autoseo`

## Configuration

Here is the default configuration and an explanation of available options:

`autoseo.yaml:`

```yaml
enabled: true # lets you turn the plugin off and on
description:
  enabled: true # lets you turn the plugin off and on for the description metadata only
  length: 30 # maximal count of words that will be used to fill the description metadata.
keyword:
  enabled: true # lets you turn the plugin off and on for the keyword metadata only
  length: 20 # maximal count of words that will be used to fill the keyword metadata.
```

  * The first `enabled` field lets you turn the plugin on and off.
  * The `enabled` field in each metadata section lets you turn on and off the plugin for this metadata only.
  * `length` is the maximal count of words that will be used to fill the corresponding metadata.

If you need to change any value, then the best process is to copy the `autoseo.yaml` file into your `users/config/plugins/` folder (create it if it doesn't exist), and then modify there. This will override the default settings.

## Per-Page Configuration

If you want to alter the settings for one or a few pages only, you can do so by adding page specific configurations into your page headers, e.g.

```yaml
autoseo: false
```
to disable the `AutoSeo` plugin just for this page.

Remember you can also override the general plugin settings for a specific page by adding settings in its header. For instance, adding the following lines in a page headers

```yaml
autoseo:
  enabled: true
  description:
    enabled: true
    length: 10
  keyword:
    enabled: true
    length: 10
```

will change the description and keyword lengths to 10 words for these page only.

## Usage

There's not much to do with this plugin, simply install, enabled it and check its configuration to meet your needs.

No need to do anything else.

### Performances

As each page must be analyzed and its content filtered by the plugins, the impact on performance can be important when first access to a page.
But as the result will be cached, once the first analysis is done, you will find no further negative performance impact.

To limit the impact on performance, **the content analyzed will always be truncated to the 1000 first characters for description metadata**, then the limit given by the `length` setting will be applied.

### Notes

`AutoSeo` will make a few cleaning but won't check the content used to auto fill the metadata.
So be sure that the first words of your content (the summary) will be correct to be used as a description.
In a same way, check that the tags and categories of the page are OK to be used as keyword.

### Bugs And TODO

Please send any comments or bug reports to the plugin's issue tracker.

#### TODO

### Authors

`AutoSeo` is developed and maintained by Laurent Ongaro.
