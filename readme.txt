=== Improved Simpler CSS ===
Contributors: enej, ctlt-dev, oltdev
Tags: css, wpmu, appearance, themes, custom css, edit css, live edit css, revisions css, custom post type
Requires at least: 3.0
Tested up to: 3.1.2



Add the ability to add css to your existing style sheet. 

== Description ==

This Simpler CSS mu-plugin allows WordPress multisite hosts to enable users to add custom stylesheets to their blogs.

The plugin creates a new menu item under the Appearance menu in WordPress 2.7
that allows users to enter custom CSS code that will be injected into the
`<head>` section of their blog. The CSS is stored in the blog's options table
and is filtered through a standard PHP function before being outputted, preventing
the user from inserting malicious code into the header.

For non-WordPress µ blogs, this plugin provides an easy way to modify the appearance of installed
themes or plugins (such as Sociable) without modifying theme/plugin files that may change
with upgrades. No write access is required to any files for Simpler CSS to function, as it stores
its data in the database's options table — and that means theme/plugin upgrades won't impact
your custom CSS.
   
The custom CSS will only show when the theme has the necessary `wp_header()` function
in the `<head>` section, as most themes now do.

Props go to Jeremiah Orem  and Frederick D. who plugin this one is based on. 

== Installation ==
= For WordPress µ =
1. Upload the `simpler-css.php` file to the `/wp-content/mu-plugins/` directory. The other files
should not be uploaded, and the file cannot be in a subdirectory.
2. You're done! As a mu-plugin, Simpler CSS is automatically enabled for all blogs.
= For normal WordPress installations =
1. Upload the `simpler-css` directory to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= Why isn't the code showing up on the blog? =
Remember that this plugin depends on standard WordPress hooks to operate. If the
active theme does not have `wp_header()` in its code, this plugin is ineffective.
*Remedy:* add the code `<?php wp_header(); ?>` to the theme files in the `<head>` section.

= Why can't I add JavaScript to the blog's code? =
This plugin will only operate for Cascading Style Sheets code. The custom CSS is escaped
and outputted within a set of `<style>` tags, preventing bots from abusing the functionality
to inject malicious code. Allowing users to inject JavaScript into the blog's header
is a security vulnerability, thus this plugin does not permit it.

= Why isn't my CSS showing as it should be? =
Check first of all to make sure that your custom CSS *does not* include the opening `<style type="text/css">`
and closing `</style>` HTML tags. These tags are outputted automatically, and including
them manually in your CSS code could lead to malfunctions.

== Screenshots ==
1. The menu item as it appears under the Appearance menu.
2. Live Edit CSS
3. OLD Edit Screen 
4. Revisions Screen 

== Changelog ==

= 2.0.2 =
Minor bug fixes

= 2 = 
Completely rewritten 


= 1.0 = 
* custom css stored in post tabe as a content type
* custom css revisions are enabled
* improved custom css  