<? $v = ( url_param('action', true) );?>
<? if($v) {
	 
	 include("snippets/".$v.'.php');
	 
	 
 }  else { ?>
<? 
$modules_options = array();
$modules_options['skip_admin'] = true;
$modules_options['ui'] = true;
 
$modules = get_modules($modules_options );
 

//

?>


<script type="text/javascript">

 Modules_List = {}

</script>

<ul class="modules-list">
  <? foreach($modules as $module2): ?>
  <?
		 $module_group2 = explode(DIRECTORY_SEPARATOR ,$module2['module']);
		 $module_group2 = $module_group2[0];
		?>
  <? if($module_group2 != $module_group)  : ?>
  <? if($module_group2 != $module_group)  : ?>
  <? $module2['module'] = str_replace('\\','/',$module2['module']); ?>
  <? $module2['module_clean'] = str_replace('/','__',$module2['module']); ?>
  <? $module2['name_clean'] = str_replace('/','-',$module2['module']); ?>
  <? $module2['name_clean'] = str_replace(' ','-',$module2['name_clean']); ?>
  <?php
    $module_id = $module2['module_clean'] . "_" . uniqid();
   ?>
  <li id="<?php print($module_id); ?>"  class="module-item">
  <script type="text/javascript">
     Modules_List['<?php print($module_id); ?>'] = {
       id:'<?php print($module_id); ?>',
       name:'<? print $module2["module"] ?>',
       title:'<? print $module2["name"] ?>',
       description:'<? print addslashes($module2["description"]) ?>',
       category:'<? print $module2["categories"]; ?>'
     }
  </script>
  <span class="mw_module_hold">
    <? if($module2['icon']): ?>


          <span class="mw_module_image">
            <span class="mw_module_image_shadow"></span>
            <img
                alt="<? print $module2['name'] ?>"
                title="<? print addslashes($module2['description']) ?>"
                class="module_draggable"
                data-module-name="<? print $module2['module'] ?>"
                src="<? print $module2['icon'] ?>"  />

          </span>



    <? endif; ?>
    <span class="module_name" alt="<? print addslashes($module2['description']) ?>"><? print $module2['name'] ?></span>
    </span>
    <!--    <div class="description"><? print $module2['description'] ?></div>--> 
  </li>
  <? endif; ?>
  <? endif; ?>
  <? endforeach; ?>
  <? foreach($modules as $module): ?>
  <?
 $module_group = explode(DIRECTORY_SEPARATOR ,$module['module']);
 $module_group = $module_group[0];
 $showed_module_groups = array();

?>
  <? if(!in_array($module_group, $showed_module_groups))  : ?>
  <? endif; ?>
  <?  $showed_module_groups[] = $module_group; ?>
  <? endforeach; ?>
</ul>
<?  }   ?>
