/**
 * BuddyBoss JavaScript functionality
 *
 * @since    3.0
 * @package  buddyboss
 *
 * ====================================================================
 *
 * 1. jQuery Global
 * 2. Main BuddyBoss Class
 * 3. Inline Plugins
 */



/**
 * 1. jQuery Global
 * ====================================================================
 */
var jq = $ = jQuery;



/**
 * 2. Main BuddyBoss Class
 *
 * This class takes care of BuddyPress additional functionality and
 * provides a global name space for BuddyBoss plugins to communicate
 * through.
 *
 * Event name spacing:
 * $(document).on( "buddyboss:*module*:*event*", myCallBackFunction );
 * $(document).trigger( "buddyboss:*module*:*event*", [a,b,c]/{k:v} );
 * ====================================================================
 * @return {class}
 */
var BuddyBossMain = ( function( $, window, undefined ) {

	/**
	 * Globals/Options
	 */
	var _l = {
		$document: $(document),
		$window: $(window)
	};

	// Controller
	var App = {};

	// Custom Events
	var Vent = $({});

	// Responsive
	var Responsive = {};

	// BuddyPress Defaults
	var BuddyPress = {};

	// BuddyPress Legacy
	var BP_Legacy = {};


	/** --------------------------------------------------------------- */

	/**
	 * Application
	 */

	// Initialize, runs when script is processed/loaded
	App.init = function() {

		_l.$document.ready( App.domReady );

		BP_Legacy.init();
	}

	// When the DOM is ready (page laoded)
	App.domReady = function() {
		_l.body = $('body');
		_l.$buddypress = $('#buddypress');

		Responsive.domReady();
	}

	/** --------------------------------------------------------------- */

	/**
	 * BuddyPress Responsive Help
	 */
	Responsive.domReady = function() {

		// GLOBALS *
		// ---------
		window.BuddyBoss = window.BuddyBoss || {};

		window.BuddyBoss.is_mobile = null;

		var
			$document         = $(document),
			$window           = $(window),
			$body             = $('body'),
			$mobile_check     = $('#mobile-check').css({position:'absolute',top:0,left:0,width:'100%',height:1,zIndex:1}),
			mobile_width      = 720,
			is_mobile         = false,
			has_item_nav      = false,
			mobile_modified   = false,
			swiper            = false,
			$main             = $('#main-wrap'),
			$inner            = $('#inner-wrap'),
			$buddypress       = $('#buddypress'),
			$item_nav         = $buddypress.find('#item-nav'),
			Panels            = {},
			$selects,
			$mobile_nav_wrap,
			$mobile_item_wrap,
			$mobile_item_nav;

		// Detect android stock browser
		// http://stackoverflow.com/a/17961266
		var isAndroid = navigator.userAgent.indexOf('Android') >= 0;
		var webkitVer = parseInt((/WebKit\/([0-9]+)/.exec(navigator.appVersion) || 0)[1],10) || void 0; // also match AppleWebKit
		var isNativeAndroid = isAndroid && webkitVer <= 534 && navigator.vendor.indexOf('Google') == 0;

		/*------------------------------------------------------------------------------------------------------
		1.0 - Core Functions
		--------------------------------------------------------------------------------------------------------*/

			/**
			 * Checks for supported mobile resolutions via media query and
			 * maximum window width.
			 *
			 * @return {boolean} True when screen size is mobile focused
			 */
			function check_is_mobile() {
				// The $mobile_check element refers to an empty div#mobile-check we
				// hide or show with media queries. We use this to determine if we're
				// on mobile resolution
				$mobile_check.remove().appendTo( $body );

				is_mobile = BuddyBoss.is_mobile = $mobile_check.is(':visible') || ($window.width() < mobile_width);

				if ( is_mobile ) {
					$body.addClass('is-mobile');
					mobile_width = $window.width();
				}
				else {
					$body.removeClass('buddyboss-is-mobile');
				}

				return is_mobile;
			}

			/**
			 * Checks for a BuddyPress sub-page menu. On smaller screens we turn
			 * this into a left/right swiper
			 *
			 * @return {boolean} True when BuddyPress item navigation exists
			 */
			function check_has_item_nav() {
				if ( $item_nav && $item_nav.length ) {
					has_item_nav = true;
				}

				return has_item_nav;
			}

			function render_layout() {
				var
					window_height = $window.height(), // window height - 60px (Header height) - carousel_nav_height (Carousel Navigation space)
					carousel_width = ($item_nav.find('li').length * 94);

				// If on small screens make sure the main page elements are
				// full width vertically
				if ( is_mobile && ( $inner.height() < $window.height() ) ) {
					$('#page').css( 'min-height', $window.height() - ( $('#mobile-header').height() + $('#colophon').height() ) );
				}

				// Swipe/panel shut area
				if ( is_mobile && $('#buddyboss-swipe-area').length && Panels.state ) {
					$('#buddyboss-swipe-area').css({
						left:   Panels.state === 'left' ? 240 : 'auto',
						right:  Panels.state === 'right' ? 240 : 'auto',
						width:  $(window).width() - 240,
						height: $(window).outerHeight(true) + 200
					});
				}

				// Log out link in left panel
				var $left_logout_link = $('#wp-admin-bar-logout'),
						$left_account_panel = $('#wp-admin-bar-user-actions'),
						$left_settings_menu = $('#wp-admin-bar-my-account-settings .ab-submenu').first();

				if ( $left_logout_link.length && $left_account_panel.length && $left_settings_menu.length ) {
					// On mobile user's accidentally click the link when it's up
					// top so we move it into the setting menu
					if ( is_mobile ) {
						$left_logout_link.appendTo( $left_settings_menu );
					}
					// On desktop we move it back to it's original place
					else {
						$left_logout_link.appendTo( $left_account_panel );
					}
				}

				// Runs once, first time we experience a mobile resolution
				if ( is_mobile && has_item_nav && ! mobile_modified ) {
					mobile_modified = true;
					$mobile_nav_wrap  = $('<div id="mobile-item-nav-wrap" class="mobile-item-nav-container mobile-item-nav-scroll-container">');
					$mobile_item_wrap = $('<div class="mobile-item-nav-wrapper">').appendTo( $mobile_nav_wrap );
					$mobile_item_nav  = $('<div id="mobile-item-nav" class="mobile-item-nav">').appendTo( $mobile_item_wrap );
					$mobile_item_nav.append( $item_nav.html() );

					$mobile_item_nav.css( 'width', ($item_nav.find('li').length * 94) );
					$mobile_nav_wrap.insertBefore( $item_nav ).show();
					$('#mobile-item-nav-wrap, .mobile-item-nav-scroll-container, .mobile-item-nav-container').addClass('fixed');
					$item_nav.css({display:'none'});
				}
				// Resized to non-mobile resolution
				else if ( ! is_mobile && has_item_nav && mobile_modified ) {
					$mobile_nav_wrap.css({display:'none'});
					$item_nav.css({display:'block'});
					$document.trigger('menu-close.buddyboss');
				}
				// Resized back to mobile resolution
				else if ( is_mobile && has_item_nav && mobile_modified ) {
					$mobile_nav_wrap.css({
						display:'block',
						width: carousel_width
					});

					$mobile_item_nav.css({
						width: carousel_width
					});

					$item_nav.css({display:'none'});
				}

				// Update select drop-downs
				populate_select_label();
			}

			/**
			 * Renders the layout, called when the page is loaded and on resize
			 *
			 * @return {void}
			 */
			function do_render()
			{
				check_is_mobile();
				check_has_item_nav();
				render_layout();
				mobile_carousel();
			}

		/*------------------------------------------------------------------------------------------------------
		1.1 - Startup (Binds Events + Conditionals)
		--------------------------------------------------------------------------------------------------------*/

			// Render layout
			do_render();

			// Re-render layout after everything's loaded
			$window.bind( 'load', function() {
				do_render();
			});

			// Re-render layout on resize
			var throttle;
			$window.resize( function() {
				clearTimeout( throttle );
				throttle = setTimeout( do_render, 150 );
			});



		/*------------------------------------------------------------------------------------------------------
		2.0 - Responsive Menus
		--------------------------------------------------------------------------------------------------------*/

		Panels = {
			state: 'init',
			engine: 'CSS',

			click_throttle: null,
			click_status: true,

			$swipe_area: null,
			$left: null,
			$left_icon: null,
			$right: null,
			$right_icon: null,
			$content: null,

			init: function() {
				Panels.$content    = $('#mobile-header, #main-wrap');
				Panels.$items      = $('body, #mobile-header, #main-wrap');

				Panels.$left       = $('#wpadminbar');
				Panels.$right      = $('#masthead');

				Panels.$left_icon  = $('#user-nav');
				Panels.$right_icon = $('#main-nav');

				// Panels.$swipe_area = $('<div id="buddyboss-swipe-area" />').hide().appendTo($body);
				Panels.$swipe_area = $('#buddyboss-swipe-area').hide();

				Panels.state       = 'closed';

				var ieMobile = navigator.userAgent.indexOf('IEMobile') !== -1;
				var isLegacy = ieMobile || isNativeAndroid;

				// CSS3 animations by default, but fallback to jQuery
				// when not available
				if ( isLegacy || ! Modernizr || ! Modernizr.csstransitions || ! Modernizr.csstransforms || ! Modernizr.csstransforms3d ) {
					Panels.engine = 'JS';
					$('html').addClass('buddyboss-js-transitions');
				}

				// Global events
			  $document.on( 'open-left-menu.buddyboss', { side: 'left' }, Panels.open );
			  $document.on( 'open-right-menu.buddyboss', { side: 'right' }, Panels.open );
			  $document.on( 'menu-close.buddyboss', Panels.close );

			  // Swipes
			 //  var $swipe_targets = $().add($body)
			 //  	.add(Panels.$left).add(Panels.$left.find('a'))
			 //  	.add(Panels.$right).add(Panels.$right.find('a'));

				// $swipe_targets.swipe({
				//   swipe: function(event, direction, distance, duration, fingerCount) {
				// 		if ( Panels.state === 'left' && direction === 'right'
				// 		     || Panels.state === 'right' && direction === 'left' ) {
				// 			console.log( 'SWIPE' );
				// 			$document.trigger( 'menu-close.buddyboss' );
				// 		}
				//   }
				// });

			  // Menu events
			  Panels.$swipe_area.on( 'fastclick click', { target: 'content' }, Panels.on_click );
			  Panels.$left_icon.on( 'fastclick click', { target: 'icon', side: 'left' }, Panels.on_click );
			  Panels.$right_icon.on( 'fastclick click', { target: 'icon', side: 'right' }, Panels.on_click );
			},

			/**
			 * Handle touch events on open menus, sometimes devices
			 * will handle the first 'click/touch' as a hover event
			 * if it thinks there might be a flyout or sub-menu
			 *
			 * This only affects clicking links to other pages inside
			 * our left/right panels, so we do that manually when a
			 * 'tap' event is detected on a link element in either
			 * panel
			 *
			 * @param  {object} e jQuery event object
			 * @return {void}
			 */
			on_menu_click: function( e ) {
				// console.log( 'tap' );
				// console.log( e );

				var href = !! this.getAttribute('href')
						     ? this.getAttribute('href')
						     : false;

				if ( href ) {
					$document.trigger( 'menu-close.buddyboss' );
					window.location = href;
					return false;
				}
			},

			on_click: function( e ) {
				// console.log( 'on_click() e.type', e.type );
				// console.log( e );

				clearTimeout(Panels.click_throttle);
				click_throttle = setTimeout(function(){
					Panels.click_status = true;
				}, 150 );

				var status = true;

				// If this event wasn't initiated by us bail
				if ( e.isTrigger && e.type !== 'fastclick' ) {
					status = false;
				}

				if ( ! Panels.click_status ) {
					status = false;
				}

				if ( status ) {
					e.stopImmediatePropagation();
					e.stopPropagation();
					e.preventDefault();

					// If it's closed, open a panel
					if ( Panels.state === 'closed' && e.data && e.data.target === 'icon' ) {
						$document.trigger( 'open-'+e.data.side+'-menu.buddyboss' );
					}
					// Otherwise close the panels
					else {
						$document.trigger( 'menu-close.buddyboss' );
						return false;
					}

					Panels.click_status = false;
				}
			},

			open: function( e ) {
				var side = Panels.state = e.data.side;

				var opt  = {
					css: {
						zIndex: 999,
						opacity: 1,
						display: 'block',
						height: '100%'
					},
					ani: {}
				};

				opt.css[side] = -240;
				opt.ani[side] = 0;

				var $menu     = Panels[ '$' + side ];

				// Use CSS Transitions where possible
				if ( Panels.engine === 'CSS' ) {
					$body.addClass( 'open-' + side ).removeClass( 'close-left close-right' );
				}
				// jQuery/JS fallback
				else {
					$body.addClass( 'open-' + side ).removeClass( 'close-left close-right' );
					$menu.css( opt.css ).animate( opt.ani );
				}

				setTimeout( function() {
					Panels.$content.on( 'fastclick click', { target: 'content' }, Panels.on_click );
				  $menu.on( 'fastclick click', 'a', { target: 'menu' }, Panels.on_menu_click );
				}, 200 );

				Panels.$swipe_area.css({
					left:   side === 'left' ? 240 : 'auto',
					right:  side === 'right' ? 240 : 'auto',
					width:  $(window).width() - 240,
					height: $(window).outerHeight(true) + 200
				}).show();
			},

			close: function() {
				var side  = Panels.state;
				var $menu = Panels[ '$' + side ];
				var opt   = {};
				opt[side] = -240;

				if ( ! side || ! $menu || ! $menu.length ) {
					return;
				}

				// Use CSS Transitions where possible
				if ( Panels.engine === 'CSS' ) {
					$body.addClass( 'close-' + side );
					setTimeout( function(){
						$body.removeClass( 'open-left open-right' );
					},400);
				}
				// jQuery/JS fallback
				else {
					$body.removeClass( 'open-left open-right' ).addClass( 'close-' + side );
					$menu.animate( opt );
				}

			  $menu.off( 'fastclick click' );
			  Panels.$content.off( 'fastclick click' );

				Panels.$swipe_area.hide();

				Panels.state = 'closed';
			}
		} // Panels

		Panels.init();

		/*------------------------------------------------------------------------------------------------------
		2.1 - Mobile/Tablet Carousels
		--------------------------------------------------------------------------------------------------------*/

			function mobile_carousel() {
				if ( is_mobile && has_item_nav && ! swiper ) {
					// console.log( 'Setting up mobile nav swiper' );
					swiper = $('.mobile-item-nav-scroll-container').swiper({
						scrollContainer : true,
						slideElement : 'div',
						slideClass : 'mobile-item-nav',
						wrapperClass : 'mobile-item-nav-wrapper'
					});
				}
			}

		/*------------------------------------------------------------------------------------------------------
		2.2 - Responsive Dropdowns
		--------------------------------------------------------------------------------------------------------*/

			// On page load we'll go through each select element and make sure
			// we have a label element to accompany it. If not, we'll generate
			// one and add it dynamically.
			function init_select() {
				var current = 0;

				$selects = $('#page select:not([multiple])');

				$selects.each( function() {
					var $select = $(this),
							$wrap, id, $label, dynamic = false;

					if ( this.style.display === 'none' ) {
						return;
					}

					$wrap   = $('<div class="buddyboss-select"></div>');
					id      = this.getAttribute('id') || 'buddyboss-select-' + current;
					$label  = $select.prev('label');

					$select.wrap( $wrap );

					// If there's no label, let's append one
					if ( ! $label.length ) {
						$label  = $('<label></label>').hide();
						dynamic = true;
					}

					$label.insertBefore( $select );

					// Set data on select element to use later
					$select.data( 'buddyboss-select-info', {
						state:     'init',
						dynamic:   dynamic,
						$wrap:     $wrap,
						$label:    $label,
						orig_text: $label.text()
					} );

					// On select change, repopulate label
					$select.on( 'change', function( e ) {
						populate_select_label();
					});
				});

			}

			init_select();

			// On mobile, we add a better select box. This function
			// populates data from the <select> element to it's
			// <label> element which is positioned over the select box.
			function populate_select_label() {

				// Abort when no select elements are found
				if ( ! $selects || ! $selects.length ) {
					return;
				}

				// Handle small screens
				if ( is_mobile ) {

					$selects.each( function( idx, val ) {
						var $select = $(this),
								data    = $select.data( 'buddyboss-select-info' ),
								$label;

						if ( ! data || ! data.$label ) {
							return;
						}

						$label = data.$label;

						if ( $label && $label.length ) {

							data.state = 'mobile';

							$label.text( $select.find('option:selected').text() ).show();
						}
					});

				}

				// Handle larger screens
				else {

					$selects.each( function( idx, val ) {
						var $select   = $(this),
								data      = $select.data( 'buddyboss-select-info' ),
								$label, orig_text;

						if ( ! data || ! data.$label || data.orig_text === false ) {
							return;
						}

						$label    = data.$label || false;
						orig_text = data.orig_text ||  BuddyBossOptions.select_label;

						if ( data.state !== 'desktop' && $label && $label.length ) {

							data.state = 'desktop';

							// If it's a dynamic select/label, we should hide the added
							// label that wasn't there before because we're only using
							// it on smaller screens
							if ( data.dynamic ) {
								$label.hide();
							}

							// Otherwise, let's set the original label's text
							else {
								$label.text( orig_text );
							}
						}
					});

				} // end is_mobile

			} // end populate_select_label();

		/*------------------------------------------------------------------------------------------------------
		2.3 - Notifications Area
		--------------------------------------------------------------------------------------------------------*/

		// Add Notifications Area, if there are notifications to show

		if ( is_mobile && $window.width() < 720 ) {

			if ($('#wp-admin-bar-bp-notifications').length != 0){

				// Clone and Move the Notifications Count to the Header
				$('li#wp-admin-bar-bp-notifications a.ab-item > span#ab-pending-notifications').clone().appendTo('#user-nav');

			}
		}

		/*------------------------------------------------------------------------------------------------------
		3.0 - Content
		--------------------------------------------------------------------------------------------------------*/
		/*------------------------------------------------------------------------------------------------------
		3.1 - Members (Group Admin)
		--------------------------------------------------------------------------------------------------------*/

		// Hide/Reveal action buttons
		$('a.show-options').click(function(event){
			event.preventDefault;

			parent_li = $(this).parent('li');
			if ($(parent_li).children('ul#members-list span.small').hasClass('inactive')){
				$(this).removeClass('inactive').addClass('active');
				$(parent_li).children('ul#members-list span.small').removeClass('inactive').addClass('active');
			}
			else{
				$(this).removeClass('active').addClass('inactive');
				$(parent_li).children('ul#members-list span.small').removeClass('active').addClass('inactive');
			}

		});


		/*------------------------------------------------------------------------------------------------------
		3.2 - Search Input Field
		--------------------------------------------------------------------------------------------------------*/
		$('#buddypress div.dir-search form, #buddypress div.message-search form, div.bbp-search-form form, form#bbp-search-form').append('<a href="#" id="clear-input"> </a>');
		$('a#clear-input').click(function(){
			jQuery("#buddypress div.dir-search form input[type=text], #buddypress div.message-search form input[type=text], div.bbp-search-form form input[type=text], form#bbp-search-form input[type=text]").val("");
		});


		/*------------------------------------------------------------------------------------------------------
		3.3 - Hide Profile and Group Buttons Area, when there are no buttons (ex: Add Friend, Join Group etc...)
		--------------------------------------------------------------------------------------------------------*/

		if ( ! $('#buddypress #item-header #item-buttons .generic-button').length ) {
		  $('#buddypress #item-header #item-buttons').hide();
		}

		/*------------------------------------------------------------------------------------------------------
		3.4 - Move the Messages Checkbox, below the Avatar
		--------------------------------------------------------------------------------------------------------*/

		$('#message-threads.messages-notices .thread-options .checkbox').each( function() {
			move_to_spot = $(this).parent().siblings('.thread-avatar');
			$(this).appendTo(move_to_spot);
		});

		/*------------------------------------------------------------------------------------------------------
		3.5 - Select unread and read messages in inbox
		--------------------------------------------------------------------------------------------------------*/

		// Overwrite/Re-do some of the functionality in buddypress.js,
		// to accommodate for UL instead of tables in buddyboss theme
		jq("#message-type-select").change(
			function() {
				var selection = jq("#message-type-select").val();
				var checkboxes = jq("ul input[type='checkbox']");
				checkboxes.each( function(i) {
					checkboxes[i].checked = "";
				});

				switch(selection) {
					case 'unread':
						var checkboxes = jq("ul.unread input[type='checkbox']");
						break;
					case 'read':
						var checkboxes = jq("ul.read input[type='checkbox']");
						break;
				}
				if ( selection != '' ) {
					checkboxes.each( function(i) {
						checkboxes[i].checked = "checked";
					});
				} else {
					checkboxes.each( function(i) {
						checkboxes[i].checked = "";
					});
				}
			}
		);

		/* Bulk delete messages */
		jq("#delete_inbox_messages, #delete_sentbox_messages").on( 'click', function() {
			checkboxes_tosend = '';
			checkboxes = jq("#message-threads ul input[type='checkbox']");

			jq('#message').remove();
			jq(this).addClass('loading');

			jq(checkboxes).each( function(i) {
				if( jq(this).is(':checked') )
					checkboxes_tosend += jq(this).attr('value') + ',';
			});

			if ( '' == checkboxes_tosend ) {
				jq(this).removeClass('loading');
				return false;
			}

			jq.post( ajaxurl, {
				action: 'messages_delete',
				'thread_ids': checkboxes_tosend
			}, function(response) {
				if ( response[0] + response[1] == "-1" ) {
					jq('#message-threads').prepend( response.substr( 2, response.length ) );
				} else {
					jq('#message-threads').before( '<div id="message" class="updated"><p>' + response + '</p></div>' );

					jq(checkboxes).each( function(i) {
						if( jq(this).is(':checked') )
							jq(this).parent().parent().fadeOut(150);
					});
				}

				jq('#message').hide().slideDown(150);
				jq("#delete_inbox_messages, #delete_sentbox_messages").removeClass('loading');
			});
			return false;
		});

		/*------------------------------------------------------------------------------------------------------
		3.6 - Make Video Embeds Responsive - Fitvids.js
		--------------------------------------------------------------------------------------------------------*/

		$('.wp-video').find('object').addClass('fitvidsignore');
		$('#content').fitVids();

		// This ensures that after and Ajax call we check again for
		// videos to resize.
		var fitVidsAjaxSuccess = function() {
			$('.wp-video').find('object').addClass('fitvidsignore');
			$('#content').fitVids();
		}
		$(document).ajaxSuccess( fitVidsAjaxSuccess );

	}


	/** --------------------------------------------------------------- */

	/**
	 * BuddyPress Legacy Support
	 */

	// Initialize
	BP_Legacy.init = function() {
		BP_Legacy.injected = false;
		_l.$document.ready( BP_Legacy.domReady );
	}

	// On dom ready we'll check if we need legacy BP support
	BP_Legacy.domReady = function() {
		BP_Legacy.check();
	}

	// Check for legacy support
	BP_Legacy.check = function() {
		if ( ! BP_Legacy.injected && _l.body.hasClass('buddypress') && _l.$buddypress.length == 0 ) {
			BP_Legacy.inject();
		}
	}

	// Inject the right code depending on what kind of legacy support
	// we deduce we need
	BP_Legacy.inject = function() {
		BP_Legacy.injected = true;

		var $secondary  = $('#secondary'),
				do_legacy = false;

		var $content  = $('#content'),
				$padder   = $content.find('.padder').first(),
				do_legacy = false;

		var $article = $content.children('article').first();

		var $legacy_page_title,
				$legacy_item_header;

		// Check if we're using the #secondary widget area and add .bp-legacy inside that
		if ( $secondary.length ) {
			$secondary.prop( 'id', 'secondary' ).addClass('bp-legacy');

			do_legacy = true;
		}

		// Check if the plugin is using the #content wrapper and add #buddypress inside that
		if ( $padder.length ) {
			$padder.prop( 'id', 'buddypress' ).addClass('bp-legacy entry-content');

			do_legacy = true;

			// console.log( 'Buddypress.js #buddypress fix: Adding #buddypress to .padder' );
		}
		else if ( $content.length ) {
			$content.wrapInner( '<div class="bp-legacy entry-content" id="buddypress"/>' );

			do_legacy = true;

			// console.log( 'Buddypress.js #buddypress fix: Dynamically wrapping with #buddypresss' );
		}

		// Apply legacy styles if needed
		if ( do_legacy ) {

			_l.$buddypress = $('#buddypress');

			$legacy_page_title = $('.buddyboss-bp-legacy.page-title');
			$legacy_item_header = $('.buddyboss-bp-legacy.item-header');

			// Article Element
			if ( $article.length === 0 ) {
				$content.wrapInner('<article/>');
				$article = $( $content.find('article').first() );
			}

			// Page Title
			if ( $content.find('.entry-header').length === 0 || $content.find('.entry-title').length === 0 ) {
				$legacy_page_title.prependTo( $article ).show();
				$legacy_page_title.children().unwrap();
			}

			// Item Header
			if ( $content.find('#item-header-avatar').length === 0 && _l.$buddypress.find('#item-header').length ) {
				$legacy_item_header.prependTo( _l.$buddypress.find('#item-header') ).show();
				$legacy_item_header.children().unwrap();
			}
		}
	}

	// Boot er' up
	jQuery(document).ready(function(){
	    App.init();
	});

}( jQuery, window ) );




