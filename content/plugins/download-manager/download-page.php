<?php 

/*if($file[preview]!='')
$file['thumb'] = "<img src='".plugins_url().'/download-manager/timthumb.php?w='.get_option('_wpdm_pthumb_w').'&h='.get_option('_wpdm_pthumb_h').'&zc=1&src='.$file[preview]."'/>";
else
$file['thumb'] = "";
 
$file['files'] = maybe_unserialize($file[files]);     
$file['file_count'] = count($file['files']);
$fhtml = "<table class='wpdm-filelist dtable'><tr><th>File</th><th>Password</th><th>Download</th></tr>";
$idvdl = get_wpdm_meta($file['id'],'individual_download');
$fileinfo = get_wpdm_meta($file['id'],'fileinfo');
 
if(count($file['files'])>0) {
foreach($file['files'] as $ind=>$sfile){    
    //$sfile = preg_replace("/([0-9]+)_/","",$sfile);
    //<a rel='noindex nofollow' href='".wpdm_download_url($file)."&ind=".$ind."' class='ind-download'>
    if(!is_array($fileinfo[$sfile])) $fileinfo[$value] = array();
    if($idvdl==1) {
    if($fileinfo[$sfile]['password']==''&&get_wpdm_meta($file['id'],'password_lock',true)==1) $fileinfo[$sfile]['password'] = $file['password'];
    $ttl = $fileinfo[$sfile][title]?$fileinfo[$sfile][title]:preg_replace("/([0-9]+)_/","",$sfile);
    $fhtml .= "<tr><td>{$ttl}</td>";
    $fhtml .= "<td width='90' align=right>";
    if($fileinfo[$sfile]['password']!='')
    $fhtml .= "<span><input onkeypress='jQuery(this).removeClass(\"error\");' size=10 type='text' value='Password' id='pass_{$file[id]}_{$ind}' onfocus='this.select()' onblur='if(this.value==\"\") this.value=\"Password\"' name='pass' />";    
    $fhtml .= "</td>";
    if($fileinfo[$sfile]['password']!='')
    $fhtml .= "<td width=90><button class='inddl wpdm-gh-button' file='{$sfile}' rel='".wpdm_download_url($file)."&ind=".$ind."' pass='#pass_{$file[id]}_{$ind}'>Download</button></td></tr>";
    else
    $fhtml .= "<td width=90><a class='wpdm-gh-button' href='".wpdm_download_url($file)."&ind=".$ind."'>Download</a></td></tr>";
    }
    else
    $fhtml .= "<tr><td>{$fileinfo[$sfile][title]}</td></tr>";
}}
$fhtml .= "</table>";

if($idvdl!=1) $fhtml = "";

$file['file_list'] =  $fhtml;
$dkey = is_array($file['files'])?md5(serialize($file['files'])):md5($file['files']);
$file['download_url'] = home_url("/?file={$file[id]}&downloadkey=".$dkey);  */

$file['description'] = stripcslashes($file['description']);
$file['page_template'] = stripcslashes($file['page_template']);

 
 
$data = FetchTemplate($file['page_template'],$file, 'page');
$siteurl = site_url('/');
$data .= "<script type='text/javascript' language='JavaScript'> jQuery('.inddl').click(function(){ var tis = this; jQuery.post('{$siteurl}',{wpdmfileid:'{$file['id']}',wpdmfile:jQuery(this).attr('file'),actioninddlpvr:jQuery(jQuery(this).attr('pass')).val()},function(res){ res = res.split('|'); var ret = res[1]; if(ret=='error') jQuery(jQuery(tis).attr('pass')).addClass('error'); if(ret=='ok') location.href=jQuery(tis).attr('rel')+'&_wpdmkey='+res[2];});}); </script> ";
