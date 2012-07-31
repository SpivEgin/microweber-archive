<?php

class Module extends CI_Controller {
	function __construct() {
		parent::__construct();

		require_once (APPPATH . 'controllers/default_constructor.php');
		// require_once (APPPATH . 'controllers/api/default_constructor.php');
	}

	function index() {
		if ($_SERVER["HTTP_REFERER"]) {
			$url = $_SERVER["HTTP_REFERER"];
			if (trim($url) == '') {

				$page = $this -> content_model -> getContentHomepage();
				// var_dump($page);
			} else {
				if ($page['is_home'] != 'y') {
					$post_maybe = $this -> content_model -> getContentByURLAndCache($url);
				}
				if (intval($post_maybe['id']) != 0) {
					$post_maybe = $this -> content_model -> contentGetByIdAndCache($post_maybe['id']);
				}

				$page = $this -> content_model -> getPageByURLAndCache($url);
			}

			if ($post_maybe['content_type'] == 'post') {
				$post = $post_maybe;
				if (defined('POST_ID') == false) {
					define('POST_ID', $post['id']);
				}

				if (defined('PAGE_ID') == false) {
					define('PAGE_ID', $page['id']);
				}
			}
		}

		$module_info = url_param('module_info', true);

		if ($module_info) {
			if ($_POST['module']) {
				$_POST['module'] = str_replace('..', '', $_POST['module']);
				$try_config_file = MODULES_DIR . '' . $_POST['module'] . '_config.php';
				$try_config_file = normalize_path($try_config_file, false);
				if (is_file($try_config_file)) {
					include ($try_config_file);
					if ($config['icon'] == false) {
						$config['icon'] = MODULES_DIR . '' . $_POST['module'] . '.png'; ;
						$config['icon'] = pathToURL($config['icon']);
					}
					print json_encode($config);
					exit();
				}
			}
		}

		$is_iframe = url_param('iframe');

		$base64 = url_param('base64', true);

		$admin = url_param('admin', true);

		$mod1 = url_param('module_name', true);

		$decode_vars = url_param('decode_vars', true);
		$reload_module = url_param('reload_module', true);

		$mod_to_edit = url_param('module_to_edit', true);
		$element_id = url_param('element_id', true);

		if ($mod1 != false) {
			$mod1 = urldecode($mod1);
		}
		$mod_iframe = false;
		if ($mod_to_edit != false) {
			$mod_to_edit = str_ireplace('_mw_slash_replace_', '/', $mod_to_edit);
			$mod_iframe = true;
		}
		// p($mod_to_edit);
		if ($base64 == false) {
			if ($is_iframe) {
				$data = $is_iframe;
				$data = base64_decode($data);

				$data = unserialize($data);
			} else {
				$data = $_POST;
			}

			if ($reload_module == 'edit_tag') {
				$reload_module = ($_POST);
				$data = $reload_module;
				// p($data);
			}

			if ($mod1 != '') {
				$data['module'] = $mod1;
			}

			$is_page_id = url_param('page_id', true);
			if ($is_page_id != '') {
				$data['page_id'] = $is_page_id;
			}

			$is_post_id = url_param('post_id', true);
			if ($is_post_id != '') {
				$data['post_id'] = $is_post_id;
			}

			$is_category_id = url_param('category_id', true);
			if ($is_category_id != '') {
				$data['category_id'] = $is_category_id;
			}

			$is_rel = url_param('rel', true);
			if ($is_rel != '') {
				$data['rel'] = $is_rel;

				if ($is_rel == 'page') {
					$test = get_ref_page();
					if (!empty($test)) {
						if ($data['page_id'] == false) {
							$data['page_id'] = $test['id'];
						}
					}
					// p($test);
				}

				if ($is_rel == 'post') {
					// $refpage = get_ref_page ();
					$refpost = get_ref_post();
					if (!empty($refpost)) {
						if ($data['post_id'] == false) {
							$data['post_id'] = $refpost['id'];
						}
					}
				}

				if ($is_rel == 'category') {
					// $refpage = get_ref_page ();
					$refpost = get_ref_post();
					if (!empty($refpost)) {
						if ($data['post_id'] == false) {
							$data['post_id'] = $refpost['id'];
						}
					}
				}
			}

			$tags = false;
			$mod_n = false;
			if ($data['mw_params_module'] != false) {
				if (trim($data['mw_params_module']) != '') {
					$mod_n = $data['module'] = $data['mw_params_module'];
				}
			}
			if ($data['data-type'] == false) {
				$mod_n = $data['data-type'] = $data['module'];
			}
			if ($data['data-module'] != false) {
				if (trim($data['data-module']) != '') {
					$mod_n = $data['module'] = $data['data-module'];
				}
			}
			// p($data);
			if ($data['module']) {
				unset($data['module']);
			}

			$has_id = false;
			foreach ($data as $k => $v) {

				if ($k == 'id') {
					$has_id = true;
				}

				if (is_array($v)) {
					$v1 = encode_var($v);
					$tags .= "{$k}=\"$v1\" ";
				} else {
					$tags .= "{$k}=\"$v\" ";
				}
			}

			if ($has_id == false) {

				$mod_n = url_title($mod_n).'-'.date("YmdHis");
				$tags .= "id=\"$mod_n\" ";

			}

			$tags = "<module {$tags} />";
		} else {
		}

		$opts = array();
		if ($_POST) {
			$opts = $_POST;
		}
		$opts['admin'] = $admin;

		if (($base64 != false) or $is_iframe != false) {
			$opts['do_not_wrap'] = true;
		}
		// $this->load->model ( 'Template_model', 'template_model' );
		// $res = $this->template_model->parseMicrwoberTags ( $tags, $opts );
		$res = parse_micrwober_tags($tags, $opts);
		$res = preg_replace('~<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>\s*~i', '', $res);

		/*	if ($admin == true) {
		 $is_admin = is_admin ();
		 if ($is_admin == false) {
		 $go = site_url ( 'login' );
		 safe_redirect ( $go );
		 }
		 $opts ['no_cache'] = true;

		 $res_1 =$this->load->view ( 'admin/module_admin', true, true );
		 $res_1 = parse_micrwober_tags($tags, $opts) ;
		 //$res_1 = $this->template_model->parseMicrwoberTags ( $res_1, $opts );
		 }

		 if ($res_1) {
		 $res = str_replace('{content}', $res, $res_1);
		 }*/
		print $res;
		exit();
		// phpinfo();
	}

}
