=== Plugin Name ===
Contributors: fattymembrane
Donate link: http://launchsite.us/
Tags: search, results, pin, redirect, curate, better search, improve search, relevant, relevance, search results, terms,
Requires at least: 3.0.1
Tested up to: 4.3
Stable tag: 4.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Specify the content you want users to see for specific search queries by pinning results, intercepting similar search terms, and redirecting based on keyword.

== Description ==

You know what information users should be seeing for the most common searches on your site, but the WordPress search engine usually has other ideas. Until now, there have been two options:

1. Use Google search results, making the user feel like he’s been escorted out of the building by burly security guards.
2. Spend a few hours playing with the endless settings of search plugins that use fancy algorithms, but don’t seem to do much other than slow down your site.

But most of the time, you know exactly what information your users should be seeing for common searches. Curated Search gives you the tools to send users to the best content using the perfect algorithm - you.

https://www.youtube.com/watch?v=nO75kPExREw

Specify a search term for which you would like to control the results, then use the following features to curate the content displayed to your visitors:

* Pinned results: Want to make sure your 10k word article on vaporwave vs. chillwave shows up every time users search for “seapunk”? Pin it for the search term and make sure it’s the first result they see.
* Synonyms: Create search term “synonyms” to show desired search results on less common terms or common misspellings. Make sure users see results for “soda” when they search for “pop” and people searching for “resturaunt” get the results for “restaurant”.
* Contextual search content: Build content in the standard WordPress WYSIWYG editor  and display it above the search results for specific terms. Want to provide a special download link for people searching for “ebooks”, or coupon codes for people searching for “handbags”? No problem.
* Redirect specific search terms: If you have 1,000 articles about vinyl records on your site, a search for “vinyl” will be practically useless for the user. Send the user to a more useful page (landing page, topic center, archive, etc.)
* Batch hide content: Don’t want people seeing search results for certain parts of the site? Mark categories, tags, and custom taxonomies as off-limits to the site search with a handy wizard - no more tracking down category IDs.
* Hide individual pieces of content: A handy meta box on individual posts/pages/etc. lets you hide single items without having to assign them a custom tag or category.
* Limit total search results: Nobody is looking at page 6 of 15 in your site search. Ditch the overflow.

== Installation ==

1. Upload the `curated-search` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. If you would like to display content from the WYSIWYG editor of a Special Search at the top of the search results page, paste `<?php do_action('cs_search_after_title'); ?>` into the search.php file of your theme below the header and above The Loop.

== Frequently Asked Questions ==

= Do I have to modify my theme to use this? =

No. The plugin will work without modifying your theme. If you wish to display content from the visual editor on the search results for Special Searches, you need to place a single line of PHP code in your search.php file.

= Will this automatically make my search results better? =

This plugin won't do anything automatically. It is for modifying search results for specific queries that you choose.

= Will this catch misspellings and direct them to the correct results? =

 No, not out of the box. You can, however, manually set common misspellings as Synonyms of a Special Search so that they are intercepted.

== Screenshots ==

1. The plugin settings screen. Set a maximum number of search results for all searches, redirect the user for searches with a single result, and batch exclude content based on post type and taxonomy.
2. Create a new Special Search for a desired search term. If you plan to add the PHP snippet to your theme, you can enter content in the editor window (including shortcodes) that will appear above the search results for the search term or its synonyms.
3. Special Search (continued): Set the primary term for which search results should be modified, the other words that should display the same results, and a destination URL if you would like to redirect users to a specific page rather than show search results. The Pinned Content box allows you to select posts, pages, and custom post types that should appear before organic search results.
4. Search results for a Special Search. Content from the visual editor for the Special Search is displayed above results, and the Pinned Results are displayed before any organic results.
5. Search results for terms marked as Synonyms of the Special Search are the same as the primary search term.

== Changelog ==

= 1.2 =
* NEW: Import and export special searches for backup or use on other sites.
* FIX: Plugin used a function called CS() which caused errors with some other plugins, notably Theme.co's Cornerstone and X theme. The function has been renamed to remedy these issues.
* FIX: Contextual content code was accidentally removed from the settings page in 1.1 and has been restored.

= 1.1 =
* Added "Destination URL" column to the Special Searches list
* Revised Settings screen and added a "Support" tab with overview video and support links
* Minor graphical tweaks to "Pinned Results" field

= 1.0 =
* Initial release

== Upgrade Notice ==

= 1.0 =
Initial release
