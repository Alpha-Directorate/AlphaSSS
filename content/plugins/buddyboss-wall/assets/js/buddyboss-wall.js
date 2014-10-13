/**
 * BuddyBoss Wall
 *
 * A BuddyPress plugin for activity feeds
 *
 * This file should load in the footer
 *
 * @author      BuddyBoss
 * @since       BuddyBoss Wall (1.0.0)
 * @package     BuddyBoss Wall
 *
 * ====================================================================
 *
 * 1. jQuery + Globals
 * 2. Main Wall Functionality
 * 2. Wall Tooltips
 */


/**
 * 1. jQuery + Globals
 * ====================================================================
 */
var jq = $ = jQuery;

// Window.Code fallback
window.Code = window.Code || {};

// Util
window.Code.BuddyBoss_Wall_Util = ( function( window, $, opt, undefined ) {

  var Util = {
    state: opt,
    lang: function( key ) {
      var key = key || 'undefined key!';
      return opt[key] || 'Language key missing for: ' + key;
    }
  }

  return Util;

}
(
  window,
  window.jQuery,
  window.BuddyBoss_Wall_Appstate || false
));

/**
 * 2. Main Wall Functionality
 * ====================================================================
 * @returns {object} BuddyBoss_Wall
 */

window.Code.BuddyBoss_Wall = ( function( window, $, util, undefined ) {

  var _l = {};

  var state = util.state || {},
      lang  = util.lang;

  var Wall = {
    on: null,
    setup_form: function() {
      var $form = $('#buddyboss-wall-tpl-form'),
          $activity = $('body.just-me.has-activity #item-body > .activity').first(),
          $whats_new = $('#whats-new-form');


      if ( ! $whats_new.length && $form.length && $activity.length ) {
        // $activity.before( $form.html() );
        // $(document).trigger('buddyboss:post:form:injected');
        // $(document).trigger('ready');
      }
      // console.log( 'form' );
      // console.log( $form );
    },
    setup_comments: function() {
      var $comments = $('.buddyboss-wall-tpl-activity-comments');

      $comments.each(function(){
        var $comment = $(this),
            $wrap    = $comment.parent().find('.activity-comments');

        if ( $wrap.length && ! $comment.data( 'buddyboss-wall-appended' ) ) {
          $comment.data( 'buddyboss-wall-appended', true )
          $wrap.prepend( $comment.html() );
        }
      });
    },
    setup: function() {
      Wall.on = true;

      $.ajaxPrefilter( Wall.prefilter );
      $(document).ajaxSuccess( function( response ) {
        Wall.setup_comments();
        setTimeout( Wall.setup_comments, 205 );
        setTimeout( Wall.setup_comments, 500 );
      } );

      Wall.setup_form();

      Wall.setup_comments();

      // Activity greeting
      var $greeting_tpl = $('#buddyboss-wall-tpl-greeting').html(),
          $greeting     = $('#whats-new-form .activity-greeting');
	  
      if ( $greeting.length && !! $greeting_tpl ) {
        $greeting.html( $greeting_tpl ).show();
      }

      // Fav/My likes tab
      var $fav_tab = $('#activity-favorites'),
          tab_text = state.fav_tab_name || false,
          $tab_span;

      if ( $fav_tab.length && tab_text ) {
        $tab_span = $fav_tab.find('span');

        $fav_tab.find('a').html( tab_text ).append( $tab_span ).addClass( 'localized' );
      }

    },
    teardown: function() {
      Wall.on = false;
    },
    prefilter: function( options, origOptions, jqXHR ) {
	item_type = origOptions.data && origOptions.data.item_type || '';
	
      var act_id         = parseInt( origOptions.data && origOptions.data.id || 0 ),
          action         = origOptions.data && origOptions.data.action || '',
          is_like_action = ( action === 'activity_mark_fav' || action === 'activity_mark_unfav' ),
	  is_a_comment	 = ( item_type === 'comment' ),
          target;

      /**
      console.dir({
        options: options,
        origOptions: origOptions,
        act_id: act_id,
        action: action,
        is_like_action: is_like_action
      });
      /**/

      if ( is_like_action && act_id > 0 ) {

        target = $( '#activity-' + act_id ).find( '.button.loading' );
        type   = target.hasClass('fav') ? 'fav' : 'unfav';
	
	if( is_a_comment ){
	    target = $( '#acomment-' + act_id ).find( '.acomment-like.loading' );
	    type   = target.hasClass('fav-comment') ? 'fav' : 'unfav';
	}

        options.success = ( function( old_success ) {
          return function( response, txt, xhr ) {
            // Let the default buddypress.js return handler
            // take care of errors
            if ( response[0] + response[1] === '-1' && $.isFunction( old_success ) ) {
              old_success( response );
            }
            else {
              Wall.success( target, type, is_a_comment, response, txt, xhr );
            }
          }
        })(options.success);

      }

    },
    success: function( target, type, is_a_comment, response, text, xhr ) {
	
      /* BuddyBoss: Modified to get number of likes */
      var has_like_text = false,
          but_text = '',
          num_likes_text = '',
          bp_default_like_count = 0,
          remove_comment_ul = false,
          responseJSON = response.indexOf('{') != -1
                       ? jQuery.parseJSON( response )
                       : false;

      // console.log( response );

      // We have a response and button text
      if ( responseJSON && responseJSON.but_text ) {
        but_text = responseJSON.but_text;
      }
      else {
        but_text = response;
      }

      // We have a response and like count (int)
      if ( responseJSON && responseJSON.hasOwnProperty( 'like_count' ) ) {

        // If the count is above 0
        if ( responseJSON.like_count ) {
          has_like_text = true;
          num_likes_text = responseJSON.num_likes;
        }

        // If the count is 0 we need to remove the activity wrap
        else {
          remove_comment_ul = true;
        }
      }

      // console.log(  has_like_text  );

      target.fadeOut( 200, function() {
        var button             = jq(this),
            item               = button.parents('.activity-item'),
            comments_wrap      = item.find('.activity-comments'),
            comments_ul        = comments_wrap.find('ul').first(),
            existing_like_text = comments_wrap.find('.activity-like-count'),
            existing_comments  = comments_wrap.find('li').not('.activity-like-count'),
            new_like_text      = num_likes_text;
	    
	if( is_a_comment ){
	    comments_wrap	= button.parents('.acomment-options');
	    comments_ul		= comments_wrap.find('ul').first();
            existing_like_text	= comments_wrap.find('.activity-like-count');
            existing_comments	= comments_wrap.find('li').not('.activity-like-count');
	    new_like_text	= num_likes_text;
	}

        /**
        console.dir({
          item: item,
          comments_wrap: comments_wrap,
          comments_ul: comments_ul
        });
        /**/

        // Take care of replacing the button with like/unlike
        button.html(but_text);
        button.attr('title', 'fav' == type ? BP_DTheme.remove_fav : BP_DTheme.mark_as_fav);
        button.fadeIn(200);

        // Remove existing like text, might be replaced if this isn't an unlike
        // or there are existing likes left
        existing_like_text.remove();

        // If we have 'you like this' type text
        if ( has_like_text ) {

          // console.log( num_likes_text );
          // console.log( new_like_text );
          // console.log( bp_default_like_count );

          // If we have an existing UL prepend the LI
          if ( comments_ul.length ) {
            comments_ul.prepend( new_like_text );
            // console.log( 'UL found' );
          }

          // Otherwise lets wrap it up again and add to the comments
          else {
	      if( is_a_comment ){
		  comments_wrap.append( '<ul class="acomment-reply-like-content">' + new_like_text + '</ul>' );
	      }
	      else{
		  comments_wrap.prepend( '<ul>' + new_like_text + '</ul>' );
	      }
            // console.log( 'UL not found' );
          }

        }

        // If we need to clean up the comment UL, this happens when
        // someone unlikes a post and there are no comments so an empty
        // UL element stays around causing some spacing and design flaws,
        // we remove that below
        if ( remove_comment_ul && comments_ul.length && ! existing_comments.length ) {
          comments_ul.remove();
        }

      });

      if ( 'fav' == type ) {
        bp_default_like_count = Number( jq('.item-list-tabs ul #activity-favorites span').html() ) + 1;

        if ( !jq('.item-list-tabs #activity-favorites').length )
          jq('.item-list-tabs ul li#activity-mentions').before( '<li id="activity-favorites"><a href="#" class="localized">' + BP_DTheme.my_favs + ' <span>0</span></a></li>');

	  if( is_a_comment ){
	      target.removeClass('fav-comment');
	      target.addClass('unfav-comment');
	  }
	  else{
	      target.removeClass('fav');
	      target.addClass('unfav');
	  }
        jq('.item-list-tabs ul #activity-favorites span').html( Number( jq('.item-list-tabs ul #activity-favorites span').html() ) + 1 );

      }
      else {

        bp_default_like_count = Number( jq('.item-list-tabs ul #activity-favorites span').html() ) - 1;
	if( is_a_comment ){
	    target.removeClass('unfav-comment');
	    target.addClass('fav-comment');
	}
	else{
	    target.removeClass('unfav');
	    target.addClass('fav');
	}

        jq('.item-list-tabs ul #activity-favorites span').html( bp_default_like_count );

        if ( bp_default_like_count == 0 ) {
          if ( jq('.item-list-tabs ul #activity-favorites').hasClass('selected') )
            bp_activity_request( null, null );

          jq('.item-list-tabs ul #activity-favorites').remove();
        }
      }

      // BuddyBoss: usually there's parent().parent().parent(), but our markup is slightly different.
      if ( 'activity-favorites' == jq( '.item-list-tabs li.selected').attr('id') )
        //target.parent().parent().slideUp(100);
	target.closest('.activity-item').slideUp(100);

      target.removeClass('loading');
      // document.write = document.oldDocumentWrite;


    //   function(response) {
    //     target.removeClass('loading');

    //     target.fadeOut( 200, function() {
    //       jq(this).html(response);
    //       jq(this).attr('title', 'fav' == type ? BP_DTheme.remove_fav : BP_DTheme.mark_as_fav);
    //       jq(this).fadeIn(200);
    //     });

    //     if ( 'fav' == type ) {
    //       if ( !jq('.item-list-tabs #activity-favs-personal-li').length ) {
    //         if ( !jq('.item-list-tabs #activity-favorites').length )
    //           jq('.item-list-tabs ul #activity-mentions').before( '<li id="activity-favorites"><a href="#">' + BP_DTheme.my_favs + ' <span>0</span></a></li>');

    //         jq('.item-list-tabs ul #activity-favorites span').html( Number( jq('.item-list-tabs ul #activity-favorites span').html() ) + 1 );
    //       }

    //       target.removeClass('fav');
    //       target.addClass('unfav');

    //     } else {
    //       target.removeClass('unfav');
    //       target.addClass('fav');

    //       jq('.item-list-tabs ul #activity-favorites span').html( Number( jq('.item-list-tabs ul #activity-favorites span').html() ) - 1 );

    //       if ( !Number( jq('.item-list-tabs ul #activity-favorites span').html() ) ) {
    //         if ( jq('.item-list-tabs ul #activity-favorites').hasClass('selected') )
    //           bp_activity_request( null, null );

    //         jq('.item-list-tabs ul #activity-favorites').remove();
    //       }
    //     }

    //     if ( 'activity-favorites' == jq( '.item-list-tabs li.selected').attr('id') )
    //       target.parent().parent().parent().slideUp(100);
    //   });
    // }

    } // success()

  } // Wall {}

  $(document).ready( function() {
    Wall.setup();
  } );

  var API = {
    setup: function() {
      Wall.setup();
    },
    teardown: function() {
      Wall.teardown();
    }
  } // API

  return API;
}
(
  window,
  window.jQuery,
  window.Code.BuddyBoss_Wall_Util
));



