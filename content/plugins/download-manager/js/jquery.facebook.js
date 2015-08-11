jQuery(document).ready(function() {  

      
  
window.fbAsyncInit = function() {
    
    try{ FB.init({appId: appid, status: true, cookie: true, xfbml: true});  } catch(err){}
     
    FB.Event.subscribe('edge.create', function(href, widget) {       
      var id = href.replace(/[^0-9a-z-]/g,"");       
      var pkgid = jQuery('#'+id).html();  
      jQuery.cookie('unlocked_'+pkgid,1); 
       
      jQuery.post(siteurl,{id:pkgid,dataType:'json',execute:'getlink',force:force,social:'f',action:'wpdm_ajax_call'},function(res){                                                                
                            if(res.downloadurl!=''&&res.downloadurl!='undefined'&&res!='undefined') {
                            location.href=res.downloadurl;
                            jQuery('#pkg_'+pkgid).html('<a style=\"color:#000\" href=\"'+res.downloadurl+'\">Download</a>');
                            } else {             
                                jQuery('#msg_'+pkgid).html(''+res.error);                                
                            } 
                    });
      return false;
 });
 
 
 
};
   
});