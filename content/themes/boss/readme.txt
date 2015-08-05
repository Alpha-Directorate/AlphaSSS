/*--------------------------------------------------------------
INSTALLATION
--------------------------------------------------------------*/

= From your WordPress dashboard =

-BuddyPress-

1. Visit 'Plugins > Add New'
2. Search for 'BuddyPress'
3. Activate BuddyPress from your Plugins page

-Boss Theme-

1. Go to 'Appearance > Themes'
2. Click 'Add New'
3. Upload this theme (as a ZIP file)
4. Upload the included child theme (as a ZIP file)
5. Activate the child theme
6. Customize your website at 'Appearance > Customize'
7. Have fun!

Instructions: http://www.buddyboss.com/tutorials/
Support: http://www.buddyboss.com/support-forums/
Release Notes: http://www.buddyboss.com/release-notes/


/*--------------------------------------------------------------
CHANGELOG
----------------------------------------------------------------
/*--------------------------------------------------------------
1.1.8 - July 10, 2015
--------------------------------------------------------------*/

FEATURES:

	Compressed images, saves 1.7mb of space
	LearnDash compatibility
	Improved documentation

BUG FIXES:

	Fixed front-page post pagination
	Fixed issues with cover photo cropping
	Fixed comment padding
	Fixed PHP errors with notifications off
	Fixed extra links in profile dropdown

CHANGED FILES:

	/buddyboss-inc/cover-photo.php
	/buddyboss-inc/theme-functions.php
	buddypress-group-single.php
	/css/main-desktop.css
	/css/main-global.css
	front-page.php
	header.php
	/images/ (all files)
	/languages/en_US.mo
	/languages/en_US.po
	/languages/fr_FR.mo
	/languages/fr_FR.po
	readme.txt
	style.css

TESTED WITH:

	-- WordPress --
	WordPress 4.0, 4.1, 4.2+
	BuddyPress 2.1, 2.2, 2.3+
	bbPress 2.5+

	-- Mobile --
	iOS 6, 7
	Android 4.1+ 
	Windows Phone

	-- Browsers --
	Chrome
	Safari
	Firefox
	Internet Explorer 9

/*--------------------------------------------------------------
1.1.7 - June 23, 2015
--------------------------------------------------------------*/

BUG FIXES:
	
	Better compatibility with WooCommerce
	Better compatibility with WooThemes Sensei
	Fixed paragraph spacing in comments and messages
	Fixed spacing between first level replies in activity
	Fixed "Search Messages" text visibility
	Friend vs Friend(s) logic added to profile header
	Accept jpeg filetype for Cover Photo
	Starred messages compatibility for BuddyPress 2.3
	"Change Profile Photo" compatibility for BuddyPress 2.3
	Faster loading of filter styles
	Fixed PHP error "Notice: Undefined offset: 1"

CHANGED FILES:

	/buddyboss-inc/buddyboss-bp-legacy/bp-legacy-loader.php
	/buddyboss-inc/buddyboss-customizer/admin/buddyboss-customizer-admin.js
	/buddyboss-inc/buddyboss-customizer/buddyboss-customizer-loader.php
	/buddyboss-inc/init.php
	/buddyboss-inc/theme-functions.php
	/buddypress/groups/create.php
	/buddypress/members/members-loop.php
	/buddypress/members/single/friends/requests.php
	/buddypress/members/single/messages/messages-loop.php
	/buddypress/members/single/profile/change-avatar.php
	buddypress.php
	/css/main-desktop.css
	/css/main-global.css
	/css/main-mobile.css
	/js/buddyboss.js
	/js/ui-scripts/selectboxes.js
	/languages/en_US.mo
	/languages/en_US.po
	readme.txt
	style.css	

TESTED WITH:

	-- WordPress --
	WordPress 4.0, 4.1, 4.2+
	BuddyPress 2.1, 2.2, 2.3+
	bbPress 2.5+

	-- Mobile --
	iOS 6, 7
	Android 4.1+ 
	Windows Phone

	-- Browsers --
	Chrome
	Safari
	Firefox
	Internet Explorer 9

