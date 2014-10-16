/**
 * BuddyBoss Media > Pics JavaScript functionality
 *
 * A BuddyPress plugin combining user activity feeds with media management.
 *
 * This file should load in the footer
 *
 * @author      BuddyBoss
 * @since       BuddyBoss Media 1.0, BuddyBoss Media 1.0, BuddyBoss Media Pics 1.0
 * @package     buddyboss-media
 *
 * ====================================================================
 *
 * 1. jQuery + Globals
 * 2. BuddyBoss Media Picture Grid + PhotoSwipe
 * 3. BuddyBoss Media Uploader
 */


/**
 * 1. jQuery + Globals
 * ====================================================================
 */
var jq = $ = jQuery;

// Window.Code fallback
window.Code = window.Code || { Util: false, PhotoSwipe: false };

// Util
window.BuddyBoss_Media_Util = ( function( window, $, opt, undefined ) {

  var $window = $(window);

  var Util = {
    state: opt,
    lang: function( key ) {
      var key = key || 'undefined key!';
      return opt[key] || 'Language key missing for: ' + key;
    }
  }

  var resizeThrottle;

  // Check for mobile resolution
  function checkMobile() {
    // Set to true if not set and is mobile
    if ( ! Util.state.isMobile && $window.width() <= 800 ) {
      Util.state.isMobile = true;
    }
    // Set to false if not set
    else if ( ! Util.state.isMobile ) {
      Util.state.isMobile = false;
    }
  }
  checkMobile();

  // Check for mobile resolution on resize
  $window.on( 'resize orientationchange', function() {
    clearTimeout( resizeThrottle );
    resizeThrottle = setTimeout( checkMobile, 75 );
  });

  return Util;

}
(
  window,
  window.jQuery,
  window.BuddyBoss_Media_Appstate || {}
));

/**
 * 2. BuddyBoss Media Picture Grid + PhotoSwipe
 * ====================================================================
 * @returns {object} BuddyBossSwiper
 *
 * window.Code.BuddyBossSwiper.has_media()
 */

