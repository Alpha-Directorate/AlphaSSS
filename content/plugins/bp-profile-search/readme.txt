=== BP Profile Search ===
Contributors: dontdream
Tags: buddypress, directory, member, members, user, users, friend, friends, profile, profiles, search, filter
Requires at least: 3.6
Tested up to: 4.1.1
Stable tag: 4.1

Let visitors search your BuddyPress Members Directory and their Friends list.

== Description ==

With BP Profile Search you can build custom Members search forms, build custom Members Directory pages and use them as search results pages. Visitors can search your BuddyPress Members Directory and, if they are Members, their Friends list.

You can insert the search forms in a Members Directory page, in a sidebar or widget area, or in any post or page without modifying your theme.

When visitors click the *Search* button, they are redirected to your form's Members Directory page showing their search results. The *All Members* tab shows all the results, while the *My Friends* tab shows the results found among your visitor's friends.

Requires at least BuddyPress 1.8 -- Tested up to BuddyPress 2.2.1

== Installation ==

After the standard plugin installation procedure, you'll be able to access the plugin settings page *Users -> Profile Search*, where you can build and customize your search forms.

= Form Fields =

In this section you can:

* Add and remove form fields
* Enter the field label and description, or leave them empty to use the default
* Enable the *Value Range Search* for numeric fields, or the *Age Range Search* for date fields
* Change the order of the fields

= Form Attributes =

In this section you can select your form's *method* attribute:

* POST: the form data are not visible in the URL and it's not possible to bookmark the results page
* GET: the form data are sent as URL variables and it's possible to bookmark the results page

You can also select your form's *action* attribute. The *action* attribute points to your form's results page, that could be:

* The BuddyPress Members Directory page
* A custom Members Directory page

You can create a custom Members Directory page using the shortcode **[bps_directory]**, and you can even use a custom directory template. To learn more, read the [Custom Directories](http://dontdream.it/bp-profile-search/custom-directories/) tutorial.

= Add to Directory =

With this option you can insert your search form in its Members Directory page. If you enable *Add to Directory*, you can also:

* Enter the HTML text for the optional form header
* Enable the *Toggle Form* option
* Enter the text for the *Toggle Form* button

= Text Search Mode =

With this option you can select your text search mode, between:

* LIKE: a search for *John* finds *John*, *Johnson*, *Long John Silver*, and so on
* SAME: a search for *John* finds *John* only

In both modes, two wildcard characters are available:

* Percent sign (%): matches any text, or no text at all
* Underscore (_): matches any single character

= Display your search form =

After you build your search form, you can display it:

* In its Members Directory page, selecting the option *Add to Directory*
* In a sidebar or widget area, using the widget *Profile Search*
* In a post or page, using the shortcode **[bps_display form=ID]** (*)
* Anywhere in your theme, using the PHP code<br>
**&lt;?php do_action ('bps_display_form', ID); ?&gt;** (*)

(*) Replace ID with your actual form ID.

== Changelog ==

= 4.1 =
* Added ability to create custom Members Directory pages, and to use them as search results pages

Note: If you are upgrading from version 4.0.x, you have to update your existing forms with your Directory page selection. Go to *Users -> Profile Search*, *Edit* each form, select its *Form Action (Results Directory)* and *Update*.

= 4.0.3 =
* Fixed PHP fatal error when BP component *Extended Profiles* was not active
* Replaced deprecated like_escape()
= 4.0.2 =
* Fixed PHP warning when using the *SAME* search mode
= 4.0.1 =
* Fixed bug with field options not respecting sort order
* Fixed bug with search strings containing ampersand (&)
= 4.0 =
* Added support for multiple forms
* Added ability to export/import forms
* Added selection of the form *method* attribute
* Updated Italian and Russian translations
= 3.6.6 =
* Added French translation
= 3.6.5 =
* Fixed bug when searching in a *number* profile field type
= 3.6.4 =
* Added support for custom profile field types, see [documentation](http://dontdream.it/bp-profile-search/custom-profile-field-types/)
= 3.6.3 =
* Reduced the number of database queries
= 3.6.2 =
* Updated for the *number* profile field type (BP 2.0)
= 3.6.1 =
* Fixed PHP warnings after upgrade
= 3.6 =
* Redesigned settings page, added Help section
* Added customization of field label and description
* Added *Value Range Search* for multiple numeric fields
* Added *Age Range Search* for multiple date fields
* Added reordering of form fields
* Updated Italian translation
* Updated Russian translation
= 3.5.6 =
* Replaced deprecated $wpdb->escape() with esc_sql()
* Added *Clear* link to reset the search filters
= 3.5.5 =
* Fixed the CSS for widget forms and shortcode generated forms
= 3.5.4 =
* Added Serbo-Croatian translation
= 3.5.3 =
* Added Spanish, Russian and Italian translations
= 3.5.2 =
* Fixed a pagination bug introduced in 3.5.1
= 3.5.1 =
* Fixed a few conflicts with other plugins and themes
= 3.5 =
* Added the *Add to Directory* option
* Fixed a couple of bugs with multisite installations
* Ready for localization
* Requires BuddyPress 1.8 or higher
= 3.4.1 =
* Added *selectbox* profile fields as candidates for the *Value Range Search*
= 3.4 =
* Added the *Value Range Search* option (Contributor: Florian Shießl)
= 3.3 =
* Added pagination for search results
* Added searching in the *My Friends* tab of the Members Directory
* Removed the *Filtered Members List* option in the *Advanced Options* tab
* Requires BuddyPress 1.7 or higher
= 3.2 =
* Updated for BuddyPress 1.6
* Requires BuddyPress 1.6 or higher
= 3.1 =
* Fixed the search when field options contain trailing spaces
* Fixed the search when field type is changed after creation
= 3.0 =
* Added the *Profile Search* widget
* Added the [bp_profile_search_form] shortcode
= 2.8 =
* Fixed the *Age Range Search*
* Fixed the search form for required fields
* Removed field descriptions from the search form
* Requires BuddyPress 1.5 or higher
= 2.7 =
* Updated for BuddyPress 1.5 multisite
* Requires BuddyPress 1.2.8 or higher
= 2.6 =
* Updated for BuddyPress 1.5
= 2.5 =
* Updated for BuddyPress 1.2.8 multisite installations
= 2.4 =
* Added the *Filtered Members List* option in the *Advanced Options* tab
= 2.3 =
* Added the choice between *Partial match* and *Exact match* for text searches
= 2.2 =
* Added the *Age Range Search* option
= 2.1 =
* Added the *Toggle Form* option to show/hide the search form
* Fixed a bug where no results were found in some installations
= 2.0 =
* Added support for *multiselectbox* and *checkbox* profile fields
* Added support for % and _ wildcard characters in text searches
= 1.0 =
* First version released to the WordPress Plugin Directory

== Upgrade Notice ==

= 4.1 =
Note: If you are upgrading from version 4.0.x, you have to update your existing forms with your Directory page selection. Go to *Users -> Profile Search*, *Edit* each form, select its *Form Action (Results Directory)* and *Update*.

= 4.0.3 =
Note: BP Profile Search version 4 is not compatible with version 3. When you first upgrade to version 4, you have to reconfigure your BP Profile Search forms and widgets, and modify any BP Profile Search shortcodes and *do_action* codes you are using.
In a multisite installation, the BP Profile Search settings page is in the individual Site Admin(s), and no longer in the Network Admin.