/*--------------------------------------------------------------
1.1.6 - May 13, 2015
--------------------------------------------------------------*/

FEATURES:

	Default button on profiles is now "Private Message", instead of "Cancel Friendship"
	Improved activity post-form layout
	Allow users to select default Accordion item in shortcode
	Added Customizer option for mobile titlebar color
	Added French language translations, credits to Jean-Pierre Michaud

BUG FIXES:
	
	Selectbox fixes
	Filter fixes
	Video embed width fixes
	Logo height fixes
	Search fixes
	Social links fixes
	Improved panel scrolling
	Better WooCommerce CSS compatibility
	Better rtMedia compatibility
	Better Form Maker compatibility

CHANGED FILES:

	/buddyboss-inc/buddyboss-customizer/buddyboss-customizer-loader.php
	/buddyboss-inc/buddyboss-customizer/buddyboss-customizer.js
	/buddyboss-inc/buddyboss-shortcodes/admin/shortcode-tinymce-button-old-wp.js
	/buddyboss-inc/buddyboss-shortcodes/admin/shortcode-tinymce-button.js
	/buddyboss-inc/buddyboss-shortcodes/admin/shortcodes.php
	/buddyboss-inc/theme-functions.php
	/buddyboss-inc/user-options.php
	/buddypress/activity/index.php
	/buddypress/activity/post-form.php (added)
	/buddypress/groups/single/media.php (added)
	/buddypress/groups/single/members.php
	/buddypress/members/single/member-header.php
	buddypress-group-single.php
	/css/main-desktop.css
	/css/main-global.css
	/css/main-mobile.css
	footer.php
	/js/buddyboss.js
	/js/ui-scripts/selectboxes.js
	/languages/en_US.mo
	/languages/en_US.po
	/languages/fr_FR.mo (added)
	/languages/fr_FR.po (added)
	readme.txt
	style.css

TESTED WITH:

	-- WordPress --
	WordPress 4.0, 4.1+
	BuddyPress 2.1, 2.2
	bbPress 2.5+

	-- Mobile --
	iOS 6, 7
	Android 4.1+ 
	Windows Phone

	-- Browsers --
	Chrome
	Safari
	Firefox
	Internet Explorer 9

/*--------------------------------------------------------------
1.1.5 - April 9, 2015
--------------------------------------------------------------*/

FEATURES: 

	CSS compatibility with new Privacy feature in BuddyBoss Wall plugin

BUG FIXES:

	Fixed mobile panels not opening after rotation
	Fixed mobile titlebar icon selection
	Fixed mobile messages width
	Fixed mobile WooCommerce coupon code layout

CHANGED FILES:

	/buddyboss-inc/buddyboss-customizer/buddyboss-customizer-loader.php
	/buddyboss-inc/theme-functions.php
	/css/main-desktop.css
	/css/main-global.css
	/css/main-mobile.css
	editor-style.css
	header.php
	/js/buddyboss.js
	/js/ui-scripts/selectboxes.js (added)
	/languages/en_US.mo
	/languages/en_US.po
	readme.txt
	style.css

TESTED WITH:

	-- WordPress --
	WordPress 4.0+
	BuddyPress 2.1, 2.2
	bbPress 2.5+

	-- Mobile --
	iOS 6, 7
	Android 4.1+ 
	Windows Phone

	-- Browsers --
	Chrome
	Safari
	Firefox
	Internet Explorer 9+

/*--------------------------------------------------------------
1.1.4 - April 3, 2015
--------------------------------------------------------------*/

FEATURES: 

	Social Icons in footer, set in Customizer
	Improved WooCommerce styling
	Better mobile support for WooCommerce

BUG FIXES:

	Display profile links to Subscribers with WooCommerce enabled
	Fixed mobile device Cover Photo uploading
	Message template updated for latest BuddyPress compatibility
	Improved dialogs in WordPress admin
	Fixed Social fields code errors
	CSS fixes for Firefox

