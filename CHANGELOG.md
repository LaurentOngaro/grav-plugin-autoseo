# v1.3.5
## 01/26/2018

1. [](#bugfix)
    * fix release dates in changelog.

# v1.3.3
## 01/26/2018

1. [](#bugfix)
    * replace meta title content by page title instead of page slug.

# v1.3.2
## 01/10/2018

1. [](#bugfix)
    * The meta tag fields were wrongly named "property" instead of "name".
    
# v1.3.1
## 08/03/2017

1. [](#improved)
    * Add Admin plugin dependency (Dávid Szabó <david.szabo97@gmail.com>)
    * Use Slug utility function (Dávid Szabó <david.szabo97@gmail.com)

# v1.3.0
## 07/31/2017

1. [](#bugfix)
    * the metadata were not overridden if some of them were already set in the site settings
    * the content was not used when its length in words was less than the value of the "length" settings
    * rename the metadata "keyword" to "keywords" (its valid name !)
2. [](#improved)
    * code optimizations
    * remove some code redundancies

# v1.2.6
## 07/30/2017

1. [](#bugfix)
    * fix: plugin was not running as expected after the 1.3.1 grav update

# v1.2.5
## 07/27/2017

1. [](#bugfix)
    * fix version numbering in changelog

# v1.2.4
## 07/27/2017

1. [](#bugfix)
    * fix a bug in changelog that prevents auto upgrading

# v1.2.3
## 07/27/2017

1. [](#bugfix)
    * fix a bug in changelog that prevents auto upgrading
    * remove useless dependency

# v1.2.2
## 07/27/2017

1. [](#improved)
    * minor changes in config files
	* update readme and changelog files

# v1.2.1
## 07/27/2017

1. [](#improved)
    * update readme file

# v1.2.0
## 07/26/2017

1. [](#new)
    * add metadata for Facebook Open Graph (thanks to Victor Rosset for the Social Meta Tags plugin source code I used for this part)
    * add metadata for Twitter Cards Meta Tags (thanks to Victor Rosset for the Social Meta Tags plugin source code I used for this part)
2. [](#improved)
    * heavy code refactoring

# v1.1.1
## 07/04/2017

1. [](#improved)
    * update readme file

# v1.1.0
## 07/04/2017

1. [](#improved)
    * simplify the plugin structure by removing subclasses and autoload
2. [](#bugfix)
    * per-page configuration is now fully functional

# v1.0.2
## 07/04/2017

1. [](#bugfix)
    * rename keywords.php to keyword.php

# v1.0.1
## 07/04/2017

1. [](#improved)
    * change "keywords" to "keyword" in source files to avoid confusion
    * minor changes in blueprints.yaml
    * change date format in CHANGELOG.md
2. [](#bugfix)
    * remove a forgotten "dump" line

# v1.0.0
## 07/04/2017

1. [](#improved)
    * enable auto fill for keyword metadata
2. [](#bugfix)
    * Fixed several issue before public release (see commit list for details)

# v0.1.0
## 07/04/2017

1. [](#new)
    * ChangeLog started...