/**
 * 3. Inline Plugins
 * ====================================================================
 * Inline Plugins
 */



/*------------------------------------------------------------------------------------------------------
Inline Plugins
--------------------------------------------------------------------------------------------------------*/

/*
 * jQuery Mobile Plugin: jQuery.Event.Special.Fastclick
 * http://nischenspringer.de/jquery/fastclick
 *
 * Copyright 2013 Tobias Plaputta
 * Released under the MIT license.
 * http://nischenspringer.de/license
 *
 */
;(function(e){var t=e([]),n=800,r=30,i=10,s=[],o={};var u=function(e){var t,n;for(t=0,n=s.length;t<n;t++){if(Math.abs(e.pageX-s[t].x)<r&&Math.abs(e.pageY-s[t].y)<r){e.stopImmediatePropagation();e.stopPropagation();e.preventDefault()}}};var a=true;if(Modernizr&&Modernizr.hasOwnProperty("touch")){a=Modernizr.touch}var f=function(){s.splice(0,1)};e.event.special.fastclick={touchstart:function(t){o.startX=t.originalEvent.touches[0].pageX;o.startY=t.originalEvent.touches[0].pageY;o.hasMoved=false;e(this).on("touchmove",e.event.special.fastclick.touchmove)},touchmove:function(t){if(Math.abs(t.originalEvent.touches[0].pageX-o.startX)>i||Math.abs(t.originalEvent.touches[0].pageX-o.startY)>i){o.hasMoved=true;e(this).off("touchmove",e.event.special.fastclick.touchmove)}},add:function(t){if(!a){return}var r=e(this);r.data("objHandlers")[t.guid]=t;var i=t.handler;t.handler=function(t){r.off("touchmove",e.event.special.fastclick.touchmove);if(!o.hasMoved){s.push({x:o.startX,y:o.startY});window.setTimeout(f,n);var u=this;var a=e([]);var l=arguments;e.each(r.data("objHandlers"),function(){if(!this.selector){if(r[0]==t.target||r.has(t.target).length>0)i.apply(r,l)}else{e(this.selector,r).each(function(){if(this==t.target||e(this).has(t.target).length>0)i.apply(this,l)})}})}}},setup:function(n,r,i){var s=e(this);if(!a){s.on("click",e.event.special.fastclick.handler);return}t=t.add(s);if(!s.data("objHandlers")){s.data("objHandlers",{});s.on("touchstart",e.event.special.fastclick.touchstart);s.on("touchend touchcancel",e.event.special.fastclick.handler)}if(!o.ghostbuster){e(document).on("click vclick",u);o.ghostbuster=true}},teardown:function(n){var r=e(this);if(!a){r.off("click",e.event.special.fastclick.handler);return}t=t.not(r);r.off("touchstart",e.event.special.fastclick.touchstart);r.off("touchmove",e.event.special.fastclick.touchmove);r.off("touchend touchcancel",e.event.special.fastclick.handler);if(t.length==0){e(document).off("click vclick",u);o.ghostbuster=false}},remove:function(t){if(!a){return}var n=e(this);delete n.data("objHandlers")[t.guid]},handler:function(t){var n=t.type;t.type="fastclick";e.event.trigger.call(this,t,{},this,true);t.type=n}}})(jQuery)