CHANGED FILES:

	/buddyboss-inc/buddyboss-customizer/buddyboss-customizer-loader.php
	/buddyboss-inc/buddyboss-shortcodes/admin/admin-style.css
	/buddyboss-inc/buddyboss-shortcodes/admin/shortcode-tinymce-button-old-wp.js
	/buddyboss-inc/buddyboss-shortcodes/admin/shortcode-tinymce-button.js
	/buddyboss-inc/cover-photo.php
	/buddyboss-inc/theme-functions.php
	/buddyboss-inc/user-options.php
	/buddypress/members/single/messages/messages-loop.php
	/buddypress/members/single/messages/notices-loop.php
	/css/main-desktop.css
	/css/main-global.css
	/css/main-mobile.css
	footer.php
	header.php
	/js/buddyboss.js
	readme.txt
	style.css

TESTED WITH:

	-- WordPress --
	WordPress 4.0+
	BuddyPress 2.1, 2.2
	bbPress 2.5+

	-- Mobile --
	iOS 6, 7
	Android 4.1+ 
	Windows Phone

	-- Browsers --
	Chrome
	Safari
	Firefox
	Internet Explorer 9+

/*--------------------------------------------------------------
1.1.3 - March 28, 2015
--------------------------------------------------------------*/

BUG FIXES:

	Search color for No BuddyPanel template
	Customizer selection for Body font
	Customizer selection for Body text color
	Customizer selection for Footer bottom text color
	Customizer selection for titlebar
	Directory content fixes

CHANGED FILES:

	/buddyboss-inc/buddyboss-customizer/buddyboss-customizer-loader.php
	/buddyboss-inc/theme-functions.php
	/css/main-desktop.css
	/css/main-global.css
	/css/main-mobile.css
	readme.txt

TESTED WITH:

	-- WordPress --
	WordPress 4.0+
	BuddyPress 2.1, 2.2
	bbPress 2.5+

	-- Mobile --
	iOS 6, 7
	Android 4.1+ 
	Windows Phone

	-- Browsers --
	Chrome
	Safari
	Firefox
	Internet Explorer 9+

/*--------------------------------------------------------------
1.1.2 - March 25, 2015
--------------------------------------------------------------*/

FEATURES:

	Improved WooCommerce plugin styling
	Better support for Sensei plugin

BUG FIXES:

	Fixed theme updates losing customizations (removed options.css)
	Fixed issues with video embed sizing
	Fixed padding above profile avatar on mobile panel
	Fixed Blog index widget/sidebar logic
	Fixed desktop header logo showing on mobile
	Mobile CSS fixes

CHANGED FILES:

	/buddyboss-inc/buddyboss-customizer/buddyboss-customizer-loader.php
	/buddyboss-inc/buddyboss-slides/buddyboss-slides-loader.php
	/buddyboss-inc/buddyboss-slides/css/buddyboss-slides.css
	/buddyboss-inc/buddyboss-slides/js/fwslider.js
	/buddyboss-inc/buddyboss-slides/js/fwslider.min.js
	/buddyboss-inc/theme-functions.php
	/css/main-desktop.css
	/css/main-global.css
	/css/main-mobile.css
	front-page.php
	index.php
	/js/buddyboss.js
	/js/ui-scripts/fitvids.js
	/languages/en_US.mo
	/languages/en_US.po
	readme.txt
	style.css

TESTED WITH:

	-- WordPress --
	WordPress 4.0+
	BuddyPress 2.1, 2.2
	bbPress 2.5+

	-- Mobile --
	iOS 6, 7
	Android 4.1+ 
	Windows Phone

	-- Browsers --
	Chrome
	Safari
	Firefox
	Internet Explorer 9+

/*--------------------------------------------------------------
1.1.1 - March 14, 2015
--------------------------------------------------------------*/

FEATURES:

	Improved support for WooCommerce plugin
	Improved support for BP Profile Search plugin
	Admin option to enable/disable Activity infinite scrolling

BUG FIXES:

	Fixed error when paginating in your Friends list
	Fixed Sensei plugin notice bug
	Fixed Slider notice bug
	Datebox CSS fix

CHANGED FILES:

	/buddyboss-inc/buddyboss-customizer/buddyboss-customizer-loader.php
	/buddyboss-inc/buddyboss-slides/buddyboss-slides-loader.php
	/buddyboss-inc/theme-functions.php
	/buddypress/members/single/member-header.php
	/css/main-desktop.css
	/css/main-global.css
	/css/main-mobile.css
	header.php
	/js/buddyboss.js
	/languages/en_US.mo
	/languages/en_US.po
	page.php
	readme.txt
	style.css
	woocommerce.php

