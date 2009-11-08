=== Advertising Manager ===
Contributors: switzer, mutube
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=scott%40switzer%2eorg&item_name=Do%20you%20like%20Advertising%20Manager%20for%20Wordpress%3F%20%20Please%20help%20development%20by%20donating%21&currency_code=USD&bn=PP%2dDonationsBF&charset=UTF%2d8
Tags: ad, adbrite, adgridwork, adify, admin, adpinion, adroll, ads, adsense, adserver, advertisement, advertising, affiliate, banner, banners, chitika, cj, commercial, commission, crispads, google, income, junction, link, manager, media, money, openx, plugin, random, referral, revenue, rotator, seo, server, shoppingads, widget, widgetbucks, yahoo, ypn
Requires at least: 2.5
Tested up to: 2.8
Stable tag: 3.4.8

Easily place Google Adsense and other ads on your Wordpress blog.  Simple to use, with powerful management and optimisation features.

== Description ==

This plugin will manage and rotate your Google Adsense and other ads on your Wordpress blog.  It automatically recognises many ad networks including [Google Adsense](http://www.google.com/adsense), [AdBrite](http://www.adbrite.com/mb/landing_both.php?spid=51549&afb=120x60-1-blue), [Adify](http://www.adify.com), [AdGridWork](http://www.adgridwork.com/?r=18501), [Adpinion](http://www.adpinion.com/), [Adroll](http://re.adroll.com/a/D44UNLTJPNH5ZDXTTXII7V/7L73RCFU5VCG7FRNNIGH7O/d6ca1e265e654df2010a2153d5c42ed4.re), [Chitika](http://chitika.com/publishers.php?refid=switzer), [Commission Junction](http://www.cj.com/), [CrispAds](http://www.crispads.com/), [OpenX](http://www.openx.org/), [ShoppingAds](http://www.shoppingads.com/refer_1ebff04bf5805f6da1b4), [Yahoo!PN](http://ypn.yahoo.com/), and [WidgetBucks](http://www.widgetbucks.com/home.page?referrer=468034).  Unsupported ad networks can be used as well.

Features:

* Import ad code automatically
* Widgets and sidebar modules compatible (as used in the popular K2 theme).
* Automatic limiting of ad display to meet network terms and conditions (e.g. Google Adsense 3 units/page)

Related Links:

* <a href="http://code.openx.org/projects/show/advertising-manager" title="Advertising Manager plugin for WordPress">Plugin Homepage</a>
* <a href="http://code.openx.org/projects/activity/advertising-manager" title="Recent changes for the Advertising Manager plugin for WordPress">Recent Changes</a>
* <a href="http://code.openx.org/projects/advertising-manager/issues" title="Feature requests and bugs">Feature Requests and Bugs</a>
* <a href="http://wordpress.org/tags/advertising-manager">Support Forum</a>

== Installation ==

1. Unzip the package and upload the advertising-manager directory into your wp-content/plugins directory
1. Activate the plugin at the plugin administration page
1. Add your ad networks by clicking 'Create new' under the 'Ads' menu.  Manage your ad networks by clicking 'Edit' under the 'Ads' menu.
1. Place ads in your template by adding '<?php advman_ad(name) ?>' to your template in the place you want to see an ad (where 'name' is the name of your ad).
1. Place ads in your blog posts and pages by adding '[ad#name]' to your blog post (where 'name' is the name of your ad)

More detailed installation instructions can be found [here](http://code.openx.org/wiki/advertising-manager/Installation_Instructions).

If you are upgrading from Adsense Manager, Adsense Deluxe, or a previous version of Advertising Manager, upgrade instructions can be found [here](http://code.openx.org/wiki/advertising-manager/Upgrading_Instructions).


== Screenshots ==

1. Manage Your Advertising in Wordpress 2.7
1. Create ad in Wordpress 2.7
1. Edit ad in Wordpress 2.7
1. Ad settings in Wordpress 2.7
1. Manage Your Advertising in Wordpress 2.5
1. Create ad in Wordpress 2.5
1. Edit ad in Wordpress 2.5
1. Ad settings in Wordpress 2.5


== Frequently Asked Questions ==

= I previously used the Adsense Manager plugin for Wordpress.  Do I need to reconfigure my ads? =
Advertising Manager will automatically import your settings from Adsense Manager.  There is no modification necessary.  In addition, Advertising Manager will accept the Adsense Manager ad calls ('<?php adsensem_ad() ?>') as well.

= I previously used Adsense Deluxe plugin for Wordpress.  Do I need to reconfigure my ads?
Advertising Manager will automatically import your settings from Adsense Deluxe.  There is no modification necessary.  In addition, Advertising Manager will accept the Adsense Manager ad calls ('<!--adsense#name-->') as well.

= Does Advertising Manager support Wordpress MU (multi-user)? =
Yes.

= Can Advertising Manager work in my language? =
Yes.  Advertising Manager is localised.  If your blog is in another language, and Advertising Manager shows in English, then it is most likely that a translation has not been done.
Don't worry - if you are a native speaker in English as well as your local language, you can help out!  Information on how to get involved can be found here:

http://code.openx.org/wiki/advertising-manager/Translate_Advertising_Manager_in_your_language

= Do I still need Advertising Manager now I can manage ads through Google's system? =

Advertising manager allows you to rotate ads, easily turn on/off ads, place them in your blog, and many features other than configuration of the ad colors and format.  It a critical tool to use if you want to make more from your advertising.

= Why does changing Ad Format/Dimensions sometimes not change the size of the ad? =

For some ad networks (e.g. WidgetBucks, Adroll, etc.) the dimensions of ads are managed through the online interface. There is no way to change these settings from within the WordPress system that would work reliably. You do not have to update these dimension settings if you update your Ad online, however, it can be useful in correctly identifying 'Alternate Ads' for AdSense blocks.

= How do I place Ad code at the top, bottom, left, right, etc. of the page? =

There is a (nice tutorial here)[http://www.tamba2.org.uk/wordpress/adsense/] which explains positioning using code in template files. You can use this together with Advertising Manager: just place the ad code tags <?php advman_ad('name'); ?> where it says "place code here". 

= Upgrading has gone a bit wrong... What can I do? =

To revert to an old copy of your Ad database, go to your Dashboard and add ?advman-revert-db=X to your URL. Replace X with the major version that you want to revert to.
 
If the latest version to work was 2.1, enter: ?advman-revert-db=2

Load the page and Advertising Manager will revert to that version of the database and re-attempt the upgrade.

= How can I share revenue with my authors? =

1.  Load YOUR ad into Advertising Manager.
1.  Load your AUTHOR's ad into Advertising Manager.
1.  Name both ads the same - this will allow them to rotate.
1.  In your author's ad, be sure to select the authors username in Display Options
1.  Set the weights of the ads according to your revenue share.  The easiest way to do this is to set YOUR ad weight to 36, and then set the weight of your author's ad according to the revenue share deal.  For 10% revenue share, set the author ad weight to 4; 20% = 9; 33% = 18; 40% = 24; 50% = 36; 60% = 54; 66.7% = 72; 70% = 84; 80% = 144; 90% = 324.  For the nerdy wonks out there, the formula is (AUTHOR AD WEIGHT) = (MY AD WEIGHT * REVENUE SHARE) / (1 - REVENUE SHARE)

More detailed instuctions can be found in the documentation:  [Concepts - Placing ads on your blog](http://code.openx.org/wiki/advertising-manager/Placing_ads_on_your_blog)


== To Do ==

* Re-introduce 'limit ads per page' (per Adsense T&C)
* Link into OpenX Sync and OpenX Market for optimisation
* Auto-inserting of ads into posts based on configurable rules (i.e. All Posts, 2nd Paragraph)
* Support for Amazon Affiliates and any other networks I hear about.


== Change Log ==

By popular demand, below are the changes for versions listed. Use this to determine whether it is worth upgrading and also to see when bugs you've reported have been fixed.

As a general rule the version X.Y.Z increments Z with bugfixes, Y with additional features, and X with major overhaul.

* **3.4.8** Fixed minor Google Adsense 'type' issue, minor notice, and fixed defaulting
* **3.4.7** Fixed adsense account id importing issue, short tag issue, and openx importing issue
* **3.4.6** Fixed and expanded widget functionality for WP2.8 users
* **3.4.5** Fixed many small bugs, formatting changes, and missing fields from some ad networks
* **3.4.4** Fixed an issue where scripts were being delivered to all admin screens
* **3.4.3** Fixed display bug with WP 2.8
* **3.4.2** Added multiple select for author field, changed the page type field (show *), fixed error in factory method
* **3.4.1** Fixed settings screen, and random bug with printf
* **3.4** New architecture - less space, more efficient code.  Tons of ad network bug fixes.  Added plugin design, and new ad serving engine.
* **3.3.18** Added ability to suppress widget formatting on ads, fixed issue with PHP_INT_MAX on versions of PHP before 4.4.
* **3.3.17** Fixed a bug with widget display, updated all language files, fixed a bug with Ozh plugin
* **3.3.16** Added functionality around reverting to older versions of adsensem, fixed a bug with 0 weight ads, fixed a bug displaying ID rather than name for post ads
* **3.3.15** Fixed small bug in upgrade script, added counter support to widgets
* **3.3.14** Only enable Advertising Manager when Adsense Manager is disabled
* **3.3.13** Fixed a notice error in WP 2.6, added a small script which removes a notice set by adsense manager
* **3.3.12** Fixed error when using Advertising Manager as a widget
* **3.3.11** Added 'advman' to all variables which reside in the wordpress scope, to ensure that they do not stomp on other plugins
* **3.3.10** Added Chitika support, added counter support, fixed regex for ad in posts
* **3.3.9** Public beta - rotating ads, Adify support, much bug fixing and code restructuring
* **3.3.4** First alpha version that is separate from Adsense Manager


== Licence ==

This plugin is released under the GPL - you can use it free of charge on your personal or commercial blog. Make sure to submit back to the project any changes that you make!

== Translations ==

The plugin comes with various translations, please refer to the [WordPress Codex](http://codex.wordpress.org/Installing_WordPress_in_Your_Language "Installing WordPress in Your Language") for more information about activating the translation. If you want to help to translate the plugin to your language, please have a look at the advman.pot file which contains all defintions and may be used with a [gettext](http://www.gnu.org/software/gettext/) editor like [Poedit](http://www.poedit.net/) (Windows).