/*
* FitVids 1.1
*
* Copyright 2013, Chris Coyier - http://css-tricks.com + Dave Rupert - http://daverupert.com
* Credit to Thierry Koblentz - http://www.alistapart.com/articles/creating-intrinsic-ratios-for-video/
* Released under the WTFPL license - http://sam.zoy.org/wtfpl/
*
*/
;(function(a){a.fn.fitVids=function(b){var e={customSelector:null,ignore:null};if(!document.getElementById("fit-vids-style")){var d=document.head||document.getElementsByTagName("head")[0];var c=".fluid-width-video-wrapper{width:100%;position:relative;padding:0;}.fluid-width-video-wrapper iframe,.fluid-width-video-wrapper object,.fluid-width-video-wrapper embed {position:absolute;top:0;left:0;width:100%;height:100%;}";var f=document.createElement("div");f.innerHTML='<p>x</p><style id="fit-vids-style">'+c+"</style>";d.appendChild(f.childNodes[1])}if(b){a.extend(e,b)}return this.each(function(){var g=['iframe[src*="player.vimeo.com"]','iframe[src*="youtube.com"]','iframe[src*="youtube-nocookie.com"]','iframe[src*="kickstarter.com"][src*="video.html"]',"object","embed"];if(e.customSelector){g.push(e.customSelector)}var h=".fitvidsignore";if(e.ignore){h=h+", "+e.ignore}var i=a(this).find(g.join(","));i=i.not("object object");i=i.not(h);i.each(function(){var n=a(this);if(n.parents(h).length>0){return}if(this.tagName.toLowerCase()==="embed"&&n.parent("object").length||n.parent(".fluid-width-video-wrapper").length){return}if((!n.css("height")&&!n.css("width"))&&(isNaN(n.attr("height"))||isNaN(n.attr("width")))){n.attr("height",9);n.attr("width",16)}var j=(this.tagName.toLowerCase()==="object"||(n.attr("height")&&!isNaN(parseInt(n.attr("height"),10))))?parseInt(n.attr("height"),10):n.height(),k=!isNaN(parseInt(n.attr("width"),10))?parseInt(n.attr("width"),10):n.width(),l=j/k;if(!n.attr("id")){var m="fitvid"+Math.floor(Math.random()*999999);n.attr("id",m)}n.wrap('<div class="fluid-width-video-wrapper"></div>').parent(".fluid-width-video-wrapper").css("padding-top",(l*100)+"%");n.removeAttr("height").removeAttr("width")})})}})(window.jQuery||window.Zepto);