TESTED WITH:

	-- WordPress --
	WordPress 4.0+
	BuddyPress 2.1, 2.2
	bbPress 2.5+

	-- Mobile --
	iOS 6, 7
	Android 4.1+ 
	Windows Phone

	-- Browsers --
	Chrome
	Safari
	Firefox
	Internet Explorer 9+

/*--------------------------------------------------------------
1.1.0 - March 10, 2015
--------------------------------------------------------------*/

BUG FIXES:

	CSS fixes for mobile header
	CSS fixes for flickering on Activity

CHANGED FILES:

	/buddyboss-inc/buddyboss-customizer/buddyboss-customizer-loader.php
	/buddyboss-inc/theme-functions.php
	/css/main-desktop.css
	/css/main-global.css
	/css/main-mobile.css
	header.php
	/js/buddyboss.js
	readme.txt
	style.css

TESTED WITH:

	-- WordPress --
	WordPress 4.0+
	BuddyPress 2.1, 2.2
	bbPress 2.5+

	-- Mobile --
	iOS 6, 7
	Android 4.1+ 
	Windows Phone

	-- Browsers --
	Chrome
	Safari
	Firefox
	Internet Explorer 9+

/*--------------------------------------------------------------
1.0.9 - March 8, 2015
--------------------------------------------------------------*/

NEW FEATURES:

	Added mobile device detection, with media query fallback
	Added button to manually switch between Mobile or Desktop layout
	Added Infinite Scroll for activity
	Added Customizer color options for "No BuddyPanel" template
	Re-organized Customizer, to handle new options

BUG FIXES:

	Added Charset options for better font handling
	Support for Sensei plugin
	Improved CSS and JS for tables
	Fixed Blog template showing sidebar with no widgets added
	Fixed Notification tables
	Fixed menu header sublists
	Better font previewing in Customizer

CHANGED FILES:

	/buddyboss-inc/buddyboss-customizer/admin/buddyboss-customizer-admin.css
	/buddyboss-inc/buddyboss-customizer/admin/buddyboss-customizer-admin.js
	/buddyboss-inc/buddyboss-customizer/buddyboss-customizer-loader.php
	/buddyboss-inc/buddyboss-customizer/buddyboss-customizer.js
	/buddyboss-inc/theme-functions.php
	/css/bbpress.css (removed)
	/css/buddypress.css (removed)
	/css/plugins.css (removed)
	/css/wordpress.css (removed)
	/css/main-desktop.css (new)
	/css/main-global.css (new)
	/css/main-mobile.css (new)
	footer.php
	header.php
	/js/buddyboss.js
	/languages/en_US.mo
	/languages/en_US.po
	readme.txt
	single.php
	style.css

TESTED WITH:

	-- WordPress --
	WordPress 4.0+
	BuddyPress 2.1, 2.2
	bbPress 2.5+

	-- Mobile --
	iOS 6, 7
	Android 4.1+ 
	Windows Phone

	-- Browsers --
	Chrome
	Safari
	Firefox
	Internet Explorer 9+

/*--------------------------------------------------------------
1.0.8 - March 3, 2015
--------------------------------------------------------------*/

BUG FIXES:

	Fixed PHP error causing white screen on Bluehost

CHANGED FILES:

	/buddyboss-inc/theme-functions.php	
	readme.txt
	style.css

TESTED WITH:

	-- WordPress --
	WordPress 4.0+
	BuddyPress 2.1, 2.2
	bbPress 2.5+

	-- Mobile --
	iOS 6, 7
	Android 4.1+ 
	Windows Phone

	-- Browsers --
	Chrome
	Safari
	Firefox
	Internet Explorer 9+

/*--------------------------------------------------------------
1.0.7 - February 21, 2015
--------------------------------------------------------------*/

CHANGES:

	Changed cover photo icon to Camera, more intuitive

