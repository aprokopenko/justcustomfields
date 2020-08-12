=== Just Custom Fields ===
Contributors: aprokopenko
Plugin Name: Just Custom Fields for Wordpress
Plugin URI: http://justcoded.com/blog/just-custom-fields-for-wordpress-plugin/
Tags: custom, fields, custom fields, meta, post meta, object meta, editor, custom gallery
Author: JustCoded / Alex Prokopenko
Author URI: http://justcoded.com/
Requires at least: 4.7
Tested up to: 5.5
Stable tag: trunk
License: GNU General Public License v2

Turn WordPress into more powerful CMS by adding advanced and easy to use custom fields

== Description ==

Just Custom Fields adds ability to extend your Posts, Pages (and other custom post types) and Taxonomies with additional fields. After installation, you will see a simple settings page, which is self-explanatory to use.
We use the standard WordPress PostMeta API and TaxonomyMeta API to save fields data, so you can use standard WordPress functions in your themes/plugins to get data.

**IMPORTANT** We do not recommend update plugins on your existing sites from version 2.* to version 3.0+.
JCF v3.0+ is not compatible with versions 2.*, so some field settings can be lost (and as a result - wrong values on post edit pages).

Plugin supports such field types:

* Input text
* Select Box
* Multiple Select Box
* Checkbox (single and multiple)
* Textarea (you can use WordPress editor too)
* Datepicker
* Simple Media (files and images upload)
* Table
* Fields Collection (repeatable fields groups)
* Related Content (to set a relation to another Post/Page or Custom Post Type)

You can read full documentation on our site: http://justcustomfields.com.

For quick demo you can watch our presentation video:
https://www.youtube.com/watch?v=7KJeH2d_v48

Starting from v2.0 we have different options to save plugin configuration:

* Ability to set Fields Settings global if you have MultiSite. So you can set them once, without copying all settings to every new site.
* Ability to save Fields Settings to a file system (inside theme or wp-content folder). We expect this option will be popular among the developers. It will be much easier to move your fields’ settings between site versions (dev/production).

Starting from v2.2:

* Simple Media field is now a single file only (before it was multiple). It uses new media upload box from WordPress and saves post thumbnail ID (before it was just a file url). So now you can use the get_the_post_thumbnail() function to work with attachments.
* All fields now have "Shortcodes" and hints how to use them inside the templates for non-professional developers.

For easy migrations between different sites, we have Export/Import options.

FEEL FREE TO CONTACT ME IF YOU FIND ANY BUGS/ISSUES!

**ISSUES TRACKER**
Plugin code is open source and placed in the GitHub public repository. I listed known bugs and future features there. You can post new bugs or feature requests for me there.
https://github.com/aprokopenko/justcustomfields/issues

== Installation ==

1. Download, unzip and upload to your WordPress plugins directory.
2. Activate the plugin within your WordPress Administration Backend.
3. Go to Settings > Just Custom Fields.
4. Choose Standard/Custom Post Type, which you want, to edit custom fields.
5. Create a Fieldset.
6. Add fields to the Fieldset.

To use values from these fields in your theme, you can use the usual post meta functions such as:

* get_post_meta()
* get_post_custom()

To use values from Taxonomy fields in your theme, you can use standard taxonomy meta function:

* get_term_meta()

== Upgrade Notice ==

To upgrade remove the old plugin folder. After than follow the installation steps 1-2. All settings will be saved.

== Screenshots ==

1. Plugin settings page where you can manage custom fields
2. The edit post page meta box with fields created on the settings page

== Changelog ==
* Version 3.3.2 - 12 August 2020
    * Issue fix: Added jQuery migrate for dependents scripts
    * Tests: New tests with WordPress 5.5
* Version 3.3.1 - 15 February 2019
    * Bug: Simplemedia shortcode do not display a link if it's not an image.
    * Hotfix: Disabled Textarea editors inside collections for WordPress 5+, because tinyMCE is not working there.
    * Note: Added a note that visibility rules are not working with a Gutenberg plugin.
* Version 3.3 - 26 April 2017
	* New feature: Taxonomy term custom fields!
	* Bug: Editor "Add media" button row overlap the Posts sidebar
	* Bug: Theme config path hook doesn't work inside migration process.
	* Tests: New tests with WordPress 4.7.4
* Version 3.2 - 17 March 2017
	* New feature: Google Maps component (Latitude and Longitude selector with Google Maps)
	* Tests: New tests with WordPress 4.7.3
* Version 3.1 - 21 February 2017
	* New feature: Added ability to migrate old data to match latest plugin code base. Support migrations from v2.3.2 and v3.0+
	* Tests: New tests with WordPress 4.7.2
