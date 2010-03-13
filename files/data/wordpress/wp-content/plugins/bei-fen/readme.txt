=== Bei Fen - Wordpress Backup Plugin ===
Contributors: d3rd4v1d
Donate link: http://www.beifen.info/donate/
Tags: backup, admin
Requires at least: 2.8
Tested up to: 2.9
Stable tag: 1.4.2

'Bei Fen' is chinese for 'Backup', and that's what this plugin creates: backups!

== Description ==

You can create backups of your complete Wordpress installation, only the files, or only your Wordpress database.

Backups can be restored with 1 click (only backups created by version 1.3 can be restored)!

Both PHP4 (>=4.3.2) and PHP5 are supported now. Backups can be compressed as zip archives (PHP Version >= 5.2 only).

If you need help or want to contribute your translation, feel free to leave a reply at [Bei Fen](http://www.beifen.info/ "Bei Fen").

== Installation ==

1. Download the plugin
2. Extract it
3. Rename the plugin folder, if you want, but dont rename the files inside the plugin folder
4. Upload everything to the plugins folder of your Wordpress installation
5. Go the the 'Settings' page and configure it for the first use.

== Frequently Asked Questions ==

= Is PHP4 supported? =

PHP4 is fully supported since version 1.2.4!

= Where can I get help? =

Visit the plugin website of [Bei Fen](http://www.beifen.info/ "Bei Fen"). There you can find a FAQ which might help you. If not please fill in a description of your problem or request in the request-support-form and I will take care of it as soon as possible!

== Screenshots ==

1. This screen shot describes the 'Schedule New Backup' page, where you can schedule frequent backups
2. This screen shot describes the 'Create New Backup' page, where you can create new backups
3. This screen shot describes the 'Manage Backups' page, where you can see all created backups. You can also delete them, if necessary.
4. This screen shot describes the 'Settings' page.

== Changelog ==

= 1.4.2 =
* Added a warning message in case of disabled scheduling with existing scheduled backups

= 1.4.1 =
* Fixed the schedule functionality
* Fixed the stylesheet
* Added persian translation (thanks to Mehrdad)
* Added french translation (thanks to Fabien)
* Added russian translation (thanks to iMike)

= 1.4 =
* Added scheduled backup functionality
* Improved interface design

= 1.3.2 =
* Added persian translation (thanks to Mehrdad)
* Added french translation (thanks to Fabien)
* Enabled debugging by default

= 1.3.1 =
* Added russian translation (thanks to iMike)
* Added italian translation (thanks to Gianluca)
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