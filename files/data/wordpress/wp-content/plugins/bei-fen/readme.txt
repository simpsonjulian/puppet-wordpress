=== Bei Fen - Wordpress Backup Plugin ===
Contributors: d3rd4v1d
Donate link: http://www.david-schneider.name/
Tags: backup
Requires at least: 2.6
Tested up to: 2.8.5
Stable tag: 1.3.1

'Bei Fen' is chinese for 'Backup', and that's what this plugin creates: backups!

== Description ==

You can create backups of your complete Wordpress installation, only the files, or only your Wordpress database. Backups can be restored with 1 click (only backups created by version 1.3 can be restored)! Both PHP4 (>=4.3.2) and PHP5 are supported now. You can also create zip archives (PHP Version >= 5.2 only). It includes a debug option to get memory usage and easily track problems. The plugin contains translations for:

*   English (default)
*   German
*   Chinese (simplified)
*   Russian (Thanks to iMike)

If you need help or want to contribute your translation, feel free to send me an email to david.schneider@xin-it.com!

== Installation ==

1. Download the plugin
2. Extract it
3. Rename the plugin folder, if you want, but dont rename the files inside the plugin folder
4. Upload everything to the plugins folder of your Wordpress installation
5. Go the the 'Settings' page and configure it for the first use.

== Frequently Asked Questions ==

= Is PHP4 supported? =

PHP4 is fully supported since version 1.2.4!

= Why is the 'Donate to this plugin' link pointing to the authors website? I can't find any donation options =

I didn't create this plugin for money, but because I love coding. If you visit my website, read my posts, make a comment, or give this plugin a good rating I feel that my work has paid off, because someone spent some of his valuable time on my work.

= Will there be an automatic backup function? =

I am currently working on something like this.

= Where can I get help? =

Just send me an email to david.schneider@xin-it.com with a description of your problem or request and I will take care of it as soon as possible!

== Screenshots ==

1. This screen shot describes the 'Settings' page.
2. This screen shot describes the 'Manage Backups' page, where you can see all created backups. You can also delete them, if necessary,
3. This screen shot describes the 'Create New Backup' page, where you can create new backups

== Changelog ==

= 1.3.1 =
* Added russian translation (thanks to iMike)
* Only database tables with the same prefix will be included into the backup now (thanks to Gerd)

= 1.3 =
* Added function to restore backups
* Added option to override PHP script timeout, helpful for large blogs
* Added option to include/exclude existing backups into new backups
* Added debug option for users in the settings. This will display memory usage and critical errors occured during script execution
* Added debug option for developers in the bei-fen.php file. This will display ALL errors and warnings occured during script execution
* Excluded backup table from the database dump to prevent losing information about existing backups, when restoring a backup

= 1.2.4 =
* Removed a CSS link in the settings page pointing at a local server (thanks to iMike)
* Fixed variable declaration in custom json_encode (thanks to iMike)
* Fixed class definition and constructor for PHP4 backward compability

= 1.2.3 =
* Fixed the CSS link for Windos based systems
* Fixed the recursive folder copy function on Windows based systems
* Fixed the zip function

= 1.2.2 =
* Patch for wrong encoding in custom json_encode

= 1.2.1 =
* Patch for missing json_encode function in PHP4

= 1.2 =
* Public release