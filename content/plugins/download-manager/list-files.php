<?php
global $wpdb, $current_user;
$limit = 10;
get_currentuserinfo(); 
if(wpdm_multi_user()&&!wpdm_is_custom_admin()) $cond[] = "uid='{$current_user->ID}'";
$_REQUEST['q'] = isset($_POST['q'])&&$_POST['q']!=''?$_POST['q']:$_GET['q'];
$_GET['paged'] = $_GET['paged']?$_GET['paged']:1;
$q = explode(" ",$_REQUEST['q']);
foreach($q as $st){
    $squery[] = "(`title` LIKE '%$st%' or `description` LIKE '%$st%')";
}
//mysql_escape_string(trim($_REQUEST[q]));
if($_REQUEST['q']!='') $cond[] = "(".implode(" and ", $squery).")";
if($_REQUEST['cat']!='') $cond[] = "category like '%\"$_REQUEST[cat]\"%'";
$cond = count($cond)>0?"where ".implode(" and ", $cond):'';
$start = $_GET['paged']?(($_GET['paged']-1)*$limit):0;
$field = $_GET['sfield']?$_GET['sfield']:'id';
$ord = $_GET['sorder']?$_GET['sorder']:'desc';
if($_REQUEST['q']) $qr = "&q=$_REQUEST[q]";
else $qr = '';
$res = $wpdb->get_results("select * from {$wpdb->prefix}ahm_files $cond order by {$field} {$ord} limit $start, $limit",ARRAY_A);
$total = $wpdb->get_var("select count(*) as t from {$wpdb->prefix}ahm_files $cond");
 
?>

<div class="wrap">
    <div class="icon32" id="icon-file-manager"><br></div>
<h2><?php echo __('Manage Download Packages','wpdmpro'); ?> <a class="button add-new-h2" href="admin.php?page=file-manager/add-new-package"><?php echo __('Add New','wpdmpro'); ?></a> </h2>
 <i><b style="font-family:Georgia"><?php echo __('Simply Copy and Paste the embed code at anywhere in post contents','wpdmpro'); ?></b></i><br><br>


           
<form method="get" action="" id="posts-filter">
<input type="hidden" name="page" value="file-manager">
<div class="tablenav">

<div class="actions">
 
<select class="select-action" name="task" onchange="if(this.value=='search') {jQuery('#sfld').fadeIn();jQuery('#posts-filter').attr('method','get');jQuery('input[type=checkbox]').removeAttr('checked');} else  {jQuery('#sfld').fadeOut();jQuery('#posts-filter').attr('method','post');}">
<option value="search"><?php echo __('Search','wpdmpro'); ?></option>
<option value="DeleteFile"><?php echo __('Delete Permanently','wpdmpro'); ?></option>
</select>&nbsp;<input type="text" id="sfld" style="width: 200px;" name="q" value="<?php echo $_REQUEST['q']; ?>">
<input type="submit" class="button-secondary action" id="doaction" value="<?php echo __('Apply','wpdmpro'); ?>">
<div  style="float: right;">
<select onchange="location.href='admin.php?page=file-manager&cat='+this.value">
<option value="">Select Category:</option>
<option value="">All Categories</option>
    <?php
    $scat = isset($_GET['cat'])?$_GET['cat']:'';
    wpdm_dropdown_categories('',0,$scat);
    ?>
</select>
</div>
<?php if(isset($_GET['q'])||isset($_GET['cat'])) { ?>
<input type="button" class="button-secondary action" onclick="location.href='admin.php?page=file-manager'" value="<?php echo __('Reset Search','wpdmpro'); ?>">
<?php } ?>

 



</div>

<br class="clear">
</div>

<div class="clear"></div>

