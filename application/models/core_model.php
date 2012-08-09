<?php

if (! defined ( 'BASEPATH' ))

	exit ( 'No direct script access allowed' );

	/**
 * Microweber
 *
 * An open source CMS and application development framework for PHP 5.1 or newer
 *
 * @package Microweber
 * @author Peter Ivanov
 * @copyright Copyright (c), Mass Media Group, LTD.
 * @license http://ooyes.net
 * @link http://ooyes.net
 * @since Version 1.0
 * @filesource
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 */

// ------------------------------------------------------------------------

/**
 * Core Class
 *
 * core class
 *
 * @access public
 * @category Core API
 * @subpackage Core
 * @author Peter Ivanov
 * @link http://ooyes.net
 */

class Core_model extends CI_Model {

	public $loaded_plugins = array ();

	public $running_plugins = array ();

	public $plugins_data = array ();

	public $path_list = array ();

	public $cache_storage = array ();

	public $cache_storage_decoded = array ();

	public $cache_storage_mem = array ();
	public $mw_cache_storage = array ();

	public $cache_storage_hits = array ();

	public $cache_storage_not_found = array ();

	public $cms_db_tables_search_fields = array ('content_title', "content_body", "content_url" );

	private $hash_pass;

	function __construct() {

		parent::__construct();

	}

	/**
	 * returns array that contains only keys that has the same names as the
	 * table fields from the database
	 *
	 * @param
	 *       	 string
	 * @param
	 *       	 array
	 * @return array
	 * @author Peter Ivanov
	 * @version 1.0
	 * @since Version 1.0
	 */

	function mapArrayToDatabaseTable($table, $array) {

		if (empty ( $array )) {

			return false;

		}

		$fields = $this->dbGetTableFields ( $table );

		foreach ( $fields as $field ) {

			$field = strtolower ( $field );

			if (array_key_exists ( $field, $array )) {

				if ($array [$field] != false) {

					// print ' ' . $field. ' <br>';
					$array_to_return [$field] = $array [$field];

				}

				if ($array [$field] == 0) {

					$array_to_return [$field] = $array [$field];

				}

			}

		}

		return $array_to_return;

	}

	/**
	 * Generic save data function, it saves data to the database
	 *
	 * @param
	 *       	 string
	 * @param
	 *       	 array
	 * @param
	 *       	 array
	 * @return string
	 * @author Peter Ivanov
	 * @version 1.0
	 * @since Version 1.0
	 */

	function saveData($table, $data, $data_to_save_options = false) {

		global $cms_db;

		global $cms_db_tables;

		if (is_array ( $data ) == false) {

			return false;

		}

		$data ['session_id'] = $this->session->userdata ( 'session_id' );

		$original_data = $data;

		$is_quick = isset ( $original_data ['quick_save'] );

		if ($is_quick == false) {
			if ($data ['updated_on'] == false) {

				$data ['updated_on'] = date ( "Y-m-d H:i:s" );

			}
		}

		if (! empty ( $data_to_save_options )) {

			if (! empty ( $data_to_save_options ['delete_cache_groups'] )) {

				foreach ( $data_to_save_options ['delete_cache_groups'] as $item ) {

					cache_clean_group ( $item );

				}

			}

		}

		$user_session = $this->session->userdata ( 'user_session' );

		if ($data ['cf_temp']) {
			$cf_temp = $data ['cf_temp'];

		}

		if ($data ['created_by']) {
			$the_user_id = $data ['created_by'];

			$the_user_id = $data ['created_by'];

		} else {
			$the_user_id = $this->userId ();
		}
		if ($data ['screenshot_url']) {
			$screenshot_url = $data ['screenshot_url'];
		}

		if ($data ['debug']) {
			$dbg = 1;
			unset ( $data ['debug'] );
		} else {

			$dbg = false;
		}

		if ($data ['queue_id'] != false) {
			$queue_id = $data ['queue_id'];
		}

		if ($data ['url'] == false) {
			$url = url ();
			$data ['url'] = $url;

		}

		if ($data ['user_ip'] == false) {

			$data ['user_ip'] = USER_IP;

		}

		// var_dump($data);
		if (intval ( $data ['id'] ) == 0) {

			if ($data ['created_on'] == false) {

				$data ['created_on'] = date ( "Y-m-d H:i:s" );

			}

			$data ['created_by'] = $the_user_id;

			$data ['edited_by'] = $the_user_id;

		} else {

			// $data ['created_on'] = false;
			$data ['edited_by'] = $the_user_id;

		}

		$criteria_orig = $criteria;

		$criteria = $this->mapArrayToDatabaseTable ( $table, $data );

		// p($original_data);p($criteria);die;
		if ($data_to_save_options ['do_not_replace_urls'] == false) {

			$criteria = $this->encodeLinksAndReplaceSiteUrl ( $criteria );

		}

		if ($data_to_save_options ['use_this_field_for_id'] != false) {

			$criteria ['id'] = $criteria_orig [$data_to_save_options ['use_this_field_for_id']];

		}

		$criteria = $this->addSlashesToArrayAndEncodeHtmlChars ( $criteria );
			$this->load->database();
		// p($criteria, 1);
		// $criteria = $this->addSlashesToArray ( $criteria );
		if (intval ( $criteria ['id'] ) == 0) {

			// insert
			$data = $criteria;

			// $this->db->insert ( $table, $data );
			$q = " INSERT INTO  $table set ";

			foreach ( $data as $k => $v ) {

				// $v
				if (strtolower ( $k ) != $data_to_save_options ['use_this_field_for_id']) {

					if (strtolower ( $k ) != 'id') {

						// $v =
						// $this->content_model->applyGlobalTemplateReplaceables
						// ( $v );
						// $v = htmlspecialchars ( $v, ENT_QUOTES );
						$q .= "$k = '$v' , ";

					}

				}

			}

			if (intval ( $original_data ['new_id'] ) != 0) {
				$n_id = $original_data ['new_id'];
			} else {
				$n_id = "NULL";
			}

			if ($data_to_save_options ['use_this_field_for_id'] != false) {

				$q .= " " . $data_to_save_options ['use_this_field_for_id'] . "={$n_id} ";

			} else {

				$q .= " id={$n_id} ";

			}


			// exit ();
			// $this->dbQ ( $q );
			// p($q

			$this->db->query ( $q );

			$id_to_return = $this->dbLastId ( $table );

		} else {

			if ($data ['is_active'] != 1) {

			}

			// update
			$data = $criteria;

			// $n = $this->db->update ( $table, $data, "id = {$data ['id']}" );
			$q = " UPDATE  $table set ";

			foreach ( $data as $k => $v ) {

				// $v = addslashes ( $v );
				// $v = htmlspecialchars ( $v, ENT_QUOTES );
				$q .= "$k = '$v' , ";

			}

			$q .= " id={$data ['id']} WHERE id={$data ['id']} ";

			$this->db->query ( $q );

			$id_to_return = $data ['id'];

		}




		if ($dbg != false) {
			p ( $q );
		}

		// find table assoc name
		// var_dump($cms_db_tables, $table);
		// var_dump($id_to_return);
		// exit;
		// print $id_to_return;

		// var_dump ( $cms_db_tables );
		if (! empty ( $cms_db_tables )) {

			foreach ( $cms_db_tables as $k => $v ) {

				// var_dump($k, $v);
				if (strtolower ( $table ) == strtolower ( $v )) {

					$table_assoc_name = $k;

				}

			}

		}

		if ($table_assoc_name == false) {

			$table_assoc_name = $this->dbGetAssocDbTableNameByRealName ( $table );

		}

		// p($original_data);
		/*
		 * if (!empty ( $original_data ['taxonomy_categories_str'] )) {
		 * p($original_data ['taxonomy_categories_str'] ,1); foreach (
		 * $original_data ['taxonomy_categories_str'] as $taxonomy_item ) {
		 * $test_if_exist_cat = get_category ( $taxonomy_item ); } }
		 */

		$taxonomy_table = $cms_db_tables ['table_taxonomy'];
		$taxonomy_items_table = $cms_db_tables ['table_taxonomy_items'];
		// p ( $original_data );
		if (is_array ( $original_data ['taxonomy_categories'] )) {

			$taxonomy_save = array ();

			$taxonomy_save ['to_table'] = $table_assoc_name;

			$taxonomy_save ['to_table_id'] = $id_to_return;

			$taxonomy_save ['taxonomy_type'] = 'category_item';

			if (trim ( $original_data ['content_type'] ) != '') {

				$taxonomy_save ['content_type'] = $original_data ['content_type'];

			}

			// $this->deleteData ( $taxonomy_table, $taxonomy_save, 'taxonomy'
			// );
			$q = " DELETE FROM  $taxonomy_items_table where to_table='$table_assoc_name' and to_table_id='$id_to_return' and  content_type='{$original_data ['content_type']}' and  taxonomy_type= 'category_item'     ";
			// p ( $q );
			$this->dbQ ( $q );

			foreach ( $original_data ['taxonomy_categories'] as $taxonomy_item ) {

				$taxonomy_item = trim ( $taxonomy_item );
				$parent_cat = get_category ( $taxonomy_item );

				$parent_cat_id = intval ( $parent_cat ['id'] );
				// var_dump($parent_cat);
				// $taxonomy_item = explode($taxonomy_item);

				// check if parent category exists
				// $taxonomy_item
				// $q = " SELECT * FROM $taxonomy_table where
				// id='$taxonomy_item' and taxonomy_value LIKE '$taxonomy_item'
				// and taxonomy_type= 'category' ";

				// $catcheck = $this->dbQuery ( $q );

				$q = " INSERT INTO  $taxonomy_items_table set to_table='$table_assoc_name', to_table_id='$id_to_return' , content_type='{$original_data ['content_type']}' ,  taxonomy_type= 'category_item' , parent_id='$parent_cat_id'   ";
				// p ( $q );
				$this->dbQ ( $q );
				cache_clean_group ( 'taxonomy/' . $parent_cat_id );

			}
			cache_clean_group ( 'taxonomy/global' );

			// exit ();
		}

		if (($original_data ['taxonomy_tags_csv']) != '') {

			/*
			 * $taxonomy_save = array (); $taxonomy_save ['to_table'] =
			 * $table_assoc_name; $taxonomy_save ['to_table_id'] =
			 * $id_to_return; $taxonomy_save ['taxonomy_type'] = 'tag'; if (trim
			 * ( $original_data ['content_type'] ) != '') { $taxonomy_save
			 * ['content_type'] = $original_data ['content_type']; }
			 * $this->deleteData ( $taxonomy_table, $taxonomy_save, 'taxonomy'
			 * ); $tags = explode ( ',', $original_data ['taxonomy_tags_csv'] );
			 * $tags = trimArray ( $tags ); $tags = array_unique_FULL ( $tags );
			 * asort ( $tags ); foreach ( $tags as $taxonomy_item ) {
			 * $taxonomy_item = trim ( $taxonomy_item ); if ($taxonomy_item !=
			 * '') { $q = " INSERT INTO $taxonomy_table set
			 * to_table='$table_assoc_name', to_table_id='$id_to_return' ,
			 * content_type='{$original_data ['content_type']}' , taxonomy_type=
			 * 'tag' , taxonomy_value='$taxonomy_item' "; $this->dbQ ( $q ); } }
			 */

		}

		// upload media
		if ($table_assoc_name != 'table_media') {
			if ($queue_id) {
				$this->mediaAfterUploadAssociatetheMediaQueueWithTheId ( $table_assoc_name, $id_to_return, $queue_id );

			}
		}

		if ($table_assoc_name != 'table_media') {

			if (strval ( $original_data ['media_queue_pictures'] ) != '') {

				$this->mediaAfterUploadAssociatetheMediaQueueWithTheId ( $table_assoc_name, $id_to_return, $original_data ['media_queue_pictures'] );

			}

			if (strval ( $original_data ['media_queue_videos'] ) != '') {

				$this->mediaAfterUploadAssociatetheMediaQueueWithTheId ( $table_assoc_name, $id_to_return, $original_data ['media_queue_videos'] );

			}

			if (strval ( $original_data ['media_queue_files'] ) != '') {

				$this->mediaAfterUploadAssociatetheMediaQueueWithTheId ( $table_assoc_name, $id_to_return, $original_data ['media_queue_files'] );

			}

			//

			$this->mediaUpload ( $table_assoc_name, $id_to_return );

			$this->mediaUploadVideos ( $table_assoc_name, $id_to_return );

			$this->mediaUploadFiles ( $table_assoc_name, $id_to_return );

			// $this->mediaFixOrder ( $to_table, $to_table_id, 'picture' );

			// $this->mediaFixOrder ( $to_table, $to_table_id, 'video' );

			// $this->mediaFixOrder ( $to_table, $to_table_id, 'file' );

			// var_dump ( $id_to_return );
			// exit ();
		} else {

			$this->mediaUpload ( $table_assoc_name, $id_to_return );

		}

		if (strval ( $screenshot_url ) != '') {
			$this->mediaUploadByUrl ( $screenshot_url, $table_assoc_name, $id_to_return );
		}

		// adding custom fields

		if (($original_data ['skip_custom_field_save'] == false) and $table_assoc_name != 'table_custom_fields') {

			$custom_field_to_save = array ();

			foreach ( $original_data as $k => $v ) {





				if (stristr ( $k, 'custom_field_' ) == true) {

					// if (strval ( $v ) != '') {
					$k1 = str_ireplace ( 'custom_field_', '', $k );

					if (trim ( $k ) != '') {

						$custom_field_to_save [$k1] = $v;

					}

					// }
				}

			}

			if(is_array($original_data['custom_fields']) and !empty($original_data['custom_fields'])){
				$custom_field_to_save = array_merge($custom_field_to_save,$original_data['custom_fields'] );
			}




			if ($cf_temp) {
				$custom_field_table = $cms_db_tables ['table_custom_fields'];

				$up_cf = " update $custom_field_table
					set
					to_table ='{$table_assoc_name}',

						to_table_id ='{$id_to_return}'

					where

						to_table_id ='{$cf_temp}' or to_table='{$cf_temp}'


						";

				$this->dbQ ( $up_cf );

				cache_clean_group ( 'custom_fields' );

			}

			if (! empty ( $custom_field_to_save )) {
				// p($is_quick);
				$custom_field_table = $cms_db_tables ['table_custom_fields'];
				if ($is_quick == false) {

					// clean

					$custom_field_to_delete ['to_table'] = $table_assoc_name;

					$custom_field_to_delete ['to_table_id'] = $id_to_return;

					// $this->deleteData ( $custom_field_table,
					// $custom_field_to_delete, 'custom_fields' );

					// $clean = " delete from $custom_field_table where to_table
					// is null or to_table_id is null ";

					// $this->dbQ ( $clean );

				}
			//	p($original_data);
				if (($original_data ['skip_custom_field_save'] == false)){
				foreach ( $custom_field_to_save as $cf_k => $cf_v ) {

					if (($cf_v != '')) {

						if ($cf_k != '') {
							$clean = " delete from $custom_field_table where
						to_table ='{$table_assoc_name}'
						and
						to_table_id ='{$id_to_return}'
						and
						custom_field_name ='{$cf_k}'


						";
						//	p($clean);
							$this->dbQ ( $clean );
						}

						$custom_field_to_save ['custom_field_name'] = $cf_k;
						if (is_array ( $cf_v )) {
							$custom_field_to_save ['custom_field_values'] = base64_encode ( json_encode ( $cf_v ) );
						}
						$custom_field_to_save ['custom_field_value'] = $cf_v;

						$custom_field_to_save ['to_table'] = $table_assoc_name;

						$custom_field_to_save ['to_table_id'] = $id_to_return;
						$custom_field_to_save ['skip_custom_field_save'] = true;

						$custom_field_to_save ['delete_cache_groups'] = array ('custom_fields' );
						// p($custom_field_table);
						// p($custom_field_to_save);
					//	 $save = $this->saveData ( $custom_field_table, $custom_field_to_save );


						 $custom_field_to_save = $this->addSlashesToArrayAndEncodeHtmlChars ( $custom_field_to_save );
						 $add = " insert into $custom_field_table set
						custom_field_name =\"{$cf_k}\",
						custom_field_values =\"".$custom_field_to_save['custom_field_values']."\",
						custom_field_value =\"".$custom_field_to_save['custom_field_value']."\",
				 to_table =\"".$custom_field_to_save['to_table']."\",
				 to_table_id =\"".$custom_field_to_save['to_table_id']."\"
						";

 
							$this->dbQ ( $add );



					}

				}
			 	cache_clean_group ( 'custom_fields' );
			//	cache_clean_group ( 'global' );
				cache_clean_group ( 'extract_tags' );
			}
		}
		}

		if (intval ( $data ['edited_by'] ) == 0) {

			$user_session = $this->session->userdata ( 'user_session' );

			$data ['edited_by'] = $user_session ['user_id'];

		}

		// p($aUserId,1);
		// var_dump ( $table_assoc_name );
		// p ( $data ['edited_by'], 1 );
		if (strval ( $table_assoc_name ) != '') {

			if (intval ( $data ['edited_by'] ) != 0) {

				$to_execute_query = false;

				global $users_log_exclude;

				global $users_log_include;

				if (empty ( $users_log_include )) {

					// ..p($users_log_exclude,1);
					if (empty ( $users_log_exclude )) {

						$to_execute_query = true; // be careful
					} else {

						if (in_array ( $table_assoc_name, $users_log_exclude ) == true) {

							$to_execute_query = false;

						} else {

							$to_execute_query = true;

						}

					}

					if ($table_assoc_name == 'table_users_notifications') {

						$to_execute_query = false;

					}

					if ($table_assoc_name == 'table_custom_fields') {

						$to_execute_query = false;

					}

					if ($table_assoc_name == 'table_media') {

						$to_execute_query = false;

					}

					if ($table_assoc_name == 'bb_forums') {

						$to_execute_query = false;

					}

				} else {

					if (in_array ( $table_assoc_name, $users_log_include ) == true) {

						$to_execute_query = true;

					} else {

						$to_execute_query = false;

					}

				}

				if ($to_execute_query == true) {

					// @todo later: funtionality and documentation to move the
					// log in seperate database cause of possible load issues on
					// social networks created witm microweber

					if ($table_assoc_name == 'table_votes') {

					}

					$rel_table = $data ['to_table'];
					$rel_table_id = $data ['to_table_id'];

					if ($rel_table == false) {
						$rel_table = $table_assoc_name;
					}

					if ($rel_table_id == false) {
						$rel_table_id = $id_to_return;
					}

					global $cms_db_tables;

					$by = intval ( $data ['edited_by'] );
					$by2 = intval ( $data ['created_by'] );

					$now = date ( "Y-m-d H:i:s" );

					$session_id = $this->session->userdata ( 'session_id' );

					$users_table = $cms_db_tables ['table_users_log'];

					$q = " INSERT INTO   $users_table set ";

					$q .= "  created_on ='{$now}', user_id={$by}, ";

					$q .= "  to_table_id={$id_to_return}, ";

					$q .= "  to_table='{$table_assoc_name}' ,";

					$q .= "  rel_table='{$rel_table}', ";

					$q .= "  rel_table_id={$rel_table_id} ,";
					$q .= "  edited_by={$by} ,";
					$q .= "  created_by={$by2} ,";

					$q .= "  session_id='{$session_id}' , ";

					$q .= "  is_read='n' , ";

					$q .= "  user_ip='{$_SERVER['REMOTE_ADDR']}'";

					// p( $q);
					//$this->dbQ ( $q );

					//$dir = cache_get_dir ( 'log/global/' );
					//@touch ( $dir . 'skip_cache.php' );

					// p($dir);

				}

			}

		}

		cache_clean_group ( 'global' );

		return intval ( $id_to_return );

	}

	function addSlashesToArray($arr) {

		if (! empty ( $arr )) {

			$ret = array ();

			foreach ( $arr as $k => $v ) {

				$ret [$k] = addslashes ( $v );

			}

			return $ret;

		}

	}

	function removeSlashesFromArray($arr) {

		if (! empty ( $arr )) {

			$ret = array ();

			foreach ( $arr as $k => $v ) {

				// $v = htmlspecialchars_decode ( $v, ENT_QUOTES );
				$ret [$k] = stripslashes ( $v );

			}

			return $ret;

		}

	}

	function encodeLinksAndReplaceSiteUrl($arr) {

		$site = site_url ();

		if (! empty ( $arr )) {

			$ret = array ();

			foreach ( $arr as $k => $v ) {

				if (is_array ( $v )) {

					$v = $this->encodeLinksAndReplaceSiteUrl ( $v );

				} else {

					$v = str_ireplace ( $site, '{SITE_URL}', $v );

					// $v = addslashes ( $v );
					// $v = htmlspecialchars ( $v, ENT_QUOTES, 'UTF-8' );
				}

				$ret [$k] = ($v);

			}

			return $ret;

		}

	}

	function decodeLinksAndReplaceSiteUrl($arr) {

		$site = site_url ();

		// exit($site);
		if (! empty ( $arr )) {

			$ret = array ();

			foreach ( $arr as $k => $v ) {

				if (is_array ( $v ) == true) {

					$v = $this->decodeLinksAndReplaceSiteUrl ( $v );

				} else {

					// var_dump( $v);
					$v = str_ireplace ( '{SITE_URL}', $site, $v );

					// var_dump( $v);
					// exit;
					// $v = addslashes ( $v );
					// $v = htmlspecialchars ( $v, ENT_QUOTES, 'UTF-8' );
				}

				$ret [$k] = ($v);

			}

			return $ret;

		} else {

			return false;

		}

	}

	function addSlashesToArrayAndEncodeHtmlChars($arr) {

		if (! empty ( $arr )) {

			$ret = array ();

			foreach ( $arr as $k => $v ) {

				if (is_array ( $v )) {

					$v = $this->addSlashesToArrayAndEncodeHtmlChars ( $v );

				} else {
					// $v =utfString( $v );
					// $v =
					// preg_replace("/[^[:alnum:][:space:][:alpha:][:punct:]]/","",$v);

					$v = addslashes ( $v );
					// $v = htmlentities ( $v, ENT_NOQUOTES, 'UTF-8' );
					// $v = htmlspecialchars ( $v );
					$v = htmlspecialchars ( $v );

				}

				$ret [$k] = ($v);

			}

			return $ret;

		}

	}

	function removeSlashesFromArrayAndDecodeHtmlChars($arr) {

		if (! empty ( $arr )) {

			$ret = array ();

			foreach ( $arr as $k => $v ) {

				if (is_array ( $v )) {

					$v = $this->removeSlashesFromArrayAndDecodeHtmlChars ( $v );

				} else {

					$v = htmlspecialchars_decode ( $v );
					// $v = htmlspecialchars_decode ( $v );
					// $v = html_entity_decode ( $v, ENT_NOQUOTES );
					// $v = html_entity_decode( $v );
					$v = stripslashes ( $v );

				}

				$ret [$k] = $v;

			}

			return $ret;

		}

	}

	/**
	 * sql
	 *
	 * @author Peter Ivanov
	 */

	function sqlQuery($sql_q, $cache_group = false) {

		$result = $this->dbQuery ( $sql_q );

		return $result;

	}

	/**
	 * save data
	 *
	 * @author Peter Ivanov
	 */

	function dbLastId($table) {

		$q = "SELECT LAST_INSERT_ID() as the_id FROM $table";

		$q = $this->db->query ( $q );

		$result = $q->row_array ();

		// var_dump($result);
		return intval ( $result ['the_id'] );

	}

	function dbCheckIfIdExistsInTable($table, $id = 0) {

		$q = "SELECT count(*) as qty from $table where id= $id";

		$q = $this->dbQuery ( $q );

		$q = $q [0] ['qty'];

		$q = intval ( $q );

		if ($q > 0) {

			return true;

		} else {

			return false;

		}

	}

	function dbGetRealDbTableNameByAssocName($assoc_name) {

		global $cms_db_tables;

		if (! empty ( $cms_db_tables )) {

			foreach ( $cms_db_tables as $k => $v ) {

				// var_dump($k, $v);
				if (strtolower ( $assoc_name ) == strtolower ( $k )) {

					// $table_assoc_name = $k;
					return $v;

				}

			}

			return $assoc_name;

		}

	}

	function dbGetAssocDbTableNameByRealName($assoc_name) {

		global $cms_db_tables;

		if (! empty ( $cms_db_tables )) {

			foreach ( $cms_db_tables as $k => $v ) {

				// var_dump($k, $v);
				if (strtolower ( $assoc_name ) == strtolower ( $v )) {

					// $table_assoc_name = $k;
					return $k;

				}

			}

			return $assoc_name;

		}

	}

	/**
	 * Gets all field names from a DB table
	 *
	 * @param $table string
	 *       	 - table name
	 * @param $exclude_fields array
	 *       	 - fields to exclude
	 * @return array
	 * @author Peter Ivanov
	 * @version 1.0
	 * @since Version 1.0
	 */

	function dbGetTableFields($table, $exclude_fields = false) {

		if (! $table) {

			return false;

		}

		$function_cache_id = false;

		$args = func_get_args ();

		foreach ( $args as $k => $v ) {

			$function_cache_id = $function_cache_id . serialize ( $k ) . serialize ( $v );

		}

		$function_cache_id = __FUNCTION__ . crc32 ( $function_cache_id );

		$cache_content = $this->cacheGetContentAndDecode ( $function_cache_id, 'db' );

		if (($cache_content) != false) {

			return $cache_content;

		}

		$table = $this->dbGetRealDbTableNameByAssocName ( $table );

		$sql = "show columns from $table";
		$this->load->database();
		// var_dump($sql );
		$query = $this->db->query ( $sql );

		$fields = $query->result_array ();

		$exisiting_fields = array ();

		foreach ( $fields as $fivesdraft ) {

			$fivesdraft = array_change_key_case ( $fivesdraft, CASE_LOWER );

			$exisiting_fields [strtolower ( $fivesdraft ['field'] )] = true;

		}

		// var_dump ( $exisiting_fields );
		$fields = array ();

		foreach ( $exisiting_fields as $k => $v ) {

			if (! empty ( $exclude_fields )) {

				if (in_array ( $k, $exclude_fields ) == false) {

					$fields [] = $k;

				}

			} else {

				$fields [] = $k;

			}

		}

		$this->core_model->cacheWriteAndEncode ( $fields, $function_cache_id, $cache_group = 'db' );

		// $fields = (array_change_key_case ( $fields, CASE_LOWER ));
		return $fields;

	}

	function dbCheckExistTable($table) {

		if (! $table) {

			return false;

		}

		$sql = "show tables like '$table'";

		// var_dump($sql );
		$query = $this->db->query ( $sql );

		$res = $query->result_array ();

		if (! empty ( $res ))

			return true;

		return false;

	}

	function dbRegexpSearch($table, $string, $only_in_ids = false, $only_in_those_table_fields = false) {

		$function_cache_id = serialize ( $table ) . serialize ( $string ) . serialize ( $only_in_ids );

		$function_cache_id = __FUNCTION__ . crc32 ( __FUNCTION__ . $function_cache_id );

		$cache_content = $this->cacheGetContentAndDecode ( $function_cache_id );

		if (($cache_content) != false) {

			return $cache_content;

		}

		$fields = $this->dbGetTableFields ( $table );

		if (! empty ( $only_in_those_table_fields )) {

			$fields = $only_in_those_table_fields;

		}

		// var_dump ( $fields );
		// exit ();

		// var_dump($string, $string1, $string2, $stringg );
		// exit;
		$q = "SELECT id FROM $table ";

		$where = false;

		if (! empty ( $fields )) {

			$where = " WHERE ";

			$string = str_replace ( "\\", ' ', $string );

			$string_array = explode ( '+', $string );

			$string = str_replace ( "+", ' ', $string );

			$string1 = string_cyr2lat ( $string );

			$string2 = string_lat2cyr ( $string );

			$string = mysql_real_escape_string ( $string );

			$string1 = mysql_real_escape_string ( $string1 );

			$string2 = mysql_real_escape_string ( $string2 );

			foreach ( $fields as $item ) {

				$where .= "$item REGEXP '$string' OR ";

			}

			if ($string2 != false) {

				foreach ( $fields as $item ) {

					$where .= "$item REGEXP '$string2' OR ";

				}

			}

			if ($string1 != false) {

				foreach ( $fields as $item ) {

					$where .= "$item REGEXP '$string1' OR ";

				}

			}

			$where .= "  id='fake id' OR ";

			$where .= " id REGEXP '$string' ";

			if (! empty ( $string_array )) {

				$where = $where . " OR ";

				foreach ( $string_array as $string ) {

					$string = str_replace ( "\\", ' ', $string );

					$string1 = string_cyr2lat ( $string );

					$string2 = string_lat2cyr ( $string );

					$string = mysql_real_escape_string ( $string );

					$string1 = mysql_real_escape_string ( $string1 );

					$string2 = mysql_real_escape_string ( $string2 );

					foreach ( $fields as $item ) {

						$where .= "$item REGEXP '$string' OR ";

					}

					if ($string2 != false) {

						foreach ( $fields as $item ) {

							$where .= "$item REGEXP '$string2' OR ";

						}

					}

					if ($string1 != false) {

						foreach ( $fields as $item ) {

							$where .= "$item REGEXP '$string1' OR ";

						}

					}

				}

				$where .= " id IS NULL    ";

			}

		}

		if ($where != false) {

			$q = $q . $where;

		}

		if (! empty ( $only_in_ids )) {

			$ids_q = implode ( ',', $only_in_ids );

			$q = $q . "and id in ($ids_q) ";

		}

		$q = $q . " group by id limit 0,100  ";

		// print $q;
		// exit;

		$result = $this->dbQuery ( $q );

		if (empty ( $result )) {

			return false;

		} else {

			$ids = array ();

			foreach ( $result as $item ) {

				if (! empty ( $only_in_ids )) {

					if (in_array ( $item ['id'], $only_in_ids ) == true) {

						$ids [] = $item ['id'];

					}

				} else {

					$ids [] = $item ['id'];

				}

			}

		}

		$ids = array_unique ( $ids );

		if (! empty ( $ids )) {

			$this->cacheWriteAndEncode ( $ids, $function_cache_id, $cache_group = 'global' );

			return $ids;

		} else {

			return false;

		}

	}

	function dbQ($q) {

		global $cms_db;

		global $cache;

		global $cms_db_tables;

		if (trim ( $q ) == '') {
			return false;
		}

		if(!is_callable($this->db)){
		$this->load->database();
		}
		// $this->db->query ( 'SET NAMES utf8' );
		// $this->db->query ( $q );
		$this->db->query ( $q );

		return true;

	}

