=== Plugin Name ===
Contributors: mneil
Donate link: http://mneilsworld.com/donate
Tags: css, javascript, js, cascade, style, sheet, combine, compress, uglify, minify, closure, admin, aggregate, cache 
Requires at least: 3.3.1
Tested up to: 4.4.2
Stable tag: 1.1.6

Easily manage the merging and compression of js and css files from plugins and themes

== Description ==

Finds all possible .js and .css files from a WP install available and allows you to combine and/or compress the files to reduce 
load time. The plugin can monitor file changes in "development" mode (by hashing file mtime) which allows the plugin to 
recompile the files when a file changes. Or, it can cache the files in "production" mode so that files are only recompiled 
if they are not found or are deleted manually from the cache folder. Additionally, this plugin will allow you to force the 
inclusion of javascript files into either the head or the foot of the page.

There are two modes, development and production, the ability to force the files to print in the header or footer*, the use of 
Google Closure as a JS compiler, and finally the ability to pick and choose which files, including dependencies, should be combined.

*forcing head compiles can fail on JS files queued after the call to wp_head(). The plugin will, in this case, render the late 
queued files in the footer as originally intended.

== Installation ==


1. Upload `mncombine` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Visit the admin panel and choose your options

== Frequently Asked Questions ==

= Why does the first compile take so long with Google Closure OR why is development mode so slow to recache with Google Closure? =

Google Closure is a 3rd party JS compiler that the plugin must make requests to in order to receive compressed markup. And, because
we allow you to choose whether or not to combine some files we have to make multiple requests to the service to maintain dependency
inclusion. This results in a bit of latency when caching your files. However, in production mode, once the files are generated they
do not have to be regenerated again and load times are improved significantly. Using JSMin is much faster and recommended for use in 
conjunction with development mode.

= Can I exclude certain pages from using the plugin =

As of version 1.1.0 you can exclude pages from compiling/compressing css or js files using a regular expression in the general 
settings tab.


== Screenshots ==

1. Choose the compression settings and mode
2. Select which files to combine from a list of files available in your WP install, active plugins, and active theme
3. Total request of 17 uncompressed/combined files : 5136ms. 79.254kb of transfered data. Page load time 1.19s
4. Total request of 3 compressed/combined files : 578ms. 66kb of transfered data. Page load time 1.13s. Significantly more 
time is saved when comparing the browser cached results of the two requests.

== Changelog ==

= 1.1.6 =
* Admin page requires edit_posts capability

= 1.1.5 =
* Skip checking when script handle is empty

= 1.1.4 =
* Suppress errors on strstr($js, $use) when use is empty

= 1.1.3 =
* Verified working on WP version 3.8.1

= 1.1.2 =
* Verified against WP version 3.6.1
* Removed warning on login page where wp_scripts is not set

= 1.1.1 =
* Fixed Fatal Error on "Cache" admin page before cache folder had been generated

= 1.1.0 =
* Added ability to not combine css files based on REQUEST_URI
* Added ability to not combine js files based on REQUEST_URI

= 1.0.4 =
* Fixed bug where plugin combined all css if no css files were selected

= 1.0.3 =
* Option to compress css or not
* Dependency bug fixed when opting not to combine js files that share dependencies with compressed files

= 1.0.0 =
* The very first version in all it's glory