/**
 * 3. Wall Tooltips
 * ====================================================================
 */

;( function ( window, $, util, undefined ) {

  var config = {
    ajaxResetTimeout: 201
  }

  var state = util.state || {};
  var lang  = util.lang;

  var Tooltips = {};
  var $el = {};

  /**
   * Init

   * @return {void}
   */
  Tooltips.init = function() {
    // Globals
    $el.document = $(document);

    // Events
    $el.document.ajaxSuccess( Tooltips.ajaxSuccessListener );

    // console.log( '' );
    // console.log( 'Tooltips:' );
    // console.log( '=========' );
    // console.log( 'state' );
    // console.log( state );

    // First run
    Tooltips.initTooltips();

    // Localization, we need to override some BP_Dtheme variables
    if ( BP_DTheme && state ) {
      $.extend( BP_DTheme, state );
    }
  }

  /**
   * Listen to AJAX requests and refresh dynamic content/functionality

   * @return {void}
   */
  Tooltips.ajaxSuccessListener = function( event, jqXHR, options ) {
    Tooltips.destroyTooltips();

    window.setTimeout( Tooltips.initTooltips, config.ajaxResetTimeout );
  }

  /**
   * Teardown tooltips if they exist
   *
   * @return {void}
   */
  Tooltips.destroyTooltips = function() {
    if ( $el.tooltips && $el.tooltips.length ) {
      $el.tooltips.tooltipster('destroy');
      $el.tooltips = null;
    }
  }

  /**
   * Prepare tooltips
   *
   * @return {void}
   */
  Tooltips.initTooltips = function() {
    // Destroy any existing tooltips
    // if ( $el.tooltips && $el.tooltips.length ) {
    //  $el.tooltips.tooltipster('destroy');
    //  $el.tooltips = null;
    // }

    // Find tooltips on page
    $el.tooltips = $('.buddyboss-wall-tt-others');

    // Init tooltips
    if ( $el.tooltips.length ) {
      $el.tooltips.tooltipster({
        contentAsHTML:  true,
        functionInit:   Tooltips.getTooltipContent,
        interactive:    true,
        position:       'top-left',
        theme:          'tooltipster-buddyboss'
      });
    }
  }

  /**
   * Get tooltip content
   *
   * @param  {object} origin  Original tooltip element
   * @param  {string} content Current tooltip content
   *
   * @return {string}         Tooltip content
   */
  Tooltips.getTooltipContent = function( origin, content ) {
    var $content = origin.parent().find('.buddyboss-wall-tt-content').detach().html();
    
    return $content;
  }

  if ( state.load_tooltips ) {
      Tooltips.load_tooltips = true;
	//Tooltips.init();
  }
  jQuery(document).ready(function(){ 
    if( Tooltips.load_tooltips===true ) 
	Tooltips.initTooltips();
 });
}
(
  window,
  window.jQuery,
  window.Code.BuddyBoss_Wall_Util
));

