=== Datalist it ===
Contributors: datalistit
Donate link: none
Tags: import, table, csv, *csv, database, database table
Requires at least: 3.0.1
Tested up to: 3.0.1
Stable tag: 0.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create a table from a csv file to display on a website or blog using Ajax. No technical knowledge required.

== Description ==

This plugin creates a table out of data contained in a cvs file. The table can then be displayed on a website or in a blog.
You need absolutely no technical knowledge. The plugin will convert the cvs file into a database enabling you to decide how the data will be displayed online.
You can define:

 * orderby - sort the data the way you wish to present it,
 * columns - decide which columns will be displayed online,
 * norows - define how many rows will be displayed on each "page" of the table. The viewer will be able to switch  between pages using "Previous" and "Next".
 * ID - name the table

Styling (css) can be defined in the Dashboard->Datalist it->Advanced.

HTML version of the full table is also available.

= Example of database table shortcode: =
`[datalistit 
   dbtable='Sales_results_Q1_sample_file' 
   norows=2 
   orderby='stabilisers' 
   columns='month, stabilisers' id='table1' ]`
= Example of html table shortcode: =
`[datalistit table='Sales_results_sample_file' ]`

Anybody with "Edit post" capability can insert the short code onto a WP site or blog, thus creating a table. No user data is held on the server - any uploaded data is deleted automatically when the server sends the HTML file to the user.

In order to create a table, the user uploads a csv file using the plugin interface. The data is sent to the datalistit.com server where the files are processed. Datalistit.com is a service and the plugin is required to access this service. Having processed the data, the server returns a table and short code is created. The user can display the table on their WP site or blog by copying and pasting the short code that is returned by the server.

== Installation ==

1. Upload `plugin-name.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress or by [Automatic Plugin Installation](http://codex.wordpress.org/Managing_Plugins#Automatic_Plugin_Installation)

== Frequently Asked Questions ==

= What can I use the Datalist it plugin for? =
The plugin enables you to create a database table as well as an HTML table from data held in csv file. The table can then be displayed on your WP site or blog.

= Why is the data sent to an external server? =
The data is sent to a server used by the datalistit.com service. This has been done to enable future developments of the plugin.

= Is the data kept on the external server the plugin uses? =
No data is kept on the external server. The data is removed automatically when the server returns the table to the user.

== Screenshots ==

1. "Datalist it" menu screen
2. example of a table as seen by a user

== Changelog ==

= 0.0.1 =
initial version
= 0.0.2 =
user can create database table 
= 0.0.3 =
upgrade to a new php version  

== Upgrade Notice ==

= 0.0.1 =
initial version
= 0.0.2 =
user can create database table 
= 0.0.3 =
upgrade to a new php version 

== Arbitrary section ==

