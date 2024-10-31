=== Post Head Includes ===
Contributors: rbuczynski
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=49Q7JW9AJHJMG
Tags: post-head-includes,wp_enqueue_script,wp_enqueue_style,javascript,css,stylesheets,page-head
Requires at least: PHP 5.3, WordPress 3.5.2 (suggested)
Tested up to: 3.5.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easily add scripts and stylesheets to the HEAD of your posts, keeping your HTML cleaner without inline scripts or styles.

== Description ==

Easily add scripts and stylesheets per post. This is a great tool for developers creating custom pages for their clients, but also useful for the blogger who wants to separate inline CSS & JS from their post content.

This plugin provides an interface for wp_enqueue_script and wp_enqueue_style functions, so all of the functionality that a developer would find there is available in this plugin.

However, script localization differs from wp_localize_script in that with this plugin you are allowed to input raw JavaScript code for localization, library initialization, and more.

This plugin may also help to alleviate modern browser XSS detection from blocking potentially unsafe JavaScript.

== Installation ==

1. Upload files to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Create a new post or page
4. Configure your post head includes in the meta box below the editor

== Frequently asked questions ==

= Where are my scripts and stylesheets? =

They will be added to the HEAD area of the page or post.

= Can I have more than one script or stylesheet? =

Yes, you can add as many as you'd like or need.

= Can I add my scripts to the footer for faster loading? =

Yes, under the Advanced Options you can set `In Footer` to `true`.

= My included script needs to be initialized. How can I do this? =

Under `Advanced Options` you can add any custom JavaScript to the `Localization` text area. This will be added after your JS file.

= How to conditions work? =

When adding a condition, you need only to write the expression, and not the entire comment tag. For example, to load the item only for IE browsers at version 9, you would write `if IE 9`. Note: Adding a condition will force the item to be included at the end of the <HEAD> element (after all other scripts and stylesheets). Therefore, specifying dependencies will have no effect.

== Screenshots ==

1. Easily add unlimited items to your post or page.
2. You only need to configure a type and a source to get your includes properly loaded.
3. For more advanced options, you can completely customize your scripts, adding localizations or initializer code.
4. Tag your included stylesheets for properly CSS media types to add greater flexibility to your pages.

== Changelog ==

0.2.1 - Fixed inconsistent post ID management on save.
0.2.0 - Added support for IE conditional tags; minor code cleanup.
0.1.0 - First release.