BUG FIXES:

	Fixed gray square when updating cover photo
	Fixed live preview of all fonts
	Improved font loading

CHANGED FILES:

	/buddyboss-inc/buddyboss-customizer/buddyboss-customizer-loader.php	
	/buddyboss-inc/buddyboss-customizer/buddyboss-customizer.js
	/buddyboss-inc/cover-photo.php
	/buddyboss-inc/theme-functions.php
	/css/buddypress.css
	/js/buddyboss.js
	readme.txt
	style.css

TESTED WITH:

	-- WordPress --
	WordPress 4.0+
	BuddyPress 2.1, 2.2
	bbPress 2.5+

	-- Mobile --
	iOS 6, 7
	Android 4.1+ 
	Windows Phone

	-- Browsers --
	Chrome
	Safari
	Firefox
	Internet Explorer 9+

/*--------------------------------------------------------------
1.0.6 - February 18, 2015
--------------------------------------------------------------*/

BUG FIXES:

	Fixed theme updating breaking Customizer settings in options.css
	Better compatibility with plugins Gravity Forms and Formidable PRO
	Correct "Page" icons for titlebar links added to mobile panel
	Fixed CSS issues on bbPress forums
	Added missing translation strings for bbPress
	Font loading fixes
	CSS, JS, and PHP cleanup

CHANGED FILES:

	/bbpress/form-topic-merge.php
	/bbpress/form-topic-split.php
	/buddyboss-inc/buddyboss-customizer/buddyboss-customizer-loader.php	
	/buddyboss-inc/buddyboss-shortcodes/admin/ (new)
	/buddyboss-inc/buddyboss-shortcodes/admin/admin-style.css
	/buddyboss-inc/buddyboss-shortcodes/shortcodes.php
	/buddyboss-inc/buddyboss-slides/buddyboss-slides-loader.php
	/buddyboss-inc/theme-functions.php
	/css/bbpress.css
	/css/buddypress.css
	/css/plugins.css
	/css/wordpress.css
	header.php
	/js/buddyboss.js
	/js/ui-scripts/ (removed)
	/languages/en_US.mo
	/languages/en_US.po
	readme.txt
	style.css

TESTED WITH:

	-- WordPress --
	WordPress 4.0+
	BuddyPress 2.1, 2.2
	bbPress 2.5+

	-- Mobile --
	iOS 6, 7
	Android 4.1+ 
	Windows Phone

	-- Browsers --
	Chrome
	Safari
	Firefox
	Internet Explorer 9+

/*--------------------------------------------------------------
1.0.5 - February 11, 2015
--------------------------------------------------------------*/

BUG FIXES:

	Load only selected fonts on front-end
	Global Search dropdown hover
	Fixes for "No BuddyPanel" full screen template
	Avatars added to child theme now take over
	Mobile search fixes
	Better CSS for plugin BP Group Documents
	Better CSS for plugin Gravity Forms

CHANGED FILES:

	/buddyboss-inc/buddyboss-customizer/buddyboss-customizer-loader.php	
	/buddyboss-inc/theme-functions.php
	/css/buddypress.css
	/css/plugins.css
	/css/wordpress.css
	header.php
	/js/buddyboss.js
	/languages/en_US.mo
	/languages/en_US.po
	readme.txt
	style.css

TESTED WITH:

	-- WordPress --
	WordPress 4.0+
	BuddyPress 2.1, 2.2
	bbPress 2.5+

	-- Mobile --
	iOS 6, 7
	Android 4.1+ 
	Windows Phone

	-- Browsers --
	Chrome
	Safari
	Firefox
	Internet Explorer 9+

/*--------------------------------------------------------------
1.0.4 - February 6, 2015
--------------------------------------------------------------*/

FEATURES:

	Option to hide BuddyPanel from logged out users
	New page template to hide BuddyPanel
	Option to display search in mobile titlebar

BUG FIXES:

	Added missing language translations
	Fixed duplicate notification counter in desktop right panel