	function dbExtractIdsFromArray($array) {

		if (empty ( $array )) {

			return false;

		} else {

			$ids = array ();

			foreach ( $array as $item ) {

				$ids [] = $item ['id'];

			}

		}

		$ids = array_unique ( $ids );

		return $ids;

	}

	function dbQuery($q, $cache_id = false, $cache_group = 'global', $time = false) {
		if (trim ( $q ) == '') {
			return false;
		}

		if ($cache_id != false) {

			$results = $this->cacheGetContentAndDecode ( $cache_id, $cache_group, $time );
			if ($results != false) {
				if ($results == '---empty---') {
					return false;
				} else {
					return $results;
				}
			}

		}
		$this->load->database();
		$q = $this->db->query ( $q );
		if (empty ( $q )) {
			if ($cache_id != false) {
				$this->cacheWriteAndEncode ( '---empty---', $cache_id, $cache_group );
			}
			return false;
		}

		$result = $q->result_array ();
		if ($cache_id != false) {
			if (! empty ( $result )) {
				$this->cacheWriteAndEncode ( $result, $cache_id, $cache_group );
			} else {
				$this->cacheWriteAndEncode ( '---empty---', $cache_id, $cache_group );

			}
		}

		return $result;

	}

	function saveHistory($data = array()) {

		$table = $data ['table'];
		$id = $data ['id'];
		$value = $data ['value'];
		$field = $data ['field'];
		$full_path = $data ['full_path'];
 				$field = str_replace(':', '_', $field);
				 				$id = str_replace(':', '_', $id);
				
 
		// copy for hiustory
		$today = date ( 'Y-m-d H-i-s' );

		if ($full_path == false) {
			$history_dir = HISTORY_DIR . $table . '/' . $id . '/' . $field . '/';
			$history_dir = normalize_path ( $history_dir );
	
		} else {
	
		
			$history_dir = dirname ( $full_path );
			$history_dir = normalize_path ( $history_dir );
		}
	
	
	
	
	
	
	
	
	$history_dir = str_replace('..', '_', $history_dir);
		$dir = $history_dir;
		$pattern = '\.(php)$';
		
			if (is_dir ( $history_dir ) == false) {
			mkdir_recursive ( $history_dir );

		}

		$newstamp = 0;
		$newname = "";

		$file_counter_to_keep = 0;

		$dc = opendir ( $dir );
		while ( $fn = readdir ( $dc ) ) {

			// if ($file_counter < 1000) {

			// Eliminate current directory, parent directory
		//	if (ereg ( '^\.{1,2}$', $fn ))
			//	continue;

				// Eliminate other pages not in pattern
			//if (! ereg ( $pattern, $fn ))
			//	continue;
			$timedat = filemtime ( "$dir/$fn" );
			if ($timedat > $newstamp) {
				$newstamp = $timedat;
				$newname = $fn;
			}

			// }
		}
		// $timedat is the time for the latest file
		// $newname is the name of the latest file
		// p($newname);

		$newest = @file_get_contents ( "$dir/$newname" );
		if (trim ( $value ) != '') {
			if ($newest != $value) {

				touch ( HISTORY_DIR . 'index.php' );

				$hf = $history_dir . $today . '.php';
				// p($hf);
				$value = html_entity_decode ( $value );

				//$data = mb_convert_encoding ( $value, 'UTF-8', 'OLD-ENCODING' );
				file_put_contents ( $hf, $value );

				// file_put_contents ( $hf, $value );
			} else {
				// print 'skip';
			}
		}

	}

	function getHistoryFiles($data = array()) {

		$table = $data ['table'];
		$id = $data ['id'];
		$value = $data ['value'];
		$field = $data ['field'];
		if ($table == false) {
			$table = 'global';
		}
				$field = str_replace(':', '_', $field);
								 				$id = str_replace(':', '_', $id);
				
			
		// copy for hiustory
		$today = date ( 'Y-m-d H-i-s' );
		$history_dir = HISTORY_DIR . $table . '/' . $id . '/' . $field . '/';
		$history_dir = normalize_path ( $history_dir );
	$history_dir = str_replace('..', '_', $history_dir);
	
	
	

	
	
	
	
	
			//	$history_dir = str_replace(':', '_', $history_dir);
								$history_dir = str_replace('^', '_', $history_dir);
		if ($history_dir == false) {
			mkdir_recursive ( $history_dir );
		}

		// p($history_dir);
		$his = array ();
		$file_counter = 0;

		$filez = glob ( $history_dir . "*.php" );
		if (! empty ( $filez )) {
			$filez = array_reverse ( $filez );
			foreach ( $filez as $filename ) {

				if ($file_counter < 200) {

					$size = filesize ( $filename );
					// p($size);
					if (intval ( $size ) != 0) {
						$his [] = $filename;
					} else {
						print $filename;
						@unlink ( $filename );
					}
				} else {
					@unlink ( $filename );
				}

				$file_counter ++;

			}
		}
		if (! empty ( $his )) {
			// $his = array_reverse($his);
		}
		return $his;
	}

	function saveCustomFieldConfig($data_to_save) {

		global $cms_db_tables;

		$table_custom_field = $cms_db_tables ['table_custom_fields_config'];

		if ($data_to_save ['for']) {
			$data_to_save ['to_table'] = $this->guessDbTable ( $data_to_save ['for'] );
		}

		// p($data_to_save);

		if (intval ( $data_to_save ['post_id'] ) != 0) {
			if (($data_to_save ['param'])) {

				$q = " delete from  $table_custom_field where
								 post_id = '{$data_to_save ['post_id']}'
								 and param='{$data_to_save ['param']}'
								   ";

				// p($q);

				// $q = $this->dbQ ( $q );

			}
		}

		if (intval ( $data_to_save ['post_id'] ) != 0) {
			if (($data_to_save ['id'])) {
				$data_to_save ['id'] = intval ( $data_to_save ['id'] );
				$q = " select * from  $table_custom_field where
								 id = '{$data_to_save ['id']}'	   ";

				// p($q);

				$q = $this->dbQuery ( $q );
				if (empty ( $q [0] )) {

					$q = " INSERT INTO  $table_custom_field set
								 id = '{$data_to_save ['id']}'	   ";

					// p($q);

					$q = $this->dbQ ( $q );
				}

			}
		}

		$save = $this->saveData ( $table_custom_field, $data_to_save );

		cache_clean_group ( 'custom_fields' );

		return $save;

	}



function saveCustomField($data_to_save) {

		global $cms_db_tables;

		$table_custom_field = $cms_db_tables ['table_custom_fields'];

		if ($data_to_save ['for']) {
			$data_to_save ['to_table'] = $this->guessDbTable ( $data_to_save ['for'] );
		}

		// p($data_to_save);

		if (intval ( $data_to_save ['post_id'] ) != 0) {
			if (($data_to_save ['param'])) {

				$q = " delete from  $table_custom_field where
								 post_id = '{$data_to_save ['post_id']}'
								 and param='{$data_to_save ['param']}'
								   ";

				// p($q);

				// $q = $this->dbQ ( $q );

			}
		}

		if (intval ( $data_to_save ['post_id'] ) != 0) {
			if (($data_to_save ['id'])) {
				$data_to_save ['id'] = strval ( $data_to_save ['id'] );
				$q = " select * from  $table_custom_field where
								 id = '{$data_to_save ['id']}'	   ";

				// p($q);

				$q = $this->dbQuery ( $q );
				if (empty ( $q [0] )) {

					$q = " INSERT INTO  $table_custom_field set
								 id = '{$data_to_save ['id']}'	   ";

					// p($q);

					$q = $this->dbQ ( $q );
				}

			}
		}
		
		
		if (is_array ( $data_to_save ['custom_field_value']  )) {
			
			$array =  $data_to_save ['custom_field_value'] ;
			array_walk_recursive($array, function(&$item, $key) {
            if(is_string($item)) {
                //$item = decodeUnicodeString($item);
            }
        });
        $json = json_encode($array);
     //  p($json);
			//$d = base64_encode ( json_encode ( $data_to_save ['custom_field_value']   ) );
		//	$d = base64_encode ( $json );
			$d = base64_encode ( serialize ( $data_to_save ['custom_field_value']   ) );
			
			$data_to_save ['custom_field_values'] = $d;
			
							//$data_to_save ['custom_field_values'] = base64_encode ( json_encode ( $data_to_save ['custom_field_value']   ) );
						}




		$save = $this->saveData ( $table_custom_field, $data_to_save );

		cache_clean_group ( 'custom_fields' );

		return $save;

	}



	function getCustomFieldsConfig($get) {

		global $cms_db_tables;
		$cache_group = 'custom_fields';
		$table_custom_field = $cms_db_tables ['table_custom_fields_config'];

		$orderby = array ('field_order', 'asc' );

		$get = $this->getDbData ( $table_custom_field, $get, false, false, $orderby, $cache_group, $debug = false, $ids = false, $count_only = false, $only_those_fields = false, $exclude_ids = false, $force_cache_id = false, $get_only_whats_requested_without_additional_stuff = true );

		return $get;

	}

	function deleteCustomFieldById($id) {

		$id = intval ( $id );

		if ($id == 0) {

			return false;

		}
		global $cms_db_tables;
		$custom_field_table = $cms_db_tables ['table_custom_fields'];
		$custom_field_to_delete = array ();
		$custom_field_to_delete ['id'] = $id;

		$id = $this->deleteData ( $custom_field_table, $custom_field_to_delete, 'custom_fields' );
		print $id;

	}

	function getCustomFieldById($id) {
		$id = intval ( $id );

		if ($id == 0) {

			return false;

		}

		global $cms_db_tables;
		$table_custom_field = $cms_db_tables ['table_custom_fields'];
		$q = " SELECT *  from  $table_custom_field  where	id={$id}";

		$cache_id = __FUNCTION__ . '_' . src32 ( $q );

		 

		$q = $this->dbQuery ( $q, $cache_id, 'custom_fields/' . $id );

		if (! empty ( $q [0] )) {
			return $q [0];
		}
	}
	/**
	 * Converts an object into an array
	 *
	 * @param $object type
	 * @return type
	 */
	protected function objectToArray($object) {
		if (is_object ( $object )) {
			foreach ( $object as $key => $value ) {
				if (is_object ( $value )) {
					$array [$key] = $this->objectToArray ( $value );
				} else {
					$array [$key] = $value;
				}
			}
		} else if (is_array ( $object )) {
			foreach ( $object as $key => $value ) {
				$array [$key] = $this->objectToArray ( $value );
			}
		} else {
			$array = $object;
		}
		return $array;
	}
	function getCustomFields($table, $id = 0, $return_full = false, $field_for = false, $debug = false) {

		//$id = intval ( $id );
		 
		
		
		

		if ($id == 0) {

		//	return false;

		}
		
		// $id = addslashes( $id );
	//	$id = addslashes($id, '%_');
		

		global $cms_db_tables;
if($table != false){
		$table_assoc_name = $this->dbGetAssocDbTableNameByRealName ( $table );


} else {
	
	$table_assoc_name = "MW_ANY_TABLE";
}




		$table_custom_field = $cms_db_tables ['table_custom_fields'];

		$the_data_with_custom_field__stuff = array ();

		if (strval ( $table_assoc_name ) != '') {
 

				if ($field_for != false) {
					$field_for = trim ( $field_for );
					$field_for_q = " and  (field_for='{$field_for} OR custom_field_name='{$field_for}')'";
				} else {
					$field_for_q = " ";
				}

			if($table_assoc_name == 'MW_ANY_TABLE'){
				
				
				$qt = '';
				
			} else {
				$qt = "to_table = '{$table_assoc_name}' and";
			}

		 if ($return_full == true) {
		 	
			$select_what = '*';
		 } else {
		 	$select_what = '*';
			
		 }

				$q = " SELECT
								 {$select_what} from  $table_custom_field where
								 {$qt}
								  to_table_id='{$id}'
								 $field_for_q
								 order by field_order asc  ";



if($debug != false){
	p($q );
}


				$cache_id = __FUNCTION__ . '_' . crc32 ( $q );

			 
			 
				
				$q = $this->dbQuery ( $q, $cache_id, 'custom_fields' );
				// $q = $this->dbQuery ( $q );
				// p($q);
				if (! empty ( $q )) {


 


					if ($return_full == true) {
						$to_ret = array ();
						foreach ( $q as $it ) {

							// $it ['value'] = $it ['custom_field_value'];
							$it ['value'] = $it ['custom_field_value'];
							$it ['values'] = $it ['custom_field_value'];
							if (strtolower ( trim ( $it ['custom_field_value'] ) ) == "array" or trim ( $it ['custom_field_values'] ) != '') {
								if ($it ['custom_field_values']) {
									// $it ['custom_field_values'] = str_replace
									// ( '&quot;', '"', $it
									// ['custom_field_values'] );
									// $it ['custom_field_values'] =
									// html_entity_decode($it
									// ['custom_field_values']);

									$a1 = (base64_decode ( $it ['custom_field_values'] ));
									// p ( $a1 );
									$a1 = stripslashes ( $a1 );
									// p ( $a1 );
									//$a1 = json_decode ( $a1 );
									$a1 = unserialize ( $a1 );
									//$a1 = $this->objectToArray ( $a1 );
									// p ( $a1 );
									// p($it ['custom_field_values']);
									// $a = unserialize( base64_decode ( $it
									// ['custom_field_values'] ) );
									$a = $a1;
									// p ( $a );
									$it ['values'] = $a;
									$it ['value'] = $a;
									$it ['custom_field_values'] = $a;

									// $it ['value'] = $it
									// ['custom_field_values'];
								}
							}
							$it ['cssClass'] = $it ['custom_field_type'];
							$it ['type'] = $it ['custom_field_type'];

							$it ['baseline'] = "undefined";

							$it ['title'] = $it ['custom_field_name'];
							$it ['required'] = $it ['custom_field_required'];

							$to_ret [] = $it;
						}
						return $to_ret;

					}

					$append_this = array ();

					foreach ( $q as $q2 ) {

						$i = 0;

						$the_name = false;

						$the_val = false;

						foreach ( $q2 as $cfk => $cfv ) {

							if ($cfk == 'custom_field_name') {

								$the_name = $cfv;

							}

							if ($cfk == 'custom_field_value') {

								$the_val = $cfv;

							}

							$i ++;

						}

						if ($the_name != false and $the_val != false) {
							if ($return_full == false) {
								$the_data_with_custom_field__stuff [$the_name] = $the_val;
							} else {
								$cf_cfg = array ();
								$cf_cfg ['name'] = $the_name;
								$cf_cfg = $this->getCustomFieldsConfig ( $cf_cfg );
								if (! empty ( $cf_cfg )) {
									$cf_cfg = $cf_cfg [0];
									$q2 ['config'] = $cf_cfg;
								}

								$the_data_with_custom_field__stuff [$the_name] = $q2;
							}

						}

					}

				}

		 

		}

		$result = $the_data_with_custom_field__stuff;
		$result = (array_change_key_case ( $result, CASE_LOWER ));
		return $result;

	}

	function getById($table, $id = 0, $is_this_field = false) {

		$id = intval ( $id );

		if ($id == 0) {

			return false;

		}

		if ($is_this_field == false) {
			$is_this_field = "id";
		}

		$table = $this->dbGetRealDbTableNameByAssocName ( $table );

		$q = "SELECT * from $table where {$is_this_field}=$id limit 1";

		$q = $this->dbQuery ( $q );

		$q = $q [0];

		// /$q = intval ( $q );

		if (count ( $q ) > 0) {

			return $q;

		} else {

			return false;

		}

	}

	/**
	 * get data from the database
	 *
	 * @author Peter Ivanov
	 * @version 1.0
	 * @since Version 1.0
	 */

	function getDbData2($options_array) {

		if (! $options_array ['get_only_whats_requested_without_additional_stuff']) {

			$options_array ['get_only_whats_requested_without_additional_stuff'] = true;

		}

		$data = $this->getDbData ( $table = $options_array ['table'], $criteria = $options_array ['criteria'], $limit = $options_array ['limit'], $offset = $options_array ['offset'], $orderby = $options_array ['orderby'], $cache_group = $options_array ['cache_group'], $debug = $options_array ['debug'], $ids = $options_array ['ids'], $count_only = $options_array ['count_only'], $only_those_fields = $options_array ['only_those_fields'], $exclude_ids = $options_array ['exclude_ids'], $force_cache_id = $options_array ['force_cache_id'], $get_only_whats_requested_without_additional_stuff = $options_array ['get_only_whats_requested_without_additional_stuff'] );

		return $data;

	}

	/**
	 * get data from the database this is the MOST important function in the
	 * Microweber CMS.
	 * Everything relies on it.
	 *
	 * @author Peter Ivanov
	 * @version 1.0
	 * @since Version 1.0
	 */

	function getDbData($table = false, $criteria = false, $limit = false, $offset = false, $orderby = false, $cache_group = false, $debug = false, $ids = false, $count_only = false, $only_those_fields = false, $exclude_ids = false, $force_cache_id = false, $get_only_whats_requested_without_additional_stuff = false) {

		global $cms_db;

		global $cms_db_tables;

		// $this->db->query ( 'SET NAMES utf8' );
		if ($table == false) {

			return false;

		}
		if (! empty ( $cms_db_tables )) {

			foreach ( $cms_db_tables as $k => $v ) {

				// var_dump($k, $v);
				if (strtolower ( $table ) == strtolower ( $v )) {

					$table_assoc_name = $k;

				}

			}

		}

		$aTable_assoc = $this->dbGetAssocDbTableNameByRealName ( $table );

		if (! empty ( $criteria )) {
			if ($criteria ['debug'] == true) {
				$debug = true;
				if (is_string ( $criteria ['debug'] )) {
					$criteria ['debug'] = false;
				} else {
					unset ( $criteria ['debug'] );
				}
			}
			if ($criteria ['cache_group'] == true) {
				$cache_group = $criteria ['cache_group'];
			}
			if ($criteria ['no_cache'] == true) {
				$cache_group = false;
				if (is_string ( $criteria ['no_cache'] )) {
					$criteria ['no_cache'] = false;
				} else {
					unset ( $criteria ['no_cache'] );
				}
			}

			if ($criteria ['count_only'] == true) {
				$count_only = $criteria ['count_only'];

				unset ( $criteria ['count_only'] );

			}

			if ($criteria ['count'] == true) {
				$count_only = $criteria ['count'];

				unset ( $criteria ['count'] );

			}

			if ($criteria ['get_count'] == true) {
				$count_only = true;

				unset ( $criteria ['get_count'] );

			}

			if ($criteria ['count'] == true) {
				$count_only = $criteria ['count'];

				unset ( $criteria ['count'] );

			}

			if ($criteria ['with_pictures'] == true) {
				$with_pics = true;
			}

			if ($criteria ['limit'] == true and $count_only == false) {
				$limit = $criteria ['limit'];
			}
			if ($criteria ['limit']) {
				$limit = $criteria ['limit'];
			}

			if ($criteria ['items_per_page'] and $criteria ['curent_page'] == false) {
				// $limit = array (0, $criteria ['items_per_page'] );
			}

			$curent_page = isset ( $criteria ['curent_page'] ) ? $criteria ['curent_page'] : null;
			if ($curent_page == false) {
				$curent_page = isset ( $criteria ['page'] ) ? $criteria ['page'] : null;
			}

			$offset = isset ( $criteria ['offset'] ) ? $criteria ['offset'] : false;

			if ($limit == false) {
				$limit = isset ( $criteria ['limit'] ) ? $criteria ['limit'] : false;
			}
			if ($offset == false) {
				$offset = isset ( $criteria ['offset'] ) ? $criteria ['offset'] : false;
			}

			if ($count_only == false) {

				if ($limit == false) {

					$qLimit = "";

					if ($items_per_page == false) {

						$items_per_page = 30;

					}

					$items_per_page = intval ( $items_per_page );

					if (intval ( $curent_page ) < 1) {

						$curent_page = 1;

					}

					$page_start = ($curent_page - 1) * $items_per_page;

					$page_end = ($page_start) + $items_per_page;

					$temp = $page_end - $page_start;

					if (intval ( $temp ) == 0) {

						$temp = 1;

					}

					$qLimit .= "LIMIT {$temp} ";

					if (($offset) == false) {

						$qLimit .= "OFFSET {$page_start} ";

					}

				}
				$limit_from_paging_q = $qLimit;

			}

			if ($debug) {
				// p($limit_from_paging_q);
				// p($limit);
			}

			if ($criteria ['fields'] == true) {
				$only_those_fields = $criteria ['fields'];
				if (is_string ( $criteria ['fields'] )) {
					$criteria ['fields'] = false;
				} else {
					unset ( $criteria ['fields'] );
				}
			}

		}
		if (! empty ( $criteria )) {
		foreach ( $criteria as $fk => $fv ) {
			if (strstr ( $fk, 'custom_field_' ) == true) {

				$addcf = str_ireplace ( 'custom_field_', '', $fk );

				// $criteria ['custom_fields_criteria'] [] = array ($addcf => $fv );

				$criteria ['custom_fields_criteria'] [$addcf] =   $fv  ;

			}
		}
		}
		if (! empty ( $criteria ['custom_fields_criteria'] )) {

			$table_custom_fields = $cms_db_tables ['table_custom_fields'];

			$only_custom_fieldd_ids = array ();

			$use_fetch_db_data = true;

			$ids_q = "";

			if (! empty ( $ids )) {

				$ids_i = implode ( ',', $ids );

				$ids_q = " and to_table_id in ($ids_i) ";

			}

			$only_custom_fieldd_ids = array ();
			// p($data ['custom_fields_criteria'],1);
			foreach ( $criteria ['custom_fields_criteria'] as $k => $v ) {

				if (is_array ( $v ) == false) {

					$v = addslashes ( $v );
				$v = html_entity_decode(  $v );
								$v = urldecode(  $v );

				}
				$is_not_null = false;
				if ($v == 'IS NOT NULL') {
					$is_not_null = true;
				}

				$k = addslashes ( $k );

				if (! empty ( $category_content_ids )) {

					$category_ids_q = implode ( ',', $category_content_ids );

					$category_ids_q = " and to_table_id in ($category_ids_q) ";

				} else {

					$category_ids_q = false;

				}

				$only_custom_fieldd_ids_q = false;

				if (! empty ( $only_custom_fieldd_ids )) {

					$only_custom_fieldd_ids_i = implode ( ',', $only_custom_fieldd_ids );

					$only_custom_fieldd_ids_q = " and to_table_id in ($only_custom_fieldd_ids_i) ";

				}
				if ($is_not_null == true) {
					$cfvq = "custom_field_value IS NOT NULL  ";
				} else {
					$cfvq = "custom_field_value LIKE '$v'  ";
				}
				$q = "SELECT  to_table_id from $table_custom_fields where

            to_table = '$aTable_assoc' and

            custom_field_name = '$k' and

            $cfvq

             $ids_q   $only_custom_fieldd_ids_q


             $my_limit_q

             order by field_order asc

                    ";

				$q2 = $q;
			// p($q);
				$q = $this->core_model->dbQuery ( $q, md5 ( $q ), 'custom_fields' );
				//


				if (! empty ( $q )) {

					$ids_old = $ids;

					$ids = array ();

					foreach ( $q as $itm ) {

						$only_custom_fieldd_ids [] = $itm ['to_table_id'];

						// if(in_array($itm ['to_table_id'],$category_ids)==
						// false){

						$includeIds [] = $itm ['to_table_id'];

						// }

						//

					}

				} else {

					// $ids = array();

					$remove_all_ids = true;

					$includeIds = false;

					$includeIds [] = '0';

					$includeIds [] = 0;

				}

			}

		}

		$original_cache_group = $cache_group;

		if (! empty ( $criteria ['only_those_fields'] )) {

			$only_those_fields = $criteria ['only_those_fields'];

			// unset($criteria['only_those_fields']);
			// no unset xcause f cache
		}

		if (! empty ( $criteria ['include_taxonomy'] )) {

			$include_taxonomy = true;

		} else {

			$include_taxonomy = false;

		}

		if (! empty ( $criteria ['exclude_ids'] )) {

			$exclude_ids = $criteria ['exclude_ids'];

			// unset($criteria['only_those_fields']);
			// no unset xcause f cache
		}

		if (! empty ( $criteria ['ids'] )) {
			foreach ( $criteria ['ids'] as $itm ) {

				$includeIds [] = $itm;

			}
		}

		$to_search = false;

		if ($criteria ['keyword']) {
			if ($criteria ['search_by_keyword'] == false) {
				$criteria ['search_by_keyword'] = $criteria ['keyword'];
			}
		}

		if ($criteria ['keywords']) {
			if ($criteria ['search_by_keyword'] == false) {
				$criteria ['search_by_keyword'] = $criteria ['keywords'];
			}
		}

		if ($criteria ['search_keyword']) {
			if ($criteria ['search_by_keyword'] == false) {
				$criteria ['search_by_keyword'] = $criteria ['search_keyword'];
			}
		}

		if ($criteria ['search_in_fields']) {
			if ($criteria ['search_by_keyword_in_fields'] == false) {
				$criteria ['search_by_keyword_in_fields'] = $criteria ['search_in_fields'];
			}
		}

		if (strval ( trim ( $criteria ['search_by_keyword'] ) ) != '') {

			$to_search = trim ( $criteria ['search_by_keyword'] );

			// p($to_search,1);
		}

		if (is_array ( ($criteria ['search_by_keyword_in_fields']) )) {

			if (! empty ( $criteria ['search_by_keyword_in_fields'] )) {

				$to_search_in_those_fields = $criteria ['search_by_keyword_in_fields'];

			}

		}

		// if ($count_only == false) {
	 // var_dump ( $cache_group );
		if ($cache_group != false) {

			$cache_group = trim ( $cache_group );

			$start_time = mktime ();

			if ($force_cache_id != false) {

				$cache_id = $force_cache_id;

				$function_cache_id = $force_cache_id;

			} else {

				$function_cache_id = false;

				$args = func_get_args ();

				foreach ( $args as $k => $v ) {

					$function_cache_id = $function_cache_id . serialize ( $k ) . serialize ( $v );

				}

				$function_cache_id = __FUNCTION__ . crc32 ( $function_cache_id );

				$cache_id = $function_cache_id;

			}

			$original_cache_id = $cache_id;

			$cache_content = $this->cacheGetContentAndDecode ( $original_cache_id, $original_cache_group );

			if ($cache_group == 'taxonomy') {

				//
			}

			if (($cache_content) != false) {

				if ($cache_content == '---empty---') {

					return false;

				}

				if ($count_only == true) {

					$ret = $cache_content [0] ['qty'];

					return $ret;

				} else {

					return $cache_content;

				}

			}

		}

		if (! empty ( $orderby )) {

			$order_by = " ORDER BY  {$orderby[0]}  {$orderby[1]}  ";

		} else {

			$order_by = false;

		}

		if ($qLimit == '' and ! empty ( $limit ) and $count_only == false) {

			$offset = $limit [1] - $limit [0];

			$limit = " limit  {$limit[0]} , $offset  ";

		} else {

			$limit = false;

		}

		$criteria = $this->mapArrayToDatabaseTable ( $table, $criteria );

		if (! empty ( $criteria )) {

			// $query = $this->db->get_where ( $table, $criteria, $limit,
			// $offset );
		} else {

			// $query = $this->db->get ( $table, $limit, $offset );
		}

		if ($only_those_fields == false) {

			$q = "SELECT * FROM $table ";

		} else {

			if (is_array ( $only_those_fields )) {

				if (! empty ( $only_those_fields )) {

					$flds = implode ( ',', $only_those_fields );

					$q = "SELECT $flds FROM $table ";

				} else {

					$q = "SELECT * FROM $table ";

				}

			} else {

				$q = "SELECT * FROM $table ";

			}

		}

		if ($count_only == true) {

			$q = "SELECT count(*) as qty FROM $table ";

		}

		$where = false;




		if (is_array ( $ids )) {

			if (! empty ( $ids )) {

				$idds = false;

				foreach ( $ids as $id ) {

					$id = intval ( $id );

					$idds .= "   OR id=$id   ";

				}

				$idds = "  and ( id=0 $idds   ) ";

			} else {

				$idds = false;

			}

		}

		if (! empty ( $exclude_ids )) {

			$first = array_shift ( $exclude_ids );

			$exclude_idds = false;

			foreach ( $exclude_ids as $id ) {

				$id = intval ( $id );

				$exclude_idds .= "   AND id<>$id   ";

			}

			$exclude_idds = "  and ( id<>$first $exclude_idds   ) ";

		} else {

			$exclude_idds = false;

		}

		if (! empty ( $includeIds )) {

			// $first = array_shift ( $includeIds );

			$includeIds_idds = false;
			// p ( $includeIds );
			// p($includeIds);

			$includeIds_i = implode ( ',', $includeIds );

			$includeIds_idds .= "   AND id IN ($includeIds_i)   ";

		} else {

			$includeIds_idds = false;

		}




		if ($to_search != false) {

			$fieals = $this->dbGetTableFields ( $table );

			$where_post = ' OR ';

			if (! $where) {

				$where = " WHERE ";

			}
			$where_q = '';

			foreach ( $fieals as $v ) {

				$add_to_seachq_q = true;

				if (! empty ( $to_search_in_those_fields )) {

					if (in_array ( $v, $to_search_in_those_fields ) == false) {

						$add_to_seachq_q = false;

					}

				}

				if ($add_to_seachq_q == true) {

					if ($v != 'id' && $v != 'password') {

						// $where .= " $v like '%$to_search%' " . $where_post;

						$where_q .= " $v REGEXP '$to_search' " . $where_post;

						// 'new\\*.\\*line';

						// $where .= " MATCH($v) AGAINST ('*$to_search* in
						// boolean mode') " . $where_post;

					}

				}

			}


			$where_q = rtrim ( $where_q, ' OR ' );

			if($includeIds_idds != false){
			$where = $where.'  (' .$where_q.')' .$includeIds_idds;
			} else {

							$where = $where.$where_q;

			}





		} else {

			if (! empty ( $criteria )) {

				if (! $where) {

					$where = " WHERE ";
				}
				foreach ( $criteria as $k => $v ) {
					$compare_sign = '=';
					if (stristr ( $v, '[lt]' )) {
						$compare_sign = '<=';
						$v = str_replace ( '[lt]', '', $v );
					}

					if (stristr ( $v, '[mt]' )) {

						$compare_sign = '>=';

						$v = str_replace ( '[mt]', '', $v );
					}
					/*
					 * var_dump ( $k ); var_dump ( $v ); print '<hr>';
					 */
					if (($k == 'updated_on') or ($k == 'created_on')) {

						$v = strtotime ( $v );
						$v = date ( "Y-m-d H:i:s", $v );
					}

					$where .= "$k {$compare_sign} '$v' AND ";

				}
				if ($table_assoc_name != 'table_comments') {
					if ($with_pics == true) {
						$table_media = $cms_db_tables ['table_media'];
						$where .= " id in (select to_table_id from $table_media where to_table='$table_assoc_name'   )     AND ";
					}
				}

				$where .= " ID is not null ";

			} else {

				$where = " WHERE ";

				$where .= " ID is not null ";

			}

		}



		if ($where != false) {

			$q = $q . $where . $idds . $exclude_idds;

		} else {
			$q = $q . " WHERE " . $idds . $exclude_idds;
		}
		if ($includeIds_idds != false) {
			$q = $q . $includeIds_idds;
		}
		if ($count_only != true) {
			$q .= " group by ID  ";
		}
		if ($order_by != false) {

			$q = $q . $order_by;

		}

		if (trim ( $limit_from_paging_q ) != "") {
			$limit = $limit_from_paging_q;
		} else {

		}
		if ($limit != false) {

			$q = $q . $limit;

		}

		if ($debug == true) {

			var_dump ( $table, $q );

		}
		// var_dump ( $table, $q );
		// print $q;
		// exit;
		// $select = $this->db->select()->from("$table"),array('product_id',
		// 'product_name')) ->limit(10, 20);
		// $select = $this->db->select ()->from ( "$table" ) . eval($where);

		// $stmt = $this->db->query ( $q );
		// $result = $stmt->fetchAll ();

		$result = $this->dbQuery ( $q );
		if ($count_only == true) {

			// var_dump ( $result );
			// exit ();
		}

		if ($result [0] ['qty'] == true) {

			// p($result);
			$ret = $result [0] ['qty'];

			return $ret;

		}

		if ($only_those_fields == false) {

			if ($count_only == false) {

				if (! empty ( $result )) {

					if (count ( $result ) < 2) {

						$table_custom_field = $cms_db_tables ['table_custom_fields'];

						if (strval ( $table_assoc_name ) != 'table_custom_fields') {

							if (strval ( trim ( $table_assoc_name ) ) != '') {

								if (strval ( $table_assoc_name ) == 'table_content') {

									$this_cache_id = __FUNCTION__ . 'custom_fields_stuff' . md5 ( serialize ( $result ) );

									$this_cache_content = $this->cacheGetContentAndDecode ( $this_cache_id );

									// $this_cache_content = false;
									if (($this_cache_content) != false) {

										$result = $this_cache_content;

									} else {

										$the_data_with_custom_field__stuff = array ();

										foreach ( $result as $item ) {

											if (strval ( $table_assoc_name ) != '') {

												if (intval ( $item ['id'] ) != 0) {

													$q = " SELECT
								 * from  $table_custom_field where
								 to_table = '$table_assoc_name'
								 and to_table_id={$item['id']}

								 order by field_order asc
								   ";

													// print $q;
													$cache_id = __FUNCTION__ . 'custom_fields_stuff' . md5 ( $q );

													$cache_id = md5 ( $cache_id );

													$q = $this->dbQuery ( $q );

													if (! empty ( $q )) {

														$append_this = array ();

														foreach ( $q as $q2 ) {

															$i = 0;

															$the_name = false;

															$the_val = false;

															foreach ( $q2 as $cfk => $cfv ) {

																if ($cfk == 'custom_field_name') {

																	$the_name = $cfv;

																}

																if ($cfk == 'custom_field_value') {

																	$the_val = $cfv;

																}

																$i ++;

															}

															if ($the_name != false and $the_val != false) {

																$append_this [$the_name] = $the_val;

															}

														}

														// var_dump (
														// $append_this );
														$item ['custom_fields'] = $append_this;

													}

												}

											}

											$the_data_with_custom_field__stuff [] = $item;

										}

										$result = $the_data_with_custom_field__stuff;

										// var_dump($result);
										$this->cacheWriteAndEncode ( $result, $this_cache_id, $cache_group = 'global' );

									}

								}

							}

						}

					}

				}

			}

		}

		$result = $this->decodeLinksAndReplaceSiteUrl ( $result );

		if ($table_assoc_name != 'table_options') {

			// todo

			if ($get_only_whats_requested_without_additional_stuff == false) {

				if ($only_those_fields == false) {

					// print $table_assoc_name;
					if (strval ( $table_assoc_name ) != '') {

						if ($table_assoc_name != 'table_media') {

							$result_with_media = array ();

							if (! empty ( $result )) {

								if (count ( $result ) == 1) {

									foreach ( $result as $item ) {

										// get media
										if (intval ( $item ['id'] ) != 0) {

											$media = $this->mediaGetAndCache ( $table_assoc_name, $item ['id'] );

											if (! empty ( $media )) {

												$item ['media/global'] = $media;

											}

										}

										$result_with_media [] = $item;

									}

									$result = $result_with_media;

								}

							}

						}

					}

					if ($table_assoc_name != 'table_taxonomy') {

						// todo
					}

				}

			}

		}


		if ($cache_group != false) {

			if (! empty ( $result )) {

				// p($original_cache_group);
				// p($cache_id);
				$this->cacheWriteAndEncode ( $result, $original_cache_id, $original_cache_group );

			} else {

				$this->cacheWriteAndEncode ( '---empty---', $original_cache_id, $original_cache_group );

			}

		}



		// var_dump($result);
		if ($count_only == true) {

			$ret = $result [0] ['qty'];

			return $ret;

		}

		$return = array ();

		if (! empty ( $result )) {

			foreach ( $result as $k => $v ) {

				$v = $this->removeSlashesFromArrayAndDecodeHtmlChars ( $v );

				$return [$k] = $v;

			}

		}

		// var_dump ( $return );
		return $return;

	}

