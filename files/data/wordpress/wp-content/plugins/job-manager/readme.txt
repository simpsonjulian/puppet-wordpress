=== Job Manager ===
Contributors: pento
Donate link: http://pento.net/donate/
Tags: jobs, job, manager, list, listing, employer, application, board
Requires at least: 2.9
Tested up to: 3.0
Stable tag: trunk

A job listing and job application management plugin for WordPress.

== Description ==

A plugin for managing job lists and job applications on your WordPress site. It supports all the features you need to manage your organisation's job openings.

Do you speak a language other than English? Do you have time to translate some strings? If so, I'd love to [hear from you](http://pento.net/contact/)!

*    *Job Listing*
    *    Categories to create multiple job boards
    *    Jobs can be filed under multiple categories
    *    Icons can be assigned to jobs, to make them stand out in the listing
	*    Customisable fields, so you can display information about your jobs specific to your situation
	*    Powerful templating system, allowing you to control the look and feel of your jobs and job boards
	*    Widgets to fit into your WordPress theme
*    *Job Management*
    *    Jobs can be defined to display between certain dates, or indefinitely
    *    Simple admin interface for editing, updating and creating jobs
	*    Jobs can be easily archived or made public as needed
*    *Applications*
    *    Applicants can apply through the website, using a form that you can customize and template, so you get the information you need
    *    Advanced filtering on application forms, to ensure you only get applications that match your criteria: [Documentation](http://code.google.com/p/wordpress-job-manager/wiki/CustomApplicationForm)
    *    Upon successful application, you can be emailed the details, so you're always up to date with new applicants
*    *Applicant Management*
    *    Simple interface for viewing all applicants
    *    List can be filtered based on any criteria in your custom application form
    *    Email individuals or groups of candidates, to keep them updated on new job opportunities in your organisation
	*    Interview scheduling, linked directly to jobs and applications
	*    Internal comments, for easy reference when you need to decide

Related links:

* [Plugin Homepage](http://pento.net/projects/wordpress-job-manager-plugin/)
* [Support Forum](http://wordpress.org/tags/job-manager?forum_id=10)
* [Report Bugs and Request Features](http://code.google.com/p/wordpress-job-manager/issues/list)
* [Development Roadmap](http://code.google.com/p/wordpress-job-manager/wiki/Roadmap)
* [Translations](http://translations.pento.net/)
* [Mailing List](http://groups.google.com/group/wordpress-job-manager)

== Installation ==

Job Manager Requires:

* WordPress 2.9 or later
* PHP 5 or later

= The Good Way =

1. In your WordPress Admin, go to the Add New Plugins page
1. Search for: job manager
1. Job Manager should be the first result. Click the Install link.

= The Old Way =

If you use this method, please remember to Deactivate/Activate Job Manager between upgrades, to ensure that the upgrade routines are run properly.

1. Upload the plugin to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

= The Living-On-The-Edge Way =
(Please don't do this in production, you will almost certainly break something!)

1. Checkout the current development version from http://wordpress-job-manager.googlecode.com/svn/trunk/
1. Subscribe to the [update feed](http://code.google.com/p/wordpress-job-manager/source/list) to be notified of changes

== Frequently Asked Questions ==

= How do I setup a custom application form? =

For a full description of how to use the application form customization features, please read [this page in the documentation](http://code.google.com/p/wordpress-job-manager/wiki/CustomApplicationForm).

= DOCX files don't download properly =

Apache's `mod_mime_magic` doesn't recognise docx file type properly, and older versions of Apache don't have docx in their mime.types file. You should update your copy of Apache to something more recent, or (if you're unable to update or turn off `mod_mime_magic`) create a .htaccess file as [described here](http://www.webdeveloper.com/forum/showpost.php?p=898935&postcount=2).

= I can't upload DOC files in WordPress MultiSite =

In your WordPress MultiSite admin, go to Site Admin->Options. Update the "Upload File Types" option to include the various extensions (doc, docx, pdf, odt) that people are likely to upload their resume with.

== Other Plugin Support ==

Job Manager supports added functionality when other plugins are installed. If you think your plugin could add some functionality to Job Manager, please [submit a feature request](http://code.google.com/p/wordpress-job-manager/issues/list).

= Google XML Sitemaps =

Job Manager will add all of your job lists and job detail pages to your sitemap, when [Google XML Sitemaps](http://wordpress.org/extend/plugins/google-sitemap-generator/) is installed on your site.

= SI CAPTCHA =

If you want a [CAPTCHA](http://en.wikipedia.org/wiki/CAPTCHA) on your Application Form, install the [SI CAPTCHA](http://wordpress.org/extend/plugins/si-captcha-for-wordpress/) plugin.

== Credits ==

= Translations =

Notice the version number in brackets. This is the version series that the translation is up-to-date with. If that version series is lower than the current release, you can still use the translation, there just may be some strings that still show in English. If you'd like to add your own language, or help keep an existing language up-to-date, please [contact me](http://pento.net/contact/)!

* Danish Translation (0.7.x), provided by [Christian Olesen](http://www.lithin.com/) and [Caspar Lange](http://www.lithin.com/)
* Dutch Translation (0.7.x, 93% complete), provided by [Patrick Tessels](http://www.centrologic.nl/) and [Henk van den Bor](http://webtaurus.nl/)
* Estonian Translation (0.7.x), provided by Robert Jakobson
* French Translation (0.7.x), provided by [Fabrice Fotso](http://www.procure-smart.com/) and Vincent Clady
* German Translation (0.7.x, 95% complete), provided by [tolingo translations](http://www.tolingo.com/) and [Joachim Richter](http://www.la-palma-diving.com/)
* Portuguese (Brazil) Translation (0.7.x), provided by [Alex Ferreira](http://www.alexfj.com.br/)
* Spanish Translation (0.7.x), provided by [TradiArt](http://www.tradiart.com)
* Swedish Translation (0.7.x), provided by [Berndt Axelsson](http://www.saxekon.se/)

= Special Thanks =

* [EuroPlacements](http://europlacements.it/), for their input and support of the initial development of Job Manager.
* [Automattic](http://automattic.com/), for their support and feedback on features, design and usability.
* All the wonderful people who've submitted bugs, feedback and feature requests - you're the people who keep me with things to work on!

Print Icon courtesy of [VisualPharm](http://www.visualpharm.com/), under a [CC BY-ND](http://creativecommons.org/licenses/by-nd/3.0/) license.

== Changelog ==

= 0.7.14 =
* ADDED: Swedish Translation, provided by [Berndt Axelsson](http://www.saxekon.se/)
* CHANGED: If no Sort Order is selected in Job List Sorting, it will default to ASC, rather than random
* UPDATED: German Translation, provided by [Joachim Richter](http://www.la-palma-diving.com/)
* UPDATED: Portuguese (Brazil) Translation, provided by [Alex Ferreira](http://www.alexfj.com.br/)
* UPDATED: Spanish Translation, provided by [TradiArt](http://www.tradiart.com)
* FIXED: Fields with no label not showing mandatory asterisk
* FIXED: Checkboxes being skipped in mandatory field check
* FIXED: Fields with no label not showing nicely in mandatory field check
* FIXED: Stop overriding Mystique theme CSS
* FIXED: Don't check Heading, HTML or Blank fields for Mandatory or Filter rules
* FIXED: Admin JS error in IE7
* FIXED: Jobs not displaying if WPML is enabled
* FIXED: Applications not printing properly in IE7

= 0.7.13 =
* ADDED: Estonian Translation, provided by Robert Jakobson
* ADDED: Portuguese (Brazil) translation, provided by [Alex Ferreira](http://www.alexfj.com.br/)
* FIXED: User registration page not working under some circumstances
* FIXED: Job fields not being sorted correctly
* FIXED: Play nicely with MultiSite
* FIXED: Applications incorrectly failing mandatory field checks
* FIXED: A few PHP warnings
* FIXED: Some settings not being initialised
* FIXED: Incorrect SI Captcha detection message

= 0.7.12 =
* FIXED: Category list on application form could fail with fatal error
* FIXED: Some PHP warnings
* FIXED: Some undefined default settings
* FIXED: Icon list is ugly when there are many icons

= 0.7.11 =
* FIXED: New rewrite rules caused category lists to fail

= 0.7.10 =
* FIXED: Silly bug in activation

= 0.7.9 = 
* FIXED: Some activations could fail with the new rewrite rules code
* FIXED: Dutch translation also provided by [Christian Olesen](http://www.lithin.com/)

= 0.7.8 =
* ADDED: Danish translation, provided by [Caspar Lange](http://www.lithin.com/)
* CHANGED: Updated Google Maps API to v3, removed API key settings box
* FIXED: Some bad HTML in admin area
* FIXED: Application Email not working
* FIXED: Application count in admin job list limited to 5
* FIXED: Incompatibility with Proper Network Activation plugin
* FIXED: Category links not working in one case
* FIXED: Restricted height of category lists in admin, for really long lists
* FIXED: Flushing rewrite rules is expensive, only do it when we must
* FIXED: Bad main page edit link

= 0.7.7 =
* UPDATED: French Translation, provided by [Fabrice Fotso](http://www.procure-smart.com/)

= 0.7.6 =
* CHANGED: Pages are now owned by the installing user, rather than superadmin
* FIXED: Frontend JS being loaded in wp-admin
* FIXED: Wrong offset for jobs list when limiting number of jobs per page
* FIXED: Wrong job count for last page of jobs
* FIXED: Single job not being selected in job dropdown on application form
* FIXED: Applications not working if wp-admin is moved
* FIXED: `<title>` having incorrect information
* FIXED: File download link in job details displaying when no file was uploaded

= 0.7.5 =
* CHANGED: Application list now shows Job ID with Job Title
* FIXED: JS bug in application form check

= 0.7.4 =
* FIXED: Application email not sending to individual job addresses
* FIXED: Job select dropdown not having a job selected when an individual job was being applied for
* FIXED: Possible performance issued caused by `dirname()`

= 0.7.3 =
* FIXED: Application email not sending if no email set for the categories
* FIXED: Application form required field check sometimes failing
* FIXED: Authors being able to archive or delete other users posts
* FIXED: Category widget breaking WP < 3.0

= 0.7.2 =
* FIXED: PHP Warning showing on job pages

= 0.7.1 =
* ADDED: 'category-foo-job.php' template, which overrides 'category-foo.php' when viewing a job in the category 'foo'
* ADDED: ref attribute to [job_apply_link] shortcode
* ADDED: Link to individually archive/unarchive jobs
* ADDED: Reverse GeoLoc lookup for browsers that don't do it be default
* ADDED: Gravatar support in applications
* CHANGED: Job editor now uses TinyMCE for editing textareas
* CHANGED: Applications filter box now displays quicker
* CHANGED: Settings now on one page, with tabs
* CHANGED: Tweaked applications list layout
* FIXED: Job multi-select popout now has `<label>` tags
* FIXED: JavaScript error with Geoloc code in Chrome Dev Channel
* FIXED: RSS feed showing wrong publication dates
* FIXED: Authors could edit jobs created by other Authors
* FIXED: Using ASCII instead of &larr; on Application Details page
* FIXED: Application filter not working on un-rated applications
* FIXED: Forgot password link not working under some circumstances
* FIXED: Future jobs not displaying in admin job list
* FIXED: Unable to unarchive future jobs

= 0.7 =
* ADDED: Optional template for application form
* ADDED: `<select>` type to application forms
* ADDED: Ability to apply for multiple jobs at once
* ADDED: Job selector in application form
* ADDED: Geolocation field to application form
* ADDED: Ability to search applications by distance from a given location
* ADDED: WordPress.com support
* ADDED: Category template option
* ADDED: Option to show job fields in admin job list
* ADDED: Interview scheduling
* ADDED: Job mass-edit option to archive/unarchive
* ADDED: Ability to comment on interviews and applications
* ADDED: Option to block application fields from being included in the application email
* ADDED: Widget to show a custom list of selected jobs
* ADDED: Before/After text for Registration form
* ADDED: Option to format job date fields. Defaults to no formatting.
* ADDED: Option to show a limited number of jobs per page, and related shortcodes
* ADDED: Shortcodes for the current category
* ADDED: Spanish Translation, provided by [TradiArt](http://www.tradiart.com)
* ADDED: Jobs will now check for job.php template
* UPDATED: Dutch translation, provided by [Patrick Tessels](http://www.centrologic.nl/)
* UPDATED: French Translation, provided by [Fabrice Fotso](http://www.procure-smart.com/)
* CHANGED: Removed user dropdown from Application filter, replaced with a text box. It required a bad query, so had to go.
* CHANGED: Separated admin jobs list by future/live/expired, so it's easier to read
* CHANGED: Job list sorting now allows sorting by any job field
* CHANGED: Job file attachments can now be shown as the URL or an <img> tag, instead of just a Download link
* CHANGED: Large text fields in jobs can be restricted to how much information they'll show.
* CHANGED: RSS Feed now uses the Individual Job template
* CHANGED: Category widget can now show job counts for each category
* CHANGED: Category widget can now hide empty categories
* CHANGED: Job edit date selector now opens with button, rather than on click
* CHANGED: Display an error to users if there was a problem with file upload
* CHANGED: Job saving success message now has yellow background, to fit in with WP style
* FIXED: Some PHP warnings
* FIXED: HTML errors in Admin
* FIXED: Admin menu now uses handles rather than file references
* FIXED: Some strings not going through the translation functions
* FIXED: Email form being printed with application details
* FIXED: Renaming category titles not saving
* FIXED: Categories not playing nicely with breadcrumb plugins
* FIXED: Emails not working properly
* FIXED: Some performance tweaks
* FIXED: Some print CSS tweaks
* FIXED: Javascript error when deleting a new the default new field
* FIXED: Upgrading now works with Maintenance Mode upgrades
* FIXED: RSS showing expired jobs
* FIXED: Performance tweaks on job lists, widgets
* FIXED: Checkboxes not staying checked when editing an existing job
* FIXED: Fresh installations not using translated strings for defaults
* FIXED: Some strings not being translated correctly
* FIXED: Uninstall not working properly
* FIXED: Job lists not obeying ASC/DESC sorting setting
* FIXED: Admin boxes being too wide on small screens
* FIXED: Job URL occasionally not being generated properly
* FIXED: Application form category restrictions not working
* FIXED: Play nice-ish with WPML - proper support will be in a later version
* FIXED: Possible PHP error when uploading files to jobs
* FIXED: Some dropdowns not displaying properly on small screens
* FIXED: Bad SSL check
* FIXED: Forcing WP to think that all lists are a single page (some themes had issues)
* FIXED: Recent Jobs widget sometimes not saving job count properly
* FIXED: Some IE8 admin JS errors

= 0.6.6 =
* ADDED: Individual job pages will try to load category templates before default template
* FIXED: Potential upload error when attaching a file to a job
* FIXED: Applications/emails not displaying in fresh installations
* FIXED: Job List breaking in WordPress 3
* FIXED: Trimming whitespace from application form data fields

= 0.6.5 =
* ADDED: German translation, provided by [tolingo translations](http://www.tolingo.com/)

= 0.6.4 =
* ADDED: Support for category templates, as category-{foo}.php
* FIXED: Add new job fields not working
* FIXED: Add new application form fields not working
* FIXED: Application details page not displaying fields in correct order
* FIXED: Application details page not printing correctly
* FIXED: One more potential PHP warning

= 0.6.3 =
* FIXED: Jobs not saving properly if not empty fields are changed to being empty
* FIXED: Files attached to jobs could be deleted when saving the job
* FIXED: More PHP warnings

= 0.6.2 =
* FIXED: Potential PHP Warning when editing the application form
* FIXED: Files attached to jobs not displaying properly when using the individual field code in the template

= 0.6.1 =
* FIXED: File encoding, causing weird characters to appear
* FIXED: File upload on jobs not uploading correctly

= 0.6 =
* ADDED: 'Related Categories' section to job list displays
* ADDED: Mandatory fields filter
* ADDED: Widgets for Latest Jobs, Categories and Highlighted Jobs
* ADDED: Option to change sort order of job lists
* ADDED: Options to highlight jobs, and stick them to the top of job lists
* ADDED: Exporting Applications to CSV
* ADDED: Link on jobs list to applications for that job
* ADDED: IDs to all admin tables, so they can be styled more easily
* ADDED: Uninstall options
* ADDED: Options to change text before/after data being displayed
* ADDED: Option to change Job title prefix
* ADDED: Option to change Application Acceptance message
* ADDED: Option to set the name in the "From" field of application emails
* ADDED: Support for CAPTCHAs, through the [SI CAPTCHA](http://wordpress.org/extend/plugins/si-captcha-for-wordpress/) plugin.
* ADDED: RSS feed for jobs
* ADDED: Ability to customise job fields
* ADDED: Shortcode-based templates for jobs and job list displays
* ADDED: Dutch Translation, provided by [Patrick Tessels](http://www.centrologic.nl/)
* CHANGED: Removed pages hack for displaying categories. This will change category list permalinks if nice permalinks are off.
* CHANGED: Split admin and display settings into two admin pages
* CHANGED: HTML fields now stretch across both columns of the application table. The label is no longer displayed.
* CHANGED: Can now remove ratings from applications
* CHANGED: Uploads and icons are now stored as attachments
* FIXED: Some small string changes for clarity
* FIXED: Added category links to Google XML Sitemap
* FIXED: Admin CSS/JS are only loaded on the appropriate pages
* FIXED: Job list filter not filtering by categories
* FIXED: Some PHP warnings
* FIXED: No empty message on emails list
* FIXED: Added some CSS to make Full lists line up nicely
* FIXED: WP themes could think they were on the front page when in Job Manager

= 0.5.4 =
* ADDED: French Translation, provided by [Fabrice Fotso](http://www.procure-smart.com/)

= 0.5.3 =
* FIXED: Application list not filtering correctly if no rating selected
* FIXED: Warning when deleting applications with no file attached

= 0.5.2 =
* FIXED: Job list not loading after creating a new job
* FIXED: Application categories somehow got wiped, restored them
* FIXED: Category could not be stored on application, under some circumstances
* FIXED: Wrong translation domain on a string

= 0.5.1 =
* FIXED: Moved upload directories outside of the plugin directory

= 0.5 =
* ADDED: Ability for applicants to register
* ADDED: New settings for user registration
* ADDED: Nicer explanations of settings
* ADDED: Links to categories from settings page
* ADDED: Applicant filter on Applications list
* ADDED: Application star rating, and filtering by rating
* ADDED: 'Add Job' item to the wp-admin menu
* ADDED: Job field for applications to be emailed to a custom address
* ADDED: Emails are now stored when they're sent
* ADDED: Interface for browsing sent emails
* ADDED: Admin print stylesheet, so applications can be printed nicely
* ADDED: Print icon to Application Details page
* ADDED: Option in Application Details to email application a different person
* ADDED: HTML Code field to Application Form Settings
* ADDED: A bunch of CSS classes to the front end elements
* CHANGED: User permissions: 'publish_posts' capability (author) is required for posting jobs, 'read_private_pages' capability (editor) is required for viewing applications
* CHANGED: Removed main URL editing from settings
* CHANGED: Settings page layout, for readability
* CHANGED: Removed the "WordPress" name from application emails
* FIXED: Category listing now significantly more efficient
* FIXED: Google XML Sitemaps option not saving correctly
* FIXED: Google XML Sitemaps code updated to use new data storage format
* FIXED: Some strings not going through i18n functions
* FIXED: A handful of grammar/spelling mistakes
* FIXED: Code cleanup, to conform more closely with [WordPress Coding Standards](http://codex.wordpress.org/WordPress_Coding_Standards)
* FIXED: Job Lists not obeying Display End Date
* FIXED: Default Application Form had an incorrect data entry
* FIXED: Bug in file naming for downloading applicant files

= 0.4.8 =
* FIXED: Timeout problem on Application List page, if there are less than 5 applications

= 0.4.7 =
* FIXED: Empty job list message not displaying correctly
* FIXED: New job showing a bad start date
* FIXED: Some PHP notices
* FIXED: Template from main page not being used correctly
* FIXED: Removed 5 job limit from display code

= 0.4.6 =
* FIXED: Application email not being sent correctly
* FIXED: Not displaying if used with a theme that doesn't have a page.php
* FIXED: Broken XHTML tag in admin
* FIXED: Jobs with no icon had a broken icon displaying
* FIXED: 'Job: ' job title prefix displaying in wrong place
* FIXED: Escape error message in application form setup
* FIXED: Escape default values in application form display
* FIXED: Custom filter error messages not displaying
* FIXED: `<title>` not being displayed correctly
* FIXED: Some PHP notices

= 0.4.5 =
* FIXED: Job list not displaying under some circumstances
* FIXED: Not retrieving job list in category pages

= 0.4.4 =
* FIXED: Job permalinks now being treated as pages
* FIXED: Jobs/application form not showing if main jobs page was set as a child page
* FIXED: Not all applications displaying in application list
* FIXED: Permalinks now allow for a lack of trailing '/'
* FIXED: Application field sort order not being obeyed
* FIXED: Job link not being display in application list
* FIXED: Category pages not storing correctly

= 0.4.3 =
* FIXED: Removed some references to the old code removed in 0.4.2

= 0.4.2 =
* FIXED: Google XML Sitemap option not showing correctly
* FIXED: Incorrect check could cause plugin activation to fail
* FIXED: Removed some dead code

= 0.4.1 =
* FIXED: Application fields not saving properly
* FIXED: Miscellaneous PHP warnings
* FIXED: Upload directory write check failing under some circumstances

= 0.4.0 =
* ADDED: Check to make sure data directories are writeable by the plugin
* ADDED: Nonce fields are now in all Admin forms, for added security
* ADDED: Ability to delete jobs
* ADDED: Ability to change the page template used
* CHANGED: Job Manager now requires WordPress 2.9 or higher
* CHANGED: All data is now stored in default WordPress tables
* CHANGED: All options are now stored in a single wp_options entry
* FIXED: A job being displayed could include an incorrect <title>
* FIXED: No longer re-write the .htaccess file. Unnecessary, and was causing problems on 1&1 hosting.
* FIXED: Problem with including symlinked files
* FIXED: Secured the uploaded files directory
* FIXED: Link to files in the Application List

= 0.3.3 =
* FIXED: SQL errors when deleting applications

= 0.3.2 =
* FIXED: SQL error when submitting an application

= 0.3.1 =
* FIXED: A default value for Category slugs is now inserted. Upgrading will create default slugs if no slug exists.
* FIXED: Bug preventing icons from being deleted.
* FIXED: Code cleanup

= 0.3.0 =
* ADDED: Framework for supporting extra functionality through other plugins
* ADDED: Google Sitemap support, through the [Google XML Sitemaps](http://wordpress.org/extend/plugins/google-sitemap-generator/) plugin.
* ADDED: POT file, for translations
* FIXED: Potential Application submission error
* FIXED: Storing incorrect information if no file was uploaded
* FIXED: Logic bug in plugin activation
* FIXED: Options upgrade function wasn't being called
* FIXED: Minor string fixes

= 0.2.4 =
* FIXED: Still some circumstances where jobs weren't displaying
* FIXED: Removed some CSS that should be in a site's main.css

= 0.2.3 =
* FIXED: Jobs were not displaying if the start or end date was empty.

= 0.2.2 =
* FIXED: Applications without an associated job were not being stored correctly.
* FIXED: Minor bugs with filtering applications.

= 0.2.1 =
* FIXED: Bad homepage link

= 0.2.0 =
* ADDED: Ability to switch between summary and full view for the Job List

= 0.1.0 =
* Initial release

== Upgrade Notice ==
