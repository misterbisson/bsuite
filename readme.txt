=== bSuite ===
Contributors: misterbisson
Donate link: http://MaisonBisson.com/
Tags: cms, content management, tags, stats, statistics, formatting, pages, manage, integration, admin
Requires at least: 2.2
Tested up to: 2.3.2
Stable tag: /trunk/

Suite of tools used to improve WordPress' CMS capabilities and usefulness as an application platform.

== Description ==

bSuite includes a set of tools (some in seperate plugins) that improve WordPress' tag management and CMS capabilities, make it easier to build dynamic pages, and leverage WP as an application platform. Plugins that can be activated seperately include one for stats tracking (bSuite bStat), a tag importer, and an example plugin that illustrates how to leverage the features bSuite adds to WordPress.

*NOTE: users of previous versions of bStat or bSuite should see the upgrade section of the installation instructions.

== Installation ==

1. Upload `bsuite` to the `/wp-content/plugins/` directory
1. Activate bSuite through the 'Plugins' menu in WordPress
1. Activate any desired optional plugins through the Plugins tab in your WordPress admin

= Upgrading = 

Upgrades from previous versions of bSuite 3 simply require replacing the old plugin folder with the new (or use SVN!). *Upgrades from bSuite b2 (any version) or bStat (any version) take a little more work.*

bSuite 3 uses a different table structure than previous versions of bSuite or bStat. The package includes a plugin that will import the old old into the new tables, but you must run it manually. Here are the steps:

1. Deactivate all previous versions of bSuite or bStat
1. Install and activate the latest bSuite and bSuite bStat
1. Activate the bStat Upgrader. Run it in Manage -> Import, then deactivate the plugin

The upgrader will not remove any old tables, but you should feel free do remove them on your own using a MySQL management tool. The new tables all include `bsuite3` in their name. See the <a href="http://maisonbisson.com/blog/bsuite/bstat#11904_upgrading_1/">step by step screenshot walkthrough of the upgrader</a> for more.

== Frequently Asked Questions ==

= How come these stats are different from Google Analytics or product X? =

The difference is often in how the stats are counted. bSuite bStat counts the number of times WordPress generates a page. This can vary if you're using caching tools, but for most sites, that means bStat is tracking every page load, no matter who or what loads the page. Google Anylitics and other hosted services use a JavaScript app that contacts a remote server to count the stats. As a result, any client that doesn't run JavaScript goes uncounted. As just one example, I'm not aware of any webcrawlers that execute the JavaScript they find, so hosted stats services can't measure their traffic even though bStat can. 

bStat's primary features are to offer information to your readers about what stories are popular and help illustrate those trends to blog authors. I leave it up to blog administrators to decide if the mechanism bStat uses to get that data is sufficient to the purpose, though I will add that I run a number of stats applications in addition to bStat (Google Analytics and AWstats).

= How do I...? =

Full documentation and usage examples are available at <a href="http://maisonbisson.com/blog/bsuite/">MaisonBisson.com</a>.

== Screenshots ==

1. The bSuite Share Links widget, which leads to <a href="http://maisonbisson.com/blog/post/11971/how-expensive-does-commercial-software-need-to-get-before-we-consider-open-source/share">a page of bookmark, feed, and translate links for for each post and page</a>.
2. <a href="http://borkweb.com/">Matt Batchelder</a>'s <a href="http://borkweb.com/story/ajax-templating-and-the-separation-of-layout-and-logic">use of</a> the `[[innerindex]]` token. <a href="http://maisonbisson.com/blog/bsuite/core#11903_innerindex_1">Innerindex</a> automatically generates a list of headings in the page, with links to jump to them.
3. <a href="http://www.plymouth.edu/library/">Lamson Library</a>'s <a href="http://www.plymouth.edu/library/by-subject/art-history">use of</a> the `[[pagemenu]]` token. <a href="http://maisonbisson.com/blog/bsuite/core#11903_pagemenu_1">Pagemenu</a> automatically generates a list of child pages for a given page. (<a href="http://maisonbisson.com/blog/bsuite/core#11903_using-tokens_1">The included tokens</a> are just a start, extend them with <a href="http://maisonbisson.com/blog/bsuite/slideshow">your own plugin</a>.)
4. <a href="http://spiralbound.net/">Cliff Pearson</a>'s Quickstats report, part of bStat.
5. <a href="http://spiralbound.net/">Cliff Pearson</a>'s page loads report, part of bStat.
6. <a href="http://spiralbound.net/">Cliff Pearson</a>'s use of the bStat Posts widget, part of bStat that reports popular posts.
7. <a href="http://spiralbound.net/">Cliff Pearson</a>'s use of the bSuite Recently Commented widget. This widget differs from the built in Recent Comments widget in that it only shows posts, not individual comments.
8. The <a href="http://maisonbisson.com/blog/bsuite/machine-tags">machine tag</a> input field in the edit post and edit page screen. 

