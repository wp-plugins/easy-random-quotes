=== Easy Random Quotes ===
Contributors: trepmal 
Donate link: http://kaileylampert.com/donate/
Tags: random quotes, widget, plugin, shortcodes
Requires at least: 2.8
Tested up to: 3.0
Stable tag: trunk

Insert quotes and pull them randomly into your pages and posts (via shortcodes) or your template (via template tags). 

== Description ==

Works with 3.0. Insert quotes and pull them randomly into your pages and posts (via shortcodes) or your template (via template tags). 
Can refer to quote IDs to use specific quotes. Also widget-enabled

== Installation ==

1. Upload the contents of the zip file to the your plugins directory (default: `/wp-content/plugins/`)
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to the Easy Random Quotes page under Settings
4. Add/edit the quotes you'd like to use on your site
5. To display in a page or post, use the short code: [erq], or [erq id={id}] if you'd like to use only a specific quote
6. To add to your template, use the template tag: `<?php echo erq_shortcode(); ?>`, or `<?php echo erq_shortcode(array('id' => '{id}')); ?>`  if you'd like to use only a specific quote

== Screenshots ==

1. Admin screen
2. Widget
3. Shortcode

== Changelog ==

= 1.4 =
* added reset button to delete all quotes without uninstalling
* added title option in widget
* ready for localization

= 1.3 =
* fixed data storage/retrieval issue

= 1.2 =
* Actually fixed php error when saving data - seriously, if you'll tell me it's broken, I can fix it faster and I won't look like an idiot for so long...

= 1.1 =
* Fixed php error when saving data

= 1.0 =
* Initial Release