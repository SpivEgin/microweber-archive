<?

//p($params);



if(url_param('category') != false){
	if($params['category'] == false){
	
	$params['category'] = url_param('category');
	}
}


if(url_param('keyword') != false){
	if($params['keyword'] == false){
	
	$params['keyword'] = url_param('keyword');
	}
}
 
$posts = get_posts($params);
 //p($posts);
?>
<? if(!empty($posts)): ?>
<? $posts_list =$posts['posts'];  ?>
<? foreach($posts_list as $the_post):  ?>

<div class="post item" id="post_<? print $the_post['id'] ?>">
  <input type="hidden" class="checkselector" onclick="posts_categorize(this);" />
  <a href="<? print ADMIN_URL ?>/action:post_edit/id:<? print $the_post['id'] ?>" class="img"> <img src="<? print thumbnail($the_post['id'], 75) ?>" /> </a>
  <h2><? print $the_post['content_title'] ?> </h2>
  <div style="float:left; width:500px;">
    <? $c = CI::model ( 'taxonomy' )->getTaxonomiesForContent($the_post['id'], $taxonomy_type = 'categories'); 
 print character_limiter($the_post['content_description'] , 100) ;  

 ?>
  </div>
  <? //$stats = CI::model ( 'stats' )->get_visits_by_url(post_link($the_post['id'])); ?>
  <? $comments = CI::model ( 'comments' )->commentsGetCountForContentId(($the_post['id'])); ?>
  <div class="post_btns_holder"> <a class="xbtn" href="<? print  post_link($the_post['id']);  ?>" target="_blank">Read</a> <a class="xbtn" href="<? print ADMIN_URL ?>/action:post_edit/id:<? print $the_post['id'] ?>">Edit</a> <a class="xbtn" href="#" onclick="mw.content.del('<? print $the_post['id'] ?>','#post_<? print $the_post['id'] ?>');">Delete</a> </div>
  <div class="post_info">
    <div class="post_comments post_info_inner"><? print $comments ?></div>
    <div class="post_author post_info_inner"><? print user_name($the_post['created_by']) ?></div>
    <!-- 
   
     <div class="post_views post_info_inner"><? print $stats ?></div>
   <div class="post_title"><? print $the_post['content_title'] ?></div>
    <div class="post_id"><? print $the_post['id'] ?></div>-->
  </div>
</div>
<? endforeach; ?>
<? if($params['keyword'] == false):?>
<div class="paging">
  <? $i=1; foreach($posts['posts_pages_links']  as $paging): ?>
  <a href="<? print $paging; ?>" <?  if($posts['posts_pages_curent_page'] == $i): ?> class="active"  <? endif; ?>  ><? print $i ?></a>
  <? $i++; endforeach; ?>
</div>
<? endif; ?>
<? else: ?>
<? $curent_cat = url_param('category');
	if($curent_cat  !=  false){
	$curent_cat  = get_category($curent_cat );
	 $add_post_link = site_url('admin/action:post_edit/id:0').'/add_to_category:'.$curent_cat['id'];
	} else {
	 $add_post_link = site_url('admin/action:post_edit/id:0');	
		
	}
	?>
<div class="mw_admin_no_posts"> <strong>No posts found.</strong> <br />
  <br />
  <br />
  <a href="<? print $add_post_link ?>" class="sbm">Add new post</a> </div>
<? endif; ?>