window.Code.BuddyBossSwiper = ( function( window, PhotoSwipe_Util, PhotoSwipe ) {

  if ( ! PhotoSwipe_Util || ! PhotoSwipe ) {
    return false;
  }

  var
    $buddyboss_photo_grid = $('#buddyboss-media-grid'),
    $document = $(document),
    buddyboss_mediawipe = false,
    current_photo_permalink,
    current_photo_activity_text,
    $caption,
    $comment_link,
    buddyboss_mediawipe_options   = {

      preventSlideshow: true,
      imageScaleMethod: 'fitNoUpscale',
      loop: false,
      captionAndToolbarAutoHideDelay: 0,

      // Toolbar HTML
      getToolbar: function() {
        return '<div class="ps-toolbar-close"><div class="ps-toolbar-content"></div></div><div class="ps-toolbar-comments"><div class="ps-toolbar-content"></div></div><div class="ps-toolbar-previous ps-toolbar-previous-disabled"><div class="ps-toolbar-content"></div></div><div class="ps-toolbar-next"><div class="ps-toolbar-content"></div></div>';
        // NB. Calling PhotoSwipe.Toolbar.getToolbar() wil return the default toolbar HTML
      },

      // Return the current activity text for the caption
      getImageCaption: function(el) {
        var $pic = $( el );

        current_photo_permalink = '#';
        current_photo_activity_text = '';

        if ( $pic.find('img').length == 0 )
          return '';

        current_photo_permalink = $pic.find('img')[0].getAttribute( 'data-permalink' );
        current_photo_activity_text = $buddyboss_photo_grid.length > 0
                                  ? $pic.parents('.gallery-icon').find('.buddyboss_media_action').text()
                                  : $pic.parents('.activity-content').find('.activity-header').text();

        return current_photo_activity_text;
      },

      // Store data we need
      getImageMetaData: function(el) {
        return {
          href: current_photo_permalink,
          caption: current_photo_activity_text
        }
      }

    }; // End PhotoSwipe setup

  BuddyBossSwiperClass = {

    has_grid: function() {
      return $buddyboss_photo_grid.length > 0;
    },

    has_photoswipe: function() {
      return buddyboss_mediawipe !== false;
    },

    location_from_current: function() {

      var current, comments_href, callback_args;

      if ( ! BuddyBossSwiperClass.has_photoswipe() ) {
        return false;
      }

      current       = buddyboss_mediawipe.getCurrentImage();
      comments_href = current.metaData.href;
      callback_args = {comments_href: comments_href, current: current};

      $(document).trigger( 'buddyboss:media:comment_link', callback_args );

      setTimeout( function() {
        window.location = comments_href;
      }, 15 );
    },
    reset: function() {

      if ( buddyboss_mediawipe !== false ) {
        try {
          PhotoSwipe.detatch( buddyboss_mediawipe );
        }
        catch( e ) {

        }
      }

      BuddyBossSwiperClass.start();

    },
    start: function() {
      // console.log( 'starting' );

      var _pics_sel = BuddyBossSwiperClass.has_grid()
                    ? '.gallery-icon > a'
                    : '.buddyboss-media-photo-wrap';

      var $buddyboss_media = $( _pics_sel );

      if ( $buddyboss_media.length > 0 ) {
        // Load PhotoSwipe
        buddyboss_mediawipe = $buddyboss_media.photoSwipe( buddyboss_mediawipe_options );

        // Before showing we need to update the comment icon with the
        // proper permalink
        buddyboss_mediawipe.addEventHandler(PhotoSwipe.EventTypes.onBeforeShow, function(e){
          // Prevent scrolling while active
          $('html').css({overflow: 'hidden'});
        });

        // After showing we need to revert any changes we made during the
        // onBeforeShow event
        buddyboss_mediawipe.addEventHandler(PhotoSwipe.EventTypes.onHide, function(e){
          // Allow scrolling again
          $('html').css({overflow: 'auto'});

          current_photo_activity_text = null;
          current_photo_permalink = null;

          $caption.off( 'click' );
          $caption = null;

          $comment_link = null;

          // console.log( 'Hiding PhotoSwipe' );
          setTimeout( function() { $(window).trigger('reset_carousel'); }, 555 );
        });

        // onCaptionAndToolbarShow
        buddyboss_mediawipe.addEventHandler( PhotoSwipe.EventTypes.onCaptionAndToolbarShow, function(e) {
          $caption = $( '.ps-caption' ).on( 'click', function( e ) {
            window.location = buddyboss_mediawipe.getCurrentImage().metaData.href;
          });
          $comment_link = $( '.ps-toolbar-comments' );
        });

        buddyboss_mediawipe.addEventHandler(PhotoSwipe.EventTypes.onToolbarTap, function(e) {
          if ( e.toolbarAction === PhotoSwipe.Toolbar.ToolbarAction.none ) {
            if ( e.tapTarget === $comment_link[0] || PhotoSwipe_Util.DOM.isChildOf( e.tapTarget, $comment_link[0] ) ) {

              var current       = buddyboss_mediawipe.getCurrentImage(),
                  comments_href = current.metaData.href,
                  callback_args = {comments_href: comments_href, current: current};

              $(document).trigger( 'buddyboss:media:comment_link', callback_args );

              window.location = comments_href;
            }
          }
        });


      } // End if pics.length > 0
    }
  }

  BuddyBossSwiperClass.start();

  function ajaxSuccessHandler( e, xhr, options ) {

    var action        = bbmedia_getQueryVariable( options.data, 'action' );
    var resetCallback = function( action ) {
      return function() {
        if ( action !== 'heartbeat' ) {
          BuddyBossSwiperClass.reset();
        }
      }
    }( action );

    // Most BuddyPress animations finish after 200ms
    window.setTimeout( resetCallback, 205 );

    // Perform again once after a longer delay just in case
    // @TODO: Get a dom observer
    window.setTimeout( resetCallback, 750 );
  }

  $(document).ajaxSuccess( ajaxSuccessHandler );

  return BuddyBossSwiperClass;

}
(
  window,
  window.Code.Util || false,
  window.Code.PhotoSwipe || false
));



/**
 * 3. BuddyBoss Media Uploader
 * ====================================================================
 * @returns {object} BuddyBoss_Media_Uploader
 *
 * window.BuddyBoss_Media_Uploader = {
 *   /.../
 * }
 */

