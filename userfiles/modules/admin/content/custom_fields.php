<?
//p($params);

if(isset($params['post_id'])){
$params['post_id'] = intval($params['post_id']);

}
$rand = rand();

$assign_custom_fields = date("ymdhis").$rand.md5(url());
 
?>
<?  if(intval($params['page_id']) == 0 or intval($params['post_id']) == 0):  ?>
<?  ?>

<input id="cf_temp" type="hidden"  name="cf_temp" value="<? print $assign_custom_fields ; ?>" />
<? endif; ?>
<?
$cf_temp = false;

if(intval($params['page_id']) == 0){
	//$params['page_id'] = $assign_custom_fields ;
	$cf_temp = "/temp:". $assign_custom_fields;
	
} else {
 
if(isset ($params['post_id'] ) and intval($params['post_id']) == 0){
//	$params['post_id'] = $assign_custom_fields ;
	$cf_temp = "/temp:". $assign_custom_fields;
} 
}


//p($params);
$js_params = false;
$cf_edit_params= false;
if(isset($params['post_id']) and strval($params['post_id'])!= ''){

$js_params = "&post_id=". $params['post_id'];
$cf_edit_params = "/post_id:". $params['post_id'];
}

if(intval($params['page_id']) != 0){
	$page = get_page($params['page_id']);
	if($page["content_layout_file"] != ""){
	$cf_files = TEMPLATE_DIR.DS.'layouts'.DS.$page["content_layout_file"];
	} else {
		$cf_files = TEMPLATE_DIR; 
	}
	$cf_files = normalize_path($cf_files);
	$cf_files = dirname($cf_files);
	$cf_files = $cf_files .DS;
	$cf_files  .= 'custom_fields.php';
	 
}

?>
<script type="text/javascript" src="<?php   print( $config["url_to_module"]);  ?>custom_fields/jquery.formbuilder.js.php?foo=1<? print $js_params   ?>"></script>
<link rel="stylesheet" type="text/css" href="<?php   print( $config["url_to_module"]);  ?>custom_fields/jquery.formbuilder.css" />
<div  class="custom-fields-form-wrap custom-fields-form-wrap-<? print $rand ?>">
  <ul class="custom-fields-form-builder custom-fields-form-builder-<? print $rand ?>">
  </ul>
  <? 
  
if(is_file($cf_files )){
include($cf_files);
}
	 ?>
</div>
<script type="text/javascript">
			$(document).ready(function(){
						 	 
				$('.custom-fields-form-builder-<? print $rand ?>').formbuilder({
					'save_url': '<? print site_url('api/content/cf_save') ?>/page_id:<? print $params['page_id']  ?><? print $cf_edit_params  ?><? print $cf_temp  ?>',
					'load_url': '<? print site_url('api/content/cf_load') ?>/page_id:<? print $params['page_id']  ?><? print $cf_edit_params  ?><? print $cf_temp  ?>',
					'del_url': '<? print site_url('api/content/delete_cf') ?>/page_id:<? print $params['page_id']  ?><? print $cf_edit_params  ?><? print $cf_temp  ?>/id:'
				});
				
 
				
				$(function() {
			    	$(".custom-fields-form-builder-<? print $rand ?>").sortable({ opacity: 0.6, cursor: 'move', items: 'li'});
			    });
			});
</script>
