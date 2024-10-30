=== HSS Embed Streaming Video ===
Author URI: http://www.hoststreamsell.com
Plugin URI: http://www.hoststreamsell.com
Contributors: hoststreamsell
Tags: streaming,video
Requires at least: 3.3
Tested up to: 5.7.1
Stable tag: 3.23
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html


Embed streaming videos through WordPress which are hosted on the HostStreamSell video platform

== Description ==

Easily embed a trailer version or full video version of a video which you are
hosting on the HostStreamSell platform through a simple shortcode. Great for
membership websites where you can put this link in a protected post.
Automatically embeds a flash,iOS,or Android video player based on teh user's
browser.

Features of the plugin include:

* add a shortcode like [hss-embed-video videoid="173" version="full"] to embed a full video

* add a shortcode like [hss-embed-video videoid="173" version="trailer"] to embed a trailer version of your video

* add a shortcode like [hss-embed-video videoid="173,157" version="full"] to embed multiple video players on the same page

* add a shortcode like [hss-embed-video videoid="173" version="full" download="true"] to embed a full video and include button to generate download links

More information at [HostStreamSell.com](http://hoststreamsell.com/).


== Installation ==

1. Activate the plugin
2. Go to Settings > HSS Embed Admin and enter API key from your HostStreamSell
account

== Frequently Asked Questions ==

= Does this work with other video platforms =

No this only works with the HostStreamSell video platform

== Screenshots ==

== Changelog ==

= 0.1 =

* Initial version uploaded to WordPress

= 0.2 =

* Added missing log function

= 0.4 =

* Responsive Player Support

= 0.5 =

* Fixed PHP open tag issue

= 0.6 =

* Added support for multiple video players on the same page

= 0.61 =

Fixed php short open tag

= 0.7 =

Added support to embed trailer version but dynamically show full version if logged in user has purchased video using another HSS plugin with EasyDigitalDownloads or WooCommerce

= 0.71 =

Fixed issue with 0.7 where trailer was sometimes shown instead of full video

= 0.72 =

Improvements to allow multiple embeds for the same video on a single page

= 0.73 =

Fixed checkin issue with 0.72

= 2.0 =

Added support to select JW Player version 6 or 7

= 2.2 =

* Added support for premium JWPlayer and Videojs HTML players using HLS and DASH streaming protocols

= 2.23 =

* Fixed issue with subtitles support for VTT files

= 2.24 =

Fixed some event logging

= 2.26 = 

Fix for missing get_the_user_ip function

= 2.28 =

Added support for player events on https websites

= 2.30 =

* add support for jwplayer v8

= 2.31 =

* fix for event ping for jwplayer on https sites 

= 2.33 =

* fix to use only HLS for JW Player 8

= 2.40 =

* add support to enable downloads links through shortcode

= 2.41 =

* fix for downloads

= 3.01 =

* added support for videojs 7 player

= 3.10 =

* Added Chromecast support for videojs7 player. Note that this will only work when your website is using https. Subtitle or alternative audio tracks are not currently supported

= 3.11 =

* Updated tested WordPress version

= 3.23 =

* Updates for m3u8 URL logic