window.BuddyBoss_Media_Uploader = ( function( window, $, util, undefined ) {

  var uploader = false;

  var _l = {};

  var state = util.state || {},
      lang  = util.lang;

  var pic_status = false;

  var APP = {

    /**
     * Startup
     *
     * @return {void}
     */
    init: function() {

      var self = this;

      this.inject_markup();

      if ( ! this.get_elements() ) {
        return false;
      }

      this.setup_textbox();

      setTimeout( function() {
        self.start_uploader();
      }, 10 );

      $.ajaxPrefilter( APP.prefilter );
    },

    /**
     * Would handle teardowns if AJAX was implemented for page
     * navigations.
     *
     * @return {void}
     */
    destroy: function() {
      // this.destroy_button();
    },

    /**
     * Dynamically inject markup, this avoids relying on BuddyPress
     * templating and helps handle plugin conflicts
     *
     * @return {void}
     */
    inject_markup: function() {
      // Activity greeting on user photo "What's new, %firstname%"
      var $activity_greeting = $('.my-gallery .activity-greeting'),
          greeting = lang( 'user_add_photo' );

      if ( $activity_greeting.length && !! greeting ) {
        $activity_greeting.text( greeting ).show();
      }

      // For our add photo, progress and preview area we rely
      // on #what-new-content
      var $whats_new_content = $('#whats-new-content');

      // Add photo button + progress area
      var $add_photo = $('#buddyboss-media-tpl-add-photo');

      if ( $add_photo.length && $whats_new_content.length ) {
        $whats_new_content.before( $add_photo.html() );
      }

      // Add photo preview pane
      var $preview_pane = $('#buddyboss-media-tpl-preview');

      if ( $preview_pane.length && $whats_new_content.length ) {
        $whats_new_content.after( $preview_pane.html() );
      }
    },

    /**
     * Get DOM elements we'll need
     *
     * @return {boolean} True if we have the required elements
     */
    get_elements: function() {
      _l.$whats_new = $('#whats-new');

      if ( _l.$whats_new.length === 0 ) {
        return false;
      }

      _l.$add_photo = $('#buddyboss-media-add-photo');
      _l.$add_photo_button = $('#buddyboss-media-add-photo-button');
      _l.$post_button = $('#whats-new-submit').find('[type=submit],button');
      _l.$preview_pane = $('#buddyboss-media-preview');
      _l.$preview_inner = $('#buddyboss-media-preview-inner');

      return true;
    },

    /**
     * Magic. BuddyPress disables the post button when there aren't any
     * characters in the post box. Since we want to allow users to upload
     * photos as status updates, we get around disabling the post button
     * with a timer.
     *
     * @return {void}
     */
    setup_textbox: function() {
      _l.$whats_new.blur(function(){
        setTimeout(function(){
          if ( pic_status && pic_status.name ) {
            _l.$post_button.removeAttr('disabled');
            _l.$post_button.prop('disabled', false);
          }
        }, 200)
      });
    },

    /**
     * We use jQuery's Ajax.preFilter hook to add picture related
     * uploads to new status update's when needed. Be wary of the
     * dragons.
     *
     * @param  {object} options      jQuery ajax options that are sending
     * @param  {object} origOptions  Original jQuery ajax options
     * @param  {object} jqXHR        jQuery XHR object
     * @return {void}
     */
    prefilter: function( options, origOptions, jqXHR ) {

      var action = bbmedia_getQueryVariable( options.data, 'action' );

      if( typeof action == 'undefined' || action != 'post_update')
	     return;

      var new_data,
          pic_html;

      if ( typeof pic_status == 'object' && pic_status.hasOwnProperty('name') ) {

        pic_html = $('<a/>')
          .attr( 'href', pic_status.url )
          .attr( 'target', '_blank' )
          .attr( 'title', pic_status.name )
          .addClass( 'buddyboss-media-photo-link' )
          .html( pic_status.name )[0].outerHTML;

        new_data = $.extend( {}, origOptions.data, {
          content: origOptions.data.content + ' ' + pic_html,
          has_pic: pic_status
        });

        options.data = $.param( new_data );

        options.success = ( function( old_success ) {
          return function( response, txt, xhr ) {
            if ( $.isFunction( old_success ) ) {
              old_success( response, txt, xhr );
            }

            if ( response[0] + response[1] !== '-1' ) {
              APP.post_success( response, txt, xhr );
            }
          }
        })(options.success);
      }
      else if ( origOptions.data && origOptions.data.action === 'get_single_activity_content' ) {
        options.success = ( function( old_success ) {
          return function( response, txt, xhr ) {
            if ( $.isFunction( old_success ) ) {
              old_success( response, txt, xhr );
            }

            if ( response[0] + response[1] !== '-1' ) {
              APP.readmore_success( response, txt, xhr );
            }
          }
        })(options.success);
      }

      /**
      console.log( 'options' );
      console.log( options );
      console.log(  );
      console.log( 'options.data' );
      console.log( options.data );
      console.log(  );
      console.log( 'origOptions' );
      console.log( origOptions );
      console.log(  );
      /**/
    },

    /**
     * This callback fires after a photo was posted as part of
     * an activity update, we'll animate the preview closed
     * and reset.
     *
     * @param  {object} response Ajax response
     * @return {void}
     */
    post_success: function( response ) {

      /* Remove picture preview and animate up */
      if ( _l.$preview_inner &&  _l.$preview_inner.length ){
        _l.$preview_inner.html('');
      }

      if ( _l.$preview_pane && _l.$preview_inner.length ) {
        _l.$preview_pane.stop().animate({height:'0px'});
      }

      /* BuddyBoss: Check if we're on the pic page and refresh after upload */
      if ( 0 !== $( '#is-buddyboss-media-grid' ).length ) {
        var refreshUrl = $( '#is-buddyboss-media-grid' ).data( 'url' );
        if ( refreshUrl.length > 6 ) {
          document.location = refreshUrl;
        }
      }

      /* BuddyBoss: If we're using pics, we need to attach PhotoSwipe */
      var $new = $("li.new-update").find('.buddyboss-media-photo-wrap');
      if ( $new.length > 0 && typeof BuddyBossSwiper == 'object'
           && BuddyBossSwiper.hasOwnProperty( 'reset' ) ) {
        BuddyBossSwiper.reset();
      }

      pic_status = false;
    },

    /**
     * Handles upload, upload progress and previewing pics
     *
     * @return {void}
     */
    start_uploader: function() {

      var $previewPane = _l.$preview_pane,
          $previewInner = _l.$preview_inner;
          $postButton = _l.$post_button,
          maxWidth = $('#whats-new-options').width();

      var $progressWrap = $('.buddyboss-media-progress').first(),
          $progressBar = $progressWrap.find('progress').first(),
          progressValue = $progressWrap.find('.buddyboss-media-progress-value'),
          progressPercent = 0,
          progressTimeout;

      var progressAnimation = function() {
        if ( $progressBar.val() < progressPercent ) {
          $progressBar.val( $progressBar.val() + 1 );
        }

        if ( $progressBar.val() < 100 && progressPercent != 100 ) {
          progressTimeout = setTimeout( progressAnimation, 1000/60 );
        }
        else if ( progressPercent == 100 ) {
          $progressBar.val( 100 );
        }
      }

      $postButton.on( 'click', function( e ) {
        // Check if we're currently uploading a picture and alert the user
        if ( $progressWrap.hasClass('uploading') ) {
          e.preventDefault();
          e.stopPropagation();
          alert( lang( 'error_photo_is_uploading' ) );
          return false;
        }
      });

      /*
      console.log( 'state' );
      console.log( state );
      console.log( _l.$add_photo_button );
      console.log( _l.$add_photo );
      */

      var uploader_state = 'closed';
      var ieMobile = navigator.userAgent.indexOf('IEMobile') !== -1;

      // IE mobile
      if ( ieMobile ) {
        _l.$add_photo.addClass('legacy');
      }

      if ( state.isMobile ) {
        // iOS/mobile
        _l.$add_photo_button.on( 'click', function( e ) {
          if ( uploader_state === 'closed' ) {
            _l.$add_photo.find('.moxie-shim').find('input').trigger( 'click' );
            return false;
          }
        });

        state.uploader_runtimes = 'html5';
      }

      uploader = new plupload.Uploader({
        runtimes: state.uploader_runtimes || 'html5,flash,silverlight,html4',
        browse_button: _l.$add_photo_button[0],
        container: _l.$add_photo[0],
        max_file_size: state.uploader_filesize || '10mb',
        multi_selection: state.uploader_multiselect || false,
        url: ajaxurl,
        multipart: true,
        multipart_params: {
          action: 'buddyboss_media_post_photo',
          'cookie': encodeURIComponent(document.cookie),
          '_wpnonce_post_update': $("input#_wpnonce_post_update").val()
        },
        flash_swf_url: state.uploader_swf_url || '',
        silverlight_xap_url: state.uploader_xap_url || '',
        filters: [
          { title: lang( 'file_browse_title' ), extensions: state.uploader_filetypes || 'jpg,jpeg,gif,png,bmp' }
        ],
        init: {
          FilesAdded: function(up, files) {
            // console.log('////// onsubmit ///////');

            $('#buddyboss-media-preview').animate({height:'0px'});
            pic_status = false;

            $progressWrap.addClass('uploading');
            $postButton.prop("disabled", true).addClass('disabled');

            progressPercent = 0;
            $progressBar.val(0);
            progressValue.html(0 + '%');
            progressTimeout = setTimeout( progressAnimation, 1000/60 );

            uploader_state = 'closed';

            up.start();
          },

          UploadProgress: function(up, file) {
            if ( file && file.hasOwnProperty( 'percent' ) ) {
              progressPercent = file.percent;
              progressValue.html( progressPercent + '%' );
            }

            if ( file && file.hasOwnProperty( 'percent' ) && file.percent == 100 && window.BuddyBoss_Media && window.BuddyBoss_Media.isMobile ) {
              progressValue.html( '' );
            }
          },

          FileUploaded: function(up, file, info) {
            var responseJSON = $.parseJSON( info.response );
            // console.log('// ----- upload response ----- //');
            // console.log(up,file,info,responseJSON);

            $postButton.prop("disabled", false).removeClass('disabled').addClass('uploaded');

            if ( ! responseJSON ) {
              alert( lang( 'error_uploading_photo' ) );
            }

            if (responseJSON.hasOwnProperty('error'))
            {
              alert(responseJSON.message);
              return false;
            }

            if ( window.BuddyBoss_Media && window.BuddyBoss_Media.isMobile ) {
              progressValue.html( lang( 'one_moment' ) );
            }

            var pic_uri = responseJSON.hasOwnProperty('url') ? responseJSON.url : false;

            $previewInner.empty();

            $previewPane.animate({ height: 0 }, function()
            {
              $progressWrap.removeClass('uploading');

              if ( pic_uri )
              {
                $previewInner.html( '<img src="' + pic_uri + '">' );

                var picWidth,
                    picHeight,
                    picRatio;

                var $img = $('#buddyboss-media-preview-inner img')
                  .load(function()
                  {
                    picWidth = this.width;   // Note: $(this).width() will not
                    picHeight = this.height; // work for in memory images.

                    //console.log( picWidth );
                    //console.log( picHeight );
                    //console.log('// ----- upload success ----- //');

                    if ( picWidth > maxWidth )
                    {
                      picRatio = maxWidth / picWidth;
                      picWidth = maxWidth;
                      picHeight = picHeight * picRatio;
                      $(this).css("width", picWidth);
                      $(this).css("height", picHeight);  // Scale height based on ratio
                    }
                    $('#whats-new-options').animate({height: 40});

                    $previewPane.animate({ height: picHeight });
                  });

                pic_status = responseJSON;
              }
            });

          },

          Error: function(up, args) {
            alert( lang( 'error_uploading_photo' ) );

            $progressWrap.removeClass('uploading');
            $postButton.prop("disabled", false).removeClass('loading');

            uploader_state = 'closed';

            pic_status = false;
          }
        }
      });

      uploader.init();

    } // start_uploader();

  } // APP


  var API = {
    setup: function() {
      APP.init();
    },
    teardown: function() {
      APP.destroy();
    }
  } // API

  $(document).ready( function() {
    APP.init();
  } );

  return API;
}
(
  window,
  window.jQuery,
  window.BuddyBoss_Media_Util
));

/* get querystring value */
function bbmedia_getQueryVariable( query, variable ) {

  var vars = query.split("&");

  for ( var i = 0; i < vars.length; i++ ) {
    var pair = vars[i].split( "=" );

    if ( pair[0] == variable ) {
      return pair[1];
    }
  }
  return(false);
}
