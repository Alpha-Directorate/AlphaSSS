/*--------------------------------------------------------------
INSTALLATION
--------------------------------------------------------------*/

= From your WordPress dashboard =

-BuddyPress-

1. Visit 'Plugins > Add New'
2. Search for 'BuddyPress'
3. Activate BuddyPress from your Plugins page

-BuddyBoss Wall-

1. Go to 'Plugins > Add New'
2. Click 'Add New'
3. Upload this plugin (as a ZIP file)
4. Activate this plugin
5. Go to 'Settings > BuddyBoss Wall'
6. Have fun!

Instructions: http://www.buddyboss.com/tutorials/
Support: http://www.buddyboss.com/support-forums/
Release Notes: http://www.buddyboss.com/release-notes/


/*--------------------------------------------------------------
CHANGELOG
--------------------------------------------------------------*/
/*--------------------------------------------------------------
1.1.2 - December 24, 2014
--------------------------------------------------------------*/

FEATURES:

	Adding quick link to "Settings" in plugin list

BUG FIXES:

	Fixed double timestamp bug when posting into Groups

CHANGED FILES:

	buddyboss-wall.php
	/includes/wall-hooks.php
	/languages/buddyboss-wall-en_US.po
	/languages/buddyboss-wall-en_US.mo
	readme.txt

TESTED WITH:

	WordPress 4.0+
	BuddyPress 2.1+

/*--------------------------------------------------------------
1.1.1 - November 22, 2014
--------------------------------------------------------------*/

BUG FIXES:

	Added body class "buddyboss-wall-active" for custom styling

CHANGED FILES:

	buddyboss-wall.php
	/includes/wall-class.php
	readme.txt

TESTED WITH:

	WordPress 3.8, 3.9, 4.0
	BuddyPress 2.0, 2.1+

/*--------------------------------------------------------------
1.1.0 - November 13, 2014
--------------------------------------------------------------*/

BUG FIXES:
	
	@mention Notifications now link to Mentions tab on Activity index

CHANGED FILES:

	buddyboss-wall.php
	/includes/main-class.php
	/includes/wall-class.php
	/includes/wall-functions.php
	/includes/wall-hooks.php
	/languages/buddyboss-wall-en_US.po
	/languages/buddyboss-wall-en_US.mo
	readme.txt

TESTED WITH:

	WordPress 3.8, 3.9, 4.0
	BuddyPress 2.0, 2.1+

/*--------------------------------------------------------------
1.0.9 - October 30, 2014
--------------------------------------------------------------*/

BUG FIXES:

	Fixed Friend activity in the News Feed
	Fixed Group activity in the News Feed

CHANGED FILES:

	buddyboss-wall.php
	/includes/wall-class.php
	readme.txt

TESTED WITH:

	WordPress 3.8, 3.9, 4.0
	BuddyPress 2.0, 2.1+

/*--------------------------------------------------------------
1.0.8 - October 27, 2014
--------------------------------------------------------------*/

BUG FIXES:

	Fixed Notifications in WordPress Toolbar not clearing when clicked
	Fixed News Feed errors when BuddyPress Friends Compontent is disabled
	Fixed Wall post replies overriding the original poster
	Fixed conflict with "Bump to Top" plugin
	Improved photo upload text on Members directory

CHANGED FILES:

	buddyboss-wall.php
	/includes/wall-class.php
	/includes/wall-functions.php
	/includes/wall-hooks.php
	/includes/widgets.php
	readme.txt

TESTED WITH:

	WordPress 3.8, 3.9, 4.0
	BuddyPress 2.0, 2.1+

/*--------------------------------------------------------------
1.0.7 - October 13, 2014
--------------------------------------------------------------*/

BUG FIXES:

	Fixed replies showing 'Error getting likes'
	Fixed replies showing 'Like' link when logged out
	Fixed certain timestamps not displaying hyperlink
	Fixed Group activity text structure
	Fixed Like text getting removed when liking/unliking a parent activity
	Fixed 'Favorites' translation on Activity index 'My Likes' tab
	Fixed user mentioning another user displaying that they mentioned themself

CHANGED FILES:

	/assets/css/buddyboss-wall.css
	/assets/css/buddyboss-wall.min.css
	/assets/js/buddyboss-wall.js
	/assets/js/buddyboss-wall.min.js
	buddyboss-wall.php
	/includes/wall-class.php
	/includes/wall-filters.php
	/includes/wall-hooks.php
	/includes/wall-template.php
	/languages/buddyboss-wall-en_US.po
	/languages/buddyboss-wall-en_US.mo
	readme.txt

TESTED WITH:

	WordPress 3.8, 3.9, 4.0
	BuddyPress 2.0, 2.1+

