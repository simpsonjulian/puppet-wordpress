=== Adsense-Deluxe ===
Tags: adsense,google,marketing
Contributors: Wayne Walrath



===================================================================
WP-AdSense-Deluxe plugin for Wordpress 1.5+ and 2.0
Version 0.8
Author: Wayne Walrath, Acme Technologies http://www.acmetech.com/
URL: http://www.acmetech.com/blog/adsense-deluxe/
email: support@acmetech.com
Last Update: June 27, 2006
===================================================================




About
-----
WP-AdSense-Deluxe lets you define one or more Google adsense ad blocks (or Yahoo Publisher Network-YPN ads) which can then be easily inserted into any post (including multiple ad blocks per post) simply by adding a comment within the post of the form:
<!--adsense-->		(to use the ad block defined as the default block)
<!--adsense#NAME-->	(to use a named block other than the default block)


Features
--------
* Updated for WordPress 2.0's Rich Editor (WYSIWYG). (see WP2.0 notes later in file)
* Includes an Options page within WordPress Options Admin
* Works the same for AdSense or Yahoo Publisher Network
* Define any number of named blocks, each with a description to remind you what the block's format is.
* Enable/disable any ad or all ads globally from the Options page.
* Includes an integrated AdSense preview tool to see which ads Google will show for a given web page (also available separately at http://www.acmetech.com/tools/adsense-preview/ ).
* A plugin function for using any of your adsense ad units from within template files. (since v0.5)
* Tested on WordPress 1.5. Will not work for earlier versions of WP (I haven't tested with pre-1.5, but I'm 99% certain it's incompatible).
* (Optionally) Apply CSS formatting to the AdSense code.
* Does not display live ads when editing a page, instead placeholders are shown.
* Ads are EXCLUDED from RSS feeds Ñ even if you have full-text feeds enabled. (new in v0.3)
* Automatic weekly version checking (since v0.5).

Upgrading from Previous Versions (if you're upgrading from 1.5 to 2.x, please read notes for 2.0 in this file)
-------------------------------
Just replace the AdSense-Deluxe.php file in .../wp-content/plugins/ with the latest version. You will not lose (unless I screwed up) any of your preferences or existing settings.



Installation
------------
* Copy the following files to the appropriate locations:
  /wp-content/plugins/
    |
    +- adsense-deluxe.php

* Go to the WordPress plugins admin panel then activate the Adsense-Deluxe plugin.
* Goto WordPress Options then select "AdSense" from the Options submenu to configure adsense blocks.

===[ WORDPRESS 2.0 INSTALLATION NOTES ]===

This section only applies ( !!!! ) if you're using WordPress 2.0 and want a popup menu for your Adsense Ads in the Rich Editor (WYSIWYG/tinyMCE). NOTE: You can disable or enable the "Rich Editor by going to "Personal Options" in your user login profile (look for "Use the visual rich editor when writing" checkbox).

If you're using WordPress 2.0 or later and insist upon editing Posts and Pages using the (WYSIWYG) Rich Editor feature (I recommend against it), you can install the QuickTag item with the following process:

	- Inside the directory "Optional_WordPress_2.0_Items" is a directory named "adsense_deluxe". Upload "adsense_deluxe" directory to your TinyMCE plugins directory on your web server: /wordpress/wp-includes/js/tinymce/plugins/

(DO NOT UPLOAD THE "Optional_WordPress_2.0_Items" DIRECTORY; IT MUST BE THE DIRECTORY INSIDE OF IT!)

	- [If and ONLY IF you performed the previous step AND are using WordPress 2.0] edit the file /wordpress/wp-includes/js/tinymce/tiny_mce_gzip.php as follows:

      -- Add 'adsense_deluxe' to the plugins filter line.  It should look like this:
 	$plugins = apply_filters('mce_plugins', array('wordpress', 'autosave', 'wphelp', 'adsense_deluxe'));

      -- Add 'adsense_deluxe' to the button filter line.  It should look like this (if you want the button to appear somewhere else, move it):
	$mce_buttons = apply_filters('mce_buttons', array('bold', 'italic', 'strikethrough', 'separator', 'bullist', 'numlist', 'outdent', 'indent', 'separator', 'justifyleft', 'justifycenter', 'justifyright' ,'separator', 'link', 'unlink', 'image', 'wordpress', 'separator', 'undo', 'redo', 'adsense_deluxe', 'code', 'wphelp'));

==========================================


Configuration
-------------
There's really no configuration needed after installation. Just goto the Options page in WordPress Admin, then choose Adsense from the submenu and begin adding your adsense code blocks.

If you have a version of PHP earlier than 4.0.6 installed, and magic_quotes_gpc enabled, you may not be able to use the plug-in at this time. I use a function named 'array_map()' which was introduced in php v4.0.6. to simplify stripping the slashes from all Get/Posts/Cookie input fields in one pass.



Questions/Suggestions/Bugs
--------------------------
Please go to http://www.acmetech.com/products/wordpress/adsense-deluxe/ and there you'll find a link to a blog entry where you can post comments/suggestions/bugs.

** Please!! ** If you're having some kind of runtime issue which looks like it's a PHP problem, you'll make it much easier for me to figure out what's wrong if you can show me your php configuration settings. The easiest way to do that is to create a text file at the root of your server (name it whatever you like with a '.php' suffix, such as myinfo.php) with this line in it:

	<?php  phpinfo();  ?>

Then email me (support@acmetech.com) the URL to that file.



Future Directions
----------------
- Show all posts where a given adsense block (the comment code) has been included.
- Delete all adsense comments from posts for a given block.
- Preview one of your adsense blocks in a post (not sure if this is really worth the effort yet).

Version History
---------------
0.7 2006-01-09
- First release for WordPress 2.0 WYSIWYG Rich Editor support. Bugs may exist with that feature!

0.5 2005-09-29
- [Per suggestion from Tim Gallagher] Added function which can be used from your templates to output one of your defined ad code blocks:
	
	adsense_deluxe_ads([ad_name]);

The PHP code would look like this:
	<?php adsense_deluxe_ads('my_AdBlock_name'); ?>

The parameter is optional; if you call it with no parameter, it writes the default ad block, otherwise it tries to match the string parameter to a named adsense block you've defined within the AdSense-Deluxe control panel and writes that code to the output stream. It honors your current settings for whether a given ad is disabled, or ads overall are disabled. In principle, this gives you the same global management of AdSense ads you place within your templates as the plug-in provides for control of ads within the Posts.

Additionally, by using that plugin function within your templates, the Adsense-Deluxe plugin is able to keep track of how many AdSense ads are used on a given page and will attempt to not place more than three (Google's maximum), but there's a caveat which is explained below, for those who crave details.

**IMPORTANT**
If you use the adsense_deluxe_ads() function in your templates or anywhere else, it's highly recommended that you first check that the function is defined, otherwise if you deactivate the plugin and have not removed those calls, you will get errors and your pages may not even render (dependent upon whether you have 'display_errors' set to true in the PHP configuration). So please use the following conditional syntax:

<?php if(function_exists('adsense_deluxe_ads')) : adsense_deluxe_ads('my_ad_unit'); endif; ?>



- Added automatic version checking.  I added a feature which is designed to check for updates to the plugin no sooner than once every seven days. It only makes the check when you're in the AdSense-Deluxe options screen. Not on a continual basis. This feature requires loading a file from the acmetech.com web server which contains the plugin's latest version number, but other than the data which the server logs for every hit, NOTHING else is transmitted! The version-check file loaded is located at this URL: http://software.acmetech.com/wordpress/plugins/adsense-deluxe-version.txt
When a newer version is found, a message will be displayed at the top of the plugin's Options page linked to the plugin home page. If you discover any flaws in when a newer version is reported, please email me with the details since I'm only 90% confident that all the logic is correct.

- Other minor modifications (too trivial to list).

* Tracking Number of Ads Shown:
the plugin keeps a global variable which counts how many ads have been substituted into a displayed page, including -- if you use the new function described above -- ads in templates. HOWEVER, the logic isn't perfect yet, and it's still possible that a page will get more than three adsense blocks. The problem is illustrated with this example: you use the function above to place one ad block within the template on a single Post page, and have three ad blocks within the post's content. The template ads are recorded which sets the tracking variable to "1". Then the plugin is asked to perform substitutions on the content of your post containing 3 adsense placeholders which will put us over the limit, but the plugin is using the most efficient search/replace function built into PHP  which doesn't allow for only replacing the first one or two matches. So until (and if) I refine that code, you will still potentially get more than three adsense ad blocks within a page. This is less of a problem if you specify the google_alternate_color option when creating the ad code, and you'll have less empty space in your page by doing so. I hope to deal with this issue one day, but for now it's just not high on my wish list and I suspect that solving the problem will require more CPU cycles every time a page is served.

0.8 2005-08-13
- Fixed bug in "reward author" option.

0.7 2006-01-10
- Initial support for WordPress 2.0 Rich Editor.
- Minor documentation changes.

0.6 2005-08-13
- Bug fixes
- Fixed problem with packaging the zip file under OS X.

0.5 2005-10-03
- Added PHP function for inserting ads into WordPress template files (see ReadMe file for usage).
- Automatic version checking (checks every 7 days for newer plugin versions)

0.4 2005-08-03
- QuickTag menu now displays properly when wp-tiger-admin plugin is in use.
- Added ability to click on your ad descriptions in AdSense-Deluxe Options to view the ad style.

0.3 2005-08-02
- Fixed problem of AdSense showing up in Full-Text RSS feeds.- Fixed call-time pass-by-reference warnings from PHP. [thanks "kashaziz"]- No longer "rewarding author" on anything other than Post or Page pages. [thanks "BeatYou", Angela]- Fixed problem with only two (2) ads being shown on a given page.- Added AdSense-Deluxe quicktag menu to post editor.- Stopped showing live adsense in Post editing previews; now displays a placeholder
- Added stripslashes() around calls to edit an ad and to display adsense code in posts.	[thanks "axodys"] reported his ads getting escaped on WP 1.5.3 (with magic_quotes_gpc Off).
- Editing an ad which was disabled causes it to be enabled when saving (fixed).

0.2 2005-07-28  - Initial (public) release.

SHOW YOUR SUPPORT PLEASE...
=--------------------------=
In the AdSense-Deluxe Options within your WordPress Admin are two ways to help support continued development of this plug-in if you find it useful to your Pro-Blogging activities. You can make donations through paypal either by clicking the link near the top of the Adsense-Deluxe options page (preferred), or by making a payment to support@acmetech.com from your PayPal account, and entering "Adsense-Deluxe Plugin Donation" in the payment comments. Alternately, turn on the 'Reward Author' feature in the Adsense-deluxe plugin options and approximately 5% of the ads shown on single-post pages (not the home page, archive or search pages) will use my AdSense or YPN publisher ID. Several users have enabled this option and I see a few clicks on most days, and greatly appreciate it!!

[wayne: support@acmetech.com]
