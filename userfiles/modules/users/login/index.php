<? if(isset($_GET['reset_password_link'])): ?>
<module type="users/forgot_password" />
<? else:  ?>




<script  type="text/javascript">

mw.require('forms.js', true);


$(document).ready(function(){
	

	 
	 mw.$('#user_login_<? print $params['id'] ?>').submit(function() {

 
 mw.form.post(mw.$('#user_login_<? print $params['id'] ?>') , '<? print site_url('api/user_login') ?>', function(){
	         mw.response('#user_login_holder_<? print $params['id'] ?>',this);

	// mw.reload_module('[data-type="categories"]');
	 // mw.reload_module('[data-type="pages"]');
	 });

 return false;
 
 
 });
 
});
</script>
<?
$module_template = get_option('data-template',$params['id']);
				if($module_template == false and isset($params['template'])){
					$module_template =$params['template'];
				}
				
				
				
				
				
				if($module_template != false){
						$template_file = module_templates( $config['module'], $module_template);
				
				} else {
						$template_file = module_templates( $config['module'], 'default');
				
				}
				
				//d($module_template );
				if(isset($template_file) and is_file($template_file) != false){
					include($template_file);
				} else {
					
						$template_file = module_templates( $config['module'], 'default');
				include($template_file);
					//print 'No default template for '.  $config['module'] .' is found';
				}

?><? endif; ?>