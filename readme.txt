=== Storipress ===
Tags: blogging, export, storipress, migrate, publishing
Requires at least: 5.0
Tested up to: 6.2
Requires PHP: 7.2
License: GPL-3.0
License URI: https://github.com/storipress/wp-storipress-exporter/blob/main/LICENSE

#STORIPRESS MIGRATOR - MIGRATE YOUR CONTENT TO STORIPRESS IN 1 CLICK

This official Storipress plugin allows you export your WordPress categories and content in a format that can be easily imported into Storipress.

== Description ==
This plugin is currently in early beta. Help us make the plugin better by opening issues in our [Github Repo](https://github.com/storipress/wp-storipress-exporter/issues)!

##Features overview
The Storipress Migrator plugin will export as much blog and publication data as it can into a clean set of exported files.

- Posts, pages, tags and authors are all automatically exported and recreated for Storipress
- Content categories will be converted automatically into desks.
- Storipress comes with native Disqus comments.
- Storipress does not have built-in comments, but it does integrate with Disqus. Provided that your URL is the same on Storipress, Disqus will automatically migrate your comments across to Storipress.
- Exporting meta is only supported for users using Yoast (for now). If you use another SEO plugin, please create an issue in our Github repo, or modify the plugin and create a pull request!

##Bug reports
Bug reports for the Storipress Migrator plugin are welcome over on our [GitHub Repository](https://github.com/storipress/wp-storipress-exporter/issues).

== Installation ==
1. Use the Add New Plugin in the WordPress admin area
2. Activate the plugin through the ‘Plugins’ menu in WordPress
3. Access the exporter functionality under Tools -> Export to Storipress

== Changelog ==

= 0.0.11 =
* Export plugins information

= 0.0.10 =
* Add build version to filename
* Add tags and categories to post export

= 0.0.9 =
* Fix category hierarchy issue

= 0.0.8 =
* Export user caps information

= 0.0.7 =
* Fix the OOM issue when exporting a large set of users

= 0.0.6 =
* Ensure category exporting hierarchy

= 0.0.5 =
* Fix a notice issue when exporting

= 0.0.4 =
* Support PHP 7.2

= 0.0.3 =
* Fix the crash issue when there are more than ten thousand posts

= 0.0.2 =
* Support exports users, tags and categories

= 0.0.1 =
* Initial release

# Copyright & License

Copyright (c) Storipress - Released under the [GNU General Public License](LICENSE).