	/**
	 * ***************************************************
	 * Get data from a given database table using filter array and formatting
	 * result according to specified options
	 *
	 * @param $aTable string
	 * @param $aFilter array|string
	 *       	 Criteria using for filtering data
	 * @param $aOptions array
	 *       	 Assocative array. Allowed options are:
	 *       	 string cache_group,
	 *       	 bool debug,
	 *       	 bool cache,
	 *       	 bool get_count,
	 *       	 array only_fields,
	 *       	 array only_fields,
	 *       	 array include_ids,
	 *       	 array exclude_ids,
	 *       	 string search_keyword,
	 *       	 arra order - the array could be one or multi dimensional. It
	 *       	 also have format ('col1' => 'ASC'|'DESC'),
	 *       	 int limit,
	 *       	 int offset,
	 * @return array
	 *
	 * @example Get all active users and use with id 1000.
	 *          Database query string will be printed.
	 *          Result will be cached in group 'users'.
	 *          Users will be orderd descending by id and ascending by email
	 *
	 *          $this->core_model->fetchDbData(
	 *          'firecms_users',
	 *          array(
	 *          array('is_active', 'y'),
	 *          array('id', 10000, '=', 'OR')
	 *          ),
	 *          array(
	 *          'debug' => true,
	 *          'cache_group' => 'users',
	 *          'order' => array(array('id', 'DESC'), array('email', 'ASC')),
	 *          )
	 *
	 * @example Get all site users.
	 *          Result will not be cached.
	 *          Users will be orderd ascending by email.
	 *          Users with id 1, 2 and 3 will be appended to result set.
	 *          Users with id 4, 5 and 6 will not be included in result set.
	 *          $this->core_model->fetchDbData(
	 *          'firecms_users',
	 *          array(
	 *          array('is_admin', 'n'),
	 *          ),
	 *          array(
	 *          'include_ids' => array(1, 2, 3),
	 *          'exclude_ids' => array(5, 6),
	 *          'cache' => false,
	 *          'order' => array('email', 'ASC'),
	 *          )
	 *
	 *
	 *          Options:
	 *
	 *          $aOptions = array();
	 *          //general
	 *          $aOptions['only_fields'] = array('id', 'content_title'); //
	 *          array of fields
	 *          $aOptions['get_params_from_url'] = true; // if true tries to get
	 *          params from the url
	 *          $aOptions['items_per_page'] = 50; // return items per page
	 *          $aOptions['debug'] = false; // if true it will print debug info
	 *          $aOptions['cache'] = true; // if true it will use cache!
	 *          Important: you must define the cache group
	 *          $aOptions['cache_group'] = 'global'; // if set it will save the
	 *          cache output in subfolder
	 *          $aOptions['get_count'] = true; // if true will return only count
	 *          of results
	 *          $aOptions['group_by'] = 'field name'; // if set the results will
	 *          be grouped by the filed name
	 *
	 *          //search options
	 *          $aOptions ['search_keyword'] = 'test' //search in the db
	 *          $aOptions ['search_keyword_only_in_those_fields'] = array('id',
	 *          'content_body'); //search in the db only in those fields
	 *
	 *
	 *
	 *          //@todo document more options here!
	 *
	 *
	 *
	 *
	 *
	 */

	function fetchDbData($aTable, $aFilter = null, $aOptions = null) {

		global $cms_db;

		global $cms_db_tables;

		$countColumn = '_total_';

		$cacheGroup = isset ( $aOptions ['cache_group'] ) ? $aOptions ['cache_group'] : null;
		if ($cacheGroup == null) {
			$cacheGroup = isset ( $aFilter ['cache_group'] ) ? $aFilter ['cache_group'] : null;
		}

		$debugQuery = isset ( $aOptions ['debug'] ) ? $aOptions ['debug'] : null;
		if ($debugQuery == null) {
			$debugQuery = isset ( $aFilter ['debug'] ) ? $aFilter ['debug'] : null;
		}

		$enableCache = isset ( $aOptions ['cache'] ) ? $aOptions ['cache'] : null;
		if ($enableCache == null) {
			$enableCache = isset ( $aFilter ['cache'] ) ? $aFilter ['cache'] : null;
		}

		$getCount = isset ( $aOptions ['get_count'] ) ? $aOptions ['get_count'] : null;

		if ($getCount == null) {
			$getCount = isset ( $aFilter ['count_only'] ) ? $aFilter ['count_only'] : null;
		}

		$onlyFields = isset ( $aOptions ['only_fields'] ) ? $aOptions ['only_fields'] : null;
		if ($onlyFields == null) {
			$onlyFields = isset ( $aFilter ['only_fields'] ) ? $aFilter ['only_fields'] : null;
		}

		$includeIds = isset ( $aOptions ['include_ids'] ) ? $aOptions ['include_ids'] : null;
		if ($includeIds == null) {
			$includeIds = isset ( $aFilter ['include_ids'] ) ? $aFilter ['include_ids'] : null;
		}

		if ($includeIds == null) {
			$includeIds = isset ( $aFilter ['ids'] ) ? $aFilter ['ids'] : null;
		}

		$includeIdsField = isset ( $aOptions ['include_ids_field'] ) ? $aOptions ['include_ids_field'] : null;
		if ($includeIdsField == null) {
			$includeIdsField = isset ( $aFilter ['include_ids_field'] ) ? $aFilter ['include_ids_field'] : null;
		}

		$excludeIds = isset ( $aOptions ['exclude_ids'] ) ? $aOptions ['exclude_ids'] : null;
		if ($excludeIds == null) {
			$excludeIds = isset ( $aFilter ['exclude_ids'] ) ? $aFilter ['exclude_ids'] : null;
		}

		$execQuery = isset ( $aOptions ['query'] ) ? $aOptions ['query'] : null;
		if ($execQuery == null) {
			$execQuery = isset ( $aFilter ['query'] ) ? $aFilter ['query'] : null;
		}

		$excludeIdsField = isset ( $aOptions ['exclude_ids_field'] ) ? $aOptions ['exclude_ids_field'] : null;

		if ($excludeIdsField == null) {
			$excludeIdsField = isset ( $aFilter ['exclude_ids_field'] ) ? $aFilter ['exclude_ids_field'] : null;
		}

		$items_per_page = isset ( $aOptions ['items_per_page'] ) ? $aOptions ['items_per_page'] : null;

		if ($items_per_page == null) {
			$items_per_page = isset ( $aFilter ['items_per_page'] ) ? $aFilter ['items_per_page'] : null;
		}

		$return_only_ids = ($aOptions ['return_only_ids']) ? $return_only_ids = true : $return_only_ids = false;

		if ($return_only_ids == null) {
			$return_only_ids = isset ( $aFilter ['return_only_ids'] ) ? $aFilter ['return_only_ids'] : null;
		}

		$get_params_from_url = ($aOptions ['get_params_from_url']) ? $get_params_from_url = true : $get_params_from_url = false;

		if ($get_params_from_url == null) {
			$get_params_from_url = isset ( $aFilter ['get_params_from_url'] ) ? $aFilter ['get_params_from_url'] : null;
		}

		// search
		$searchKeyword = isset ( $aOptions ['search_keyword'] ) ? $aOptions ['search_keyword'] : null;

		if ($searchKeyword == null) {
			$searchKeyword = isset ( $aFilter ['search_keyword'] ) ? $aFilter ['search_keyword'] : null;
		}

		$searchKeyword_in_those_fields = isset ( $aOptions ['search_keyword_only_in_those_fields'] ) ? $aOptions ['search_keyword_only_in_those_fields'] : null;

		if ($searchKeyword_in_those_fields == null) {
			$searchKeyword_in_those_fields = isset ( $aFilter ['search_keyword_only_in_those_fields'] ) ? $aFilter ['search_keyword_only_in_those_fields'] : null;
		}

		$orderBy = isset ( $aOptions ['order'] ) ? $aOptions ['order'] : null;

		$groupBy = isset ( $aOptions ['group_by'] ) ? $aOptions ['group_by'] : null;

		$limit = isset ( $aOptions ['limit'] ) ? $aOptions ['limit'] : null;

		$curent_page = isset ( $aOptions ['curent_page'] ) ? $aOptions ['curent_page'] : null;
		if ($curent_page == false) {
			$curent_page = isset ( $aOptions ['page'] ) ? $aOptions ['page'] : null;
		}
		if ($curent_page == false) {
			$curent_page = isset ( $aFilter ['page'] ) ? $aFilter ['page'] : null;
		}

		if (($curent_page == false) and $items_per_page == false) {
			$offset = isset ( $aOptions ['offset'] ) ? $aOptions ['offset'] : null;

			if ($limit == false) {
				$limit = isset ( $aFilter ['limit'] ) ? $aFilter ['limit'] : null;
			}
			if ($offset == false) {
				$offset = isset ( $aFilter ['offset'] ) ? $aFilter ['offset'] : null;
			}
		}
		//
		//

		if ($enableCache == true) {

			$function_cache_id = false;

			$args = func_get_args ();

			foreach ( $args as $k => $v ) {

				$function_cache_id = $function_cache_id . serialize ( $k ) . serialize ( $v );

			}

			$function_cache_id = __FUNCTION__ . crc32 ( $function_cache_id ) . md5 ( $aFilter ) . md5 ( $aOptions );

			if ($get_params_from_url == true) {

				$function_cache_id = $function_cache_id . md5 ( getCurentURL () );

			}

			$cache_id = $function_cache_id;

			$cache_content = $this->cacheGetContent ( $cache_id, $cacheGroup );

			if (strval ( $cache_content ) != '') {

				// $cache = base64_decode ( $cache_content );
				$cache = unserialize ( $cache_content );

				$return = array ();

				if (! empty ( $cache )) {

					foreach ( $cache as $k => $v ) {

						$v = $this->removeSlashesFromArrayAndDecodeHtmlChars ( $v );

						$return [$k] = $v;

					}

					if (! empty ( $return )) {

						if ($getCount == true) {

							$ret = $return [0] [$countColumn];

							return $ret;

						} else {

							return $return;

						}

					}

				}

			}

		}

		if ($execQuery != false) {
			if ($debugQuery == true) {

				p ( '------------------------------------' );

				p ( nl2br ( $aTable . ":\n" . $execQuery ) );

				p ( '------------------------------------' );

			}

			$result = $this->dbQuery ( $execQuery, __FUNCTION__ . crc32 ( $execQuery ), $cacheGroup );
			return $result;

		}

		if (! $getCount) {

			$qLimit = "";

			if (empty ( $limit )) {

				if ($get_params_from_url == true) {

					if ($items_per_page == false) {

						$items_per_page = $this->core_model->optionsGetByKey ( 'default_items_per_page' );

					}

					$items_per_page = intval ( $items_per_page );

					if ($curent_page == false) {

						$curent_page = $this->getParamFromURL ( 'curent_page' );

					}

					if (intval ( $curent_page ) < 1) {

						$curent_page = 1;

					}

					$page_start = ($curent_page - 1) * $items_per_page;

					$page_end = ($page_start) + $items_per_page;

					$temp = $page_end - $page_start;

					if (intval ( $temp ) == 0) {

						$temp = 1;

					}

					$qLimit .= "LIMIT {$temp} ";

					if (($offset) == false) {

						$qLimit .= "OFFSET {$page_start} ";

					}

					// $qLimit .= "LIMIT {$page_start}, {$page_end}";
				}

			}

			/*
			 * ~~~~~~~~~~~~~ Build limit part ~~~~~~~~~~~~~
			 */
			if (empty ( $limit )) {
				// $limit = array(0,2);
			}
			if (! empty ( $limit )) {

				if (count ( $limit ) == 1) {

					$qLimit .= "LIMIT {$limit}";

				} else {

					// $qLimit .= "LIMIT {$limit[0]} {$limit[1]}";
					$page_end = intval ( $limit [1] );

					$page_start = intval ( $limit [0] );

					$temp = $page_end - $page_start;

					if (intval ( $temp ) == 0) {

						$temp = 1;

					}

					$qLimit .= "LIMIT {$temp} ";

					if (($offset) == false) {

						$qLimit .= "OFFSET {$page_start} ";

					}

				}

			}

			/*
			 * ~~~~~~~~~~~~~ Build offset part ~~~~~~~~~~~~~
			 */

			$qOffset = "";

			if (($offset) != false) {

				$qOffset .= "OFFSET {$offset}";

			}

		}

		$aTable_assoc = $this->dbGetAssocDbTableNameByRealName ( $aTable );

		// $aOptions = $this->core_model->mapArrayToDatabaseTable($aTable,
		// $aOptions);

		// $aOptions = $this->core_model->mapArrayToDatabaseTable($aTable,
		// $aOptions);
		/*
		 * ~~~~~~~~~~~~~ Build select part ~~~~~~~~~~~~~
		 */

		$qSelect = "SELECT\n\t";

		$qHaving_a = array ();

		if ($getCount) {

			$qSelect .= "count(*) as {$countColumn}";

		} else {

			if ($return_only_ids == false) {

				if (! empty ( $onlyFields ) && is_array ( $onlyFields )) {

					$qSelect .= implode ( ",\n\t", $onlyFields );

				} else {

					$qSelect .= "*";

				}

			} else {

				$qSelect .= " id ";

			}

		}

		/*
		 * ~~~~~~~~~~~~~ Build where part ~~~~~~~~~~~~~
		 */

		$wheres = array ();

		$all_table_fields = $this->dbGetTableFields ( $aTable );

		$aTable_assoc = $this->dbGetAssocDbTableNameByRealName ( $aTable );

		/*
		 * if ($searchKeyword) { $fields = array_diff ( $this->dbGetTableFields
		 * ( $aTable ), array ('id', 'password' ) ); foreach ( $fields as $field
		 * ) { $wheres [] = array ("{$field} LIKE '%$searchKeyword%'", "OR" ); }
		 * }
		 */

		if ($get_params_from_url == true) {

			if ($searchKeyword == false) {

				$searchKeyword = $this->core_model->getParamFromURL ( 'keyword' );

			}

		}

		if ($searchKeyword != false) {

			$kwq_wheres = array ();

			// $fields = array_diff ( $this->dbGetTableFields ( $aTable ), array
			// ('id', 'password' ) );
			if (! empty ( $searchKeyword_in_those_fields )) {
				$arr = $searchKeyword_in_those_fields;
			} else {
				$arr = ($this->cms_db_tables_search_fields);
			}
			$arr2 = $all_table_fields;

			foreacH ( $arr as $fld123z ) {

				if (in_array ( $fld123z, $arr2 )) {

					$fields [] = $fld123z;

				}

			}

			$fields = $all_table_fields;

			// exit;
			// $fields = array_diff ($arr2, array('content_title',
			// "content_body", "content_url") );
			$kwq = " and ID in ";

			$searchKeyword = $this->input->xss_clean ( $searchKeyword );

			// $searchKeyword = $this->db->escape ( $searchKeyword );

			// p($fields);
			foreach ( $fields as $field ) {

				if (! empty ( $searchKeyword_in_those_fields )) {

					if (in_array ( $field, $searchKeyword_in_those_fields )) {
						// $kwq_wheres [] = " {$field} LIKE '%$searchKeyword%'
						// OR";
						// $kwq_wheres [] = "MATCH (`{$field}`) AGAINST
						// ('*$searchKeyword*' in boolean mode) OR ";
						$kwq_wheres [] = " {$field} REGEXP '$searchKeyword' OR";
					}
				} else {
					// $kwq_wheres [] = " {$field} LIKE '%$searchKeyword%' OR";

					// $kwq_wheres [] = "MATCH (`{$field}`) AGAINST
					// ('*$searchKeyword*' in boolean mode) OR ";
					$kwq_wheres [] = " {$field} REGEXP '$searchKeyword' OR";

				}

			}

			$kwq_wheres = implode ( ' ', $kwq_wheres );

			$kwq_wheres = "  ( $kwq_wheres  id=0) ";

			if (! empty ( $includeIds ) && is_array ( $includeIds )) {

				$kwq_wheres .= "\t\n   and id IN (" . implode ( ",", $includeIds ) . ")";

			}

			$q = " SELECT id from $aTable where $kwq_wheres  group by id   ";

			// var_dump ( $q );
			$q = $this->dbQuery ( $q );

			if (! empty ( $q )) {

				foreach ( $q as $v ) {

					$includeIds [] = $v ['id'];

				}

			}

			// $wheres [] = "\n {$q} ";

		}
		$sql_relations_array = array ('=', '<>', '!=', '<', '>', '<=', '>=', 'in', 'not in' );
		$sql_connections_array = array ('or', 'and', 'in' );
		$qGroupBy = "";

		if (strval ( $groupBy ) != '') {

			$groupBy = addslashes ( $groupBy );

			$qGroupBy .= "\n\tGROUP BY {$groupBy} ";

		}

		if (! empty ( $aFilter )) {

			if (is_array ( $aFilter )) {

				foreach ( $aFilter as $fk => $fv ) {
					if (strstr ( $fk, 'custom_field_' ) == true) {

						$addcf = str_replace ( 'custom_field_', '', $fk );
						$aFilter ['custom_fields_criteria'] [] = array ($addcf => $fv );

					}
				}

				if (! empty ( $aFilter ['custom_fields_criteria'] )) {

					$table_custom_fields = $cms_db_tables ['table_custom_fields'];

					$only_custom_fieldd_ids = array ();

					$use_fetch_db_data = true;

					$ids_q = "";

					if (! empty ( $ids )) {

						$ids_i = implode ( ',', $ids );

						$ids_q = " and to_table_id in ($ids_i) ";

					}

					$only_custom_fieldd_ids = array ();
					// p($data ['custom_fields_criteria'],1);
					foreach ( $aFilter ['custom_fields_criteria'] as $k => $v ) {

						if (is_array ( $v ) == false) {

							$v = addslashes ( $v );

						}

						$k = addslashes ( $k );

						if (! empty ( $category_content_ids )) {

							$category_ids_q = implode ( ',', $category_content_ids );

							$category_ids_q = " and to_table_id in ($category_ids_q) ";

						} else {

							$category_ids_q = false;

						}

						$only_custom_fieldd_ids_q = false;

						if (! empty ( $only_custom_fieldd_ids )) {

							$only_custom_fieldd_ids_i = implode ( ',', $only_custom_fieldd_ids );

							$only_custom_fieldd_ids_q = " and to_table_id in ($only_custom_fieldd_ids_i) ";

						}

						$q = "SELECT  to_table_id from $table_custom_fields where

            to_table = '$aTable_assoc' and

            custom_field_name = '$k' and

            custom_field_value = '$v'   $ids_q   $only_custom_fieldd_ids_q

            order by field_order asc

             $my_limit_q

                    ";

						$q2 = $q;
						// p($q);
						$q = $this->core_model->dbQuery ( $q, md5 ( $q ), 'custom_fields' );
						// p($q,1);
						if (! empty ( $q )) {

							$ids_old = $ids;

							$ids = array ();

							foreach ( $q as $itm ) {

								$only_custom_fieldd_ids [] = $itm ['to_table_id'];

								// if(in_array($itm
								// ['to_table_id'],$category_ids)== false){

								$includeIds [] = $itm ['to_table_id'];

								// }

								//

							}

						} else {

							// $ids = array();

							$remove_all_ids = true;

							$includeIds = false;

							$includeIds [] = '0';

							$includeIds [] = 0;

						}

					}

				}

				foreach ( $aFilter as $filter ) {

					list ( $field, $value, $relation, $connection, $no_escape ) = $filter;

					/*
					 * $filtertemp = $filter; if ($field == false) { //$field =
					 * $filter[0]; }
					 */

					// while ( list ( $field, $value, $relation, $connection ) =
					// each ( $filter ) ) {

					switch ($field) {
						//

						case 'voted' :

							if (($timestamp = strtotime ( $value )) !== false) {

								$voted = strtotime ( $value . ' ago' );

								$table_votes = $cms_db_tables ['table_votes'];

								if (! empty ( $includeIds ) && is_array ( $includeIds )) {

									$voted_ids_q = "\t\n  and to_table_id = $aTable.id  and to_table_id IN (" . implode ( ",", $includeIds ) . ")";

								} else {

									$voted_ids_q = "\t\n  and to_table_id = $aTable.id  ";

								}

								$pastday = date ( 'Y-m-d H:i:s', $voted );

								if (! $getCount) {

									$q = ", (SELECT count(to_table_id) as qty from $table_votes where
									to_table = '$aTable_assoc'
									$voted_ids_q

									and created_on >'$pastday'
									group by to_table_id
											) as voted";

									$qSelect .= "\n\t{$q}";

									$qHaving_a [] = ' voted > 0  ';

								} else {

									$q = " and id IN (SELECT (to_table_id) from $table_votes where
									to_table = '$aTable_assoc'
									$voted_ids_q

									and created_on >'$pastday'
									group by to_table_id
											)  ";

									$wheres [] = "\n\t{$q}";

								}

							}

							break;

						case 'commented' :

							if (($timestamp = strtotime ( $value )) !== false) {

								$commented = strtotime ( $value . ' ago' );

								$table_comments = $cms_db_tables ['table_comments'];

								if (! empty ( $includeIds ) && is_array ( $includeIds )) {

									$table_comments_ids_q = "\t\n  and to_table_id = $aTable.id  and to_table_id IN (" . implode ( ",", $includeIds ) . ")";

								} else {

									$table_comments_ids_q = "\t\n  and to_table_id = $aTable.id  ";

								}

								$pastday = date ( 'Y-m-d H:i:s', $commented );

								if (! $getCount) {

									$q = ", (SELECT count(to_table_id) as qty from $table_comments where
									to_table = '$aTable_assoc'
									$table_comments_ids_q

									and created_on >'$pastday'
									group by to_table_id
											) as commented";

									$qSelect .= "\n\t{$q}";

									$qHaving_a [] = ' commented > 0  ';

									//
								} else {

									$q = " and id IN (SELECT (to_table_id) from $table_comments where
									to_table = '$aTable_assoc'
									$voted_ids_q

									and created_on >'$pastday'
									group by to_table_id
											)  ";

									$wheres [] = "\n\t{$q}";

								}

							}

							break;

						default :

							// p($all_table_fields);
							// p($field);
							$this->load->database();
							if (in_array ( $field, $all_table_fields ) == true)

							{

								$relation = isset ( $relation ) ? $relation : '=';

								$relation = strtolower ( trim ( $relation ) );

								$connection = isset ( $connection ) ? $connection : 'AND';

								if (in_array ( $relation, $sql_relations_array )) {

									if ($no_escape == false) {

										if ((trim ( strtoupper ( $value ) ) == 'NULL') or (trim ( strtoupper ( $value ) ) == 'IS NULL') or (trim ( strtoupper ( $value ) ) == 'IS NOT NULL')) {

											$value = ($value);

										} else {

											$value = $this->db->escape ( $value );

										}

									}

									if (trim ( strtoupper ( $value ) ) == 'IS NULL') {

										$wheres [] = array ("{$field} {$value}", $connection );

									} elseif (trim ( strtoupper ( $value ) ) == 'IS NOT NULL') {

										$wheres [] = array ("{$field} {$value}", $connection );

									} else {

										$wheres [] = array ("{$field} {$relation} {$value}", $connection );

									}

								}

							}

							break;

					}