* Version 3.0.4 - 24 January 2017
	* New feature: Added support of fieldset visibility for custom post types based on post template
	* Update: Description updated.
	* Tests: Tested with WordPress 4.7.1
* Version 3.0.3 - 9 December 2016
	* Optimization: File system mode add caching for json_decode
	* Tests: Tested with WordPress 4.7
* Version 3.0.2 - 23 November 2016
	* Bug fix: SSL support for backend scripts and styles includes according to: https://wordpress.org/support/topic/support-for-admin-ssl-conection/
* Version 3.0.1 - 22 November 2016
	* Bug fix: Related content init in empty Collection on new content page
	* Bug fix: Field usage help popup code updated. (Added 'echo' to manual call)
	* Issue fix: #62 table prefix issue in fields visibility query
* Version 3.0 - 21 November 2016
	* NEW: Plugin code full refactoring, build all code based on OOP classes and latest WordPress coding recommendations
	* New feature: Ability to hide fieldsets based on some criterias (taxonomy relation or page template)
	* New feature: Ability to show fieldset in right column on post edit screen
	* Numerous security patches (XSS)
* Version 2.3.2
	* Bug fix: Collection styling fixes for Post edit screen for Wordpress 4.3+
	* Bug fix: PHP 5.2 blank screen error resolved
* Version 2.3
	* Improvements: Collection field settings UI and field sorting fixed
* Version 2.2
	* Bug fixes: Some annoying notices with WP_DEBUG On
	* New feature: Fields shortcodes and template functions
	* New feature: "Simple Media" field
	* New feature: Ability to group several field in "Collection" (beta)
	* Deprecated: Upload Media is now deprecated
	* Deprecated: Fields Group is now deprecated
* Version 2.1.2
	* Bug fixes: Notice on Fields edit page when E_ALL errors On
	* Bug fixes: Notice on post/page quick edit update
* Version 2.1.1
	* Bug fixes: Removed warnings with WP_DEBUG On and E_STRICT errors On
	* Bug fixes: Tiles on settings page layout for media for WP 4.3.1
* Version 2.1
	* New: Sticky field settings edit form in plugin settings
	* New: Ability to sort fieldsets between each other
	* Bug fixes: Empty result on Export/Import
* Version 2.0.1b
	* Bug fix CRITICAL: Correct support of old field settings from old versions (read source set default to DB)
* Version 2.0b
	* New: Plugin settings pages were extended.
	* New: Field Settings landing page design improvements
	* New: Experimental features: Multisite settings, Field Settings storage place
	* New: Experimental features: Import/Export
* Version 1.4.1
	* Bug fix: select box created with old versions lost it's options (https://github.com/aprokopenko/justcustomfields/issues/31)
* Version 1.4
	* New: blank option for dropdown field (https://github.com/aprokopenko/justcustomfields/issues/2)
	* New: sortable multiple fields (https://github.com/aprokopenko/justcustomfields/issues/19)
	* New: Slug for all new fields will be started from underscore (https://github.com/aprokopenko/justcustomfields/issues/26)
	* Bug fix: tinyMCE &lt;p&gt;/&lt;br&gt; tags (https://github.com/aprokopenko/justcustomfields/issues/13)
	* Bug fix: thumbs not working on edit screens (https://github.com/aprokopenko/justcustomfields/issues/12)
	* Bug fix: support of all capability types (https://github.com/aprokopenko/justcustomfields/issues/6)

For more versions logs please read our website page.
	
== Frequently Asked Questions ==
= Q: Where can I edit new fields for Posts or Pages? =
A: After installation and activation of the plugin, you will see a new menu item under Settings > Just Custom Fields

= Q: My site works slow, is it something wrong with your plugin? =
A: Plugin files are included and affect only in the WordPress dashboard. They affect only the Post edit pages and the Just Custom Fields settings page. So it can't affect the site speed.

= Q: How can I add my own component (new field type)? =
A: First of all you need to create a new PHP class for your field. Your class should be extended from the Just_Field plugin class!
You can start from copying the existing class /[plugins-folder]/just-custom-fields/components/input-text.php to your plugin folder or theme and update the code:

- Change the class name
- Change the class methods to use your specific controls and data.

Class structure is very similar to the WordPress Widget API classes.

Then you need to include your new component file into your plugin main file or theme functions.php file.

And the last step:

- Add a new action hook "add_action('jcf_register_fields', 'my_register_fields_function')"
- Add a new callback function similar to this one:

function my_register_fields_function(){
jcf_field_register('YOUR_COMPONENT_CLASS_NAME');
}