function budyboss_wall_comment_like_unlike(target){
    jq = jQuery;
    target = jq(target);
    
   /* Favoriting activity stream items */
    if ( target.hasClass('fav-comment') || target.hasClass('unfav-comment') ) {
	    var type = target.hasClass('fav-comment') ? 'fav' : 'unfav';
	    var parent = target.closest('[id^=acomment-]');
	    var parent_id = parent.attr('id').substr( 9, parent.attr('id').length );

	    target.addClass('loading');

	    jq.post( ajaxurl, {
		    action: 'activity_mark_' + type,
		    'cookie': bp_get_cookies(),
		    'id': parent_id,
		    'item_type' : 'comment'
	    },
	    function(response) {
		    target.removeClass('loading');

		    target.fadeOut( 200, function() {
			    jq(this).html(response);
			    jq(this).attr('title', 'fav' == type ? BP_DTheme.remove_fav : BP_DTheme.mark_as_fav);
			    jq(this).fadeIn(200);
		    });

		    if ( 'fav' == type ) {
			    if ( !jq('.item-list-tabs #activity-favs-personal-li').length ) {
				    if ( !jq('.item-list-tabs #activity-favorites').length )
					    jq('.item-list-tabs ul #activity-mentions').before( '<li id="activity-favorites"><a href="#">' + BP_DTheme.my_favs + ' <span>0</span></a></li>');
				    
				    jq('.item-list-tabs ul #activity-favorites span').html( Number( jq('.item-list-tabs ul #activity-favorites span').html() ) + 1 );
			    }

			    target.removeClass('fav-comment');
			    target.addClass('unfav-comment');

		    } else {
			    target.removeClass('unfav-comment');
			    target.addClass('fav-comment');

			    jq('.item-list-tabs ul #activity-favorites span').html( Number( jq('.item-list-tabs ul #activity-favorites span').html() ) - 1 );

			    if ( !Number( jq('.item-list-tabs ul #activity-favorites span').html() ) ) {
				    if ( jq('.item-list-tabs ul #activity-favorites').hasClass('selected') )
					    bp_activity_request( null, null );

				    jq('.item-list-tabs ul #activity-favorites').remove();
			    }
		    }

		    /*if ( 'activity-favorites' == jq( '.item-list-tabs li.selected').attr('id') )
			    target.closest( '.activity-item' ).slideUp( 100 );*/
	    });
    } 
    return false;
}