					// }

				}

			} elseif (is_string ( $aFilter )) {

				$wheres [] = array ("({$aFilter})", 'AND' );

			} else {

				throw new Exception ( 'The aFilter must be string or array. Now its ' . var_dump ( $aFilter ) );

			}

		}

		// p ( $wheres );
		if ($get_params_from_url == true) {

			foreach ( $all_table_fields as $f ) {

				$try = $this->core_model->getParamFromURL ( $f );

				if ($try != false) {

					$try = $this->db->escape ( $try );

					$wheres [] = array ("{$f}={$try}", 'and' );

				}

			}

		}

		if (! empty ( $includeIds ) && is_array ( $includeIds )) {

			if (strval ( $includeIdsField ) != '') {

				$in_field = $includeIdsField;

			} else {

				$in_field = 'id';

			}

			$includeIds_we_accept_only_intval = array ();

			foreach ( $includeIds as $includeIds_item ) {

				$includeIds_we_accept_only_intval [] = intval ( $includeIds_item );

			}

			$includeIds_we_accept_only_intval = array_unique ( $includeIds_we_accept_only_intval );

			asort ( $includeIds_we_accept_only_intval );

			$wheres [] = array ("{$in_field} IN (" . implode ( ",", $includeIds_we_accept_only_intval ) . ")", "AND" );

		}

		if (! empty ( $excludeIds ) && is_array ( $excludeIds )) {

			if (strval ( $excludeIdsField ) != '') {

				$exclude_field = $excludeIdsField;

			} else {

				$exclude_field = 'id';

			}

			$wheres [] = array ("{$exclude_field} NOT IN (" . implode ( ",", $excludeIds ) . ")", "AND" );

		}

		$qWhere = "";

		if (! empty ( $wheres )) {

			$qWhere .= "WHERE";

			// remove last connection
			$wheres_last = count ( $wheres );
			// p($wheres_last);
			// $wheres [$wheres_last] [1] = "";

			foreach ( $wheres as $where ) {

				if (is_array ( $where )) {
					// p ( $where );
					list ( $statement, $connection ) = $where;

					$qWhere .= "\n\t{$where[0]} {$where[1]}";

					// p ( $qWhere );

				}

				if (is_string ( $where )) {

					$qWhere .= "\n\t{$where} ";

				}

			}
			$qWhere = trim ( $qWhere );
			foreach ( $sql_connections_array as $item ) {

				$item = strtolower ( $item );
				$qWhere = rtrim ( $qWhere, " {$item}" );

				$item = strtoupper ( $item );
				$qWhere = rtrim ( $qWhere, " {$item}" );

			}

			// p($qWhere);

			// $qWhere .= "\n\t id is not null ";

		}

		/*
		 * ~~~~~~~~~~~~~ Build order part ~~~~~~~~~~~~~
		 */

		$qOrder = "";

		if (! empty ( $orderBy ) && is_array ( $orderBy )) {

			// fix for multiple fields order
			if (count ( $orderBy ) == count ( $orderBy, COUNT_RECURSIVE )) {

				$orderBy = array ($orderBy );

			}

			$qOrder .= "ORDER BY";

			foreach ( $orderBy as $ord_key => $order ) {

				$ord_key = trim ( $ord_key );

				if (is_array ( $order ) == false) {

					$order = trim ( $order );

				}

				if (strtoupper ( $ord_key ) == "RAND()") {

					$qOrder .= "\n\t{$ord_key} {$order},";

				} else {

					if (is_array ( $order )) {

						list ( $orderColumn, $orderType ) = $order;

						$qOrder .= "\n\t{$orderColumn} {$orderType},";

					} else {

						$qOrder .= "\n\t{$ord_key} {$order},";

					}

				}

			}

			$qOrder = rtrim ( $qOrder, ',' );

			if (! $getCount) {

				// $qOrder .= "\n\t{$orderby1 [0]} {$orderby1 [1]}";
			} else {

				switch ($orderby1 [0]) {

					case 'voted' :

					case 'commented' :

						$orderby1 [0] = 'created_on';

						break;

					default :

						break;

				}

				$qOrder = "\n\t{$orderby1 [0]} {$orderby1 [1]}";

			}

		} else {

			if ($get_params_from_url == true) {

				$qOrder .= "ORDER BY";

				$order = $this->core_model->getParamFromURL ( 'ord' );

				$order_direction = $this->core_model->getParamFromURL ( 'ord-dir' );

				$orderby1 = array ();

				if ($order != false) {

					$orderby1 [0] = $order;

				} else {

					$orderby1 [0] = 'updated_on';

				}

				if ($order_direction != false) {

					$orderby1 [1] = $order_direction;

				} else {

					$orderby1 [1] = 'DESC';

				}

				if (! $getCount) {

					$qOrder .= "\n\t{$orderby1 [0]} {$orderby1 [1]}";

				} else {

					switch ($orderby1 [0]) {

						case 'voted' :

						case 'commented' :

							$orderby1 [0] = 'created_on';

							break;

						default :

							break;

					}

					$qOrder .= "\n\t{$orderby1 [0]} {$orderby1 [1]}";

				}

			}

		}

		if (! empty ( $qHaving_a )) {

			$qHaving = implode ( ' and ', $qHaving_a );

			$qHaving = "\n\t HAVING $qHaving ";

		}

		/*
		 * ~~~~~~~~~~~~~ Build the whole query ~~~~~~~~~~~~~
		 */

		$qSelect .= "\nFROM\n\t{$aTable}";

		$query = $qSelect . "\n" . $qWhere . "\n" . $qHaving . "\n" . $qGroupBy . "\n" . $qOrder . "\n" . $qLimit . "\n" . $qOffset . "\n";

		/*
		 * ~~~~~~~~~~~~~ Print query if debug mode is enabled ~~~~~~~~~~~~~
		 */
		// var_dump($includeIds);
		// var_dump($query);
		// exit();

		if ($debugQuery == true) {

			p ( '------------------------------------' );

			p ( nl2br ( $aTable . ":\n" . $query ) );

			p ( '------------------------------------' );

		}

		if ($enableCache != false and $cacheGroup != false) {

			if ($execQuery == false) {

				$result = $this->dbQuery ( $query, __FUNCTION__ . crc32 ( $query ), $cacheGroup );

			} else {

				$result = $this->dbQuery ( $aFilter, __FUNCTION__ . crc32 ( $aFilter ), $cacheGroup );

			}

		} else {

			if ($execQuery == false) {

				$result = $this->dbQuery ( $query );

			} else {

				$result = $this->dbQuery ( $aFilter );

			}

		}

		// p($result);

		/*
		 * // If result is small and all columns are fetched via query if (!
		 * $onlyFields && ! $getCount && ! empty ( $result ) && (count ( $result
		 * ) < 0)) { $masterTable = null; if (! empty ( $cms_db_tables )) {
		 * foreach ( $cms_db_tables as $k => $v ) { if (strtolower ( $aTable )
		 * == strtolower ( $v )) { $masterTable = $k; } } } $table_custom_field
		 * = $cms_db_tables ['table_custom_fields']; if (strval ( $masterTable )
		 * != 'table_custom_fields') { if (strval ( trim ( $masterTable ) ) !=
		 * '') { //~~~~~ Implementation for table content ~~~~~ if (strval (
		 * $masterTable ) == 'table_content') { $this_cache_id = __FUNCTION__ .
		 * 'custom_fields_stuff' . md5 ( serialize ( $result ) );
		 * $this_cache_content = $this->cacheGetContentAndDecode (
		 * $this_cache_id ); //						$this_cache_content = false; if
		 * ($enableCache && ($this_cache_content) != false) { $result =
		 * $this_cache_content; } else { $the_data_with_custom_field__stuff =
		 * array (); foreach ( $result as $item ) { if (strval ( $masterTable )
		 * != '') { if (intval ( $item ['id'] ) != 0) { $q = " SELECT * FROM
		 * {$table_custom_field} WHERE to_table = '$masterTable' AND
		 * to_table_id={$item['id']} "; //										p($q); $cache_id =
		 * __FUNCTION__ . 'custom_fields_stuff' . md5 ( $q ); $cache_id = md5 (
		 * $cache_id ); $q = $this->dbQuery ( $q ); //										p($q); if (!
		 * empty ( $q )) { $append_this = array (); foreach ( $q as $q2 ) { $i =
		 * 0; $the_name = false; $the_val = false; foreach ( $q2 as $cfk => $cfv
		 * ) { if ($cfk == 'custom_field_name') { $the_name = $cfv; } if ($cfk
		 * == 'custom_field_value') { $the_val = $cfv; } $i ++; } if ($the_name
		 * != false and $the_val != false) { $append_this [$the_name] =
		 * $the_val; } } //											p($append_this); $item ['custom_fields'] =
		 * $append_this; } } } $the_data_with_custom_field__stuff [] = $item; }
		 * $result = $the_data_with_custom_field__stuff; //var_dump($result);
		 * $this->cacheWriteAndEncode ( $result, $this_cache_id ); } } } } }
		 * //		p($result);
		 */

		$result = $this->decodeLinksAndReplaceSiteUrl ( $result );

		// cache result
		if ($enableCache) {

			$this->_cacheDbData ( $cache_id, $result, $cacheGroup );

		}

		// Some processment before return result

		$return = array ();

		if ($getCount) {

			$return = $result [0] [$countColumn];

		} elseif (! empty ( $result )) {

			if ($return_only_ids == true) {

				$result2 = array ();

				foreach ( $result as $item ) {

					$return [] = intval ( $item ['id'] );

				}

			} else {

				foreach ( $result as $k => $v ) {

					$return [$k] = $this->removeSlashesFromArrayAndDecodeHtmlChars ( $v );

				}

			}

		}

		// p($return, 1);
		return $return;

	}

	private function _cacheDbData($aCacheId, $aData, $aCacheGrpup = null) {

		if ($aCacheGrpup === null) {

			$aCacheGrpup = 'global';

		}

		if ($aData) {

			$cacheData = array ();

			foreach ( $aData as $k => $v ) {

				$cacheData [$k] = $this->addSlashesToArrayAndEncodeHtmlChars ( $v );

			}

			if ($cacheData) {

				// var_dump($cacheData);
				$cache = serialize ( $cacheData );

				$this->cacheDeleteFile ( $aCacheId, $aCacheGrpup );

				$this->cacheWriteContent ( $aCacheId, $cache, $aCacheGrpup );

			}

		}

	}

	/**
	 * save data
	 *
	 * @author Peter Ivanov
	 */

	function getData($table = false, $criteria = false, $limit = false, $offset = false, $return_type = false, $orderby = false, $cache_group = false) {

		exit ( 'getData???? This function is not maitained and used anymore, please use $this->getDbData instead' );

		if ($table == false) {

			return false;

		}

		if (! empty ( $orderby )) {

			$this->db->order_by ( $orderby [0], $orderby [1] );

		}

		//
		// print $table;
		// $criteria = $this->core_model->mapArrayToDatabaseTable($table,
		// $criteria);
		// $this->db->start_cache();
		if (! empty ( $criteria )) {

			$query = $this->db->get_where ( $table, $criteria, $limit, $offset );

		} else {

			$query = $this->db->get ( $table, $limit, $offset );

		}

		// $this->db->stop_cache();
		$result = $query->result_array ();

		$query->free_result ();

		$group_to_table = str_ireplace ( TABLE_PREFIX, '', $table );

		$the_return = array ();

		foreach ( $result as $item ) {

			if (intval ( $item ['group_id'] ) != 0) {

				$group_id_data = array ();

				$group_id_data ['id'] = $item ['group_id'];

				$group_id_data ['group_to_table'] = $group_to_table;

				$group_id_data = $this->core_model->groupsGet ( $group_id_data );

				$group_id_data = $group_id_data [0];

				// var_dump($group_id_data);
				if (! empty ( $group_id_data )) {

					$item ['group_id_data'] = $group_id_data;

				}

			}

			$the_return [] = $item;

		}

		$result = $the_return;

		if ($return_type == false) {

			return $result;

		}

		if ($return_type == 'row') {

			$result = $result [0];

			return $result;

		}

		if ($return_type == 'one') {

			$result = $result [0];

			$result = array_values ( $result );

			$result = $result [0];

			return $result;

		}

	}

	/**
	 * delete data
	 *
	 * @author Peter Ivanov
	 */

	function deleteData($table, $data, $delete_cache_group = false) {

		global $cms_db;

		global $cache;

		global $cms_db_tables;

		// var_dump($data, $table);
		// exit;

		$criteria = $this->mapArrayToDatabaseTable ( $table, $data );

		$q = "DELETE FROM $table ";

		$where = false;

		if (! empty ( $criteria )) {

			$where = " WHERE ";

			foreach ( $criteria as $k => $v ) {

				$where .= "$k = '$v' AND ";

			}

			$where .= " ID is not null ";

		}

		// var_dump($table,$data );
		if ($where != false) {

			$q = $q . $where;

		} else {

			return false;

		}

		// print $q;
		// exit;

	//	$stmt = $this->db->query ( $q );
		
				$this->dbQ ($q  );
		

		if ($delete_cache_group != false) {

			cache_clean_group ( $delete_cache_group );

		}

		return true;

	}

	/**
	 * delete data
	 *
	 * @author Peter Ivanov
	 */

	function deleteDataById($table, $id, $delete_cache_group = false) {

		global $cms_db;

		global $cache;

		global $cms_db_tables;

		if (intval ( $id ) == 0) {

			return false;

		}

		$is_real_name = TABLE_PREFIX;
		if (strstr ( $table, $is_real_name ) == false) {
			$table = $this->dbGetRealDbTableNameByAssocName ( $table );
		}

		// var_dump( "DELETE FROM $table where id='$id' ");
		$this->dbQ ( "DELETE FROM $table where id='$id' " );

		if ($delete_cache_group != false) {

			cache_clean_group ( $delete_cache_group );

		}

		return true;

	}

	function cacheGetContent($cache_id, $cache_group = 'global', $time = false) {


return cache_get_content_encoded($cache_id, $cache_group , $time) ;



		global $cms_db_tables;

		global $mw_cache_storage;

		if ($cache_group === null) {

			$cache_group = 'global';

		}

		if ($cache_id === null) {

			return false;

		}

		$cache_id = trim ( $cache_id );

		// $_ENV['cache_storage'][$cache_id];

		// $memory = $this->cache_storage [$cache_id];
		// p($mw_cache_storage);



		$cache_group = $cache_group .DS;

		$cache_group = reduce_double_slashes ( $cache_group );

		$cache_file = cache_get_file_path( $cache_id, $cache_group );
		$get_file = $cache_file;
	 

			try {
 
				if($cache_file != false){
					//$cache_file = reduce_double_slashes($cache_file);
					// p($cache_file);
 					if(isset($get_file )== true and is_file($cache_file)){
						
						
					//this is slower  
					// $cache =  implode('', file($cache_file));
					 
					 //this is faster
				 $cache = file_get_contents ( $cache_file );
				}

			}

			} catch ( Exception $e ) {
				$cache = false;
			}


		if (isset($cache) and strval ( $cache ) != '') {

			$search = CACHE_CONTENT_PREPEND;

			$replace = '';

			$count = 1;

			$cache = str_replace ( $search, $replace, $cache, $count );

			$this->mw_cache_storage ["$cache_id"] = $cache;

			//$this->cache_storage_hits ["$cache_id"] ++;

		} else {
			// $this->cache_storage_not_found [$cache_file] ;
			// print 'no cache file'.$cache_file;
			// $this->cache_storage [$cache_id] = false;
			$this->mw_cache_storage ["$cache_id"] = false;

			return false;

		}

		if (($cache) != '') {

			// print 'put in mem';
			// $this->cache_storage [$cache_id] = $cache;
			$mw_cache_storage ["$cache_id"] = $cache;

			return $cache;

		} else {

			// clean

			$mw_cache_storage ["$cache_id"] = false;

			// $this->cache_storage [$cache_id] = false;

			return false;

		}

		return false;

	}

	function cacheGetContentAndDecode($cache_id, $cache_group = 'global' , $time = false) {

	return cache_get_content($cache_id, $cache_group , $time) ;

	$cache = $this->cacheGetContent ( $cache_id, $cache_group, $time );

	if ($cache ==
 '') {

			return false;

		} else {

			// $cache = base64_decode ( $cache );

			$cache = unserialize ( $cache );

			$this->cache_storage_decoded [$cache_id] = $cache;

			return $cache;

		}

	}

	function cacheWriteContent($cache_id, $content, $cache_group = 'global') {

return cache_write_to_file($cache_id, $content, $cache_group);
		if (strval ( trim ( $cache_id ) ) == '') {

			return false;

		}

		$cache_file = cache_get_file_path( $cache_id, $cache_group );

		if (strval ( trim ( $content ) ) == '') {

			return false;

		} else {
			$cache_index = CACHEDIR . 'index.html';

			if (! in_array ( $cache_index, $this->path_list )) {
				$this->path_list [] = $cache_index;
				if (is_file ( $cache_index ) == false) {

					@touch ( $cache_index );

				}
			} else {

			}

			$see_if_dir_is_there = dirname ( $cache_file );

			if (! in_array ( $see_if_dir_is_there, $this->path_list )) {
				$this->path_list [] = $see_if_dir_is_there;
				if (is_dir ( $see_if_dir_is_there ) == false) {

					mkdir_recursive ( $see_if_dir_is_there );

				}

			}

			$content = CACHE_CONTENT_PREPEND . $content;
			//var_dump ( $cache_file, $content );
			try {
				$cache = file_put_contents ( $cache_file, $content );
			} catch ( Exception $e ) {
				$this->cache_storage [$cache_id] = $content;
				$cache = false;
			}

		}

		return $content;

	}

	function cacheWriteAndEncode($data_to_cache, $cache_id, $cache_group = 'global') {
 				
			return cache_store_data($data_to_cache, $cache_id, $cache_group);
		
 		if ($data_to_cache == false) {

			return false;

		} else {

			$data_to_cache = serialize ( $data_to_cache );

			// var_dump($data_to_cache);
			// $data_to_cache = base64_encode ( $data_to_cache );
			// .$data_to_cache = ($data_to_cache);

			$this->cacheWrite ( $data_to_cache, $cache_id, $cache_group );

			return true;

		}

	}

	function cacheDeleteFile($cache_id, $cache_group = 'global') {

		return cache_get_file_path ( $cache_id, $cache_group );

		 

	}

	function _getCacheFile($cache_id, $cache_group = 'global') {
		$cache_group = str_replace ( '/', DIRECTORY_SEPARATOR, $cache_group );
		return cache_get_dir ( $cache_group ) . DIRECTORY_SEPARATOR . $cache_id . CACHE_FILES_EXTENSION;

	}

	function _getCacheDir($cache_group = 'global', $deleted_cache_dir = false) {
		return cache_get_dir ( $cache_group, $deleted_cache_dir );
		 
	}

	public function cleanCacheGroup($cache_group = 'global') {

		return cache_clean_group ( $cache_group );

		// $startTime = slog_time ();
		/*
		 * $cleanPattern = CACHEDIR . $cache_group . DIRECTORY_SEPARATOR . '*' .
		 * CACHE_FILES_EXTENSION; $cache_group = $cache_group .
		 * DIRECTORY_SEPARATOR; $cache_group = reduce_double_slashes (
		 * $cache_group ); if (substr ( $cache_group, - 1 ) ==
		 * DIRECTORY_SEPARATOR) { $cache_group_noslash = substr ( $cache_group,
		 * 0, - 1 ); } else { $cache_group_noslash = ($cache_group); }
		 * $recycle_bin = CACHEDIR . 'deleted'. DIRECTORY_SEPARATOR; if (is_dir
		 * ( $recycle_bin ) == false) { mkdir ( $recycle_bin ); }
		 */

		// print 'delete cache:' .$cache_group;
		$dir = cache_get_dir ( 'global' );
		$dir_del = cache_get_dir ( 'global', true );
		// var_dump(CACHEDIR . $cache_group);
		if (is_dir ( $dir )) {
			// dirmv ( $dir, $dir_del, $overwrite = true, $funcloc = NULL );
			recursive_remove_directory ( $dir );

		}

		$dir = cache_get_dir ( $cache_group );
		$dir_del = cache_get_dir ( $cache_group, true );
		// var_dump(CACHEDIR . $cache_group);
		if (is_dir ( $dir )) {
			// dirmv ( $dir, $dir_del, $overwrite = true, $funcloc = NULL );
			recursive_remove_directory ( $dir );
		}

		/*
		 * foreach ( glob ( $cleanPattern ) as $file ) { @unlink ( $file ); }
		 */
		// print elog_time ( $startTime );
		// recursive_remove_directory(CACHEDIR. $cache_group);
	}

	/**
	 * delete data
	 *
	 * @author Peter Ivanov
	 */

	/*
	 * function deleteData($table, $data, $delete_cache_group = false) {
	 * $criteria = $this->core_model->mapArrayToDatabaseTable ( $table, $data );
	 * $this->db->delete ( $table, ($criteria) ); $this->db->flush_cache (); if
	 * ($delete_cache_group != false) { $this->cacheDelete ( 'cache_group',
	 * $delete_cache_group ); } return true; }
	 */

	/**
	 *
	 * @author Peter Ivanov
	 *
	 *         function groupsGet($data) {
	 *         $table = $table = TABLE_PREFIX . 'groups';
	 *         $criteria = $this->core_model->mapArrayToDatabaseTable ( $table,
	 *         $data );
	 *         $data = $this->getData ( $table, $criteria, $limit = false,
	 *         $offset = false, $return_type = false, $orderby = false );
	 *         return $data;
	 *         }
	 */

	/**
	 *
	 * @author Peter Ivanov
	 *
	 *         function groupsSave($data) {
	 *         $table = $table = TABLE_PREFIX . 'groups';
	 *         $criteria = $this->input->xss_clean ( $data );
	 *         $criteria = $this->core_model->mapArrayToDatabaseTable ( $table,
	 *         $data );
	 *         $save = $this->core_model->saveData ( $table, $criteria );
	 *         return $save;
	 *         }
	 */

	function replace_in_long_text($sRegExpPattern, $sRegExpReplacement, $sVeryLongText, $normal_replace = false) {
		$function_cache_id = false;

		$test_for_long = strlen ( $sVeryLongText );
		if ($test_for_long > 1000) {

			$args = func_get_args ();
			$i = 0;
			foreach ( $args as $k => $v ) {
				if ($i != 2) {
					$function_cache_id = $function_cache_id . serialize ( $k ) . serialize ( $v );
				} else {

				}
				$i ++;
			}

			$function_cache_id = __FUNCTION__ . crc32 ( $sVeryLongText ) . md5 ( $function_cache_id );

			$cache_group = 'extract_tags';

			$cache_content = $this->cacheGetContent ( $function_cache_id, $cache_group );

			if (($cache_content) != false) {

				return $cache_content;

			}
		}

		if ($normal_replace == false) {
			$iSet = 0; // Count how many times we increase the limit
			while ( $iSet < 10 ) { // If the default limit is 100'000 characters
			                       // the highest new limit will be 250'000
			                       // characters
				$sNewText = preg_replace ( $sRegExpPattern, $sRegExpReplacement, $sVeryLongText ); // Try
				                                                                                   // to
				                                                                                   // use
				                                                                                   // PREG

				if (preg_last_error () == PREG_BACKTRACK_LIMIT_ERROR) { // Only
				                                                        // check on
				                                                        // backtrack
				                                                        // limit
				                                                        // failure
					ini_set ( 'pcre.backtrack_limit', ( int ) ini_get ( 'pcre.backtrack_limit' ) + 15000 ); // Get
					                                                                                        // current
					                                                                                        // limit
					                                                                                        // and
					                                                                                        // increase
					$iSet ++; // Do not overkill the server
				} else { // No fail
					$sVeryLongText = $sNewText; // On failure $sNewText would be NULL
					break; // Exit loop
				}
			}

		} else {
			$sNewText = str_replace ( $sRegExpPattern, $sRegExpReplacement, $sVeryLongText );

			// $sNewText = preg_replace($sRegExpPattern,$sRegExpReplacement,
			// $sVeryLongText);

		}
		if ($test_for_long > 1000) {

			$this->core_model->cacheWrite ( $sNewText, $function_cache_id, $cache_group );
		}
		return $sNewText;

	}

	function extractTags($html, $tag, $selfclosing = null, $return_the_entire_tag = false, $charset = 'UTF-8') {

		$function_cache_id = false;

		$args = func_get_args ();
		$i = 0;
		foreach ( $args as $k => $v ) {
			if ($i > 0) {
				$function_cache_id = $function_cache_id . serialize ( $k ) . serialize ( $v );
			} else {

			}
			$i ++;
		}

		$function_cache_id = __FUNCTION__ . crc32 ( $html ) . md5 ( $function_cache_id );

		$cache_group = 'extract_tags';

		$cache_content = $this->cacheGetContentAndDecode ( $function_cache_id, $cache_group );

		if (($cache_content) != false) {

			return $cache_content;

		}

		if (is_array ( $tag )) {
			$tag = implode ( '|', $tag );
		}

		// If the user didn't specify if $tag is a self-closing tag we try to
		// auto-detect it
		// by checking against a list of known self-closing tags.
		$selfclosing_tags = array ('area', 'base', 'basefont', 'br', 'hr', 'input', 'img', 'link', 'meta', 'col', 'param' );
		if (is_null ( $selfclosing )) {
			$selfclosing = in_array ( $tag, $selfclosing_tags );
		}

		// The regexp is different for normal and self-closing tags because I
		// can't figure out
		// how to make a sufficiently robust unified one.
		if ($selfclosing) {
			$tag_pattern = '@<(?P<tag>' . $tag . ')			# <tag
			(?P<attributes>\s[^>]+)?		# attributes, if any
			\s*/?>					# /> or just >, being lenient here
			@xsi';
		} else {
			$tag_pattern = '@<(?P<tag>' . $tag . ')			# <tag
			(?P<attributes>\s[^>]+)?		# attributes, if any
			\s*>					# >
			(?P<contents>.*?)			# tag contents
			</(?P=tag)>				# the closing </tag>
			@xsi';
		}

		$attribute_pattern = '@
		(?P<name>\w+)							# attribute name
		\s*=\s*
		(
			(?P<quote>[\"\'])(?P<value_quoted>.*?)(?P=quote)	# a quoted value
			|							# or
			(?P<value_unquoted>[^\s"\']+?)(?:\s+|$)			# an unquoted value (terminated by whitespace or EOF)
		)
		@xsi';

		// Find all tags
		if (! preg_match_all ( $tag_pattern, $html, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE )) {
			// Return an empty array if we didn't find anything
			return array ();
		}

		$tags = array ();
		foreach ( $matches as $match ) {

			// Parse tag attributes, if any
			$attributes = array ();
			if (! empty ( $match ['attributes'] [0] )) {

				if (preg_match_all ( $attribute_pattern, $match ['attributes'] [0], $attribute_data, PREG_SET_ORDER )) {
					// Turn the attribute data into a name->value array
					foreach ( $attribute_data as $attr ) {
						if (! empty ( $attr ['value_quoted'] )) {
							$value = $attr ['value_quoted'];
						} else if (! empty ( $attr ['value_unquoted'] )) {
							$value = $attr ['value_unquoted'];
						} else {
							$value = '';
						}

						// Passing the value through html_entity_decode is handy
						// when you want
						// to extract link URLs or something like that. You
						// might want to remove
						// or modify this call if it doesn't fit your situation.
						$value = html_entity_decode ( $value, ENT_QUOTES, $charset );

						$attributes [$attr ['name']] = $value;
					}
				}

			}

			$tag = array ('tag_name' => $match ['tag'] [0], 'match' => $match, 'offset' => $match [0] [1], 'contents' => ! empty ( $match ['contents'] ) ? $match ['contents'] [0] : '', 			// empty
			                                                                                                                                                                             // for
			                                                                                                                                                                             // self-closing
			                                                                                                                                                                             // tags
			'attributes' => $attributes );
			if ($return_the_entire_tag) {
				$tag ['full_tag'] = $match [0] [0];
			}

			$tags [] = $tag;
		}
		$this->core_model->cacheWriteAndEncode ( $tags, $function_cache_id, $cache_group );
		return $tags;
	}

	/**
	 *
	 * @author Peter Ivanov
	 */

	/*
	 * function groupsCleanup($group_to_table) { if (strval ( $group_to_table )
	 * == '') { return false; } $table = $table = TABLE_PREFIX . 'groups';
	 * $group_to_table = $this->input->xss_clean ( $group_to_table ); $table2 =
	 * $table = TABLE_PREFIX . $group_to_table; $q = "select group_id from
	 * $table2 where group_id is not null group by group_id"; $query =
	 * $this->db->query ( $q ); $query = $query->result_array (); $clean_q =
	 * false; if (! empty ( $query )) { foreach ( $query as $item ) { $clean_q =
	 * $clean_q . " AND id!={$item['group_id']} "; } $table = $table =
	 * TABLE_PREFIX . 'groups'; $q = "delete from $table where
	 * group_to_table='$group_to_table' $clean_q"; $query = $this->db->query ( $q
	 * ); } }
	 */

	function cronjobRegister($params) {

		$table = $table = TABLE_PREFIX . 'cronjobs';

		if ($params ['cronjob_name'] != '') {

			$data = array ();

			$data ['cronjob_name'] = $params ['cronjob_name'];

			$data ['model_name'] = $params ['model_name'];

			$data = $this->getData ( $table, $data, $limit = 1, $offset = false, $return_type = 'row', $orderby = false );

			if (empty ( $data )) {

				$to_save = $params;

				$this->saveData ( $table, $to_save );

			} else {

				$to_save = $params;

				$to_save ['id'] = $data ['id'];

				$this->saveData ( $table, $to_save );

			}

		}

	}

	function cronjobGetOne($cron_group = false, $force = false, $action = false) {

		if ($cron_group != false) {

			$cg_q = " and cronjob_group='$cron_group'  ";

		} else {

			$cg_q = false;

		}

		if ($action != false) {

			$cg_a = " and cronjob_name='$action'  ";

		} else {

			$cg_a = false;

		}

		$table = $table = TABLE_PREFIX . 'cronjobs';

		/*
		 * $q = " SELECT * FROM $table as t1 WHERE ( UNIX_TIMESTAMP(now() -
		 * INTERVAL t1.interval_minutes MINUTE) >= UNIX_TIMESTAMP(last_run) OR
		 * last_run is null) $cg_q ORDER BY RAND() limit 1 ; ";
		 */

		if ($force == false) {

			$w = "( ( TIMESTAMPADD(MINUTE,t1.interval_minutes,timestamp(t1.last_run))

  < (TIMESTAMPADD(MINUTE,0,now())))


  OR
        last_run is null)";

		} else {

			$w = " id!=0 ";

		}

		$q = "
		SELECT *
  FROM $table as t1
  WHERE

$w

  $cg_q

  $cg_a

  ORDER BY RAND()
  limit 1
;


		";

		// print $q;
		$query = $this->db->query ( $q );

		$query = $query->result_array ();

		$job = $query [0];

		// lets find the date on the mysql server
		$q_date = " select now() as the_time  ";

		$q_date = $this->db->query ( $q_date );

		$q_date = $q_date->row_array ();

		$q_date = $q_date ['the_time'];

		if (! empty ( $job )) {

			$upd = array ();

			$upd ['last_run'] = "$q_date";

			$upd ['id'] = $job ['id'];

			$save = $this->saveData ( $table, $upd );

			return $job;

		}

		//
	}

	function extractEmailsFromString($string) {

		preg_match_all ( "/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i", $string, $matches );

		return $matches [0];

	}

	function guessId($for = false) {

		if ($for == false) {
			if ($_POST) {
				if ($_POST ['id'] != '') {
					$for = $_POST ['id'];
				}

				if ($_POST ['to_table_id'] != '') {
					$for = $_POST ['to_table_id'];
				}

				if ($_POST ['ttid'] != '') {
					$for = $_POST ['ttid'];
				}

				if ($_POST ['for_id'] != '') {
					$for = $_POST ['for_id'];
				}

			}
		}
		return $for;
	}

	function guessDbTable($for = false) {

		if ($for == false) {
			if ($_POST) {
				if ($_POST ['to_table'] != '') {
					$for = $_POST ['to_table'];
				}

				if ($_POST ['for'] != '') {
					$for = $_POST ['for'];
				}

				if ($_POST ['table'] != '') {
					$for = $_POST ['table'];
				}

			}
		}

		if (stristr ( $for, 'table_' ) == false) {
			switch ($for) {
				case 'user' :
				case 'users' :
					$to_table = 'table_users';
					break;

				case 'media' :
				case 'picture' :
				case 'video' :
				case 'file' :
					$to_table = 'table_media';
					break;

				case 'comment' :
				case 'comments' :

					$to_table = 'table_comments';
					break;

				case 'category' :
				case 'categories' :
				case 'cat' :
				case 'taxonomy' :
				case 'tag' :
				case 'tags' :
					$to_table = 'table_taxonomy';
					break;

				case 'post' :
				case 'page' :
				case 'content' :

				default :
					$to_table = 'table_content';
					break;
			}
			return $to_table;
		} else {
			return $for;
		}
	}

	function addParamToUrl($url, $param_name, $param_value, $position = 1) {

		$rem = site_url ();

		$url = str_ireplace ( $rem, '', $url );

		$segs = explode ( '/', $url );

		$segs_done = array ();

		foreach ( $segs as $segment ) {

			if (mb_strtolower ( $segment ) == mb_strtolower ( $param . ':' )) {

				// if (stristr ( $segment, $param_name . ':' ) == true) {
				// print $segment;
				$params_list = explode ( ',', $segment );

				$params_list [$position - 1] = $param_value;

				$segment = $param_name . ':' . implode ( ',', $params_list );

			}

			$segs_done [] = $segment;

		}

		$segs_done = implode ( '/', $segs_done );

		$return = $rem . $segs_done;

		return $return;

	}

	function getParamFromURL2($param) {

		$url = uri_string ();

		$rem = site_url ();

		$url = str_ireplace ( $rem, '', $url );

		$segs = explode ( '/', $url );

		foreach ( $segs as $segment ) {

			// if ( ( $segment ) == ( $param . ':' )) {
			// if (stristr ( $segment, $param . ':' ) == true) {

			$seg1 = explode ( ':', $segment );

			// var_dump($seg1);

			if (($seg1 [0]) == ($param)) {

				// $the_param = str_ireplace ( $param . ':', '', $segment );
				// $params_list = explode ( ',', $the_param );
				$params_list = explode ( ',', $segment );

				if (! empty ( $params_list )) {

					foreach ( $params_list as $item ) {

						$item = explode ( ':', $item );

						if ($item [0] == $param) {

							return $item [1];

						}

					}

				}

			}

		}

		return false;

	}

	function getParamFromURL($param, $param_sub_position = false, $skip_ajax = false) {

		// $segs = $this->uri->segment_array ();
		if ($_POST) {

			if ($_POST ['search_by_keyword']) {

				if ($param == 'keyword') {

					return $_POST ['search_by_keyword'];

				}

			}

		}

		$url = url ( $skip_ajax );

		$rem = site_url ();

		$url = str_ireplace ( $rem, '', $url );

		$url = str_ireplace ( '?', '/', $url );
		$url = str_ireplace ( '=', ':', $url );
		$url = str_ireplace ( '&', '/', $url );

		$segs = explode ( '/', $url );
		foreach ( $segs as $segment ) {

			$seg1 = explode ( ':', $segment );

			// var_dump($seg1);
			if (($seg1 [0]) == ($param)) {

				// if (stristr ( $segment, $param . ':' ) == true) {
				if ($param_sub_position == false) {

					$the_param = str_ireplace ( $param . ':', '', $segment );

					if ($param == 'custom_fields_criteria') {

						// $the_param1 = base64_decode ( $the_param );

						$the_param1 = decode_var ( $the_param );

						return $the_param1;

					}

					return $the_param;

				} else {

					$the_param = str_ireplace ( $param . ':', '', $segment );

					$params_list = explode ( ',', $the_param );

					if ($param == 'custom_fields_criteria') {

						$the_param1 = base64_decode ( $the_param );

						$the_param1 = unserialize ( $the_param1 );

						return $the_param1;

					}

					// $param_value = $params_list [$param_sub_position - 1];
					// $param_value = $the_param;
					return $the_param;

				}

			}

		}

	}

	function mediaGetUrlDir() {

		$media_url = SITEURL;

		$media_url = $media_url . '/' . USERFILES_DIRNAME . '/media/';

		$media_url = reduce_double_slashes ( $media_url );

		// print $media_url;
		return $media_url;

	}

	/**
	 * Functiuon to get thumbnail for media ID
	 *
	 * @return string
	 * @author Peter Ivanov
	 * @version 1.0
	 * @since Version 1.0
	 * @todo add thumbs support for videos
	 */

	function mediaGetThumbnailForMediaId($id, $size_width = 128, $size_height = false) {
		$disable_cache = false;
		if (! is_array ( $id )) {
			if (intval ( $id ) == 0) {
				return false;
			}

			$cache_group = 'media/' . $id;
		} else {
			$cache_group = 'media/global';

		}
		$args = func_get_args ();
		// p($id);
		foreach ( $args as $k => $v ) {

			$function_cache_id = $function_cache_id . serialize ( $k ) . serialize ( $v );

		}

		$function_cache_id = __FUNCTION__ . crc32 ( $function_cache_id );

		$cache_content = $this->cacheGetContentAndDecode ( $function_cache_id, $cache_group );
		if ($disable_cache == false) {
			if (($cache_content) != false) {

				if (trim ( strval ( $cache_content ) ) == 'false') {

					return false;

				} else {

					return $cache_content;

				}

			}
		}
		$size = $size_width;

		if ($size_height == false) {

			$size_height = $size;

		}

		// $this->load->library ( 'image_lib' );

		// require_once (LIBSPATH . 'thumb/ThumbLib.inc.php');
		require_once (LIBSPATH . "resize-class.php");

		global $cms_db_tables;

		$table = $cms_db_tables ['table_media'];

		// p ( $id );

		if ($id != 'no_picture') {

			if (intval ( $id ) == 0) {

				return 'Error, please enter valid media $id, this one is invalid: ' . $id;

			}

		}
		$id_orig = $id;
		if (is_array ( $id )) {
			// p($id);
			if (isset ( $id_orig ['no_picture'] )) {

				$id = 'no_picture';

			}

			if (trim ( $id_orig ["no_picture_to_table"] ) != '') {

				$generate_no_picture_image_to_table = $id_orig ["no_picture_to_table"];

			}

		}

		$media_url = $this->core_model->mediaGetUrlDir ();

		if ($id != 'no_picture') {

			$media_get = array ();

			// $media_get = $this->mediaGet ( $to_table = false, $to_table_id =
			// false, $media_type = false, $order_direction = 'DESC', $no_cache
			// = false, $id );
			// $media_get = $media_get ["pictures"];
			$media_get ['id'] = $id;

			// $media_get = $this->getDbData ( $table, $media_get, false, false,
			// $orderby, $cache_group, $debug = false );

			//
			$media_get1 = $this->mediaGetById ( $id );
			$media_get [0] = $media_get1;
			// var_dump ( $media_get );
			if (! empty ( $media_get )) {

				foreach ( $media_get as $item ) {

					// p($item);

					switch ($item ['media_type']) {

						case 'picture' :

							$file_path = MEDIAFILES . 'pictures/original/' . $item ['filename'];

							if ((is_file ( $file_path ) == true) and trim ( $item ['filename'] ) != '') {

								if ($size == 'original') {

									$file_path = MEDIAFILES . 'pictures/original/' . $item ['filename'];

									$src = pathToURL ( $file_path );

									$this->cacheWriteAndEncode ( $src, $function_cache_id, $cache_group );

									return $src;

								}

								$origina_filename = $item ['filename'];

								$the_original_dir = MEDIAFILES;

								$the_original_dir = dirname ( $file_path );

								$the_original_dir = $the_original_dir . '/';

								$the_original_dir = reduce_double_slashes ( $the_original_dir );

								$new_filename = $the_original_dir . '/' . $size . '_' . $size_height . '/' . $origina_filename;
								$new_filename2 = '/' . $size . '_' . $size_height . '/' . $origina_filename;
								$new_filename = str_ireplace ( ' ', '-', $new_filename );

								$new_filename = str_ireplace ( ' ', '-', $new_filename );

								$new_filename = str_ireplace ( ' ', '-', $new_filename );

								$new_filename = str_ireplace ( ' ', '-', $new_filename );

								$new_filename = str_ireplace ( ' ', '-', $new_filename );

								$new_filename = str_ireplace ( ' ', '-', $new_filename );

								$new_filename = str_ireplace ( ' ', '-', $new_filename );

								$new_filename = str_ireplace ( '(', '-', $new_filename );

								$new_filename = str_ireplace ( ')', '-', $new_filename );

								$new_filename = str_ireplace ( '{', '-', $new_filename );

								$new_filename = str_ireplace ( '}', '-', $new_filename );

								$new_filename = str_ireplace ( '%', '-', $new_filename );

								$new_filename = str_ireplace ( '%', '-', $new_filename );

								$new_filename = normalize_path ( $new_filename, false );

								$file_path = normalize_path ( $file_path, false );

								$new_filename_dir = dirname ( $new_filename );
								if (! is_dir ( $new_filename_dir )) {
									mkdir_recursive ( $new_filename_dir );
								}

								if (! is_dir ( $the_original_dir . $size . '_' . $size_height )) {

									@mkdir_recursive ( $the_original_dir . $size . '_' . $size_height . '/' );

								} else {

								}

								if ($size) {
									$new_filename_url = $the_original_dir . $size . '_' . $size_height . '/' . $origina_filename;
								} else {
									$new_filename_url = $the_original_dir . '/' . $origina_filename;

								}
								$new_filename_url = str_ireplace ( MEDIAFILES, $media_url, $new_filename_url );
								$new_filename = str_replace ( '/_/', '', $new_filename );
								$new_filename = str_replace ( '\_\\', '', $new_filename );

								$file_path = str_replace ( '/_/', '', $file_path );
								$file_path = str_replace ( '\_\\', '', $file_path );
								$file_path = normalize_path ( $file_path, false );

								// p ( $new_filename );
								// p ( $file_path );
								$new_filename_url = pathToURL ( $new_filename );
								$new_filename = normalize_path ( $new_filename, false );

								// unlink($new_filename);
								if (is_file ( $new_filename ) == TRUE) {

									$src = $new_filename_url;

								} else {

									// touch($new_filename);

									$config = array ();
									$config ['image_library'] = 'gd2';

									$config ['source_image'] = $file_path;

									$config ['create_thumb'] = false;

									$config ['new_image'] = $new_filename;

									$config ['maintain_ratio'] = TRUE;

									$config ['width'] = $size;

									$config ['height'] = $size_height;
									$config ['dest_folder'] = $new_filename_dir;

									$config ['quality'] = '100%';

									$src = $new_filename_url;

									// $this->image_lib->initialize ( $config );

									// @mkdir_recursive ( $new_filename_dir );

									// CI::library ( 'image_lib' )->initialize (
									// $config );
									// $this->load->library ( 'image_lib',
									// $config );
									// CI::library ( 'image_lib' )->resize ();

									// *** 1) Initialise / load image
									$resizeObj = new resize ( $file_path );

									// *** 2) Resize image (options: exact,
									// portrait, landscape, auto, crop)
									$resizeObj->resizeImage ( $size, $size_height, auto );

									// *** 3) Save image
									$resizeObj->saveImage ( $new_filename, 100 );

									// $this->image_lib->resize ();

									try {
										// $thumb = PhpThumbFactory::create (
										// $file_path );
										// var_dump($size, $size_height);
										// $thumb->resize ( $size, $size_height
										// );
										// p($new_filename);
										// $thumb->save ( $new_filename );

									} catch ( Exception $e ) {
										// handle error here however you'd like
										// var_dump($e);
										// print 'cant open image file' .
										// $file_path;
									}

									// do your manipulations

									// if (! $this->image_lib->resize ()) {

									// echo $this->image_lib->display_errors ();

									// }

									// $this->image_lib->resize ();
								}

							} else {

								$generate_no_picture_image = true;

								$generate_no_picture_image_to_table = $item ['to_table'];

							}

							break;

						case 'video' :

							$media_get_vid_thumbnail = array ();

							// $media_get = $this->mediaGet ( $to_table = false,
							// $to_table_id = false, $media_type = false,
							// $order_direction = 'DESC', $no_cache = false, $id
							// );
							// $media_get = $media_get ["pictures"];
							$media_get_vid_thumbnail ['to_table_id'] = $item ['id'];

							$media_get_vid_thumbnail ['to_table'] = 'table_media';

							$media_get_vid_thumbnail ['media_type'] = 'picture';

							$media_get_vid_thumbnail_orderby = array ();

							$media_get_vid_thumbnail_orderby [0] = 'media_order';

							$media_get_vid_thumbnail_orderby [1] = 'DESC';

							$media_get_vid_thumbnail = $this->getDbData ( $table, $media_get_vid_thumbnail, false, false, $media_get_vid_thumbnail_orderby, $cache_group = false, $debug = false );

							if (! empty ( $media_get_vid_thumbnail )) {
								$file_path = MEDIAFILES . 'pictures/original/' . $media_get_vid_thumbnail [0] ['filename'];
							} else {
								$file_path = MEDIAFILES . 'pictures/original/' . $item ['filename'];
								$media_get_vid_thumbnail [0] ['filename'] = $item ['filename'];

							}
							// p($file_path);
							$file_path = normalize_path ( $file_path, false );
							if (is_file ( $file_path ) == true) {

								//
								// $test1 = str_ireplace ( $media_url, '',
								// $file_path );
								// print 'test1 is'. $test1;
								$origina_filename = $media_get_vid_thumbnail [0] ['filename'];

								// $test1
								$the_original_dir = MEDIAFILES . $test1;

								$the_original_dir = dirname ( $file_path );

								$the_original_dir2 = dirname ( $the_original_dir );

								$the_original_dir2 = $the_original_dir2 . '/';

								$the_original_dir = $the_original_dir . '/';

								$the_original_dir = reduce_double_slashes ( $the_original_dir );

								$new_filename = $the_original_dir2 . $size . '_' . $size_height . '/' . $origina_filename;

								$new_filename = str_ireplace ( ' ', '-', $new_filename );

								$new_filename = str_ireplace ( ' ', '-', $new_filename );

								$new_filename = str_ireplace ( ' ', '-', $new_filename );

								$new_filename = str_ireplace ( ' ', '-', $new_filename );

								$new_filename = str_ireplace ( ' ', '-', $new_filename );

								$new_filename = str_ireplace ( ' ', '-', $new_filename );

								$new_filename = str_ireplace ( ' ', '-', $new_filename );

								$new_filename = str_ireplace ( '(', '-', $new_filename );

								$new_filename = str_ireplace ( ')', '-', $new_filename );

								$new_filename = str_ireplace ( '{', '-', $new_filename );

								$new_filename = str_ireplace ( '}', '-', $new_filename );

								$new_filename = str_ireplace ( '%', '-', $new_filename );

								$new_filename = str_ireplace ( '%', '-', $new_filename );

								if (is_dir ( $the_original_dir2 . $size . '_' . $size_height . '/' ) == false) {

									mkdir_recursive ( $the_original_dir2 . $size . '_' . $size_height . '/' );

								}

								$new_filename_url = $the_original_dir2 . $size . '_' . $size_height . '/' . $origina_filename;

								$new_filename_url = str_ireplace ( MEDIAFILES, $media_url, $new_filename_url );

								// p($new_filename_url);
								$new_filename_url = pathToURL ( $new_filename );

								if (is_file ( $new_filename ) == TRUE) {

									$src = $new_filename_url;

								} else {

									$config ['image_library'] = 'gd2';

									$config ['source_image'] = $file_path;

									$config ['create_thumb'] = false;

									$config ['new_image'] = $new_filename;

									$config ['maintain_ratio'] = TRUE;

									$config ['width'] = $size;

									$config ['height'] = $size_height;

									$config ['quality'] = '100%';

									$src = $new_filename_url;

									$this->image_lib->initialize ( $config );

									$this->image_lib->resize ();

								}

								$src = str_ireplace ( ' ', '-', $src );

								$src = str_ireplace ( '(', '-', $src );

								$src = str_ireplace ( ')', '-', $src );

								$src = str_ireplace ( '{', '-', $src );

								$src = str_ireplace ( '}', '-', $src );

								$src = str_ireplace ( '%', '-', $src );

								$src = str_ireplace ( '%', '-', $src );

								// $data = array ();
								// $data ['url'] = $this->mediaGetUrlDir () .
								// 'pictures/original/' . $item ['filename'];
								// // $data ['id'] = $item ['id'];
								// $data ['filename'] = $item ['filename'];
								// $media_get_to_return [] = $data;
							} else {

								// $ids_to_delete [] = $item ['id'];

								$src = ($media_url) . 'pictures/no_video.png';

								// return $src;
								$test1 = str_ireplace ( $media_url, '', $src );

							}

							break;

						default :

							break;

					}

					// $media_get_to_return[] = $item;
				}

			} else {

				$generate_no_picture_image = true;

			}

		} else {

			$generate_no_picture_image = true;

		}
		require_once (LIBSPATH . "resize-class.php");
		if ($generate_no_picture_image == true) {

			$src = ($media_url) . 'pictures/no.gif';

			$test1 = str_ireplace ( $media_url, '', $src );

			$test1_local = MEDIAFILES . $test1;
			$test1_local = normalize_path ( $test1_local, false );
			// p ( $test1_local );
			if ($generate_no_picture_image_to_table != false) {

				$tt_no_image = MEDIAFILES . 'pictures/no_' . $generate_no_picture_image_to_table . '.gif';
				$tt_no_image = normalize_path ( $tt_no_image, false );
				if (is_file ( $tt_no_image ) == true) {

					$test1 = 'pictures/no_' . $generate_no_picture_image_to_table . '.gif';

					$test1 = str_ireplace ( $media_url, '', $test1 );

					$test1_local = MEDIAFILES . $test1;

				}

			}

			if (is_file ( $test1_local ) == true) {

				$the_original_dir = $test1_local;

				$the_original_dir = dirname ( $the_original_dir );

				$the_original_dir = $the_original_dir . '/';
				$the_original_dir = normalize_path ( $the_original_dir, true );
				$the_original_dir = reduce_double_slashes ( $the_original_dir );
				// p($the_original_dir);

				$origina_filenamez = normalize_path ( MEDIAFILES . $test1, false );

				$origina_filename = str_ireplace ( $the_original_dir, '', $origina_filenamez );
				// p($origina_filename);
				$new_filename = $the_original_dir . $size . '_' . $size_height . '/' . $origina_filename;

				$new_filename = str_ireplace ( ' ', '-', $new_filename );

				$new_filename = str_ireplace ( ' ', '-', $new_filename );

				$new_filename = str_ireplace ( ' ', '-', $new_filename );

				$new_filename = str_ireplace ( ' ', '-', $new_filename );

				$new_filename = str_ireplace ( ' ', '-', $new_filename );

				$new_filename = str_ireplace ( ' ', '-', $new_filename );

				$new_filename = str_ireplace ( ' ', '-', $new_filename );

				$new_filename = str_ireplace ( '(', '-', $new_filename );

				$new_filename = str_ireplace ( ')', '-', $new_filename );

				$new_filename = str_ireplace ( '{', '-', $new_filename );

				$new_filename = str_ireplace ( '}', '-', $new_filename );

				$new_filename = str_ireplace ( '%', '-', $new_filename );

				$new_filename = str_ireplace ( '%', '-', $new_filename );

				@mkdir_recursive ( $the_original_dir . $size . '_' . $size_height . '/' );

				$new_filename_url = $the_original_dir . $size . '_' . $size_height . '/' . $origina_filename;

				$new_filename_url = str_ireplace ( MEDIAFILES, $media_url, $new_filename_url );

				$new_filename_url = pathToURL ( $new_filename );
				$new_filename = normalize_path ( $new_filename, false );

				if (is_file ( $new_filename ) == TRUE) {

					$src = $new_filename_url;

				} else {

					$config ['image_library'] = 'gd2';

					$config ['source_image'] = MEDIAFILES . $test1;

					$config ['create_thumb'] = false;

					$config ['new_image'] = $new_filename;

					$config ['maintain_ratio'] = TRUE;

					$config ['width'] = $size;

					$config ['height'] = $size;

					$config ['quality'] = '100%';

					$src = $new_filename_url;

					// $this->image_lib->initialize ( $config );

					$resizeObj = new resize ( $config ['source_image'] );

					// *** 2) Resize image (options: exact, portrait, landscape,
					// auto, crop)
					$resizeObj->resizeImage ( $config ['width'], $config ['height'], auto );

					// *** 3) Save image
					$resizeObj->saveImage ( $new_filename, 100 );

					// $this->image_lib->resize ();

				}

			}

		}

		if ($src != '') {

			//
			// $this->cacheWriteAndEncode ( $src, $function_cache_id,
			// $cache_group = 'global' );
			$this->cacheWriteAndEncode ( $src, $function_cache_id, $cache_group );

			return $src;

		} else {

			$this->cacheWriteAndEncode ( 'false', $function_cache_id, $cache_group );

			return false;

		}

	}

	function mediaGetThumbnailForItem($to_table, $to_table_id, $size = 128, $order_direction = "ASC", $media_type = 'picture', $do_not_return_default_image = false) {
		if ((trim ( $to_table ) != '') and (trim ( $to_table_id ) != '')) {
			$cache_group = "media/{$to_table}/{$to_table_id}";
		} else {
			$cache_group = 'media/global';

		}

		// return false;
		$args = func_get_args ();

		foreach ( $args as $k => $v ) {

			$function_cache_id = $function_cache_id . serialize ( $k ) . serialize ( $v );

		}

		$function_cache_id = __FUNCTION__ . crc32 ( $function_cache_id );

		$cache_content = $this->cacheGetContentAndDecode ( $function_cache_id, $cache_group );

		if (($cache_content) != false) {

			if (trim ( strval ( $cache_content ) ) == 'false') {

				return false;
			} else {
				if ($do_not_return_default_image == true) {
					if (stristr ( $cache_content, 'no.gif' )) {
						return false;
					}
				}
				return $cache_content;
			}

		}

		$media_get = array ();

		global $cms_db_tables;

		$table = $cms_db_tables ['table_media'];

		$q = "SELECT id FROM $table WHERE to_table = '$to_table'
		AND to_table_id = '$to_table_id'
		AND media_type = '$media_type'


		AND ID is not null ORDER BY media_order $order_direction limit 0,1";

		$q = $this->dbQuery ( $q, __FUNCTION__ . crc32 ( $q ), $cache_group );

		if (! empty ( $q [0] )) {

			$id = $q [0] ['id'];

			$thumb = $this->mediaGetThumbnailForMediaId ( $id, $size );
			$this->cacheWriteAndEncode ( $thumb, $function_cache_id, $cache_group );

			if ($do_not_return_default_image == true) {
				if (stristr ( $thumb, 'no.gif' )) {
					return false;
				}
			}

			return (trim ( $thumb ));

		} else {

			$media = array ();

			$media ['no_picture'] = true;

			$media ['no_picture_to_table'] = $to_table;

			$thumb = $this->mediaGetThumbnailForMediaId ( $media, $size = $size );
			$this->cacheWriteAndEncode ( $thumb, $function_cache_id, $cache_group );
			if ($do_not_return_default_image == true) {
				if (stristr ( $thumb, 'no.gif' )) {
					return false;
				}
			}
			return (trim ( $thumb ));

		}

	}

	function mediaGetAndCache($to_table, $to_table_id = false, $media_type = false, $order = "ASC", $queue_id = false, $no_cache = false, $id = false) {

		$args = func_get_args ();

		foreach ( $args as $k => $v ) {

			$function_cache_id = $function_cache_id . serialize ( $k ) . serialize ( $v );

		}

		$function_cache_id = __FUNCTION__ . crc32 ( $function_cache_id );

		$cache_content = cache_get_content ( $function_cache_id );

		if (($cache_content) != false) {

			return $cache_content;

		}

		$data = $this->mediaGet ( $to_table, $to_table_id, $media_type, $order, $queue_id, $no_cache, $id );

		$this->cacheWriteAndEncode ( $data, $function_cache_id, $cache_group = 'global' );

		return $data;

	}

	function mediaGet2($media_get = false, $orderby = false, $ids = false) {

		$args = func_get_args ();

		foreach ( $args as $k => $v ) {

			$function_cache_id = $function_cache_id . serialize ( $k ) . serialize ( $v );

		}

		$function_cache_id = __FUNCTION__ . crc32 ( $function_cache_id );

		$cache_content = cache_get_content ( $function_cache_id );

		if (($cache_content) != false) {

			if ($cache_content == 'false') {

				return false;

			} else {

				return $cache_content;

			}

		}

		global $cms_db_tables;

		$table = $cms_db_tables ['table_media'];

		$media_table = $to_table;

		// p ( $media_get );
		// var_dump($media_get);

		if ($orderby == false) {

			$orderby [0] = 'media_order';

			$orderby [1] = $order;

		}

		$media_get = $this->getDbData ( $table, $media_get, false, false, $orderby, $cache_group = false, $debug = false, $ids = $ids, $count_only = false, $only_those_fields = false, $exclude_ids = false, $force_cache_id = false, $get_only_whats_requested_without_additional_stuff = true );

		// var_dump($media_get);
		$target_path = MEDIAFILES;

		$media_get_to_return = array ();

		$media_get_to_return_pictures = array ();

		$pictures_sizes = $this->optionsGetByKeyAsArray ( 'media_image_sizes' );

		$ids_to_delete = array ();

		foreach ( $media_get as $item ) {

			switch ($item ['media_type']) {

				case 'picture' :

					$file_path = MEDIAFILES . 'pictures/original/' . $item ['filename'];

					if (is_file ( $file_path ) == true) {

						$data = array ();

						$data = $item;

						$item ['filename'] = rawurlencode ( $item ['filename'] );

						$data ['urls'] ['original'] = $this->mediaGetUrlDir () . 'pictures/original/' . $item ['filename'];

						foreach ( $pictures_sizes as $pic_size ) {

							$data ['urls'] ["$pic_size"] = $this->mediaGetUrlDir () . 'pictures/' . $pic_size . '/' . $item ['filename'];

						}

						$data ['id'] = $item ['id'];

						$data ['filename'] = $item ['filename'];

						$media_get_to_return_pictures [] = $data;

					} else {

						$ids_to_delete [] = $item ['id'];

					}

					break;

				case 'video' :

					$file_path = MEDIAFILES . 'videos/' . $item ['filename'];

					if (is_file ( $file_path ) == true) {

						$data = array ();

						$data = $item;

						$item ['filename'] = rawurlencode ( $item ['filename'] );

						$data ['url'] = $this->mediaGetUrlDir () . 'videos/' . $item ['filename'];

						$data ['id'] = $item ['id'];

						$data ['filename'] = $item ['filename'];

						$media_get_to_return_videos [] = $data;

					} else {

						$ids_to_delete [] = $item ['id'];

					}

					break;

				case 'file' :

					$file_path = MEDIAFILES . 'files/' . $item ['filename'];

					if (is_file ( $file_path ) == true) {

						$data = array ();

						$data = $item;

						$item ['filename'] = rawurlencode ( $item ['filename'] );

						$data ['url'] = $this->mediaGetUrlDir () . 'files/' . $item ['filename'];

						$data ['id'] = $item ['id'];

						$data ['filename'] = $item ['filename'];

						$media_get_to_return_files [] = $data;

					} else {

						$ids_to_delete [] = $item ['id'];

					}

					break;

				default :

					break;

			}

			// $media_get_to_return[] = $item;
		}

		//

		if (! empty ( $ids_to_delete )) {

			foreach ( $ids_to_delete as $del ) {

				$qd = " delete from  $table where id='$del'  ";

				$qd = $this->dbQ ( $qd );

			}

			cache_clean_group ( 'media/global' );

		}

		if (! empty ( $media_get_to_return_pictures )) {

			$media_get_to_return ['pictures'] = $media_get_to_return_pictures;

		}

		if (! empty ( $media_get_to_return_videos )) {

			$media_get_to_return ['videos'] = $media_get_to_return_videos;

		}

		if (! empty ( $media_get_to_return_files )) {

			$media_get_to_return ['files'] = $media_get_to_return_files;

		}

		if (! empty ( $media_get_to_return )) {

			// var_dump($media_get_to_return);

			$this->cacheWriteAndEncode ( $media_get_to_return, $function_cache_id, $cache_group = 'global' );

			return $media_get_to_return;

		} else {

			$this->cacheWriteAndEncode ( 'false', $function_cache_id, $cache_group = 'global' );

			return false;

		}

	}

	function mediaGet($to_table, $to_table_id, $media_type = false, $order = "ASC", $queue_id = false, $no_cache = false, $id = false, $collection = false) {
		if ($to_table != false) {
			$to_table = $this->guessDbTable ( $to_table );
		}

		if ($collection == false) {
			if (trim ( $to_table ) == '') {
				return false;
			}

			if ($queue_id == false) {
				if (intval ( $to_table_id ) == 0) {
					return false;
				}
			}
		}
		if ((trim ( $to_table ) != '') and (trim ( $to_table_id ) != '')) {
			$cache_group = "media/{$to_table}/{$to_table_id}";
		} else {
			$cache_group = 'media/global';

		}
		// p($cache_group);
		if ($no_cache == true) {

			$cache_group = false;
		}

		$args = func_get_args ();

		foreach ( $args as $k => $v ) {

			$function_cache_id = $function_cache_id . serialize ( $k ) . serialize ( $v );

		}
		if ($no_cache == false) {
			$function_cache_id = __FUNCTION__ . crc32 ( $function_cache_id );

			$cache_content = $this->cacheGetContentAndDecode ( $function_cache_id, $cache_group );

			if (($cache_content) != false) {

				if ($cache_content == 'false') {

					return false;

				} else {
					return $cache_content;

				}

			}
		}
		global $cms_db_tables;

		$table = $cms_db_tables ['table_media'];

		$media_table = $to_table;

		$media_get = array ();

		if (intval ( $id ) == 0) {

			if ($to_table != false) {

				$media_get ['to_table'] = $to_table;

			}

			if ($queue_id != false) {

				$media_get ['queue_id'] = $queue_id;

			}

			if ($to_table_id != false) {

				$media_get ['to_table_id'] = $to_table_id;

			}

			if ($media_type != false) {

				$media_type = str_ireplace ( 'pictures', 'picture', $media_type );
				$media_type = str_ireplace ( 'videos', 'video', $media_type );
				$media_type = str_ireplace ( 'files', 'file', $media_type );

				$media_get ['media_type'] = $media_type;

				$media_type_q = "  and media_type='$media_type'  ";

			}
			if ($collection != false) {

				$media_get ['collection'] = $collection;

			}

		}

		if (intval ( $id ) == 0 and $collection == false) {

			if ($to_table_id != false) {

				$q = " SELECT count(*) as qty from $table where to_table='$to_table' and to_table_id='$to_table_id' $media_type_q  ";
				// var_dump($q);
				// $q = $this->dbQuery ( $q, __FUNCTION__ . crc32 ( $q ),
				// $cache_group );

				// $q = $q [0] ['qty'];

				if (intval ( $q ) == 0) {

					// $this->cacheWriteAndEncode ( 'false', $function_cache_id,
					// $cache_group );

					// return false;

				}

			}

			if ($queue_id != false) {

				$q = " SELECT count(*) as qty from $table where to_table='$to_table' and queue_id='$queue_id' $media_type_q  ";

				// $q = $this->dbQuery ( $q );
				// $q = $q [0] ['qty'];
				if (intval ( $q ) == 0) {
					// $this->cacheWriteAndEncode ( 'false', $function_cache_id,
					// $cache_group );
					// return false;

				}

			}

		}

		if (intval ( $id ) != 0) {

			$media_get ['id'] = $id;

		}

		if ($orderby == false) {

			$orderby [0] = 'media_order';

			$orderby [1] = $order;

		}

		// var_dump ( $media_get );
		if ($no_cache == false) {

			// $cache_group = 'media/global';

		} else {

			$cache_group = false;

		}
		// $media_get['debug'] = 1;
		//
		$media_get = $this->getDbData ( $table, $media_get, false, false, $orderby, $cache_group, $debug = false, $ids = false, $count_only = false, $only_those_fields = false, $exclude_ids = false, $force_cache_id = false, $get_only_whats_requested_without_additional_stuff = true );
		$target_path = MEDIAFILES;

		$media_get_to_return = array ();

		$media_get_to_return_pictures = array ();

		$pictures_sizes = $this->optionsGetByKeyAsArray ( 'media_image_sizes' );

		$ids_to_delete = array ();
		if ( empty ( $media_get )) {
			return false;
		}

		foreach ( $media_get as $item ) {

			switch ($item ['media_type']) {

				case 'picture' :
				case 'pictures' :
				case 'pic' :
				case 'pics' :

					$file_path = MEDIAFILES . 'pictures/original/' . $item ['filename'];
					$file_path = normalize_path ( $file_path, false );
					// p ( $file_path );
					if (is_file ( $file_path ) == true) {

						$data = array ();

						$data = $item;
						foreach ( $item as $item_k => $item_v ) {
							$data [$item_k] = $item_v;
						}

						$item ['filename'] = rawurlencode ( $item ['filename'] );

						$data ['urls'] ['original'] = $this->mediaGetUrlDir () . 'pictures/original/' . $item ['filename'];

						foreach ( $pictures_sizes as $pic_size ) {

							$data ['urls'] ["$pic_size"] = $this->mediaGetUrlDir () . 'pictures/' . $pic_size . '/' . $item ['filename'];

						}

						$data ['id'] = $item ['id'];

						$data ['filename'] = $item ['filename'];

						$media_get_to_return_pictures [] = $data;

					} else {

						$ids_to_delete [] = $item ['id'];

					}

					break;

				case 'video' :
				case 'videos' :
				case 'vids' :
				case 'vid' :

					$file_path = MEDIAFILES . 'pictures/original/' . $item ['filename'];
					$file_path = normalize_path ( $file_path, false );
					if (is_file ( $file_path ) == true) {

						$data = array ();

						$data = $item;

						foreach ( $item as $item_k => $item_v ) {
							$data [$item_k] = $item_v;
						}

						$item ['filename'] = rawurlencode ( $item ['filename'] );

						$data ['url'] = $this->mediaGetUrlDir () . 'pictures/original/' . $item ['filename'];

						$data ['id'] = $item ['id'];

						$data ['filename'] = $item ['filename'];

						$media_get_to_return_videos [] = $data;

					} else {

						$ids_to_delete [] = $item ['id'];

					}

					break;

				case 'file' :
				case 'files' :

					$file_path = MEDIAFILES . 'files/' . $item ['filename'];
					$file_path = normalize_path ( $file_path, false );
					// var_dump($file_path);

					// if (is_file ( $file_path ) == true) {

					$data = array ();

					$data = $item;
					foreach ( $item as $item_k => $item_v ) {
						$data [$item_k] = $item_v;
					}

					$item ['filename'] = $item ['filename'];

					$data ['url'] = $this->mediaGetUrlDir () . 'files/' . $item ['filename'];

					$data ['id'] = $item ['id'];

					$data ['filename'] = $item ['filename'];

					$media_get_to_return_files [] = $data;

					// } else {

					// $ids_to_delete [] = $item ['id'];

					// }

					break;

				default :

					break;

			}

			// $media_get_to_return[] = $item;
		}

		//

		if (! empty ( $ids_to_delete )) {

			foreach ( $ids_to_delete as $del ) {

				/*
				 * $qd = " delete from $table where id='$del' "; $qd =
				 * $this->dbQ ( $qd ); cache_clean_group ( 'media/' . $del
				 * );
				 */

			}

			cache_clean_group ( 'media/global' );

		}

		if (! empty ( $media_get_to_return_pictures )) {

			$media_get_to_return ['pictures'] = $media_get_to_return_pictures;

		}

		if (! empty ( $media_get_to_return_videos )) {

			$media_get_to_return ['videos'] = $media_get_to_return_videos;

		}

		if (! empty ( $media_get_to_return_files )) {

			$media_get_to_return ['files'] = $media_get_to_return_files;

		}
		// var_dump($media_get);
		// var_dump ( $media_get_to_return );

		if (! empty ( $media_get_to_return )) {
			if ($no_cache == false) {
			$this->cacheWriteAndEncode ( $media_get_to_return, $function_cache_id, $cache_group );
			}
			return $media_get_to_return;

		} else {

			return false;

		}

	}

	function mediaGetById($id) {

		$id = intval ( $id );
		if ($id == 0) {
			return false;
		}

		$cache_group = 'media/' . $id;

		global $cms_db_tables;
		$table = $cms_db_tables ['table_media'];
		$q = " SELECT * from $table where id='$id'  ";
		$q = $this->dbQuery ( $q, __FUNCTION__ . crc32 ( $q ), $cache_group );

		if (empty ( $q )) {

			return false;

		}

		return $q [0];

	}

	/**
	 * get and resize images from the Media tablee
	 *
	 * @param $to_table the
	 *       	 table assoc name
	 * @param $to_table_id the
	 *       	 desired table ID
	 * @param $size 128
	 *       	 default (in pixels)
	 * @param $order ASC
	 * @return array
	 * @author Peter Ivanov
	 * @version 1.0
	 * @since Version 1.0
	 */

	function mediaGetImages($to_table, $to_table_id, $size = 128, $order = "ASC") {

		if (trim ( $to_table ) == '') {
			return false;
		}

		if (intval ( $to_table_id ) == 0) {
			return false;
		}

		if ((trim ( $to_table ) != '') and (trim ( $to_table_id ) != '')) {
			$cache_group = "media/{$to_table}/{$to_table_id}";
		} else {
			$cache_group = 'media/global';

		}

		global $cms_db_tables;

		$table = $cms_db_tables ['table_media'];

		$media_table = $to_table;

		$media_type = "picture";

		$media_get = array ();

		$media_get ['to_table'] = $to_table;

		$media_get ['to_table_id'] = $to_table_id;

		if ($media_type != false) {

			$media_get ['media_type'] = $media_type;

			$media_type_q = "  and media_type='$media_type'  ";

		}

		if ($orderby == false) {

			$orderby [0] = 'media_order';

			$orderby [1] = $order;

		}

		$media_get = $this->getDbData ( $table, $media_get, false, false, $orderby, $cache_group );

		if (empty ( $media_get )) {

			return false;

		}

		$target_path = MEDIAFILES;

		$media_get_to_return = array ();

		$media_get_to_return_pictures = array ();

		$pictures_sizes = $this->optionsGetByKeyAsArray ( 'media_image_sizes' );

		$pictures_sizes [] = $size;

		$ids_to_delete = array ();

		foreach ( $media_get as $item ) {

			switch ($item ['media_type']) {

				case 'picture' :

					$file_path = MEDIAFILES . 'pictures/original/' . $item ['filename'];

					if (is_file ( $file_path ) == true) {

						$data = array ();

						$data ['urls'] ['original'] = $this->mediaGetUrlDir () . 'pictures/original/' . $item ['filename'];

						foreach ( $pictures_sizes as $pic_size ) {

							// $data ['urls'] ["$pic_size"] =
							// ($this->mediaGetUrlDir () . 'pictures/' .
							// $pic_size . '/' . $item ['filename']);
							$data ['urls'] ["$pic_size"] = $thumb = $this->core_model->mediaGetThumbnailForMediaId ( $item ['id'] );

						}

						$data ['id'] = $item ['id'];

						$data ['filename'] = $item ['filename'];

						$media_get_to_return_pictures [] = $data;

					} else {

						$ids_to_delete [] = $item ['id'];

					}

					break;

				default :

					break;

			}

			// $media_get_to_return[] = $item;
		}

		if (! empty ( $ids_to_delete )) {

			foreach ( $ids_to_delete as $del ) {

				$qd = " delete from  $table where id='$del'  ";

				$qd = $this->dbQ ( $qd );
				cache_clean_group ( 'media/' . $del );

			}

			cache_clean_group ( 'media/global' );

		}

		if (! empty ( $media_get_to_return_pictures )) {

			$media_url = $this->core_model->mediaGetUrlDir ();

			foreach ( $media_get_to_return_pictures as $orig_src ) {

				$src = $this->core_model->mediaGetThumbnailForMediaId ( $orig_src ['id'], $size );

				$src = str_replace ( ' ', '-', $src );

				$media_pictures [] = $src;

			}

		}

		if (! empty ( $media_pictures )) {

			return $media_pictures;

		} else {

			return false;

		}

	}

	/**
	 * After upload quque accociation (used by the flash upload)
	 *
	 * @author Peter Ivanov
	 * @version 1.0
	 * @since Version 1.0
	 */

	function mediaAfterUploadAssociatetheMediaQueueWithTheId($to_table, $to_table_id = false, $queue_id = false) {

		global $cms_db_tables;

		$table = $cms_db_tables ['table_media'];

		if ($queue_id == false) {

			return;

		}

		$q = " UPDATE $table set to_table='$to_table', to_table_id= '$to_table_id', queue_id=NULL where queue_id='$queue_id'";

		// var_dump( $q);
		$this->dbQ ( $q );

		// $this->cacheDelete ( 'cache_group', 'media/global' );
		return true;

	}

	function upload_base64($base64, $filename) {
		// p($base64);
		$target_path = MEDIAFILES;

		if (strval ( $filename ) != '') {

			$filename = strtolower ( $filename );

			$path = $target_path . '/';

			if (is_dir ( $path ) == false) {
				@mkdir ( $path );
			}

			$the_target_path = $target_path . '/uploaded/';

			$original_path = $the_target_path;

			if (is_dir ( $the_target_path ) == false) {

				@mkdir ( $the_target_path );

			}

			$the_target_path = $the_target_path . $this->url_title ( $filename, $separator = 'dash', $no_slashes = false, $leave_dots = true );

			if (is_file ( $the_target_path ) == true) {

				$filename = date ( "ymdHis" ) . $filename;

				$the_target_path = $original_path . $this->url_title ( $filename, $separator = 'dash', $no_slashes = false, $leave_dots = true );

			}

			base64_to_file ( $base64, $the_target_path );

			if (is_file ( $the_target_path ) == true) {

				if (is_readable ( $the_target_path ) == true) {
					$fn1 = $this->url_title ( $filename, $separator = 'dash', $no_slashes = false, $leave_dots = true );

					list ( $width, $height, $type, $attr ) = getimagesize ( $the_target_path );

					$status = array ();
					$status ['done'] = 1;
					$status ['width'] = $width;
					$status ['height'] = $height;
					$status ['url'] = dirToURL ( $the_target_path );

					// $status = json_encode ( $status );
					return $status;

					// exit ();
					// $uploaded [$k] = $fn1;
					$extension = substr ( strrchr ( $fn1, '.' ), 1 );
					$extension_lower = strtolower ( $extension );
					switch ($extension_lower) {

						case 'jpg' :
						case 'jpeg' :
						case 'gif' :
						case 'png' :
						case 'bmp' :

							{

								break;

							}

					}

				}

			}

		}

	}

	function upload($to_table = false, $to_table_id = false, $queue_id = false, $collection = false, $type = false) {

		$user_id = user_id ();
		if (intval ( $user_id ) == 0) {
			exit ( 'Only logged users can upload!' );
		}

		if ($to_table == false) {
			$to_table = 'table_users';

			if ($to_table_id == false) {
				$to_table_id = $user_id;

			}

		}

		if ($to_table != false) {
			$to_table = $this->guessDbTable ( $to_table );
		}

		$target_path = MEDIAFILES;

		$uploaded = array ();

		if (empty ( $_FILES )) {

			// return false;

		}

		if (! empty ( $_FILES )) {

			foreach ( $_FILES as $k => $item ) {

				// $target_path = MEDIAFILES . '/pictures/';
				$target_path = MEDIAFILES;

				$filename = basename ( $_FILES [$k] ['name'] );

				if (strval ( $filename ) != '') {

					$filename = strtolower ( $filename );

					$path = $target_path . '/';

					if (is_dir ( $path ) == false) {

						@mkdir ( $path );

						// @chmod ( $path, '0777' );
					}
					$original_path = normalize_path ( $original_path, false );
					// $the_target_path = $target_path . '/original/';
					$the_target_path = $target_path;
					$original_path = $the_target_path;
					$original_path = normalize_path ( $original_path, true );
					if (is_dir ( $the_target_path ) == false) {

						mkdir_recursive ( $the_target_path );

						// @chmod ( $the_target_path, '0777' );
					}
					$the_target_path1 = $the_target_path;
					$the_target_path = $the_target_path . $filename;
					// if (is_file ( $the_target_path ) == true) {
					//
					// $filename = date ( "ymdHis" ) . '_' . rand () . '_' .
					// basename ( $_FILES [$k] ['name'] );
					// $filename = $this->url_title ( $filename, $separator =
					// 'dash', $no_slashes = false, $leave_dots = true );
					// $the_target_path = $original_path . $filename;
					//
					// } else {
					// $filename = $this->url_title ( $filename, $separator =
					// 'dash', $no_slashes = false, $leave_dots = true );
					//
					// $the_target_path = $the_target_path1 . $filename;
					// }
					$filename = date ( "ymdHis" ) . '_' . rand () . '_' . basename ( $_FILES [$k] ['name'] );
					$filename = $this->url_title ( $filename, $separator = 'dash', $no_slashes = false, $leave_dots = true );
					$the_target_path = $original_path . $filename;
					if (stristr ( $the_target_path, '.exe' ) or stristr ( $the_target_path, '.php' ) or stristr ( $the_target_path, '.htm' ) or stristr ( $the_target_path, '.js' ) or stristr ( $the_target_path, '.rb' ) or stristr ( $the_target_path, '.pl' ) or stristr ( $the_target_path, '.cgi' ) or stristr ( $the_target_path, '.vbs' ) or stristr ( $the_target_path, '.php3' )) {
						exit ( 'This file type is not permited due security measures!' );
					}
					// p($_FILES);
					$the_target_path = normalize_path ( $the_target_path, false );

					// touch($the_target_path);
					// if (! move_uploaded_file ( $_FILES[$k] ['tmp_name'],
					// $the_target_path )) {
					// echo "CANNOT MOVE {$_FILES["userfile"]["name"]}";
					// }

					$upl = array ();

					$fn1 = $filename;
					// exit ();
					$target_path_pictures_file = $fn1;
					$upl ['filename'] = $fn1;

					$extension = substr ( strrchr ( $fn1, '.' ), 1 );
					$extension_lower = strtolower ( $extension );

					// p ( $filename );

					// p ( $the_target_path );
					if($type != false){
						if($type == 'files'){
							$extension_lower = 'doc';
						}
					}
					switch ($extension_lower) {

						case 'jpg' :
						case 'jpeg' :
						case 'gif' :
						case 'png' :
						case 'bmp' :

							{

								$target_path_pictures_folder = MEDIAFILES . 'pictures/original/';
								$target_path_pictures_folder = normalize_path ( $target_path_pictures_folder, true );
								$target_path_pictures_folder = $target_path_pictures_folder . DIRECTORY_SEPARATOR;
								if (is_dir ( $target_path_pictures_folder ) == false) {
									mkdir_recursive ( $target_path_pictures_folder );
								}

								$target_path_pictures_new = $target_path_pictures_folder . $target_path_pictures_file;
								if (is_file ( $target_path_pictures_new )) {

								}

								if (move_uploaded_file ( $_FILES [$k] ['tmp_name'], $target_path_pictures_new )) {
									// p($target_path_pictures_new);

									// rename ( $the_target_path,
									// $target_path_pictures_new );

									list ( $width, $height, $type, $attr ) = getimagesize ( $target_path_pictures_new );

									$status = array ();
									$status ['done'] = 1;
									$status ['width'] = $width;
									$status ['height'] = $height;
									$status ['filename'] = ($target_path_pictures_file);

									$status ['url'] = dirToURL ( $target_path_pictures_new );
									$statuses [] = $status;
									// $status = json_encode ( $status
									// );
									if ($to_table == false) {
										return $status;
									}
									$upl ['status'] = $status;
									$upl ['type'] = 'picture';
								}
								break;

							}

						case 'flv' :
						case 'avi' :
						case 'mov' :
						case 'f4v' :
						case 'afs' :

							{

								$target_path_pictures_folder = MEDIAFILES . 'videos/';
								$target_path_pictures_folder = normalize_path ( $target_path_pictures_folder );

								if (is_dir ( $target_path_pictures_folder ) == false) {
									mkdir_recursive ( $target_path_pictures_folder );
								}

								$target_path_pictures_file = basename ( $the_target_path );
								$target_path_pictures_new = $target_path_pictures_folder . $target_path_pictures_file;
								// p($target_path_pictures_new);
								if (move_uploaded_file ( $_FILES [$k] ['tmp_name'], $target_path_pictures_new )) {

									// rename ( $the_target_path,
									// $target_path_pictures_new );
									$status = array ();
									$status ['done'] = 1;

									$status ['filename'] = ($target_path_pictures_file);

									$status ['url'] = dirToURL ( $target_path_pictures_new );
									$statuses [] = $status;
									// $status = json_encode ( $status
									// );
									if ($to_table == false) {
										return $status;
									}
									$upl ['status'] = $status;

									$upl ['type'] = 'video';
								}
								break;

							}
						case 'doc' :
						case 'docx' :
						default :

							$target_path_pictures_folder = MEDIAFILES . 'files/';
							$target_path_pictures_folder = normalize_path ( $target_path_pictures_folder );

							if (is_dir ( $target_path_pictures_folder ) == false) {
								mkdir ( $target_path_pictures_folder );
							}

							$target_path_pictures_file = basename ( $the_target_path );
							$target_path_pictures_new = $target_path_pictures_folder . $target_path_pictures_file;
							// p($target_path_pictures_new);
							if (move_uploaded_file ( $_FILES [$k] ['tmp_name'], $target_path_pictures_new )) {

								// rename ( $the_target_path,
								// $target_path_pictures_new );
								$status = array ();
								$status ['done'] = 1;

								$status ['filename'] = ($target_path_pictures_file);

								$status ['url'] = dirToURL ( $target_path_pictures_new );
								$statuses [] = $status;
								// $status = json_encode ( $status
								// );
								if ($to_table == false) {
									return $status;
								}
								$upl ['status'] = $status;
								$upl ['type'] = 'file';
							}
							break;

					}
					$uploaded [$k] = $upl;

				}

			}
		}

		if ($to_table == false) {

			return $status;
		}

		// p ( $upl );


		if (trim ( $collection ) == '') {
			$collection = $_POST ['collection'];

		}


		if (trim ( $_POST ['embed_code'] ) != '') {
			$embed_item = array ();

			$embed_item ['embed_code'] = $_POST ['embed_code'];

			if ($_POST ['screenshot_url']) {
				$target_path_pictures_folder = MEDIAFILES . 'pictures/original/';
				$newfilename1 = md5 ( $_POST ['screenshot_url'] ) . basename ( $_POST ['screenshot_url'] );
				$newfilename = $target_path_pictures_folder . $newfilename1;

				$this->url_getPageToFile ( $_POST ['screenshot_url'], $newfilename );
				$embed_item ['filename'] = $newfilename1;
			}

			if ($_POST ['original_link']) {

				$embed_item ['original_link'] = $_POST ['original_link'];
			}

			if ($_POST ['type']) {

				$embed_item ['type'] = $_POST ['type'];
			}
			if ($_POST ['media_type']) {

				$embed_item ['type'] = $_POST ['media_type'];
			}

			if ($_POST ['media_name']) {

				$embed_item ['media_name'] = $_POST ['media_name'];
			}
			if ($_POST ['media_description']) {

				$embed_item ['media_description'] = $_POST ['media_description'];
			}

			$uploaded [] = $embed_item;
		}

		if (! empty ( $uploaded )) {
			global $cms_db_tables;

			$table = $cms_db_tables ['table_media'];

			$media_table = $table;

			foreach ( $uploaded as $item ) {

				if (! empty ( $item )) {

					$media_save = array ();

					foreach ( $item as $item_k => $item_v ) {
						$media_save [$item_k] = $item_v;
					}

					$media_save ['media_type'] = $item ['type'];

					$media_save ['filename'] = $item ['filename'];

					$media_save ['to_table'] = $to_table;
					if ($collection != false) {

						$media_save ['collection'] = $collection;
					}
					if (intval ( $to_table_id ) != 0) {

						$media_save ['to_table_id'] = $to_table_id;

					} else {

						if (strval ( $queue_id ) != '') {

							$media_save ['queue_id'] = $queue_id;

						}

					}
					// p ( $media_save );
					$this->saveData ( $table, $media_save );

				}

			}

			//

			if (intval ( $to_table_id ) != 0) {

				$this->mediaFixOrder ( $to_table, $to_table_id, 'picture' );

			}
			if ((trim ( $to_table ) != '') and (trim ( $to_table_id ) != '')) {
				$cache_group = "media/{$to_table}/{$to_table_id}";
				cache_clean_group ( $cache_group );
			}
			cache_clean_group ( 'media/global' );
			cache_clean_group ( 'global' );
			return $uploaded;

		} else

		{

			return false;

		}

	}

	/**
	 * Generic functuion to upload Pictures from the $_FILES array, also saves
	 * the data into the DB
	 *
	 * @param
	 *       	 $to_table
	 * @param
	 *       	 $to_table_id
	 * @return $uploaded files array
	 * @author Peter Ivanov
	 * @version 1.0
	 * @since Version 1.0
	 */

	function mediaUploadPictures($to_table, $to_table_id = false, $queue_id = false) {

		$target_path = MEDIAFILES;

		$uploaded = array ();

		if (empty ( $_FILES )) {

			return false;

		}

		// cache_clean_group ( 'media/global' );

		if (! empty ( $_FILES )) {

			foreach ( $_FILES as $k => $item ) {

				$target_path = MEDIAFILES;

				$filename = basename ( $_FILES [$k] ['name'] );

				if (strval ( $filename ) != '') {

					$filename = strtolower ( $filename );

					$path = $target_path . 'pictures/';

					if (is_dir ( $path ) == false) {

						@mkdir ( $path );

						// @chmod ( $path, '0777' );
					}

					$the_target_path = $target_path . 'pictures/original/';

					$original_path = $the_target_path;

					if (is_dir ( $the_target_path ) == false) {

						@mkdir ( $the_target_path );

						// @chmod ( $the_target_path, '0777' );
					}

					$the_target_path = $the_target_path . $this->url_title ( $filename, $separator = 'dash', $no_slashes = false, $leave_dots = true );

					if (is_file ( $the_target_path ) == true) {

						$filename = date ( "ymdHis" ) . basename ( $_FILES [$k] ['name'] );

						$the_target_path = $original_path . $this->url_title ( $filename, $separator = 'dash', $no_slashes = false, $leave_dots = true );

					}

					if (move_uploaded_file ( $_FILES [$k] ['tmp_name'], $the_target_path )) {

						if (is_file ( $the_target_path ) == true) {

							if (is_readable ( $the_target_path ) == true) {

								$uploaded [$k] = $this->url_title ( $filename, $separator = 'dash', $no_slashes = false, $leave_dots = true );

							}

						}

					}

				}

				if (empty ( $uploaded )) {

					return false;

				} else {

					$sizes = array ();

					// $sizes = $this->optionsGetByKeyAsArray (
					// 'media_image_sizes' );
					/*
					 * $sizes [] = 16; $sizes [] = 24; $sizes [] = 32; $sizes []
					 * = 48; $sizes [] = 64; $sizes [] = 96; $sizes [] = 128;
					 * $sizes [] = 152; $sizes [] = 198; $sizes [] = 256; $sizes
					 * [] = 320; $sizes [] = 480; $sizes [] = 640; $sizes [] =
					 * 800; $sizes [] = 1024; $sizes [] = 1280;
					 */

					foreach ( $uploaded as $item ) {

						$extension = substr ( strrchr ( $item, '.' ), 1 );

						foreach ( $sizes as $size ) {

							$image = $original_path . $item;

							$newimage = $path . "$size/";

							if (is_dir ( $newimage ) == false) {

								@mkdir ( $newimage );

							}

							$newimage = $path . "$size/" . $item;

							$image_quality = 80;

							$max_height = $size;

							$max_width = $size;

							switch ($extension) {

								case 'jpg' :

								case 'jpeg' :

									{

										$src_img = ImageCreateFromJpeg ( $image );

										$orig_x = ImageSX ( $src_img );

										$orig_y = ImageSY ( $src_img );

										$new_y = $max_height;

										$new_x = $orig_x / ($orig_y / $max_height);

										if ($new_x > $max_width) {

											$new_x = $max_width;

											$new_y = $orig_y / ($orig_x / $max_width);

										}

										$dst_img = ImageCreateTrueColor ( $new_x, $new_y );

										ImageCopyResampled ( $dst_img, $src_img, 0, 0, 0, 0, $new_x, $new_y, $orig_x, $orig_y );

										ImageJpeg ( $dst_img, $newimage, $image_quality );

										ImageDestroy ( $src_img );

										ImageDestroy ( $dst_img );

										break;

									}

								case 'gif' :

									{

										$src_img = imagecreatefromgif ( $image );

										$orig_x = ImageSX ( $src_img );

										$orig_y = ImageSY ( $src_img );

										$new_y = $max_height;

										$new_x = $orig_x / ($orig_y / $max_height);

										if ($new_x > $max_width) {

											$new_x = $max_width;

											$new_y = $orig_y / ($orig_x / $max_width);

										}

										$dst_img = ImageCreateTrueColor ( $new_x, $new_y );

										ImageCopyResampled ( $dst_img, $src_img, 0, 0, 0, 0, $new_x, $new_y, $orig_x, $orig_y );

										imagegif ( $dst_img, $newimage, $image_quality );

										ImageDestroy ( $src_img );

										ImageDestroy ( $dst_img );

										break;

									}

								case 'png' :

									{

										$src_img = imagecreatefrompng ( $image );

										$orig_x = ImageSX ( $src_img );

										$orig_y = ImageSY ( $src_img );

										$new_y = $max_height;

										$new_x = $orig_x / ($orig_y / $max_height);

										if ($new_x > $max_width) {

											$new_x = $max_width;

											$new_y = $orig_y / ($orig_x / $max_width);

										}

										$im_dest = imagecreatetruecolor ( $new_x, $new_y );

										imagealphablending ( $im_dest, false );

										imagecopyresampled ( $im_dest, $src_img, 0, 0, 0, 0, $new_x, $new_y, $orig_x, $orig_y );

										imagesavealpha ( $im_dest, true );

										imagepng ( $im_dest, $newimage );

										imagedestroy ( $im_dest );

										break;

									}

							}

						}

					}

				}

			}

			global $cms_db_tables;

			$table = $cms_db_tables ['table_media'];

			$media_table = $table;

			foreach ( $uploaded as $item ) {

				if (strval ( $item ) != '') {

					$media_save = array ();

					$media_save ['media_type'] = 'picture';

					$media_save ['filename'] = $item;

					$media_save ['to_table'] = $to_table;

					if (intval ( $to_table_id ) != 0) {

						$media_save ['to_table_id'] = $to_table_id;

					} else {

						if (strval ( $queue_id ) != '') {

							$media_save ['queue_id'] = $queue_id;

						}

					}

					$this->saveData ( $table, $media_save );

				}

			}

			//

			if (intval ( $to_table_id ) != 0) {

				$this->mediaFixOrder ( $to_table, $to_table_id, 'picture' );

			}
			if ((trim ( $to_table ) != '') and (trim ( $to_table_id ) != '')) {
				$cache_group = "media/{$to_table}/{$to_table_id}";
				cache_clean_group ( $cache_group );
			}
			cache_clean_group ( 'media/global' );
			return $uploaded;

			// exit ();
		} else {

			// exit ();
			return false;

		}

		// exit ();
	}

	/**
	 * Generic functuion to upload Files from the $_FILES array, also saves the
	 * data into the DB
	 *
	 * @param
	 *       	 $to_table
	 * @param
	 *       	 $to_table_id
	 * @return $uploaded files array
	 * @author Peter Ivanov
	 * @version 1.0
	 * @since Version 1.0
	 */

	function mediaUploadFiles($to_table, $to_table_id = false, $queue_id = false) {

		$target_path = MEDIAFILES;

		$uploaded = array ();

		if (empty ( $_FILES )) {

			return false;

		}

		if (! empty ( $_FILES )) {

			foreach ( $_FILES as $k => $item ) {

				if (($k) == true) {

					$target_path = MEDIAFILES;

					$filename = basename ( $_FILES [$k] ['name'] );

					$filetype = ($_FILES [$k] ['type']);

					// p ( $_FILES );

					if (stristr ( $filename, '.php' ) == false) {

						if (strval ( $filename ) != '') {

							$filename = strtolower ( $filename );

							$path = $target_path . 'files/';

							if (is_dir ( $path ) == false) {

								@mkdir ( $path );

								// @chmod ( $path, '0777' );
							}

							$the_target_path = $target_path . 'files/';

							$original_path = $the_target_path;

							if (is_dir ( $the_target_path ) == false) {

								@mkdir ( $the_target_path );

								// @chmod ( $the_target_path, '0777' );
							}

							$filename = str_ireplace ( "&", 'and', $filename );

							$filename = str_ireplace ( "'", ' ', $filename );
							$filename = str_ireplace ( '"', ' ', $filename );
							$filename = str_ireplace ( '/', ' ', $filename );

							$filename = htmlentities ( $filename );

							$the_target_path = $the_target_path . $this->url_title ( $filename, $separator = 'dash', $no_slashes = false, $leave_dots = true );

							if (is_file ( $the_target_path ) == true) {

								$filename = date ( "ymdHis" ) . basename ( $_FILES [$k] ['name'] );

								$the_target_path = $original_path . $this->url_title ( $filename, $separator = 'dash', $no_slashes = false, $leave_dots = true );

							}

							if (move_uploaded_file ( $_FILES [$k] ['tmp_name'], $the_target_path )) {

								if (is_file ( $the_target_path ) == true) {

									if (is_readable ( $the_target_path ) == true) {

										$uploaded [$k] = $this->url_title ( $filename, $separator = 'dash', $no_slashes = false, $leave_dots = true );

									}

								}

							}

						}

					} else {

						// error_log ( 'Skipping file: ' . $filename . ',
						// because its invalid file type: ' . $filetype . "\n\n"
						// );

					}

				}

			}

			if (empty ( $uploaded )) {

				return false;

			} else {

			}

			global $cms_db_tables;

			$table = $cms_db_tables ['table_media'];

			$media_table = $table;

			foreach ( $uploaded as $item ) {

				if (strval ( $item ) != '') {

					$media_save = array ();

					$media_save ['media_type'] = 'file';

					$media_save ['filename'] = $item;

					$media_save ['to_table'] = $to_table;

					if (intval ( $to_table_id ) != 0) {

						$media_save ['to_table_id'] = $to_table_id;

					} else {

						if (strval ( $queue_id ) != '') {

							$media_save ['queue_id'] = $queue_id;

						}

					}

					// var_dump($media_save);
					$this->saveData ( $table, $media_save );

				}

			}

			//
			cache_clean_group ( 'media/global' );

			if (intval ( $to_table_id ) != 0) {

				$this->mediaFixOrder ( $to_table, $to_table_id, 'file' );

			}

			return $uploaded;

			// exit ();
		} else {

			// exit ();
			return false;

		}

		// exit ();
	}

	/**
	 * Generic functuion to upload Videos from the $_FILES array, also saves the
	 * data into the DB
	 *
	 * @param
	 *       	 $to_table
	 * @param
	 *       	 $to_table_id
	 * @return $uploaded files array
	 * @author Peter Ivanov
	 * @version 1.0
	 * @since Version 1.0
	 */

	function mediaUploadVideos($to_table, $to_table_id = false, $queue_id = false) {

		$target_path = MEDIAFILES;

		$uploaded = array ();

		if (empty ( $_FILES )) {

			return false;

		}

		if (! empty ( $_FILES )) {

			foreach ( $_FILES as $k => $item ) {

				if (($k) == true) {

					$target_path = MEDIAFILES;

					$filename = basename ( $_FILES [$k] ['name'] );

					$filetype = ($_FILES [$k] ['type']);

					// p ( $_FILES );

					if (($filetype == 'video/x-flv') or ($filetype == 'video/x-f4v') or ($filetype == 'video/mp4')) {

						if (strval ( $filename ) != '') {

							$filename = strtolower ( $filename );

							$path = $target_path . 'videos/';

							if (is_dir ( $path ) == false) {

								@mkdir ( $path );

								// @chmod ( $path, '0777' );
							}

							$the_target_path = $target_path . 'videos/';

							$original_path = $the_target_path;

							if (is_dir ( $the_target_path ) == false) {

								@mkdir ( $the_target_path );

								// @chmod ( $the_target_path, '0777' );
							}

							$the_target_path = $the_target_path . $this->url_title ( $filename, $separator = 'dash', $no_slashes = false, $leave_dots = true );

							if (is_file ( $the_target_path ) == true) {

								$filename = date ( "ymdHis" ) . basename ( $_FILES [$k] ['name'] );

								$the_target_path = $original_path . $filename;

							}

							if (move_uploaded_file ( $_FILES [$k] ['tmp_name'], $the_target_path )) {

								if (is_file ( $the_target_path ) == true) {

									if (is_readable ( $the_target_path ) == true) {

										$uploaded [$k] = $this->url_title ( $filename, $separator = 'dash', $no_slashes = false, $leave_dots = true );

									}

								}

							}

						}

					} else {

						error_log ( 'Skipping file: ' . $filename . ', because its invalid file type: ' . $filetype . "\n\n" );

					}

				}

			}

			if (empty ( $uploaded )) {

				return false;

			} else {

			}

			global $cms_db_tables;

			$table = $cms_db_tables ['table_media'];

			$media_table = $table;

			foreach ( $uploaded as $item ) {

				if (strval ( $item ) != '') {

					$media_save = array ();

					$media_save ['media_type'] = 'video';

					$media_save ['filename'] = $item;

					$media_save ['to_table'] = $to_table;

					if (intval ( $to_table_id ) != 0) {

						$media_save ['to_table_id'] = $to_table_id;

					} else {

						if (strval ( $queue_id ) != '') {

							$media_save ['queue_id'] = $queue_id;

						}

					}

					// var_dump($media_save);
					$this->saveData ( $table, $media_save );

				}

			}

			//
			cache_clean_group ( 'media/global' );

			if (intval ( $to_table_id ) != 0) {

				$this->mediaFixOrder ( $to_table, $to_table_id, 'video' );

			}

			return $uploaded;

			// exit ();
		} else {

			// exit ();
			return false;

		}

		// exit ();
	}

	function mediaUploadByUrl($url, $to_table, $to_table_id, $queue_id = false, $resize_options = false) {
		if (trim ( $url ) == '') {
			return false;
		}
		$target_path = MEDIAFILES;

		$uploaded = array ();

		$url = prep_url ( $url );

		$url_path = pathinfo ( $url );

		$name = $url_path ['basename'];

		$the_target_path = $target_path . 'pictures/original/';

		$original_path = $the_target_path;

		if (is_dir ( $the_target_path ) == false) {

			@mkdir_recursive ( $the_target_path );

		}
		$name = $this->url_title ( $name, $separator = 'dash', $no_slashes = false, $leave_dots = true );

		$filename = $the_target_path . $name;

		if (is_file ( $filename ) == true) {

			$name = md5 ( $url ) . $name;

			$filename = $the_target_path . $name;

		}
		$filename = normalize_path ( $filename, false );

		$saved = $this->url_getPageToFile ( $url, $filename );

		$uploaded = array ();
		$uploaded [] = $name;

		global $cms_db_tables;

		$table = $cms_db_tables ['table_media'];

		$media_table = $table;

		foreach ( $uploaded as $item ) {

			if (strval ( $item ) != '') {

				$media_save = array ();

				$media_save ['media_type'] = 'picture';

				$media_save ['filename'] = $item;

				$media_save ['to_table'] = $to_table;

				$media_save ['to_table_id'] = $to_table_id;

				$new_media_id = $this->saveData ( $table, $media_save );
				$media_save ['id'] = $new_media_id;

			}

		}

		//
		if ((trim ( $to_table ) != '') and (trim ( $to_table_id ) != '')) {
			$cache_group = "media/{$to_table}/{$to_table_id}";
			cache_clean_group ( $cache_group );
		}

		$res = $media_save;

		cache_clean_group ( 'media/global' );

		$this->mediaFixOrder ( $to_table, $to_table_id, 'picture' );

		return $res;

	}

	/**
	 * Generic functuion to upload media from the $_FILES array, also saves the
	 * data into the DB
	 *
	 * @param
	 *       	 $to_table
	 * @param
	 *       	 $to_table_id
	 * @return $uploaded files array
	 * @author Peter Ivanov
	 * @version 1.0
	 * @since Version 1.0
	 */

	function mediaUpload($to_table, $to_table_id, $queue_id = false, $resize_options = false) {

		$target_path = MEDIAFILES;

		$uploaded = array ();

		if (empty ( $_FILES )) {

			return false;

		}
		$this->load->library ( 'upload' );

		if (! empty ( $_FILES )) {

			$params ['session_id'] = $this->input->post ( "PHPSESSID" );

			// load the session library the new way, by passing it the session
			// id
			//
			$this->load->library ( 'session', $params );

			cache_clean_group ( 'media/global' );

			require_once (APPPATH . 'libraries/' . 'ImageManipulation.php');

			foreach ( $_FILES as $k => $item ) {

				if (stristr ( $k, 'picture_' ) == true) {

					$target_path = MEDIAFILES;

					$filename = basename ( $_FILES [$k] ['name'] );

					if (strval ( $filename ) != '') {

						$filename = strtolower ( $filename );

						$path = $target_path . 'pictures/';

						if (is_dir ( $path ) == false) {

							@mkdir ( $path );

							// @chmod ( $path, '0777' );
						}

						$the_target_path = $target_path . 'pictures/original/';

						$original_path = $the_target_path;

						if (is_dir ( $the_target_path ) == false) {

							@mkdir ( $the_target_path );

							// @chmod ( $the_target_path, '0777' );
						}

						$the_target_path = $the_target_path . $this->url_title ( $filename, $separator = 'dash', $no_slashes = false, $leave_dots = true );

						if (is_file ( $the_target_path ) == true) {

							$filename = date ( "ymdHis" ) . basename ( $_FILES [$k] ['name'] );
							$filename = $this->url_title ( $filename, $separator = 'dash', $no_slashes = false, $leave_dots = true );

							$the_target_path = $original_path . $filename;

						}

						if (move_uploaded_file ( $_FILES [$k] ['tmp_name'], $the_target_path )) {

							if (is_file ( $the_target_path ) == true) {

								if (is_readable ( $the_target_path ) == true) {

									if (! empty ( $resize_options )) {

										$objImage = false;

										$objImage = new ImageManipulation ( $the_target_path );

										if ($objImage->imageok) {

											$img_info = $objImage->image;

											$sizex = $img_info ['sizex'];

											// var_dump($sizex);

											if ((intval ( $resize_options ['width'] ) < intval ( $sizex )) and (intval ( $resize_options ['width'] ) > 1)) {

												$objImage->resize ( intval ( $resize_options ['width'] ) );

												$objImage->save ( $the_target_path );

											}

										}

									}

									$uploaded [$k] = $this->url_title ( $filename, $separator = 'dash', $no_slashes = false, $leave_dots = true );

								}

							}

						}

					}

					if (empty ( $uploaded )) {

						return false;

					} else {

						$sizes = array ();

						$sizes = $this->optionsGetByKeyAsArray ( 'media_image_sizes' );

						foreach ( $uploaded as $item ) {

							$extension = substr ( strrchr ( $item, '.' ), 1 );

							foreach ( $sizes as $size ) {

								$image = $original_path . $item;

								$newimage = $path . "$size/";

								if (is_dir ( $newimage ) == false) {

									@mkdir ( $newimage );

								}

								$newimage = $path . "$size/" . $item;

								$image_quality = 80;

								$max_height = $size;

								$max_width = $size;

								switch ($extension) {

									case 'jpg' :

									case 'jpeg' :

										{

											$src_img = ImageCreateFromJpeg ( $image );

											$orig_x = ImageSX ( $src_img );

											$orig_y = ImageSY ( $src_img );

											$new_y = $max_height;

											$new_x = $orig_x / ($orig_y / $max_height);

											if ($new_x > $max_width) {

												$new_x = $max_width;

												$new_y = $orig_y / ($orig_x / $max_width);

											}

											$dst_img = ImageCreateTrueColor ( $new_x, $new_y );

											ImageCopyResampled ( $dst_img, $src_img, 0, 0, 0, 0, $new_x, $new_y, $orig_x, $orig_y );

											ImageJpeg ( $dst_img, $newimage, $image_quality );

											ImageDestroy ( $src_img );

											ImageDestroy ( $dst_img );

											break;

										}

									case 'gif' :

										{

											$src_img = imagecreatefromgif ( $image );

											$orig_x = ImageSX ( $src_img );

											$orig_y = ImageSY ( $src_img );

											$new_y = $max_height;

											$new_x = $orig_x / ($orig_y / $max_height);

											if ($new_x > $max_width) {

												$new_x = $max_width;

												$new_y = $orig_y / ($orig_x / $max_width);

											}

											$dst_img = ImageCreateTrueColor ( $new_x, $new_y );

											ImageCopyResampled ( $dst_img, $src_img, 0, 0, 0, 0, $new_x, $new_y, $orig_x, $orig_y );

											imagegif ( $dst_img, $newimage, $image_quality );

											ImageDestroy ( $src_img );

											ImageDestroy ( $dst_img );

											break;

										}

									case 'png' :

										{

											$src_img = imagecreatefrompng ( $image );

											$orig_x = ImageSX ( $src_img );

											$orig_y = ImageSY ( $src_img );

											$new_y = $max_height;

											$new_x = $orig_x / ($orig_y / $max_height);

											if ($new_x > $max_width) {

												$new_x = $max_width;

												$new_y = $orig_y / ($orig_x / $max_width);

											}

											$im_dest = imagecreatetruecolor ( $new_x, $new_y );

											imagealphablending ( $im_dest, false );

											imagecopyresampled ( $im_dest, $src_img, 0, 0, 0, 0, $new_x, $new_y, $orig_x, $orig_y );

											imagesavealpha ( $im_dest, true );

											imagepng ( $im_dest, $newimage );

											imagedestroy ( $im_dest );

											break;

										}

								}

							}

						}

					}

				}

			}

			global $cms_db_tables;

			$table = $cms_db_tables ['table_media'];

			$media_table = $table;

			foreach ( $uploaded as $item ) {

				if (strval ( $item ) != '') {

					$media_save = array ();

					$media_save ['media_type'] = 'picture';

					$media_save ['filename'] = $item;

					$media_save ['to_table'] = $to_table;

					$media_save ['to_table_id'] = $to_table_id;

					$new_media_id = $this->saveData ( $table, $media_save );
					$media_save ['id'] = $new_media_id;

				}

			}

			//
			if ((trim ( $to_table ) != '') and (trim ( $to_table_id ) != '')) {
				$cache_group = "media/{$to_table}/{$to_table_id}";
				cache_clean_group ( $cache_group );
			}

			$res = $media_save;

			cache_clean_group ( 'media/global' );

			$this->mediaFixOrder ( $to_table, $to_table_id, 'picture' );

			return $res;

		} else {

			return false;

		}

		// exit ();
	}

	/*
	 * function cacheDelete($what, $value) { $table = TABLE_PREFIX . 'cache'; $q
	 * = " delete from $table where $what='$value' "; $q = $this->db->query ( $q
	 * ); }
	 */

	function mediaFixOrder($to_table, $to_table_id, $media_type = 'picture') {

		if ((trim ( $to_table ) != '') and (trim ( $to_table_id ) != '')) {
			$cache_group = "media/{$to_table}/{$to_table_id}";
		} else {
			$cache_group = 'media/global';

		}

		global $cms_db_tables;

		$table = $cms_db_tables ['table_media'];

		$q = " SELECT id from $table where to_table='$to_table' and to_table_id='$to_table_id' and  media_type='$media_type' order by media_order  ASC ";

		$q = $this->dbQuery ( $q );

		if (empty ( $q )) {

			return false;

		}

		$new_order = array ();

		if (! empty ( $q )) {

			foreach ( $q as $item ) {

				$new_order [] = $item ['id'];

			}

		}

		if (! empty ( $new_order )) {

			$i = 1;

			foreach ( $new_order as $item ) {

				$q = " UPDATE $table set media_order = $i  where id=$item ";

				$q = $this->dbQ ( $q );

				$i ++;

			}

		}

		cache_clean_group ( $cache_group );

		return true;

	}

	function mediaReOrder($new_order) {

		global $cms_db_tables;

		$table = $cms_db_tables ['table_media'];

		if (empty ( $new_order )) {

			return false;

		}

		$one = $new_order [0];

		$q = " SELECT id,to_table,to_table_id,media_type,media_order from $table where id=$one ";

		$q = $this->dbQuery ( $q );

		$q = $q [0];

		if (empty ( $q )) {

			return false;

		} else {

			$to_table = $q ['to_table'];

			$to_table_id = $q ['to_table_id'];

			$media_type = $q ['media_type'];

			if (! empty ( $new_order )) {

				$i = 1;

				foreach ( $new_order as $item ) {

					$q = " UPDATE $table set media_order = $i  where id=$item ";

					$q = $this->dbQ ( $q );
					cache_clean_group ( 'media/' . $item );

					$i ++;

				}

			}
			cache_clean_group ( 'media/global' );
			// cache_clean_group ( 'media/global');
			$this->mediaFixOrder ( $to_table, $to_table_id, $media_type );

		}

		return true;

	}

	function mediaSave($data) {

		global $cms_db_tables;

		$table = $cms_db_tables ['table_media'];

		// $this->cacheDelete ( 'cache_group', 'media/global' );
		$save = $this->saveData ( $table, $data );

		if ((trim ( $data ['to_table'] ) != '') and (trim ( $data ['to_table_id'] ) != '')) {

			cache_clean_group ( "media/{$data['to_table']}/{$data['to_table_id']}" );

			$temp = $data ['to_table'];
			$temp = str_replace ( 'table_', '', $temp );

			cache_clean_group ( "media/{$temp}/{$data['to_table_id']}" );

		}
		cache_clean_group ( 'media/' . $save );

		cache_clean_group ( 'media/global' );

		if (intval ( $save ) != 0) {
			$get = $this->mediaGetById ( $save );
			if ((trim ( $get ['to_table'] ) != '') and (trim ( $get ['to_table_id'] ) != '')) {

				cache_clean_group ( "media/{$get['to_table']}/{$get['to_table_id']}" );

				$temp = $get ['to_table'];
				$temp = str_replace ( 'table_', '', $temp );

				cache_clean_group ( "media/{$temp}/{$get['to_table_id']}" );

			}
		}

		cache_clean_group ( 'global' );

		return true;

	}

	function mediaDeleteAllByParams($critera) {

		// @todo: delete files too
		global $cms_db_tables;

		$select_media = $critera;

		$table = $cms_db_tables ['table_media'];

		if (trim ( $select_media ['to_table'] ) == '') {

			error_log ( "Log error: File " . __FILE__ . 'on line ' . __LINE__ . 'criteria is not defined!' );

			return false;

		}

		if (trim ( $select_media ['to_table_id'] ) == '') {

			error_log ( "Log error: File " . __FILE__ . 'on line ' . __LINE__ . 'criteria is not defined!' );

			return false;

		}

		if (trim ( $select_media ['media_type'] ) == '') {

			error_log ( "Log error: File " . __FILE__ . 'on line ' . __LINE__ . 'criteria is not defined!' );

			return false;

		}

		$media = $this->mediaGet ( $select_media ['to_table'], $select_media ['to_table_id'], $select_media ['media_type'] );

		if (empty ( $media )) {

			return false;

		} else {

			foreach ( $media as $variable ) {

				$this->deleteDataById ( $table, $variable ['id'] );
				$this->core_model->cleanCacheGroup ( 'media/' . $variable ['id'] );
			}

			$this->core_model->cleanCacheGroup ( 'media/global' );

		}

	}

	function mediaDelete($id) {

		// @todo: check if files are delelted
		global $cms_db_tables;

		$target_path = MEDIAFILES;

		$table = $cms_db_tables ['table_media'];

		if (($id) == false) {

			return false;

		}

		$one = $id;

		cache_clean_group ( 'media/global' );

		$q = " SELECT * from $table where id=$one ";

		$q = $this->dbQuery ( $q );

		$q = $q [0];

		if (empty ( $q )) {

			return false;

		} else {

			$to_table = $q ['to_table'];

			$to_table_id = $q ['to_table_id'];

			$media_type = $q ['media_type'];

			$filename = $q ['filename'];

			// deltete
			$this->deleteDataById ( $table, $id );
			cache_clean_group ( 'media/' . $id );
			cache_clean_group ( 'media/global' );

			$this->mediaFixOrder ( $to_table, $to_table_id, $media_type );

			if ($media_type == 'picture') {

				$the_target_path = $target_path . 'pictures/original/' . $this->url_title ( $filename, $separator = 'dash', $no_slashes = false, $leave_dots = true );

				if (is_file ( $the_target_path ) == true) {

					@unlink ( $the_target_path );

				}

			}

			if ($media_type == 'video') {

				$the_target_path = $target_path . 'videos/' . $filename;

				if (is_file ( $the_target_path ) == true) {

					@unlink ( $the_target_path );

				}

			}

		}

		return true;

	}

	function cacheDeleteAll() {

		global $cms_db;

		global $cache;

		global $cms_db_tables;

		$this->cache_storage = array ();

		recursive_remove_directory ( CACHEDIR, true );

		recursive_remove_directory ( CACHEDIR_ROOT, true );
		return true;

	}

	function cacheDelete($what = 'cache_group', $value = 'global') {

		cache_clean_group ( $value );

		cache_clean_group ( 'global' );

	}

	function cacheGetCount() {

		die ( __METHOD__ . ': Deprecated' );

		global $cms_db_tables;

		$table = $cms_db_tables ['table_cache'];

		// $table = TABLE_PREFIX . 'cache';
		$q = " select count(*) as qty from $table  ";

		$q = $this->db->query ( $q );

		$q = $q->row_array ();

		$q = intval ( $q ['qty'] );

		return $q;

	}

	function cacheGetSize() {

		if (is_dir ( CACHEDIR ) == false) {

			@mkdir ( CACHEDIR );

		}

		// print CACHEDIR;
		$s = $this->filesystem_getDirectorySize ( CACHEDIR );

		$s = $this->filesystem_sizeFormat ( $s ['size'] );

		return $s;

	}

	function cacheWrite($data_to_cache, $cache_id, $cache_group = 'global') {
 
return cache_write($data_to_cache, $cache_id, $cache_group );
		global $cms_db_tables;

		if (strval ( trim ( $data_to_cache ) ) == '') {

			return false;

		}

		if (strval ( trim ( $cache_id ) ) == '') {

			return false;

		}

		if (strval ( trim ( $cache_group ) ) == '') {

			$cache_group = 'global';

		}

		// $this->cacheDeleteFile ( $cache_id, $cache_group );

		$this->cacheWriteContent ( $cache_id, $data_to_cache, $cache_group );

		return $data_to_cache;

	}

	// Data_length

	function helpers_treeRead($tree, $BUFFER = array()) {

		foreach ( $tree as $k => $v ) {

			if (is_array ( $v )) {

				echo "\n<ul>\n<li>" . $k;

				$this->helpers_treeRead ( $v, $BUFFER );

				echo "</li>\n</ul>";

			} else {

				$BUFFER [] = $v;

			}

		}

		if (count ( $BUFFER ) > 0) {

			echo "\n<ul>\n <li>" . (implode ( "</li>\n <li>", $BUFFER )) . "</li>\n</ul>\n";

		}

	}

	function urlConstruct($base_url = false, $params = array()) {

		// getCurentURL()
		if ($base_url == false) {

			$base_url = getCurentURL ();

		}

		// print $base_url;
		if (empty ( $params )) {

			return $base_url;

		}

		$the_url = parse_url ( $base_url, PHP_URL_QUERY );

		$the_url = $base_url;

		$the_url = explode ( '/', $the_url );

		// var_dump ( $the_url );

		if ($params ['curent_page'] == false) {

			$params ['curent_page'] = 'inherit';

		}

		$new = array ();
		$passed_keys = array ();
		foreach ( $params as $param_key => $param_value ) {
			if (in_array ( $param_key, $passed_keys ) == false) {
				$chunk = array ();

				$chunk [0] = $param_key;

				if ($param_value == 'inherit') {

					$param_value = $this->getParamFromURL ( $param_key );

					if ($param_value != false) {

						$chunk [1] = $param_value;

					} else {

						$chunk = array ();

					}

				} else {

					$chunk [1] = $param_value;

				}

				if ($param_value != 'remove') {

					if (! empty ( $chunk )) {

						$new [] = implode ( ':', $chunk );

					}

				}
				$passed_keys [] = $param_key;
			}
		}

		$new = implode ( '/', $new );

		$new_url = $base_url . '/' . $new;

		$new_url = reduce_double_slashes ( $new_url );

		return $new_url;

	}

	function url_getCurent() {

		$pageURL = 'http';

		if ($_SERVER ["HTTPS"] == "on") {

			$pageURL .= "s";

		}

		$pageURL .= "://";

		if ($_SERVER ["SERVER_PORT"] != "80") {

			$pageURL .= $_SERVER ["SERVER_NAME"] . ":" . $_SERVER ["SERVER_PORT"] . $_SERVER ["REQUEST_URI"];

		} else {

			$pageURL .= $_SERVER ["SERVER_NAME"] . $_SERVER ["REQUEST_URI"];

		}

		return $pageURL;

	}

	function url_getDomain($url) {

		if (filter_var ( $url, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED ) === FALSE) {

			return false;

		}

		/**
		 * * get the url parts **
		 */

		$parts = parse_url ( $url );

		/**
		 * * return the host domain **
		 */

		return $parts ['scheme'] . '://' . $parts ['host'];

	}

	function is_editmode() {
		if (defined ( 'IS_EDITMODE' )) {
			return IS_EDITMODE;
		}
		$editmode = $this->session->userdata ( 'editmode' );
		if ($editmode == true) {

			define ( "IS_EDITMODE", true );

			return IS_EDITMODE;
		} else {
			define ( "IS_EDITMODE", false );
			return IS_EDITMODE;
		}

	}

	function is_admin() {
		if (defined ( 'USER_IS_ADMIN' )) {
			// print USER_ID;
			return USER_IS_ADMIN;
		} else {
			$usr = $this->userId ();

			if ($usr == false) {
				return false;
			}
			$usr = intval ( $usr );
			if (($usr) == 0) {
				return false;
			}

			$cache_group = 'users/' . $usr;
			global $cms_db_tables;
			$table = $cms_db_tables ['table_users'];

			$q = " select id, is_admin from $table where id={$usr} limit 1  ";

			$q = $this->dbQuery ( $q, $cache_id = md5 ( $q ), $cache_group = $cache_group );

			$usr = $q [0];

			if ($usr ['is_admin'] == 'y') {
				define ( "USER_IS_ADMIN", true );
			} else {
				define ( "USER_IS_ADMIN", false );
			}

			return USER_IS_ADMIN;

		}
	}

	function url_title($str, $separator = 'dash', $no_slashes = false, $leave_dots = false) {

		if ($separator == 'dash') {

			$search = '_';

			$replace = '-';

		} else {

			$search = '-';

			$replace = '_';

		}

		$trans = array ('&\#\d+?;' => '', '&\S+?;' => '', '\s+' => $replace );

		$str = strip_tags ( $str );

		foreach ( $trans as $key => $val ) {

			// print $str . '______________________';

			$str = preg_replace ( "#" . $key . "#i", $val, $str );

		}

		// exit;

		$str = str_ireplace ( ',', '-', $str );

		$str = str_ireplace ( ':', '-', $str );

		$str = str_ireplace ( ';', '-', $str );

		$str = str_ireplace ( '�', '-', $str );

		$str = str_ireplace ( '"', '-', $str );
		if ($leave_dots == false) {
			$str = str_ireplace ( '.', '-', $str );
		}
		$str = str_ireplace ( '\\', '-', $str );

		$str = str_ireplace ( '/', '-', $str );
		$str = str_ireplace ( '`', '-', $str );

		$str = str_ireplace ( "'", '-', $str );

		$str = str_ireplace ( ' ', '-', $str );

		$str = str_ireplace ( '$', '-', $str );

		$str = str_ireplace ( '+', '-', $str );

		$str = str_ireplace ( '?', '', $str );

		$str = str_ireplace ( '#', '', $str );

		$str = str_ireplace ( '&', '', $str );

		$str = str_ireplace ( '=', '', $str );

		$str = str_ireplace ( '--', '-', $str );

		$str = str_ireplace ( '--', '-', $str );
		$str = str_ireplace ( ')', '-', $str );
		$str = str_ireplace ( '(', '-', $str );

		$str = str_ireplace ( '[', '-', $str );
		$str = str_ireplace ( ']', '-', $str );

		$str = str_ireplace ( '--', '-', $str );

		$str = str_ireplace ( '%', 'percent', $str );

		$str = str_ireplace ( 'â€™', '-', $str );

		$str = str_ireplace ( '*', '-', $str );

		$str = str_ireplace ( '\\', '-', $str );
		$str = str_ireplace ( '^', '-', $str );
		$str = str_ireplace ( '!', '-', $str );
		$str = str_ireplace ( '@', '-', $str );

		$str = mb_strtolower ( $str );

		$str = rtrim ( $str, "-" );

		if ($no_slashes == true) {

			$str = reduce_double_slashes ( $str );

			$str = stripslashes ( $str );

		}

		return trim ( strtolower ( $str ) );

	}

	function url_getPage($requestUrl, $timeout = 60, $cache = true) {

		if ($cache == true) {
			$function_cache_id = false;

			$args = func_get_args ();

			foreach ( $args as $k => $v ) {

				$function_cache_id = $function_cache_id . serialize ( $k ) . serialize ( $v );

			}

			$function_cache_id = __FUNCTION__ . crc32 ( $function_cache_id );

			$cache_group = 'curl';

			$cache_content = cache_get_content ( $function_cache_id, $cache_group );

			if (($cache_content) != false) {

				return $cache_content;

			}
		}

		$header [0] = "Accept: text/xml,application/xml,application/xhtml+xml,";

		$header [0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";

		$header [] = "Cache-Control: max-age=0";

		$header [] = "Connection: keep-alive";

		$header [] = "Keep-Alive: 300";

		$header [] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";

		$header [] = "Accept-Language: en-us,en;q=0.5";

		$header [] = "Pragma: "; // browsers keep this blank.

		$userAgents = array ('FireFox3' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; pl; rv:1.9) Gecko/2008052906 Firefox/3.0', 'GoogleBot' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', 'IE7' => 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0)', 'Netscape' => 'Mozilla/4.8 [en] (Windows NT 6.0; U)', 'Opera' => 'Opera/9.25 (Windows NT 6.0; U; en)' );

		shuffle ( $userAgents );

		$userAgents = $userAgents [0];

		if (function_exists ( 'curl_init' )) {

			$curl = curl_init ();

			curl_setopt ( $curl, CURLOPT_URL, $requestUrl );

			// curl_setopt ( $curl, CURLOPT_USERAGENT, 'Googlebot/2.1
			// (+http://www.google.com/bot.html)' );
			curl_setopt ( $curl, CURLOPT_USERAGENT, $userAgents );

			curl_setopt ( $curl, CURLOPT_HTTPHEADER, $header );

			curl_setopt ( $curl, CURLOPT_COOKIE, session_name () . '=' . session_id () );

			// curl_setopt ( $curl, CURLOPT_REFERER, 'http://www.google.com' );
			// curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate');
			// curl_setopt ( $curl, CURLOPT_AUTOREFERER, true );
			curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, 0 );
			curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, 0 );

			curl_setopt ( $curl, CURLOPT_TIMEOUT, $timeout );

			$pageContent = trim ( curl_exec ( $curl ) );
			// p($pageContent);
			curl_close ( $curl );

			if ($cache == true) {
				$to_cache = $pageContent;

				$this->core_model->cacheWriteAndEncode ( $to_cache, $function_cache_id, $cache_group );

				return $to_cache;

			}
		} else {

		}
		return ($pageContent);

	}

	function url_getPageToFile($url, $newfilename) {

		// @unlink ( $newfilename );

		if ($newfilename == '') {

			return false;

		}

		$dir = dirname ( $newfilename );

		if (is_dir ( $dir ) == false) {

			mkdir_recursive ( $dir );

		}

		$newfilename = normalize_path ( $newfilename, false );
		@touch ( $newfilename );

		$filename = $newfilename;

		if (is_dir ( $filename ) == false) {

			if (! $handle = @fopen ( $filename, 'w+b' )) {

				// echo "Cannot open file ($filename)";
				// exit ();
			}

		} else {

			return false;

		}
		// p($url);
		$somecontent = $this->url_getPage ( $url );

		if ($somecontent == false) {

			return false;

		}

		if ($handle == false) {

			return false;

		}

		// Write $somecontent to our opened file.
		if (fwrite ( $handle, $somecontent ) === FALSE) {

			// echo "Cannot write to file ($filename)";
			return false;

			// exit;
		} else {

			// print "saved: $newfilename";
		}

		fclose ( $handle );

		return true;

	}

	function url_IsFile($url) {

		$file = @fopen ( $url, "r" );

		if (! $file) {

			// echo "<p>Unable to open remote file.\n";
			// exit ();
			return FALSE;

		}

		// while ( ! feof ( $file ) ) {
		// $line = fgets ( $file, 1024 );
		// /* This only works if the title and its tags are on one line */
		// if (eregi ( "<title>(.*)</title>", $line, $out )) {
		// $title = $out [1];
		// break;
		// }
		// }
		fclose ( $file );

		return true;

	}

	function filesystem_getDirectorySize($path) {

		$totalsize = 0;

		$totalcount = 0;

		$dircount = 0;

		if (is_dir ( $path ) == false) {

			return false;

		}

		if ($handle = opendir ( $path )) {

			while ( false !== ($file = readdir ( $handle )) ) {

				$nextpath = $path . '/' . $file;

				if ($file != '.' && $file != '..' && ! is_link ( $nextpath )) {

					if (is_dir ( $nextpath )) {

						$dircount ++;

						$result = $this->filesystem_getDirectorySize ( $nextpath );

						$totalsize += $result ['size'];

						$totalcount += $result ['count'];

						$dircount += $result ['dircount'];

					} elseif (is_file ( $nextpath )) {

						$totalsize += filesize ( $nextpath );

						$totalcount ++;

					}

				}

			}

		}

		closedir ( $handle );

		$total ['size'] = $totalsize;

		$total ['count'] = $totalcount;

		$total ['dircount'] = $dircount;

		return $total;

	}

	function filesystem_sizeFormat($size) {

		if ($size < 1024) {

			return $size . " bytes";

		} else if ($size < (1024 * 1024)) {

			$size = round ( $size / 1024, 1 );

			return $size . " KB";

		} else if ($size < (1024 * 1024 * 1024)) {

			$size = round ( $size / (1024 * 1024), 1 );

			return $size . " MB";

		} else {

			$size = round ( $size / (1024 * 1024 * 1024), 1 );

			return $size . " GB";

		}

	}

	function optionsSetDefault($key) {
		if (is_file ( APPPATH . 'options' . '/' . trim ( $key ) . '.php' )) {
			include (APPPATH . 'options' . '/' . trim ( $key ) . '.php');
			if (! empty ( $option )) {
				// $this->optionsSave ( $option );
			}
		}

	}

	function optionsSave($data) {

		global $cms_db_tables;

		$table = $cms_db_tables ['table_options'];

		cache_clean_group ( 'options' );
		// $data ['debug'] = 1;
		$save = $this->saveData ( $table, $data );

		return true;

	}

	function optionsDeleteById($id) {

		global $cms_db_tables;

		$table = $cms_db_tables ['table_options'];

		cache_clean_group ( 'options' );

		// $save = $this->saveData ( $table, $data );
		$q = "delete from $table where id='$id' ";

		cache_clean_group ( 'options' );

		$this->dbQ ( $q );

		return true;

	}

	function optionsDeleteByKey($key, $option_group = false) {

		global $cms_db_tables;

		$table = $cms_db_tables ['table_options'];

		cache_clean_group ( 'options' );
		if ($option_group != false) {
			$option_group_q1 = "and option_group='{$option_group}'";
		}
		// $save = $this->saveData ( $table, $data );
		$q = "delete from $table where option_key='$key' $option_group_q1 ";

		$this->dbQ ( $q );

		return true;

	}

	function optionsGetGroups() {

		global $cms_db_tables;

		$table = $cms_db_tables ['table_options'];

		$groups = array ();

		$q = " select * from $table group by option_group order by position asc";

		$q = $this->dbQuery ( $q );

		if (! empty ( $q )) {

			foreach ( $q as $item ) {
				if (trim ( $item ['option_group'] ) != '') {
					$groups [] = $item ['option_group'];
				}

			}

		}

		return $groups;

	}

	function optionsGet($data) {

		global $cms_db_tables;

		$table = $cms_db_tables ['table_options'];

		if ($orderby == false) {

			$orderby [0] = 'created_on';

			$orderby [1] = 'DESC';

		}

		$save = $this->getDbData ( $table, $data, $limit = false, $offset = false, $orderby, $cache_group = 'options' );

		return $save;

	}

	function optionsGetByKey($key, $return_full = false, $orderby = false, $option_group = false) {

		$function_cache_id = false;

		$args = func_get_args ();

		foreach ( $args as $k => $v ) {

			$function_cache_id = $function_cache_id . serialize ( $k ) . serialize ( $v );

		}

		$function_cache_id = __FUNCTION__ . crc32 ( $function_cache_id );

		$cache_content = $this->cacheGetContentAndDecode ( $function_cache_id, $cache_group = 'options' );
		if (($cache_content) == '--false--') {
			return false;
		}
		if (($cache_content) != false) {

			return $cache_content;

		} else {

			global $cms_db_tables;

			$table = $cms_db_tables ['table_options'];

			if ($orderby == false) {

				$orderby [0] = 'created_on';

				$orderby [1] = 'DESC';

			}

			$data = array ();
			if (is_array ( $key )) {
				$data = $key;
			} else {
				$data ['option_key'] = $key;
			}

			if ($option_group != false) {
				$data ['option_group'] = $option_group;
			}

			$get = $this->getDbData ( $table, $data, $limit = false, $offset = false, $orderby, $cache_group = 'options' );

			if (! empty ( $get )) {

				if ($return_full == false) {

					$get = $get [0] ['option_value'];

					$this->core_model->cacheWriteAndEncode ( $get, $function_cache_id, $cache_group = 'options' );

					return $get;

				} else {

					$get = $get [0];

					$this->core_model->cacheWriteAndEncode ( $get, $function_cache_id, $cache_group = 'options' );

					return $get;

				}

			} else {
									$this->core_model->cacheWriteAndEncode ( '--false--', $function_cache_id, $cache_group = 'options' );

				return FALSE;

			}

		}

	}

	function optionsGetByKeyAsArray($key) {

		$data = $this->optionsGetByKey ( $key );

		$data = explode ( ',', $data );

		if (! empty ( $data )) {

			$to_return = array ();

			foreach ( $data as $item ) {

				$item = trim ( $item );

				if ($item != '') {

					$to_return [] = $item;

				}

			}

			return $to_return;

		} else {

			return FALSE;

		}

	}

	function helpers_str_ireplace($co, $naCo, $wCzym) {

		$wCzymM = mb_strtolower ( $wCzym );

		$coM = mb_strtolower ( $co );

		$offset = 0;

		while ( ($poz = mb_strpos ( $wCzymM, $coM, $offset )) !== false ) {

			$offset = $poz + mb_strlen ( $naCo );

			$wCzym = mb_substr ( $wCzym, 0, $poz ) . $naCo . mb_substr ( $wCzym, $poz + mb_strlen ( $co ) );

			$wCzymM = mb_strtolower ( $wCzym );

		}

		return $wCzym;

	}

	function plugins_setLoadedPlugin($dirname) {

		$this->init_model->db_setup ( PLUGINS_DIRNAME . $dirname );

		$this->loaded_plugins [$dirname] = true;

	}

	function plugins_getLoadedPlugins() {

		return $this->loaded_plugins;

	}

	function options_setup_default() {
		$function_cache_id = false;

		$args = func_get_args ();

		foreach ( $args as $k => $v ) {

			$function_cache_id = $function_cache_id . serialize ( $k ) . serialize ( $v );

		}

		$function_cache_id = __FUNCTION__ . crc32 ( $function_cache_id );

		$cache_content = cache_get_content ( $function_cache_id, $cache_group = 'options' );

		if (($cache_content) != false) {

			return $cache_content;

		} else {

			$options_array_from_config = array ();

			$options_array_from_config_new_option = array ();
			$options_array_from_config_new_option ['name'] = "Site template";
			$options_array_from_config_new_option ['help'] = "Select the curent site template";
			$options_array_from_config_new_option ['type'] = "select_template";
			$options_array_from_config_new_option ['default'] = "default";
			$options_array_from_config_new_option ['param'] = "curent_template";
			$options_array_from_config_new_option ['option_group'] = "site";
			$options_array_from_config [] = $options_array_from_config_new_option;

			$options_array_from_config_new_option = array ();
			$options_array_from_config_new_option ['name'] = "Meta title";
			$options_array_from_config_new_option ['help'] = "Edit the default meta title";
			$options_array_from_config_new_option ['type'] = "text";
			$options_array_from_config_new_option ['default'] = "";
			$options_array_from_config_new_option ['param'] = "content_meta_title";
			$options_array_from_config_new_option ['option_group'] = "meta";
			$options_array_from_config [] = $options_array_from_config_new_option;

			$options_array_from_config_new_option = array ();
			$options_array_from_config_new_option ['name'] = "Meta description";
			$options_array_from_config_new_option ['help'] = "Edit the default meta description";
			$options_array_from_config_new_option ['type'] = "text";
			$options_array_from_config_new_option ['default'] = "";
			$options_array_from_config_new_option ['param'] = "content_meta_description";
			$options_array_from_config_new_option ['option_group'] = "meta";
			$options_array_from_config [] = $options_array_from_config_new_option;

			$options_array_from_config_new_option = array ();
			$options_array_from_config_new_option ['name'] = "Meta keywords";
			$options_array_from_config_new_option ['help'] = "Edit the default meta keywords";
			$options_array_from_config_new_option ['type'] = "text";
			$options_array_from_config_new_option ['default'] = "";
			$options_array_from_config_new_option ['param'] = "content_meta_keywords";
			$options_array_from_config_new_option ['option_group'] = "meta";
			$options_array_from_config [] = $options_array_from_config_new_option;

			$options_array_from_config_new_option = array ();
			$options_array_from_config_new_option ['name'] = "Admin items per page";
			$options_array_from_config_new_option ['help'] = "Default items per page in admin";
			$options_array_from_config_new_option ['type'] = "text";
			$options_array_from_config_new_option ['default'] = "100";
			$options_array_from_config_new_option ['param'] = "admin_default_items_per_page";
			$options_array_from_config_new_option ['option_group'] = "admin";
			$options_array_from_config [] = $options_array_from_config_new_option;

			$options_array_from_config_new_option = array ();
			$options_array_from_config_new_option ['name'] = "Number of items per page";
			$options_array_from_config_new_option ['help'] = "Default items per page in site";
			$options_array_from_config_new_option ['type'] = "text";
			$options_array_from_config_new_option ['default'] = "30";
			$options_array_from_config_new_option ['param'] = "default_items_per_page";
			$options_array_from_config_new_option ['option_group'] = "site";
			$options_array_from_config [] = $options_array_from_config_new_option;

			$options_array_from_config_new_option = array ();
			$options_array_from_config_new_option ['name'] = "RSS language";
			$options_array_from_config_new_option ['help'] = "Set the RSS feed language";
			$options_array_from_config_new_option ['type'] = "text";
			$options_array_from_config_new_option ['default'] = "EN";
			$options_array_from_config_new_option ['param'] = "rss_language";
			$options_array_from_config_new_option ['option_group'] = "rss";
			$options_array_from_config [] = $options_array_from_config_new_option;

			$options_array_from_config_new_option = array ();
			$options_array_from_config_new_option ['name'] = "RSS title";
			$options_array_from_config_new_option ['help'] = "Set the RSS feed title";
			$options_array_from_config_new_option ['type'] = "text";
			$options_array_from_config_new_option ['default'] = "RSS Feed";
			$options_array_from_config_new_option ['param'] = "rss_title";
			$options_array_from_config_new_option ['option_group'] = "rss";
			$options_array_from_config [] = $options_array_from_config_new_option;

			$options_array_from_config_new_option = array ();
			$options_array_from_config_new_option ['name'] = "Site creator email";
			$options_array_from_config_new_option ['help'] = "Put your email here.";
			$options_array_from_config_new_option ['type'] = "text";
			$options_array_from_config_new_option ['default'] = "support@microweber.com";
			$options_array_from_config_new_option ['param'] = "creator_email";
			$options_array_from_config_new_option ['option_group'] = "admin";
			$options_array_from_config [] = $options_array_from_config_new_option;

			$options_array_from_config_new_option = array ();
			$options_array_from_config_new_option ['name'] = "Default subject from the site mailforms";
			$options_array_from_config_new_option ['help'] = "Type the desired subject";
			$options_array_from_config_new_option ['type'] = "text";
			$options_array_from_config_new_option ['default'] = "[Mailform]";
			$options_array_from_config_new_option ['param'] = "mailform_subject";
			$options_array_from_config_new_option ['option_group'] = "mailform";
			$options_array_from_config [] = $options_array_from_config_new_option;

			$options_array_from_config_new_option = array ();
			$options_array_from_config_new_option ['name'] = "Receive the mailforms in this email";
			$options_array_from_config_new_option ['help'] = "Put your email here.";
			$options_array_from_config_new_option ['type'] = "email";
			$options_array_from_config_new_option ['default'] = "your_email_here@site";
			$options_array_from_config_new_option ['param'] = "mailform_to";
			$options_array_from_config_new_option ['option_group'] = "mailform";
			$options_array_from_config [] = $options_array_from_config_new_option;

			$options_array_from_config_new_option = array ();
			$options_array_from_config_new_option ['name'] = "Types of content";
			$options_array_from_config_new_option ['help'] = "Put your types here. Sepecate them with comma.";
			$options_array_from_config_new_option ['type'] = "text";
			$options_array_from_config_new_option ['default'] = "inherit, posts, shop";
			$options_array_from_config_new_option ['param'] = "content_types";
			$options_array_from_config_new_option ['option_group'] = "advanced";
			$options_array_from_config [] = $options_array_from_config_new_option;

			$options_array_from_config_new_option = array ();
			$options_array_from_config_new_option ['name'] = "Forgot password email from";
			$options_array_from_config_new_option ['help'] = "We will send the reset password link from this email";
			$options_array_from_config_new_option ['type'] = "email";
			$options_array_from_config_new_option ['default'] = "noreply@microweber.com";
			$options_array_from_config_new_option ['param'] = "forgot_pass_email_from";
			$options_array_from_config_new_option ['option_group'] = "advanced";
			$options_array_from_config [] = $options_array_from_config_new_option;

			$options_array_from_config_new_option = array ();
			$options_array_from_config_new_option ['name'] = "Shipping method";
			$options_array_from_config_new_option ['help'] = "The shipping method we use to calclulate the shipping cost (only ups available for now)";
			$options_array_from_config_new_option ['type'] = "text";
			$options_array_from_config_new_option ['default'] = "ups";
			$options_array_from_config_new_option ['param'] = "shop_shipping_method";
			$options_array_from_config_new_option ['option_group'] = "shop";
			$options_array_from_config [] = $options_array_from_config_new_option;

			$options_array_from_config_new_option = array ();
			$options_array_from_config_new_option ['name'] = "UPS access license number";
			$options_array_from_config_new_option ['help'] = "The UPS license number";
			$options_array_from_config_new_option ['type'] = "text";
			$options_array_from_config_new_option ['default'] = "5C52171CBCFEE5E4";
			$options_array_from_config_new_option ['param'] = "shop_ups_access_license_number";
			$options_array_from_config_new_option ['option_group'] = "shop";
			$options_array_from_config [] = $options_array_from_config_new_option;

			$options_array_from_config_new_option = array ();
			$options_array_from_config_new_option ['name'] = "UPS shipper number";
			$options_array_from_config_new_option ['help'] = "The shipping method we use to calclulate the shipping cost (only ups available for now)";
			$options_array_from_config_new_option ['type'] = "text";
			$options_array_from_config_new_option ['default'] = "E1812E";
			$options_array_from_config_new_option ['param'] = "shop_ups_shipper_number";
			$options_array_from_config_new_option ['option_group'] = "shop";
			$options_array_from_config [] = $options_array_from_config_new_option;

			$options_array_from_config_new_option = array ();
			$options_array_from_config_new_option ['name'] = "UPS shipper_number";
			$options_array_from_config_new_option ['help'] = "The shipping method we use to calclulate the shipping cost (only ups available for now)";
			$options_array_from_config_new_option ['type'] = "text";
			$options_array_from_config_new_option ['default'] = "E1812E";
			$options_array_from_config_new_option ['param'] = "shop_ups_shipper_number";
			$options_array_from_config_new_option ['option_group'] = "shop";
			$options_array_from_config [] = $options_array_from_config_new_option;

			$options_array_from_config_new_option = array ();
			$options_array_from_config_new_option ['name'] = "UPS shipper postal code";
			$options_array_from_config_new_option ['help'] = "The USA post code to send shipments";
			$options_array_from_config_new_option ['type'] = "text";
			$options_array_from_config_new_option ['default'] = "17057";
			$options_array_from_config_new_option ['param'] = "shop_ups_shipper_postal_code";
			$options_array_from_config_new_option ['option_group'] = "shop";
			$options_array_from_config [] = $options_array_from_config_new_option;

			$options_array_from_config_new_option = array ();
			$options_array_from_config_new_option ['name'] = "UPS shipment service code";
			$options_array_from_config_new_option ['help'] = "The shipment service code";
			$options_array_from_config_new_option ['type'] = "text";
			$options_array_from_config_new_option ['default'] = "03";
			$options_array_from_config_new_option ['param'] = "shop_ups_shipment_service_code";
			$options_array_from_config_new_option ['option_group'] = "shop";
			$options_array_from_config [] = $options_array_from_config_new_option;

			$options_array_from_config_new_option = array ();
			$options_array_from_config_new_option ['name'] = "UPS username";
			$options_array_from_config_new_option ['help'] = "The UPS username to login to their site";
			$options_array_from_config_new_option ['type'] = "text";
			$options_array_from_config_new_option ['default'] = "alderin";
			$options_array_from_config_new_option ['param'] = "shop_ups_username";
			$options_array_from_config_new_option ['option_group'] = "shop";
			$options_array_from_config [] = $options_array_from_config_new_option;

			$options_array_from_config_new_option = array ();
			$options_array_from_config_new_option ['name'] = "UPS password";
			$options_array_from_config_new_option ['help'] = "The UPS password to login to their site";
			$options_array_from_config_new_option ['type'] = "text";
			$options_array_from_config_new_option ['default'] = "william";
			$options_array_from_config_new_option ['param'] = "shop_ups_password";
			$options_array_from_config_new_option ['option_group'] = "shop";
			$options_array_from_config [] = $options_array_from_config_new_option;

			$options_array_from_config_new_option = array ();
			$options_array_from_config_new_option ['name'] = "User roles";
			$options_array_from_config_new_option ['help'] = "Default site user roles. Seperate them by comma (,) ";
			$options_array_from_config_new_option ['type'] = "text";
			$options_array_from_config_new_option ['default'] = "user,manager,company";
			$options_array_from_config_new_option ['param'] = "user_roles";
			$options_array_from_config_new_option ['option_group'] = "users";
			$options_array_from_config [] = $options_array_from_config_new_option;

			foreach ( $options_array_from_config as $option ) {
				// p ( $option );
				$get_option = array ();
				$get_option ['option_key'] = $option ['param'];

				$get_option1 = $this->core_model->optionsGetByKey ( $option ['param'], true );
				// p($get_option1);
				if (empty ( $get_option1 )) {
					$get_option ['name'] = $option ['name'];
					$get_option ['group'] = $option ['group'];
					$get_option ['option_group'] = $option ['option_group'];
					$get_option ['module'] = $option ['module'];
					$get_option ['help'] = $option ['help'];
					$get_option ['type'] = $option ['type'];
					$get_option ['name'] = $option ['name'];
					$get_option ['option_value'] = $option ['default'];
					$get_option ['option_value2'] = $option ['values'];

					$save = $this->core_model->optionsSave ( $get_option );

					// p ( $save );

				}

			}
			$this->core_model->cacheWriteAndEncode ( 'true', $function_cache_id, $cache_group = 'options' );

			return true;

		}

	}

	function plugins_isPluginLoaded($name) {

		$plug = $this->loaded_plugins;

		foreacH ( $plug as $k => $v ) {

			if ($k == $name and $v == true) {

			}

		}

		return true;

		exit ();

	}

	function plugins_getPluginConfig($dirname) {

		// print PLUGINS_DIRNAME . $dirname . '/info.php';
		if (is_dir ( PLUGINS_DIRNAME . $dirname )) {

			if (is_file ( PLUGINS_DIRNAME . $dirname . '/' . $dirname . '_model.php' )) {

				// include (PLUGINS_DIRNAME . $dirname . '/info.php');
				if (is_file ( PLUGINS_DIRNAME . $dirname . '/info.php' )) {

					// print PLUGINS_DIRNAME . $dirname . '/info.php';
					// $configuration = $this->load->file ( PLUGINS_DIRNAME .
					// $dirname . '/info.php', true );
					include (PLUGINS_DIRNAME . $dirname . '/info.php');

					return $configuration;

				} else {

					return false;

				}

			} else {

				return false;

			}

		} else {

			return false;

		}

	}

	function plugins_setRunningPlugin($dirname) {

		$this->running_plugins [] = $dirname;

		array_unique ( $this->running_plugins );

	}

	function plugins_getRunningPlugins() {

		return $this->running_plugins;

	}

	function plugins_isRunning($plugin_name) {

		$running = $this->running_plugins;

		if (in_array ( $plugin_name, $running ) == true) {

			return true;

		} else {

			return false;

		}

	}

	/**
	 * Get user id
	 *
	 * @return id
	 *
	 */

	function userId() {

		$temp = intval ( $this->user_id );

		if ($temp != 0) {

			return $temp;

		}

		$currentUser = $this->session->userdata ( 'user_session' );

		$id = intval ( $currentUser ['user_id'] );

		if ($id == 0) {

			$currentUser = $this->session->userdata ( 'user' );

			$id = intval ( $currentUser ['id'] );

			if ($id == 0) {

				return false;

			}

			// return false;

		}

		$this->user_id = $id;

		return $id;

	}

	function validators_isUrl($string) {

		return false;

	}

	function securityEncryptArray($arr) {

		$arr = base64_encode ( serialize ( $arr ) );

		$arr = $this->securityEncryptString ( $arr );

		return $arr;

	}

	function securityDecryptArray($test) {

		if ($test == 'undefined') {

			return false;

		}

		$arr = $this->securityDecryptString ( $test );

		$arr = base64_decode ( $arr );

		$arr = unserialize ( $arr );

		return $arr;

	}

	function securityEncryptString($plaintext) {
		$this->load->library('encrypt');
	//	$plaintext = CI::library ( 'encrypt' )->encode ( $plaintext );
		$plaintext = $this->encrypt->encode($plaintext);
		$plaintext = base64_encode ( $plaintext );

		return $plaintext;

		/*
		 * $the_pass = $this->optionsGetByKey ( 'ecnryption_hash', true );
		 * $do_the_pass_needs_change = strtotime ( $the_pass ['updated_on'] );
		 * $yesterday = strtotime ( date ( "Y-m-d H:i:s", time () - 86400 ) );
		 * $the_pass = $the_pass ['option_value']; if ($yesterday >
		 * $do_the_pass_needs_change) { $the_pass = false;
		 * $this->optionsDeleteByKey ( 'ecnryption_hash' ); } if (($the_pass) ==
		 * false) { $the_pass = rand () . rand (); $new_key = array (); $new_key
		 * ['option_key'] = 'ecnryption_hash'; $new_key ['option_value'] =
		 * $the_pass; $new_key ['option_group'] = 'security'; $this->optionsSave
		 * ( $new_key ); }
		 */

		// $the_pass = intval ( $the_pass );
		$string = $plaintext;

		require_once 'crypt/class.hash_crypt.php';

		$the_pass = $this->session->userdata ( 'session_id' );

		$crypt = new hash_encryption ( $the_pass );

		$encrypted = $crypt->encrypt ( $plaintext );

		return $encrypted;

	}

	function securityDecryptString($plaintext) {
		$this->load->library('encrypt');
		$plaintext = base64_decode ( $plaintext );

		$plaintext = $this->encrypt->decode ( $plaintext );

		return $plaintext;

		$the_pass = $this->optionsGetByKey ( 'ecnryption_hash' );

		// $the_pass = intval ( $the_pass );
		require_once 'crypt/class.hash_crypt.php';

		$the_pass = $this->session->userdata ( 'session_id' );

		// $the_pass = $this->optionsGetByKey ( 'ecnryption_hash' );
		$crypt = new hash_encryption ( $the_pass );

		$encrypted = $crypt->decrypt ( $plaintext );

		return $encrypted;

	}

	function geoGetAllCountries($only_for_continents = false) {

		global $cms_db_tables;

		$table = $cms_db_tables ['table_countries'];

		if (is_array ( $only_for_continents )) {

			$where = false;

			foreach ( $only_for_continents as $continent ) {

				$where = $where . " or continent LIKE '$continent' ";

			}

			$where = " where continent LIKE '$only_for_continents[0]' $where";

		} else {

			$where = false;

		}

		$q = "select * from $table $where group by name";

		// var_dump($q);
		$q = $this->dbQuery ( $q, __FUNCTION__ . crc32 ( $q ), 'geo' );
		// var_dump($q);
		return $q;

	}

	function geoGetAllContinents() {

		global $cms_db_tables;

		$table = $cms_db_tables ['table_countries'];

		$q = "select continent from $table group by continent";

		$q = $this->dbQuery ( $q );

		$ret = array ();

		foreach ( $q as $c ) {

			$ret [] = $c ['continent'];

		}

		return $ret;

	}

	function sendMail($opt = array(), $return_full = false) {

		if (empty ( $opt ))

			return false;

		$option_key = $opt ['option_key'];

		$to = $opt ['email'];

		$username = $opt ['username'];

		$password = $opt ['password'];

		$parent = $opt ['parent'];

		$options = $this->optionsGetByKey ( $option_key, true );

		$admin_options = $this->optionsGetByKey ( 'admin_email', true );

		$from = (empty ( $admin_options )) ? 'noreply@ooyes.net' : $admin_options ['option_value'];

		$object = $options ['option_value'];

		$object = str_replace ( "{username}", $username, $object );

		$message = str_replace ( "{username}", $username, $options ['option_value2'] );

		$message = str_replace ( "{password}", $password, $options ['option_value2'] );

		if ($parent) {

			$message = str_replace ( "{parent}", $parent, $message );

			$message = str_replace ( "{parent}", $parent, $object );

		}

		@mail ( $to, $object, $message, "From: $from\nReply-To: $from\nContent-Type: text/html;charset=\"utf-8\"\nContent-Transfer-Encoding: 8bit" );

	}

	/**
	 * Tnis method is not fully tested as it is developed only for activation
	 * email
	 *
	 * @param
	 *       	 $sendOptions
	 */

	function sendMail2($sendOptions) {

		$mailConfig = Array ('protocol' => 'smtp', 'smtp_host' => 'ssl://smtp.googlemail.com', 'smtp_port' => 465, 'smtp_user' => 'support@ooyes.net', 'smtp_pass' => 'otivamnabali' );

		$this->load->library ( 'email', $mailConfig );

		$this->email->set_newline ( "\r\n" );

		$this->email->from ( $sendOptions ['from_email'], $sendOptions ['from_name'] );

		$this->email->to ( $sendOptions ['to_email'] );

		$this->email->subject ( $sendOptions ['subject'] );

		$this->email->message ( $sendOptions ['message'] );

		$this->email->send ();

	}

}