<table cellspacing="0" class="widefat fixed">
    <thead>
    <tr>
    <th style="" class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox"></th>     
    <th style="width:50px" style="" class="manage-column column-type"  scope="col"><?php echo __('Icon','wpdmpro'); ?></th>
    <th style="width:50px" style="" class="manage-column column-id sortable <?php echo $_GET['sorder']=='asc'?'asc':'desc'; ?>"  scope="col"><a href='admin.php?page=file-manager<?php echo $_GET['cat']!=''?"&cat={$_GET[cat]}":""; ?>&sfield=id&sorder=<?php echo $_GET['sorder']=='asc'?'desc':'asc'; ?><?php echo $qr; ?>&paged=<?php echo $_GET['paged']?$_GET['paged']:1;?>'><span><?php echo __('ID','wpdmpro'); ?></span><span class="sorting-indicator"></span></a></th>
    <th style="" class="manage-column column-media sortable <?php echo $_GET['sorder']=='asc'?'asc':'desc'; ?>" id="media" scope="col"><a href='admin.php?page=file-manager<?php echo $_GET['cat']!=''?"&cat={$_GET[cat]}":""; ?>&sfield=title&sorder=<?php echo $_GET['sorder']=='asc'?'desc':'asc'; ?><?php echo $qr; ?>&paged=<?php echo $_GET['paged']?$_GET['paged']:1;?>'><span><?php echo __('Package Title','wpdmpro'); ?></span><span class="sorting-indicator"></span></a></th>
    <th style="" class="manage-column column-media sortable <?php echo $_GET['sorder']=='asc'?'asc':'desc'; ?>" id="media" scope="col"><?php echo __('Category','wpdmpro'); ?></th>
    <th style="" class="manage-column column-password" id="author" scope="col"><?php echo __('Password','wpdmpro'); ?></th>
    <th style="" class="manage-column column-access" id="parent" scope="col"><?php echo __('Access','wpdmpro'); ?></th>    
    <th style="" class="manage-column column-parent sortable <?php echo $_GET['sorder']=='asc'?'asc':'desc'; ?>" id="parent" scope="col"><a href='admin.php?page=file-manager<?php echo $_GET['cat']!=''?"&cat={$_GET[cat]}":""; ?>&sfield=download_count&sorder=<?php echo $_GET['sorder']=='asc'?'desc':'asc'; ?><?php echo $qr; ?>&paged=<?php echo $_GET['paged']?$_GET['paged']:1;?>'><span><?php echo __('Downloads','wpdmpro'); ?></span><span class="sorting-indicator"></span></a></th>
    </tr>
    </thead>

    <tfoot>
    <tr>
    <th style="" class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox"></th> 
    <th style="width:50px" style="" class="manage-column column-type"  scope="col"><?php echo __('Icon','wpdmpro'); ?></th>
    <th style="width:50px" style="" class="manage-column column-id sortable <?php echo $_GET['sorder']=='asc'?'asc':'desc'; ?>"  scope="col"><a href='admin.php?page=file-manager<?php echo $_GET['cat']!=''?"&cat={$_GET[cat]}":""; ?>&sfield=id&sorder=<?php echo $_GET['sorder']=='asc'?'desc':'asc'; ?><?php echo $qr; ?>&paged=<?php echo $_GET['paged']?$_GET['paged']:1;?>'><span><?php echo __('ID','wpdmpro'); ?></span><span class="sorting-indicator"></span></a></th>
    <th style="" class="manage-column column-media sortable <?php echo $_GET['sorder']=='asc'?'asc':'desc'; ?>" id="media" scope="col"><a href='admin.php?page=file-manager<?php echo $_GET['cat']!=''?"&cat={$_GET[cat]}":""; ?>&sfield=title&sorder=<?php echo $_GET['sorder']=='asc'?'desc':'asc'; ?><?php echo $qr; ?>&paged=<?php echo $_GET['paged']?$_GET['paged']:1;?>'><span><?php echo __('Package Title','wpdmpro'); ?></span><span class="sorting-indicator"></span></a></th>
    <th style="" class="manage-column column-media sortable <?php echo $_GET['sorder']=='asc'?'asc':'desc'; ?>" id="media" scope="col"><?php echo __('Category','wpdmpro'); ?></th>
    <th style="" class="manage-column column-password" id="author" scope="col"><?php echo __('Password','wpdmpro'); ?></th>
    <th style="" class="manage-column column-access" id="parent" scope="col"><?php echo __('Access','wpdmpro'); ?></th>    
    <th style="" class="manage-column column-parent sortable <?php echo $_GET['sorder']=='asc'?'asc':'desc'; ?>" id="parent" scope="col"><a href='admin.php?page=file-manager<?php echo $_GET['cat']!=''?"&cat={$_GET[cat]}":""; ?>&sfield=download_count&sorder=<?php echo $_GET['sorder']=='asc'?'desc':'asc'; ?><?php echo $qr; ?>&paged=<?php echo $_GET['paged']?$_GET['paged']:1;?>'><span><?php echo __('Downloads','wpdmpro'); ?></span><span class="sorting-indicator"></span></a></th>
    </tr>
    </tfoot>

    <tbody class="list:post" id="the-list">
    <?php 
        $acats = maybe_unserialize(get_option('_fm_categories'));
        foreach($res as $media) { 
           $cats = unserialize($media['category']);
           $fcats = array();
           if(is_array($cats)){
           foreach($cats as $c){
               $fcats[] = "<a href='admin.php?page=file-manager&cat=$c'>{$acats[$c][title]}</a>";
           }}
           $cats = @implode(", ", $fcats);
           
        ?>
    <tr valign="top" class="alternate author-self status-inherit" id="post-<?php echo $media[id]; ?>">

                <th class="check-column" scope="row"><input type="checkbox" value="<?php echo $media[id]; ?>" name="id[]"></th>
                <td class="check-column" scope="row">
                <?php if($media['icon']=='') {?>
                <img rel='<?php echo count(unserialize($media['files'])); ?>' src="<?php echo plugins_url('download-manager/file-type-icons/'); if(count(unserialize($media['files']))<=1) echo end(explode('.',end(unserialize($media['files'])))); else echo 'zip'; ?>.png" onError='this.src="<?php echo plugins_url('download-manager/file-type-icons/_blank.png');?>";' />
                <?php } else { ?>
                <img rel='<?php echo count(unserialize($media['files'])); ?>' src="<?php echo plugins_url($media['icon']); ?>" onError='this.src="<?php echo plugins_url('download-manager/file-type-icons/_blank.png');?>";' />
                <?php } ?>
                </td>
                <td class="check-column" scope="row">&nbsp;<b><?php echo $media[id]; ?></b></td>
                
                <td class="media column-media">
                    <strong><a title="Edit" href="admin.php?page=file-manager&task=EditPackage&id=<?php echo $media['id']?>"><?php echo stripcslashes($media['title']);?></a></strong>  <br>
                    <code><?php echo count(unserialize($media['files'])); ?> file<?php echo count(unserialize($media['files']))>1?'s':''; ?></code> <code><input style="border:0px;background: transparent;text-align:center;" type="text" onclick="this.select()" size="20" title="Simply Copy and Paste in post contents" value="[wpdm_package id=<?php echo $media['id'];?>]" /></code>
                    <div class="row-actions"><div class="button-group"><a class="button" href="admin.php?page=file-manager&task=EditPackage&id=<?php echo $media['id']?>">Edit</a><a class="button" href="admin.php?page=file-manager/add-new-package&clone=<?php echo $media['id']?>" style="color: #336699">Clone</a><a class="button" target="_blank" href='<?php echo get_wpdm_permalink($media); ?>' style="color: #005500">View</a><a class="button" target="_blank" href='<?php echo wpdm_download_url($media, 'masterkey='.get_wpdm_meta($media['id'],'masterkey')); ?>' style="color: #6E3887">Download</a><a href="admin.php?page=file-manager&task=DeleteFile&id=<?php echo $media['id']?>" class="button submitdelete" style="color: #cc0000" rel="<?php echo $media[id]; ?>" title="Permanently">Delete</a></div></div>
                </td>
                <td class="author column-author"><?php echo $cats; ?></td>
                <td class="author column-author"><?php echo $media['password']; ?></td>
                <td class="parent column-parent"><?php echo @implode(",",unserialize($media['access'])); ?></td>                
                <td class="parent column-parent"><?php echo $media['download_count']; ?></td>
     
     </tr>
     <?php } ?>
    </tbody>
</table>
                    
<?php
$cp = $_GET['paged']?$_GET['paged']:1;
$page_links = paginate_links( array(
    'base' => add_query_arg( 'paged', '%#%' ),
    'format' => '',
    'prev_text' => __('&laquo;'),
    'next_text' => __('&raquo;'),
    'total' => ceil($total/$limit),
    'current' => $cp
));


?>

<div id="ajax-response"></div>

<div class="tablenav">

<?php if ( $page_links ) { ?>
<div class="tablenav-pages"><?php $page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s' ) . '</span>%s',
    number_format_i18n( ( $_GET['paged'] - 1 ) * $limit + 1 ),
    number_format_i18n( min( $_GET['paged'] * $limit, $total ) ),
    number_format_i18n( $total ),
    $page_links
); echo $page_links_text; ?></div>
<?php } ?>

 
<br class="clear">
</div>
</form>
<br class="clear">

</div>

<script language="JavaScript">
<!--
  jQuery(function(){
     jQuery('.submitdelete').click(function(){
          if(!showNotice.warn()) return false;
          var id = '#post-'+this.rel;
          jQuery.post(this.href,function(){
              jQuery(id).fadeOut();
          }) ;
          return false;
     });
  });
//-->
</script> 