/*--------------------------------------------------------------
1.0.6 - October 6, 2014
--------------------------------------------------------------*/

BUG FIXES:

	Fixed 'My Likes' tab disappearing on main Activity index

CHANGED FILES:

	buddyboss-wall.php
	includes/main-class.php
	readme.txt

TESTED WITH:

	WordPress 3.8, 3.9, 4.0
	BuddyPress 2.0, 2.1	

/*--------------------------------------------------------------
1.0.5 - October 4, 2014
--------------------------------------------------------------*/

BUG FIXES:

	Fixed plugin update 'version details' conflict (for future updates)
	Removed question mark from "Write something to Username?"
	Added translations for BuddyBoss Wall admin settings page
	Added empty index.php file to prevent bots from viewing contents

CHANGED FILES:

	buddyboss-wall.php (new)
	/includes/admin.php
	/includes/main-class.php
	/includes/wall-class.php
	index.php (new)
	/languages/buddyboss-wall-en_US.po
	/languages/buddyboss-wall-en_US.mo
	loader.php (removed)
	readme.txt

TESTED WITH:

	WordPress 3.8, 3.9, 4.0
	BuddyPress 2.0, 2.1

/*--------------------------------------------------------------
1.0.4 - September 24, 2014
--------------------------------------------------------------*/

BUG FIXES:

	The Wall, News Feed, and My Likes tabs are now translatable
	Now displaying 'Deleted User' text in activity post if user deletes account
	Fixed errors on Activity page in WordPress admin
	Rewrote wall input filter function, fixed issues with wall posts and user mentions

CHANGED FILES:

	/includes/wall-class.php
	/includes/wall-hooks.php
	/languages/buddyboss-wall-en_US.po
	/languages/buddyboss-wall-en_US.mo
	loader.php
	readme.txt

TESTED WITH:

	WordPress 3.8, 3.9, 4.0
	BuddyPress 2.0, 2.1

/*--------------------------------------------------------------
1.0.3 - Septembet 2, 2014
--------------------------------------------------------------*/

BUG FIXES:

	Fixed %INITIATOR% wrote on %TARGET% wall bug
	Fixed post conflict with rtMedia plugin

UPDATES:

	Updated Russian language files, credits to Ivan Dyakov

CHANGED FILES:

	/includes/wall-hooks.php
	/languages/buddyboss-wall-ru_RU.po
	/languages/buddyboss-wall-ru_RU.mo
	loader.php
	readme.txt

TESTED WITH:

	WordPress 3.8, 3.9+
	BuddyPress 2.0, 2.1 beta

/*--------------------------------------------------------------
1.0.2 - August 27, 2014
--------------------------------------------------------------*/

BUG FIXES:

	Fixed "What's New" text showing the wrong group name in post form
	Changed "Like" button default title attribute to "Like this"
	Added translation for title attribute of "Like" button
	Added translations for Wall, News Feed, My Likes tabs

CHANGED FILES:

	/includes/main-class.php
	/includes/wall-class.php
	/includes/wall-filters.php
	/languages/buddyboss-wall-en_US.po
	/languages/buddyboss-wall-en_US.mo
	loader.php
	readme.txt

TESTED WITH:

	WordPress 3.8, 3.9+
	BuddyPress 2.0, 2.1 beta

/*--------------------------------------------------------------
1.0.1 - August 22, 2014
--------------------------------------------------------------*/

FEATURES:

	You can now "Like" replies to activity posts
	Updated Swedish translations, credits to Anton Andreasson

BUG FIXES:

	Fixed blank subnav appearing on first Like
	Fixed Like button causing 'Mentions' tab to double in height and width

CHANGED FILES:

	/assets/js/buddyboss-wall.js
	/assets/js/buddyboss-wall.min.js
	/includes/wall-class.php
	/includes/wall-functions.php
	/includes/wall-hooks.php
	/includes/wall-template.php
	/languages/buddyboss-wall-en_US.po
	/languages/buddyboss-wall-en_US.mo
	/languages/buddyboss-wall-sv_SE.po
	/languages/buddyboss-wall-sv_SE.mo
	loader.php
	readme.txt

TESTED WITH:

	WordPress 3.8, 3.9+
	BuddyPress 2.0+

/*--------------------------------------------------------------
1.0.0 - August 18, 2014
--------------------------------------------------------------*/

FEATURES:

	Initial Release
	Post content to other user's profiles
	See a "News Feed" from your friends and groups
	"Like" your favorite content
	"Most Liked Activity" widget

TESTED WITH:

	WordPress 3.8, 3.9+
	BuddyPress 2.0+

