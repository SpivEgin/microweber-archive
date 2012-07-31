<?php

class js extends CI_Controller {

	function __construct() {

		parent::__construct();

		require_once (APPPATH . 'controllers/default_constructor.php');
		//p($user_session);
		//	require_once (APPPATH . 'controllers/api/default_constructor.php');

	}

	function index() {
		header("Content-type: text/javascript");
		$url = url();
		$cache_id = "js_api";
		$cache_group = 'global/blocks';

		$edit = url_param('edit');
		if ($edit) {
			$this -> template['edit'] = true;
		}

		$load_editmode = url_param('load_editmode');

		$editmode = $this -> session -> userdata('editmode');
		if ($editmode == true) {
			$edit = true;
			$cache_id = "js_api_edit";
		}
		if ($edit == true) {
			$cache_group = 'global/blocks/edit';
			$cache_id = "js_api_edit";
		}

		//	$editmode =$edit= false;
		//	$cache_content = cache_get_content ( $cache_id, $cache_group );
		$cache_content = false;
		if (($cache_content) != false) {

			$this -> output -> set_output($cache_content);

		} else {

			//header ( 'Content-type: application/javascript' );
			$load_extra_libs = false;
			$files = readDirIntoArray(APPPATH . 'controllers/api/js/', 'files');

			//$layout = $layout . "\n\n\n // File: _php.default.min.js \n\n" . $this->load->file ( APPPATH . 'controllers/api/js/' . '_php.default.min.js', true );

			//$layout = $layout . "\n\n" . $this->load->file ( APPPATH . 'controllers/api/js_dist/' . 'jquery.min.js', true );

			if ((isset($_SERVER['HTTP_REFERER'])) and ($_SERVER['HTTP_REFERER'] != '')) {

				$url = urldecode($_SERVER['HTTP_REFERER']);
				if (strstr("/admin", $url)) {
					//preg_match ( "'(\?�&)q=(.*?)(&�$)'si", " $url ", $keywords );
					$load_extra_libs = true;
					$in_admin = true;
				}
			}

			$and_ui = url_param('ui');
			if ($and_ui) {

			} else {
				$and_ui = false;
			}

			$no_mw_edit = url_param('no_mw_edit');
			if ($no_mw_edit) {

			} else {
				$no_mw_edit = false;
			}

			//if (($editmode == true) or $load_extra_libs == true or $and_ui == true) {
			//$layout = $layout . "\n\n" . $this->load->file ( APPPATH . 'controllers/api/js_dist/' . 'jquery-ui-1.8.9.js', true );
			//$apicss = '<link rel="stylesheet" href="' . ADMIN_STATIC_FILES_URL . 'css/api.css" type="text/css" media="screen"  />';
			//	}
			//$layout = $layout . "\n\n" . $this->load->file ( APPPATH . 'controllers/api/js_dist/' . 'jquery.form.js', true );
			//$layout = $layout . "\n\n" . $this->load->file ( APPPATH . 'controllers/api/js_dist/' . 'jquery.embedly.min.js', true );
			//$layout = $layout . "\n\n" . $this->load->file ( APPPATH . 'controllers/api/js_dist/' . 'jquery_plugins.js', true );
			//$layout = $layout . "\n\n" . $this->load->file ( APPPATH . 'controllers/api/js_dist/' . 'jquery.tmplPlus.min.js', true );

			//$layout = $layout . "\n\n" . $this->load->file ( APPPATH . 'controllers/api/js_dist/' . 'rangy-core.js', true );
			//$layout = $layout . "\n\n" . $this->load->file ( APPPATH . 'controllers/api/js_dist/' . 'rangy-cssclassapplier.js', true );

			//pecata

			//$layout = $layout . "\n\n" . $this->load->file ( APPPATH . 'controllers/api/js_dist/' . 'jquery.cookie.js', true );
			$layout = $layout . "\n\n" . $this -> load -> file(APPPATH . 'controllers/api/js_dist/' . 'jquery.form.js', true);
			$layout = $layout . "\n\n" . $this -> load -> file(APPPATH . 'controllers/api/js_dist/' . 'jquery.embedly.min.js', true);

			$layout = $layout . "\n\n\n // File: _mw.js \n\n" . $this -> load -> file(APPPATH . 'controllers/api/js/' . '_mw.js', true);

			$ajax = isAjax();
			if ($ajax == false) {
				if (($edit == true and $in_admin == false and $no_mw_edit == false) or $load_editmode == true) {
					//	$layout = $layout . "\n\n\n // File: _mw_edit.js \n\n" . $this->load->file ( APPPATH . 'controllers/api/js/' . '_mw_edit.js', true );
					//	$layout = $layout . "\n\n" . $this->load->file ( APPPATH . 'controllers/api/js/' . '_mw_extra.js', true );

				}
				if ($editmode == true) {
				}
			}

			$layout = $layout . "\n\n\n // File: utils.js \n\n" . $this -> load -> file(APPPATH . 'controllers/api/js/' . 'utils.js', true);
			//	$layout = $layout . "\n\n\n //  \n\n" . $this->load->file ( APPPATH . 'controllers/api/js/' . 'box.js', true );

			foreach ($files as $file) {
				if (substr($file, -2) == 'js') {
					if (($file != '_mw.js') and ($file != 'utils.js') and ($file != '_mw_edit.js')) {
						// $this->load->vars ( $this->template );
						$layout = $layout . "\n\n\n // File: $file \n\n" . $this -> load -> file(APPPATH . 'controllers/api/js/' . $file, true);
					}

				}

			}

			$layout = $this -> content_model -> applyGlobalTemplateReplaceables($layout);
			//$layout = $layout . $apicss;
			$layout = trim($layout);
			$this -> core_model -> cacheWriteAndEncode($layout, $cache_id, $cache_group);
			$this -> output -> set_output($layout);

		}
		//var_dump ($content_filename_pre, $files );

	}

}
