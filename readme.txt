=== Just Custom Fields ===
Contributors: aprokopenko
Plugin Name: Just Custom Fields for Wordpress
Plugin URI: http://justcoded.com/just-labs/just-custom-fields-for-wordpress-plugin/
Author: Alexander Prokopenko
Author URI: http://justcoded.com/
Tags: custom, fields, custom fields, meta, post meta, object meta, editor
Requires at least: 3.0.0
Tested up to: 4.3.1
Donate link: http://justcoded.com/just-labs/just-custom-fields-for-wordpress-plugin/#donate
Stable tag: trunk
License: GNU General Public License v2

This plugin add custom fields for standard and custom post types in WordPress.

== Description ==

This plugin add custom fields for standard and custom post types in WordPress. After installation you will see simple settings page which is self-explanatory to use.

For now plugin support such field types:

* Input text
* Select Box
* Multiple Select Box
* Checkbox (single and multiple)
* Textarea (you can use editor light for it)
* Date Picker\*
* Simple Media (files and images upload)
* Table
* Fields Collection (several fields grouping)
* Related Content (to set relation to another Post/Page or Custom Post Type)\*

_\*NOTE: Available **from** WordPress 3.3+_

Since version 2.2 such fields are **deprecated**, but they are still there because of backward compatibility:

* Upload Media (for upload files and images)
* Fields Group (for grouped textual data)

**IMPORTANT** Starting from version 1.4 all fields machine names (slug) will be started from underscore ('_'). This update prevents appearing them in standard WordPress Custom fields meta box.

Starting from v2.0 we have new features:

* Ability to set Fields Settings global if you have MultiSite. So you can set them once, without copying all settings to every new site.
* Ability to save Fields Settings to file system. Directly in the theme. We expect this option will be popular among the developers. It will be much easier to move your fields settings between site versions (dev/production).
* Export Fields Settings
* Import Fields Settings

**EXPERIMENTAL FEATURES v2.2**

* Simple Media field is now single file only (before it was multiple). It use new media upload box from WordPress and save post thumbnail ID (before it was just file url). So now you can use get_the_post_thumbnail() function to work with attachments.
* New "Fields Collection" has been promoted in v2.2.
* All fields now have "Shortcodes" and hints how to use them inside the templates for non-professional developers.

FILL FREE TO CONTACT ME IF YOU FIND ANY BUGS/ISSUES!

**ISSUES TRACKER**
Main plugin development are done under github public repository. I listed known bugs and future features there. You can post new bugs or feature requests for me there.
https://github.com/aprokopenko/justcustomfields/issues

== Installation ==

1. Download, unzip and upload to your WordPress plugins directory
2. Activate the plugin within you WordPress Administration Backend
3. Go to Settings > Just Custom Fields
4. Choose Standard/Custom Post Type you want to edit custom fields
5. Create Fieldset
6. Add fields to the fieldset.

To use values from these fields in your theme, you can use usual post meta functions such as:

get_post_meta()
get_post_custom()

== Upgrade Notice ==
* Remove old plugin folder.
* Follow install steps 1-2. All settings will be saved.

== Screenshots ==

1. Plugin settings page where you can manage custom fields
2. The edit post page meta box with fields created on settings page

== Changelog ==
* Next release plans
	* make fieldsets related to categories (show/hide based on category select)
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
* Version 1.3.4
	* Bug fix: JS error in related content field (https://github.com/aprokopenko/justcustomfields/issues/11)
* Version 1.3.3
	* Allow JCF extensions inside WP themes.
* Version 1.3.2
	* Bug fix: emergency fixes for WordPress 3.4
* Version 1.3.1
	* Bug fix: notices about deprecated param for add_options_page().
	* Bug fix: missing thumbnail for image upload when Site URL is differ from Wordpress URL 
* Version 1.3
	* New: Added 2 template functions to print images from Upload Media field
	* New: Select box have "Select One" option (for empty values)
	* Updated .pot files (for guys who want to create their own translations)
	* Updated Russian translations
	* New: Added Belarusian translations (Thanks to Alexander Ovsov (http://webhostinggeeks.com/science/)
	* Bug fix: Textarea field compatibility with Wordpress 3.3 (thanks Jam for bug reported)
	* Bug fix: Sometimes fieldsets works buggly with Cyrillic-only names
* Version 1.2.1:
	* Bug fix: Border radius for forms for Chrome and Safari
* Version 1.2:
	* Bug fix: Single checkbox uncheck problem
* Version 1.1.1:
	* Bug fix: Uploadmedia don't work if there are no main Content Editor field (for Custom Post Types)
* Version 1.1 :
	* Add feature to enable/disable fields without removing them
	* Add component "Related Content" (so you can add relation to another Custom Post Type with simple autocomplete field)
	* Bug fix: Randomly changing fieldset order after field update
	* Bug fix: Component css not loading without js
	* Improved css :)
* Version 1.0 :
	* Added support for multi-language
	* Added Russian translations
	* Added Italian translations (Thanks to Andrea Bersi for help with Italian version)
	* Fixed bug with blank screen on update post
	* Updated colors to match new Administrative UI in WordPress 3.2
* Version 0.9beta :
	* First version beta
	
== Frequently Asked Questions ==
= Q: Where can I edit new fields for Posts or Pages? =
A: After installation and activation of the plugin you will see new menu item under Settings > Just Custom Fields

= Q: My site works slow, is it something wrong with your plugin? =
A: Plugin files are included and affect only in WordPress dashboard and affect only Post edit pages and Just Custom Fields settings page. So it can't affect the site speed.

= Q: How can i add my own component (new field type)? =
A: first of all you need to create new php class for your field. Your class should be extended from Just_Field plugin class!
You can start from copying the existing class /[plugins-folder]/just-custom-fields/components/input-text.php to your plugin folder or theme and update the code:

- change class name
- change class methods to use your specific controls and data.

Class structure is very similar to WordPress Widget API classes.

Then you need to include your new component file into your plugin main file or theme functions.php file.

And the last step:

- add new action hook "add_action('jcf_register_fields', 'my_register_fields_function')"
- add new callback function similar to this one:

function my_register_fields_function(){
	jcf_field_register('YOUR_COMPONENT_CLASS_NAME');
}