CHANGED FILES:

	/buddyboss-inc/buddyboss-customizer/admin/buddyboss-customizer-admin.js
	/buddyboss-inc/buddyboss-customizer/buddyboss-customizer-loader.php
	/buddyboss-inc/cover-photo.php
	/buddyboss-inc/theme-functions.php
	/css/wordpress.css
	header.php
	/js/buddyboss.js
	/languages/en_US.mo
	/languages/en_US.po
	page-no-buddypanel.php (new)
	readme.txt
	style.css

TESTED WITH:

	-- WordPress --
	WordPress 4.0+
	BuddyPress 2.1, 2.2
	bbPress 2.5+

	-- Mobile --
	iOS 6, 7
	Android 4.1+ 
	Windows Phone

	-- Browsers --
	Chrome
	Safari
	Firefox
	Internet Explorer 9+

/*--------------------------------------------------------------
1.0.3 - February 4, 2015
--------------------------------------------------------------*/

FEATURES:

	Added notification counters to relevant BuddyPanel links

BUG FIXES:

	Fixed bug preventing subscriber-level users from changing group cover photo

CHANGED FILES:

	/buddyboss-inc/cover-photo.php
	/buddyboss-inc/theme-functions.php
	/css/wordpress.css
	/js/buddyboss.js
	readme.txt
	style.css

TESTED WITH:

	-- WordPress --
	WordPress 4.0+
	BuddyPress 2.1, 2.2
	bbPress 2.5+

	-- Mobile --
	iOS 6, 7
	Android 4.1+ 
	Windows Phone

	-- Browsers --
	Chrome
	Safari
	Firefox
	Internet Explorer 9+

/*--------------------------------------------------------------
1.0.2 - February 2, 2015
--------------------------------------------------------------*/

FEATURES:

	Added more font options: Ubuntu, Montserrat, Raleway, Cabin, PT+Sans

BUG FIXES:

	Fixed Group Invitations layout
	Improved profile header alignment
	Displaying Homepage Slider buttons on mobile layout

CHANGED FILES:

	/buddyboss-inc/buddyboss-customizer/buddyboss-customizer-loader.php
	/buddyboss-inc/buddyboss-customizer/buddyboss-customizer.js
	/buddyboss-inc/buddyboss-slides/css/buddyboss-slides-loader.php
	/buddyboss-inc/buddyboss-slides/css/buddyboss-slides.css
	/buddyboss-inc/theme-functions.php
	/buddypress/members/single/groups/invites.php
	/css/buddypress.css
	/css/wordpress.css
	/languages/en_US.mo
	/languages/en_US.po
	readme.txt
	style.css

TESTED WITH:

	-- WordPress --
	WordPress 4.0+
	BuddyPress 2.1, 2.2
	bbPress 2.5+

	-- Mobile --
	iOS 6, 7
	Android 4.1+ 
	Windows Phone

	-- Browsers --
	Chrome
	Safari
	Firefox
	Internet Explorer 9+

/*--------------------------------------------------------------
1.0.1 - January 31, 2015
--------------------------------------------------------------*/

FEATURES:

	Compatibility with upcoming BuddyPress 2.2
	Minor UI improvements

CHANGED FILES:

	/buddyboss-inc/buddyboss-customizer/buddyboss-customizer-loader.php
	/buddypress/members/single/groups/invites.php
	/buddypress/members/single/notifications/notifications-loop.php
	/css/buddypress.css
	/css/wordpress.css
	header.css
	/languages/en_US.mo
	/languages/en_US.po
	readme.txt
	style.css

TESTED WITH:

	-- WordPress --
	WordPress 4.0+
	BuddyPress 2.1, 2.2
	bbPress 2.5+

	-- Mobile --
	iOS 6, 7
	Android 4.1+ 
	Windows Phone

	-- Browsers --
	Chrome
	Safari
	Firefox
	Internet Explorer 9+

/*--------------------------------------------------------------
1.0.0 - January 30, 2015
--------------------------------------------------------------*/

FEATURES:

	Initial public release

TESTED WITH:

	-- WordPress --
	WordPress 4.0+
	BuddyPress 2.1+
	bbPress 2.5+

	-- Mobile --
	iOS 6, 7
	Android 4.1+ 
	Windows Phone

	-- Browsers --
	Chrome
	Safari
	Firefox
	Internet Explorer 9+


