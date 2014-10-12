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
5. Go to 'Settings > BuddyBoss Media'
6. Have fun!

Instructions: http://www.buddyboss.com/tutorials/
Support: http://www.buddyboss.com/support-forums/
Release Notes: http://www.buddyboss.com/release-notes/


/*--------------------------------------------------------------
CHANGELOG
--------------------------------------------------------------*/
/*--------------------------------------------------------------
1.0.5 - October 4, 2014
--------------------------------------------------------------*/

BUG FIXES:

	Fixed plugin update 'version details' conflict (for future updates)
	Added translations for BuddyBoss Media admin settings page
	Added empty index.php file to prevent bots from viewing contents

CHANGED FILES:
	
	buddyboss-media.php (new)
	/includes/admin.php
	index.php (new)
	/languages/buddyboss-media-en_US.po
	/languages/buddyboss-media-en_US.mo
	loader.php (removed)
	readme.txt

TESTED WITH:

	WordPress 3.8, 3.9, 4.0
	BuddyPress 2.0, 2.1

/*--------------------------------------------------------------
1.0.4 - September 24, 2014
--------------------------------------------------------------*/

BUG FIXES:

	Improved theme compatibility (using plugins.php template)
	Theme widgets now display on user Photos page
	CSS fix, prevents 'Add Photo' button from highlighting during photo upload

CHANGED FILES:

	/assets/css/buddyboss-media.css
	/assets/css/buddyboss-media.min.css
	/includes/types/photo-class.php
	/includes/types/photo-screens.php
	loader.php
	readme.txt
	/templates/members/single/buddyboss-media-photos.php

TESTED WITH:

	WordPress 3.8, 3.9, 4.0
	BuddyPress 2.0, 2.1

/*--------------------------------------------------------------
1.0.3 - September 12, 2014
--------------------------------------------------------------*/

BUG FIXES:

	Fixed image upload disappearing after 10-15 seconds, when BP "heartbeat" initiates

CHANGED FILES:

 	/assets/js/buddyboss-media.js
 	/assets/js/buddyboss-media.min.js
 	/includes/main-class.php
 	/includes/types/photo-class.php
 	loader.php
 	readme.txt

TESTED WITH:

	WordPress 3.8, 3.9, 4.0
	BuddyPress 2.0, 2.1 beta

/*--------------------------------------------------------------
1.0.2 - September 5, 2014
--------------------------------------------------------------*/

FEATURES:

	Updated FontAwesome to version 4.2
	Updated Russian language files, credits to Ivan Dyakov

BUG FIXES:

	Fixed 'pic_satus' to 'pic_status'

CHANGED FILES:

	/assets/js/buddyboss-media.js
	/assets/js/buddyboss-media.min.js
	/includes/types/photo-class.php
	/languages/buddyboss-media-ru_RU.mo
	/languages/buddyboss-media-ru_RU.po
	loader.php
	readme.txt	

TESTED WITH:

	WordPress 3.8, 3.9, 4.0
	BuddyPress 2.0, 2.1 beta

/*--------------------------------------------------------------
1.0.1 - August 27, 2014
--------------------------------------------------------------*/

FEATURES:

	New admin option to configure custom user photos template slug.
	New admin option to create a page for displaying all photo uploads from all users.

BUG FIXES:

	Fixed Font Awesome loading over HTTPS for ports other than 443
	Updated Photo grid CSS, for better compatibility with other themes

CHANGED FILES:

	/assets/css/buddyboss-media.css
	/assets/css/buddyboss-media.min.css
	/includes/admin.php
	/includes/main-class.php
	/includes/media-functions.php
	/includes/media-pagination.php
	/includes/media-template.php (added)
	/includes/types/photo-class.php
	/includes/types/photo-hooks.php
	/includes/types/photo-screens.php
	/languages/buddyboss-media-en_US.po
	/languages/buddyboss-media-en_US.mo
	loader.php
	readme.txt
	/templates/members/single/buddyboss-media-photos.php
	/templates/global-media.php (added)
	/vendor/image-rotation-fixer.php (removed)

TESTED WITH:

	WordPress 3.8, 3.9+
	BuddyPress 2.0, 2.1 beta

/*--------------------------------------------------------------
1.0.0 - August 14, 2014
--------------------------------------------------------------*/

FEATURES:

	Initial Release
	Post photos to activity streams
	View photos in a mobile-friendly slider

TESTED WITH:

	WordPress 3.8, 3.9+
	BuddyPress 2.0+
