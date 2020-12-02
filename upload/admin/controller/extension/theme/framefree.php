<?php
class ControllerExtensionThemeFrameFree extends Controller {
	private $error = array();

	public function index() {

		if (file_exists('../system/framefree.ocmod.xml')) {
	    $frame_modifier = simplexml_load_file('../system/framefree.ocmod.xml');
			$data['t1_version'] = $frame_modifier->version;
		} else {
			$this->load->model('setting/modification');
			$results = $this->model_setting_modification->getModifications();
			foreach ($results as $result) {
				$data['t1_version'] = ' null';
				if ($result['code'] == 'frame_theme') {
					$data['t1_version'] = $result['version'];
					break;
				}
			}
		}

		$this->load->language('extension/theme/framefree');

		$this->document->addStyle('view/stylesheet/framefreetheme/framefreetheme.css');

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));


		$this->load->model('extension/theme/framefree');

		$this->load->model('tool/image');
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		$this->load->model('localisation/language');

		$languages = $this->model_localisation_language->getLanguages();
		$language_id = $this->config->get('config_language_id');
		$data['languages'] = $languages;
		$data['language_id'] = $language_id;

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

			$custom_css = "";

			if (isset($this->request->post['t1_webfont_style'])) {
				$custom_css .= "body{" . html_entity_decode($this->request->post['t1_webfont_style'], ENT_QUOTES, 'UTF-8') . "}" ;
			}

			if (isset($this->request->post['t1_stikers'])) {
				foreach ($this->request->post['t1_stikers'] as $key => $stiker) {
					$custom_css .= ".stiker-" . $key . "{background-color:" . $stiker['bg_color'] . ";color:" . $stiker['txt_color'] . "}" ;
				}
			}

			if (isset($this->request->post['t1_cust_css'])) {
				$custom_css .= html_entity_decode($this->request->post['t1_cust_css'], ENT_QUOTES, 'UTF-8');
			}

			if ($custom_css) {
				$handle = fopen(DIR_CATALOG . 'view/theme/' .  $this->request->post['theme_framefree_directory'] . '/stylesheet/custom.css', 'w');
				flock($handle, LOCK_EX);
				fwrite($handle, $custom_css);
				fflush($handle);
				flock($handle, LOCK_UN);
				fclose($handle);
			}


      $scss_settings = '';

      if (isset($this->request->post['t1_bs_bp_xxl']) && $this->request->post['t1_bs_bp_xxl']) {
        $scss_settings .= '$grid-breakpoints: ( xs: 0, sm: 576px, md: 768px, lg: 992px, xl: 1200px, xxl: 1400px );';
        $scss_settings .= '$container-max-widths: ( sm: 540px, md: 720px, lg: 960px, xl: 1140px, xxl: 1340px );';
      }

      if (isset($this->request->post['t1_bs_radius'])) {
        $scss_settings .=  '$border-radius:' . (float)$this->request->post['t1_bs_radius'] . 'rem;';
      }

      if (isset($this->request->post['t1_color_schema']) && $this->request->post['t1_color_schema']) {
				foreach ($this->request->post['t1_color_schema'] as $key => $value) {
					if ( $key && $value ) {
						$scss_settings .= "\$". str_replace('_', '-', $key) . ": " . $value . ";" ;
					}
				}
      }

      if (isset($this->request->post['t1_bs_settings']['main_colors']) && $this->request->post['t1_bs_settings']['main_colors']) {
				foreach ($this->request->post['t1_bs_settings']['main_colors'] as $key => $value) {
					if ( $key && $value ) {
						$scss_settings .= "\$". str_replace('_', '-', $key) . ": " . "\$" . str_replace('_', '-', $value) . ";" ;
					}
				}
      }

      if (isset($this->request->post['t1_bs_settings']['main_settings']) && $this->request->post['t1_bs_settings']['main_settings']) {
				foreach ($this->request->post['t1_bs_settings']['main_settings'] as $key => $value) {
					if ( $key && $value ) {
            if (is_numeric($value)) {
              $scss_settings .= "\$". str_replace('_', '-', $key) . ": " . $value . ";" ;
            } else {
              $scss_settings .= "\$". str_replace('_', '-', $key) . ": " . "\$" . str_replace('_', '-', $value) . ";" ;
            }
					}
				}
      }

      $scss_settings .= '@import "'. DIR_CATALOG . 'view/theme/' . $this->request->post['theme_framefree_directory'] .'/bootstrap/settings";';

      $scss_settings .= '@import "'. DIR_CATALOG . 'view/theme/' . $this->request->post['theme_framefree_directory'] .'/bootstrap/scss/functions";';
      $scss_settings .= '@import "'. DIR_CATALOG . 'view/theme/' . $this->request->post['theme_framefree_directory'] .'/bootstrap/scss/variables";';

      $scss_settings .= isset($this->request->post['t1_bs_settings_scss']) ? $this->request->post['t1_bs_settings_scss'] : '';

      if (isset($this->request->post['t1_bs_components']) && $this->request->post['t1_bs_components']) {
        foreach ($this->request->post['t1_bs_components'] as $component) {
          $scss_settings .= '@import "'. DIR_CATALOG . 'view/theme/' . $this->request->post['theme_framefree_directory'] .'/bootstrap/scss/'. $component .'";';
        }
      }

      $scss_settings .= '@import "'. DIR_CATALOG . 'view/theme/' . $this->request->post['theme_framefree_directory'] .'/bootstrap/additional";';

      if (!file_exists(DIR_STORAGE . 'vendor/scssphp/scssphp/scss.inc.php')) {
        include_once(DIR_CATALOG . 'view/theme/ff_frame/assets/scssphp/scss.inc.php');
      }

	    $scss = new ScssPhp\ScssPhp\Compiler();
	    $scss->setFormatter('ScssPhp\ScssPhp\Formatter\Crunched');
	    // $scss->setFormatter('ScssPhp\ScssPhp\Formatter\Expanded');


      try {

        $output = $scss->compile($scss_settings);

      } catch (Exception $e) {

        $output = $e->getMessage();
        $scss_parse_error = $this->language->get('scss_parse_error') . $e->getMessage();

      }

      if (!isset($scss_parse_error)) {

        $file = DIR_CATALOG . 'view/theme/' .  $this->request->post['theme_framefree_directory'] . '/bootstrap/css/bootstrap.min.css';

        $handle = fopen($file, 'w');

        flock($handle, LOCK_EX);
        fwrite($handle, $output);
        fflush($handle);
        flock($handle, LOCK_UN);
        fclose($handle);

      }

			$this->model_extension_theme_framefree->editFrame('theme_framefree', $this->request->post, $this->request->get['store_id']);

			

			$this->session->data['success'] = $this->language->get('text_success');
       		$this->session->data['success'] .= isset($scss_parse_error) ? $scss_parse_error : '';

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=theme', true));

			
		}

		$data['heading_title'] = strip_tags($this->language->get('heading_title'));
		$data['text_heading_title'] = strip_tags($this->language->get('text_heading_title'));

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['image_category'])) {
			$data['error_image_category'] = $this->error['image_category'];
		} else {
			$data['error_image_category'] = '';
		}

    if (isset($this->error['image_manufacturer'])) {
			$data['error_image_manufacturer'] = $this->error['image_manufacturer'];
		} else {
			$data['error_image_manufacturer'] = '';
		}

		if (isset($this->error['image_thumb'])) {
			$data['error_image_thumb'] = $this->error['image_thumb'];
		} else {
			$data['error_image_thumb'] = '';
		}

		if (isset($this->error['image_popup'])) {
			$data['error_image_popup'] = $this->error['image_popup'];
		} else {
			$data['error_image_popup'] = '';
		}

		if (isset($this->error['image_product'])) {
			$data['error_image_product'] = $this->error['image_product'];
		} else {
			$data['error_image_product'] = '';
		}

		if (isset($this->error['image_additional'])) {
			$data['error_image_additional'] = $this->error['image_additional'];
		} else {
			$data['error_image_additional'] = '';
		}

		if (isset($this->error['image_related'])) {
			$data['error_image_related'] = $this->error['image_related'];
		} else {
			$data['error_image_related'] = '';
		}

		if (isset($this->error['image_compare'])) {
			$data['error_image_compare'] = $this->error['image_compare'];
		} else {
			$data['error_image_compare'] = '';
		}

		if (isset($this->error['image_wishlist'])) {
			$data['error_image_wishlist'] = $this->error['image_wishlist'];
		} else {
			$data['error_image_wishlist'] = '';
		}

		if (isset($this->error['image_cart'])) {
			$data['error_image_cart'] = $this->error['image_cart'];
		} else {
			$data['error_image_cart'] = '';
		}

		if (isset($this->error['image_location'])) {
			$data['error_image_location'] = $this->error['image_location'];
		} else {
			$data['error_image_location'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=theme', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => strip_tags($this->language->get('heading_title')),
			'href' => $this->url->link('extension/theme/framefree', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $this->request->get['store_id'], true)
		);

		$data['action'] = $this->url->link('extension/theme/framefree', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $this->request->get['store_id'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=theme', true);

		if (isset($this->request->get['store_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$setting_info = $this->model_extension_theme_framefree->getFrame('theme_framefree', $this->request->get['store_id']);
		}

		if (isset($this->request->post['theme_framefree_directory'])) {
			$data['theme_framefree_directory'] = $this->request->post['theme_framefree_directory'];
		} elseif (isset($setting_info['theme_framefree_directory'])) {
			$data['theme_framefree_directory'] = $setting_info['theme_framefree_directory'];
		} else {
			$data['theme_framefree_directory'] = 'ff_frame';
		}

		$data['directories'] = array();

		$directories = glob(DIR_CATALOG . 'view/theme/ff_*', GLOB_ONLYDIR);

		foreach ($directories as $directory) {

			$theme_info = array();

			if (file_exists($directory . '/info.txt')) {
				$lines = file($directory . '/info.txt');
				foreach ($lines as $line) {
					$theme_info[] = trim($line);
				}
			}

			if (!empty($theme_info)) { $title = explode(":", $theme_info[0]); }

			$data['directories'][] = array(
				'name'  => basename($directory),
				'info'  => $theme_info,
				'title' => trim($title[1]),
			);
		}

		if (isset($this->request->post['theme_framefree_product_limit'])) {
			$data['theme_framefree_product_limit'] = $this->request->post['theme_framefree_product_limit'];
		} elseif (isset($setting_info['theme_framefree_product_limit'])) {
			$data['theme_framefree_product_limit'] = $setting_info['theme_framefree_product_limit'];
		} else {
			$data['theme_framefree_product_limit'] = 15;
		}

		if (isset($this->request->post['theme_framefree_status'])) {
			$data['theme_framefree_status'] = $this->request->post['theme_framefree_status'];
		} elseif (isset($setting_info['theme_framefree_status'])) {
			$data['theme_framefree_status'] = $setting_info['theme_framefree_status'];
		} else {
			$data['theme_framefree_status'] = '';
		}

		if (isset($this->request->post['theme_framefree_product_description_length'])) {
			$data['theme_framefree_product_description_length'] = $this->request->post['theme_framefree_product_description_length'];
		} elseif (isset($setting_info['theme_framefree_product_description_length'])) {
			$data['theme_framefree_product_description_length'] = $setting_info['theme_framefree_product_description_length'];
		} else {
			$data['theme_framefree_product_description_length'] = 100;
		}

		if (isset($this->request->post['theme_framefree_image_category_width'])) {
			$data['theme_framefree_image_category_width'] = $this->request->post['theme_framefree_image_category_width'];
		} elseif (isset($setting_info['theme_framefree_image_category_width'])) {
			$data['theme_framefree_image_category_width'] = $setting_info['theme_framefree_image_category_width'];
		} else {
			$data['theme_framefree_image_category_width'] = 80;
		}

		if (isset($this->request->post['theme_framefree_image_category_height'])) {
			$data['theme_framefree_image_category_height'] = $this->request->post['theme_framefree_image_category_height'];
		} elseif (isset($setting_info['theme_framefree_image_category_height'])) {
			$data['theme_framefree_image_category_height'] = $setting_info['theme_framefree_image_category_height'];
		} else {
			$data['theme_framefree_image_category_height'] = 80;
		}

		if (isset($this->request->post['theme_framefree_image_manufacturer_width'])) {
			$data['theme_framefree_image_manufacturer_width'] = $this->request->post['theme_framefree_image_manufacturer_width'];
		} elseif (isset($setting_info['theme_framefree_image_manufacturer_width'])) {
			$data['theme_framefree_image_manufacturer_width'] = $setting_info['theme_framefree_image_manufacturer_width'];
		} else {
			$data['theme_framefree_image_manufacturer_width'] = 80;
		}

		if (isset($this->request->post['theme_framefree_image_manufacturer_height'])) {
			$data['theme_framefree_image_manufacturer_height'] = $this->request->post['theme_framefree_image_manufacturer_height'];
		} elseif (isset($setting_info['theme_framefree_image_manufacturer_height'])) {
			$data['theme_framefree_image_manufacturer_height'] = $setting_info['theme_framefree_image_manufacturer_height'];
		} else {
			$data['theme_framefree_image_manufacturer_height'] = 80;
		}

		if (isset($this->request->post['theme_framefree_image_thumb_width'])) {
			$data['theme_framefree_image_thumb_width'] = $this->request->post['theme_framefree_image_thumb_width'];
		} elseif (isset($setting_info['theme_framefree_image_thumb_width'])) {
			$data['theme_framefree_image_thumb_width'] = $setting_info['theme_framefree_image_thumb_width'];
		} else {
			$data['theme_framefree_image_thumb_width'] = 356;
		}

		if (isset($this->request->post['theme_framefree_image_thumb_height'])) {
			$data['theme_framefree_image_thumb_height'] = $this->request->post['theme_framefree_image_thumb_height'];
		} elseif (isset($setting_info['theme_framefree_image_thumb_height'])) {
			$data['theme_framefree_image_thumb_height'] = $setting_info['theme_framefree_image_thumb_height'];
		} else {
			$data['theme_framefree_image_thumb_height'] = 356;
		}

		if (isset($this->request->post['theme_framefree_image_popup_width'])) {
			$data['theme_framefree_image_popup_width'] = $this->request->post['theme_framefree_image_popup_width'];
		} elseif (isset($setting_info['theme_framefree_image_popup_width'])) {
			$data['theme_framefree_image_popup_width'] = $setting_info['theme_framefree_image_popup_width'];
		} else {
			$data['theme_framefree_image_popup_width'] = 500;
		}

		if (isset($this->request->post['theme_framefree_image_popup_height'])) {
			$data['theme_framefree_image_popup_height'] = $this->request->post['theme_framefree_image_popup_height'];
		} elseif (isset($setting_info['theme_framefree_image_popup_height'])) {
			$data['theme_framefree_image_popup_height'] = $setting_info['theme_framefree_image_popup_height'];
		} else {
			$data['theme_framefree_image_popup_height'] = 500;
		}

		if (isset($this->request->post['theme_framefree_image_product_width'])) {
			$data['theme_framefree_image_product_width'] = $this->request->post['theme_framefree_image_product_width'];
		} elseif (isset($setting_info['theme_framefree_image_product_width'])) {
			$data['theme_framefree_image_product_width'] = $setting_info['theme_framefree_image_product_width'];
		} else {
			$data['theme_framefree_image_product_width'] = 170;
		}

		if (isset($this->request->post['theme_framefree_image_product_height'])) {
			$data['theme_framefree_image_product_height'] = $this->request->post['theme_framefree_image_product_height'];
		} elseif (isset($setting_info['theme_framefree_image_product_height'])) {
			$data['theme_framefree_image_product_height'] = $setting_info['theme_framefree_image_product_height'];
		} else {
			$data['theme_framefree_image_product_height'] = 170;
		}

		if (isset($this->request->post['theme_framefree_image_additional_width'])) {
			$data['theme_framefree_image_additional_width'] = $this->request->post['theme_framefree_image_additional_width'];
		} elseif (isset($setting_info['theme_framefree_image_additional_width'])) {
			$data['theme_framefree_image_additional_width'] = $setting_info['theme_framefree_image_additional_width'];
		} else {
			$data['theme_framefree_image_additional_width'] = 56;
		}

		if (isset($this->request->post['theme_framefree_image_additional_height'])) {
			$data['theme_framefree_image_additional_height'] = $this->request->post['theme_framefree_image_additional_height'];
		} elseif (isset($setting_info['theme_framefree_image_additional_height'])) {
			$data['theme_framefree_image_additional_height'] = $setting_info['theme_framefree_image_additional_height'];
		} else {
			$data['theme_framefree_image_additional_height'] = 56;
		}

		if (isset($this->request->post['theme_framefree_image_related_width'])) {
			$data['theme_framefree_image_related_width'] = $this->request->post['theme_framefree_image_related_width'];
		} elseif (isset($setting_info['theme_framefree_image_related_width'])) {
			$data['theme_framefree_image_related_width'] = $setting_info['theme_framefree_image_related_width'];
		} else {
			$data['theme_framefree_image_related_width'] = 170;
		}

		if (isset($this->request->post['theme_framefree_image_related_height'])) {
			$data['theme_framefree_image_related_height'] = $this->request->post['theme_framefree_image_related_height'];
		} elseif (isset($setting_info['theme_framefree_image_related_height'])) {
			$data['theme_framefree_image_related_height'] = $setting_info['theme_framefree_image_related_height'];
		} else {
			$data['theme_framefree_image_related_height'] = 170;
		}

		if (isset($this->request->post['theme_framefree_image_compare_width'])) {
			$data['theme_framefree_image_compare_width'] = $this->request->post['theme_framefree_image_compare_width'];
		} elseif (isset($setting_info['theme_framefree_image_compare_width'])) {
			$data['theme_framefree_image_compare_width'] = $setting_info['theme_framefree_image_compare_width'];
		} else {
			$data['theme_framefree_image_compare_width'] = 90;
		}

		if (isset($this->request->post['theme_framefree_image_compare_height'])) {
			$data['theme_framefree_image_compare_height'] = $this->request->post['theme_framefree_image_compare_height'];
		} elseif (isset($setting_info['theme_framefree_image_compare_height'])) {
			$data['theme_framefree_image_compare_height'] = $setting_info['theme_framefree_image_compare_height'];
		} else {
			$data['theme_framefree_image_compare_height'] = 90;
		}

		if (isset($this->request->post['theme_framefree_image_wishlist_width'])) {
			$data['theme_framefree_image_wishlist_width'] = $this->request->post['theme_framefree_image_wishlist_width'];
		} elseif (isset($setting_info['theme_framefree_image_wishlist_width'])) {
			$data['theme_framefree_image_wishlist_width'] = $setting_info['theme_framefree_image_wishlist_width'];
		} else {
			$data['theme_framefree_image_wishlist_width'] = 47;
		}

		if (isset($this->request->post['theme_framefree_image_wishlist_height'])) {
			$data['theme_framefree_image_wishlist_height'] = $this->request->post['theme_framefree_image_wishlist_height'];
		} elseif (isset($setting_info['theme_framefree_image_wishlist_height'])) {
			$data['theme_framefree_image_wishlist_height'] = $setting_info['theme_framefree_image_wishlist_height'];
		} else {
			$data['theme_framefree_image_wishlist_height'] = 47;
		}

		if (isset($this->request->post['theme_framefree_image_cart_width'])) {
			$data['theme_framefree_image_cart_width'] = $this->request->post['theme_framefree_image_cart_width'];
		} elseif (isset($setting_info['theme_framefree_image_cart_width'])) {
			$data['theme_framefree_image_cart_width'] = $setting_info['theme_framefree_image_cart_width'];
		} else {
			$data['theme_framefree_image_cart_width'] = 47;
		}

		if (isset($this->request->post['theme_framefree_image_cart_height'])) {
			$data['theme_framefree_image_cart_height'] = $this->request->post['theme_framefree_image_cart_height'];
		} elseif (isset($setting_info['theme_framefree_image_cart_height'])) {
			$data['theme_framefree_image_cart_height'] = $setting_info['theme_framefree_image_cart_height'];
		} else {
			$data['theme_framefree_image_cart_height'] = 47;
		}

		if (isset($this->request->post['theme_framefree_image_location_width'])) {
			$data['theme_framefree_image_location_width'] = $this->request->post['theme_framefree_image_location_width'];
		} elseif (isset($setting_info['theme_framefree_image_location_width'])) {
			$data['theme_framefree_image_location_width'] = $setting_info['theme_framefree_image_location_width'];
		} else {
			$data['theme_framefree_image_location_width'] = 268;
		}

		if (isset($this->request->post['theme_framefree_image_location_height'])) {
			$data['theme_framefree_image_location_height'] = $this->request->post['theme_framefree_image_location_height'];
		} elseif (isset($setting_info['theme_framefree_image_location_height'])) {
			$data['theme_framefree_image_location_height'] = $setting_info['theme_framefree_image_location_height'];
		} else {
			$data['theme_framefree_image_location_height'] = 50;
		}

		if (isset($this->request->post['t1_high_definition_imgs'])) {
			$data['t1_high_definition_imgs'] = $this->request->post['t1_high_definition_imgs'];
		} elseif (isset($setting_info['t1_high_definition_imgs'])) {
			$data['t1_high_definition_imgs'] = $setting_info['t1_high_definition_imgs'];
		} else {
			$data['t1_high_definition_imgs'] = false;
		}

		if (isset($this->request->post['t1_logo'])) {
			$data['t1_logo'] = $this->request->post['t1_logo'];
		} elseif (isset($setting_info['t1_logo'])) {
			$data['t1_logo'] = $setting_info['t1_logo'];
		} else {
			$data['t1_logo'] = false;
		}

		if (isset($this->request->post['t1_logo_width'])) {
			$data['t1_logo_width'] = $this->request->post['t1_logo_width'];
		} elseif (isset($setting_info['t1_logo_width'])) {
			$data['t1_logo_width'] = $setting_info['t1_logo_width'];
		} else {
			$data['t1_logo_width'] = 200;
		}

		if (isset($this->request->post['t1_logo_height'])) {
			$data['t1_logo_height'] = $this->request->post['t1_logo_height'];
		} elseif (isset($setting_info['t1_logo_height'])) {
			$data['t1_logo_height'] = $setting_info['t1_logo_height'];
		} else {
			$data['t1_logo_height'] = 60;
		}

		if (isset($this->request->post['t1_logo']) && is_file(DIR_IMAGE . $this->request->post['t1_logo'])) {
			$data['logo'] = $this->model_tool_image->resize($this->request->post['t1_logo'], 100, 100);
		} elseif (isset($setting_info['t1_logo']) && is_file(DIR_IMAGE . $setting_info['t1_logo'])) {
			$data['logo'] = $this->model_tool_image->resize($setting_info['t1_logo'], 100, 100);
		} else {
			$data['logo'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}

		if (isset($this->request->post['t1_svg_logo_status'])) {
			$data['t1_svg_logo_status'] = $this->request->post['t1_svg_logo_status'];
		} elseif (isset($setting_info['t1_svg_logo_status'])) {
			$data['t1_svg_logo_status'] = $setting_info['t1_svg_logo_status'];
		} else {
			$data['t1_svg_logo_status'] = false;
		}

		if (isset($this->request->post['t1_svg_logo'])) {
			$data['t1_svg_logo'] = $this->request->post['t1_svg_logo'];
		} elseif (isset($setting_info['t1_svg_logo'])) {
			$data['t1_svg_logo'] = $setting_info['t1_svg_logo'];
		} else {
			$data['t1_svg_logo'] = false;
		}

		if (isset($this->request->post['t1_meta_theme_color'])) {
			$data['t1_meta_theme_color'] = $this->request->post['t1_meta_theme_color'];
		} elseif (isset($setting_info['t1_meta_theme_color'])) {
			$data['t1_meta_theme_color'] = $setting_info['t1_meta_theme_color'];
		} else {
			$data['t1_meta_theme_color'] = '#ffffff';
		}

		if (isset($this->request->post['t1_webfont_status'])) {
			$data['t1_webfont_status'] = $this->request->post['t1_webfont_status'];
		} elseif (isset($setting_info['t1_webfont_status'])) {
			$data['t1_webfont_status'] = $setting_info['t1_webfont_status'];
		} else {
			$data['t1_webfont_status'] = false;
		}

		if (isset($this->request->post['t1_webfont_link'])) {
			$data['t1_webfont_link'] = $this->request->post['t1_webfont_link'];
		} elseif (isset($setting_info['t1_webfont_link'])) {
			$data['t1_webfont_link'] = $setting_info['t1_webfont_link'];
		} else {
			$data['t1_webfont_link'] = false;
		}

		if (isset($this->request->post['t1_webfont_style'])) {
			$data['t1_webfont_style'] = $this->request->post['t1_webfont_style'];
		} elseif (isset($setting_info['t1_webfont_style'])) {
			$data['t1_webfont_style'] = $setting_info['t1_webfont_style'];
		} else {
			$data['t1_webfont_style'] = false;
		}

		if (isset($this->request->post['t1_fontawesome_status'])) {
			$data['t1_fontawesome_status'] = $this->request->post['t1_fontawesome_status'];
		} elseif (isset($setting_info['t1_fontawesome_status'])) {
			$data['t1_fontawesome_status'] = $setting_info['t1_fontawesome_status'];
		} else {
			$data['t1_fontawesome_status'] = false;
		}

		if (isset($this->request->post['t1_buy_button_status'])) {
			$data['t1_buy_button_status'] = $this->request->post['t1_buy_button_status'];
		} elseif (isset($setting_info['t1_buy_button_status'])) {
			$data['t1_buy_button_status'] = $setting_info['t1_buy_button_status'];
		} else {
			$data['t1_buy_button_status'] = false;
		}

		if (isset($this->request->post['t1_buy_button_disabled_text'])) {
			$data['t1_buy_button_disabled_text'] = $this->request->post['t1_buy_button_disabled_text'];
		} elseif (isset($setting_info['t1_buy_button_disabled_text'])) {
			$data['t1_buy_button_disabled_text'] = $setting_info['t1_buy_button_disabled_text'];
		} else {
			$data['t1_buy_button_disabled_text'] = false;
		}

		if (isset($this->request->post['t1_preloader_status'])) {
			$data['t1_preloader_status'] = $this->request->post['t1_preloader_status'];
		} elseif (isset($setting_info['t1_preloader_status'])) {
			$data['t1_preloader_status'] = $setting_info['t1_preloader_status'];
		} else {
			$data['t1_preloader_status'] = false;
		}

		if (isset($this->request->post['t1_preloader_color'])) {
			$data['t1_preloader_color'] = $this->request->post['t1_preloader_color'];
		} elseif (isset($setting_info['t1_preloader_color'])) {
			$data['t1_preloader_color'] = $setting_info['t1_preloader_color'];
		} else {
			$data['t1_preloader_color'] = '#e74c3c';
		}

		if (isset($this->request->post['t1_preloader_bg'])) {
			$data['t1_preloader_bg'] = $this->request->post['t1_preloader_bg'];
		} elseif (isset($setting_info['t1_preloader_bg'])) {
			$data['t1_preloader_bg'] = $setting_info['t1_preloader_bg'];
		} else {
			$data['t1_preloader_bg'] = '#ffffff';
		}

		if (isset($this->request->post['t1_preloader_timeout'])) {
			$data['t1_preloader_timeout'] = $this->request->post['t1_preloader_timeout'];
		} elseif (isset($setting_info['t1_preloader_timeout'])) {
			$data['t1_preloader_timeout'] = $setting_info['t1_preloader_timeout'];
		} else {
			$data['t1_preloader_timeout'] = 2000;
		}

		if (isset($this->request->post['t1_preloader_type'])) {
			$data['t1_preloader_type'] = $this->request->post['t1_preloader_type'];
		} elseif (isset($setting_info['t1_preloader_type'])) {
			$data['t1_preloader_type'] = $setting_info['t1_preloader_type'];
		} else {
			$data['t1_preloader_type'] = 0;
		}


		// help header menu

		if (isset($this->request->post['t1_help_menu_toggle'])) {
			$data['t1_help_menu_toggle'] = $this->request->post['t1_help_menu_toggle'];
		} elseif (isset($setting_info['t1_help_menu_toggle'])) {
			$data['t1_help_menu_toggle'] = $setting_info['t1_help_menu_toggle'];
		} else {
			$data['t1_help_menu_toggle'] = false;
		}

		if (isset($this->request->post['t1_help_menu_text'])) {
			$data['t1_help_menu_text'] = $this->request->post['t1_help_menu_text'];
		} elseif (isset($setting_info['t1_help_menu_text'])) {
			$data['t1_help_menu_text'] = $setting_info['t1_help_menu_text'];
		} else {
			$data['t1_help_menu_text'] = false;
		}

		if (isset($this->request->post['t1_help_menu_item'])) {
			$results = $this->request->post['t1_help_menu_item'];
		} elseif (isset($setting_info['t1_help_menu_item'])) {
			$results = $setting_info['t1_help_menu_item'];
		} else {
			$results = array();
		}

		$data['t1_help_menu_items'] = array();

		foreach ($results as $result) {
			$data['t1_help_menu_items'][] = array(
					'title' => $result['title'],
					'link'  => $result['link'],
					'sort'  => $result['sort']
				);
			}



		// main header menu

		if (isset($this->request->post['t1_main_menu_toggle'])) {
			$data['t1_main_menu_toggle'] = $this->request->post['t1_main_menu_toggle'];
		} elseif (isset($setting_info['t1_main_menu_toggle'])) {
			$data['t1_main_menu_toggle'] = $setting_info['t1_main_menu_toggle'];
		} else {
			$data['t1_main_menu_toggle'] = false;
		}

		if (isset($this->request->post['t1_main_menu_item'])) {
			$results = $this->request->post['t1_main_menu_item'];
		} elseif (isset($setting_info['t1_main_menu_item'])) {
			$results = $setting_info['t1_main_menu_item'];
		} else {
			$results = array();
		}

		$data['t1_main_menu_items'] = array();

		foreach ($results as $result) {
			$data['t1_main_menu_items'][] = array(
				'title' => $result['title'],
				'link'  => $result['link'],
				'sort'  => $result['sort']
			);
		}


		// header contacts

		if (isset($this->request->post['t1_contact_main_toggle'])) {
			$data['t1_contact_main_toggle'] = $this->request->post['t1_contact_main_toggle'];
		} elseif (isset($setting_info['t1_contact_main_toggle'])) {
			$data['t1_contact_main_toggle'] = $setting_info['t1_contact_main_toggle'];
		} else {
			$data['t1_contact_main_toggle'] = false;
		}

		if (isset($this->request->post['t1_contact_main_phone'])) {
			$data['t1_contact_main_phone'] = $this->request->post['t1_contact_main_phone'];
		} elseif (isset($setting_info['t1_contact_main_phone'])) {
			$data['t1_contact_main_phone'] = $setting_info['t1_contact_main_phone'];
		} else {
			$data['t1_contact_main_phone'] = false;
		}

		if (isset($this->request->post['t1_contact_main_phone_link'])) {
			$data['t1_contact_main_phone_link'] = $this->request->post['t1_contact_main_phone_link'];
		} elseif (isset($setting_info['t1_contact_main_phone_link'])) {
			$data['t1_contact_main_phone_link'] = $setting_info['t1_contact_main_phone_link'];
		} else {
			$data['t1_contact_main_phone_link'] = false;
		}

		if (isset($this->request->post['t1_contact_hint'])) {
			$data['t1_contact_hint'] = $this->request->post['t1_contact_hint'];
		} elseif (isset($setting_info['t1_contact_hint'])) {
			$data['t1_contact_hint'] = $setting_info['t1_contact_hint'];
		} else {
			$data['t1_contact_hint'] = false;
		}

		if (isset($this->request->post['t1_cb_link'])) {
			$data['t1_cb_link'] = $this->request->post['t1_cb_link'];
		} elseif (isset($setting_info['t1_cb_link'])) {
			$data['t1_cb_link'] = $setting_info['t1_cb_link'];
		} else {
			$data['t1_cb_link'] = false;
		}

		if (isset($this->request->post['t1_cb_link_text'])) {
			$data['t1_cb_link_text'] = $this->request->post['t1_cb_link_text'];
		} elseif (isset($setting_info['t1_cb_link_text'])) {
			$data['t1_cb_link_text'] = $setting_info['t1_cb_link_text'];
		} else {
			$data['t1_cb_link_text'] = false;
		}

		if (isset($this->request->post['t1_callback_form_toggle'])) {
			$data['t1_callback_form_toggle'] = $this->request->post['t1_callback_form_toggle'];
		} elseif (isset($setting_info['t1_callback_form_toggle'])) {
			$data['t1_callback_form_toggle'] = $setting_info['t1_callback_form_toggle'];
		} else {
			$data['t1_callback_form_toggle'] = false;
		}

		if (isset($this->request->post['t1_callback_mail'])) {
			$data['t1_callback_mail'] = $this->request->post['t1_callback_mail'];
		} elseif (isset($setting_info['t1_callback_mail'])) {
			$data['t1_callback_mail'] = $setting_info['t1_callback_mail'];
		} else {
			$data['t1_callback_mail'] = false;
		}

		if (isset($this->request->post['t1_callback_form_button_text'])) {
			$data['t1_callback_form_button_text'] = $this->request->post['t1_callback_form_button_text'];
		} elseif (isset($setting_info['t1_callback_form_button_text'])) {
			$data['t1_callback_form_button_text'] = $setting_info['t1_callback_form_button_text'];
		} else {
			$data['t1_callback_form_button_text'] = false;
		}

		if (isset($this->request->post['t1_callback_hint'])) {
			$data['t1_callback_hint'] = $this->request->post['t1_callback_hint'];
		} elseif (isset($setting_info['t1_callback_hint'])) {
			$data['t1_callback_hint'] = $setting_info['t1_callback_hint'];
		} else {
			$data['t1_callback_hint'] = false;
		}

		if (isset($this->request->post['t1_callback_form_success_text'])) {
			$data['t1_callback_form_success_text'] = $this->request->post['t1_callback_form_success_text'];
		} elseif (isset($setting_info['t1_callback_form_success_text'])) {
			$data['t1_callback_form_success_text'] = $setting_info['t1_callback_form_success_text'];
		} else {
			$data['t1_callback_form_success_text'] = false;
		}

		if (isset($this->request->post['t1_callback_phone_mask'])) {
			$data['t1_callback_phone_mask'] = $this->request->post['t1_callback_phone_mask'];
		} elseif (isset($setting_info['t1_callback_phone_mask'])) {
			$data['t1_callback_phone_mask'] = $setting_info['t1_callback_phone_mask'];
		} else {
			$data['t1_callback_phone_mask'] = false;
		}

		if (isset($this->request->post['t1_contact_add_toggle'])) {
			$data['t1_contact_add_toggle'] = $this->request->post['t1_contact_add_toggle'];
		} elseif (isset($setting_info['t1_contact_add_toggle'])) {
			$data['t1_contact_add_toggle'] = $setting_info['t1_contact_add_toggle'];
		} else {
			$data['t1_contact_add_toggle'] = false;
		}



		if (isset($this->request->post['t1_additional_number'])) {
			$results = $this->request->post['t1_additional_number'];
		} elseif (isset($setting_info['t1_additional_number'])) {
			$results = $setting_info['t1_additional_number'];
		} else {
			$results = array();
		}

		$data['t1_additional_numbers'] = array();

		foreach ($results as $result) {

			if (is_file(DIR_IMAGE . $result['image'])) {
				$image = $result['image'];
				$thumb = $result['image'];
			} else {
				$image = '';
				$thumb = 'no_image.png';
			}

			$data['t1_additional_numbers'][] = array(
				'image' => $image,
				'thumb' => $this->model_tool_image->resize($thumb, 60, 60),
				'link' => $result['link'],
				'title' => $result['title'],
				'hint' => $result['hint'],
				'sort'  => $result['sort']
			);
		}



		if (isset($this->request->post['t1_additional_contact'])) {
			$results = $this->request->post['t1_additional_contact'];
		} elseif (isset($setting_info['t1_additional_contact'])) {
			$results = $setting_info['t1_additional_contact'];
		} else {
			$results = array();
		}

		$data['t1_additional_contacts'] = array();

		foreach ($results as $result) {

			if (is_file(DIR_IMAGE . $result['image'])) {
				$image = $result['image'];
				$thumb = $result['image'];
			} else {
				$image = '';
				$thumb = 'no_image.png';
			}

			$data['t1_additional_contacts'][] = array(
				'image' => $image,
				'thumb' => $this->model_tool_image->resize($thumb, 60, 60),
				'link' => $result['link'],
				'title' => $result['title'],
				'hint' => $result['hint'],
				'sort'  => $result['sort']
			);
		}

		if (isset($this->request->post['t1_category_shown_pages'])) {
			$data['t1_category_shown_pages'] = $this->request->post['t1_category_shown_pages'];
		} elseif (isset($setting_info['t1_category_shown_pages'])) {
			$data['t1_category_shown_pages'] = $setting_info['t1_category_shown_pages'];
		} else {
			$data['t1_category_shown_pages'] = array('common/home', 'product/category');
		}

		if (isset($this->request->post['t1_category_mask_toggle'])) {
			$data['t1_category_mask_toggle'] = $this->request->post['t1_category_mask_toggle'];
		} elseif (isset($setting_info['t1_category_mask_toggle'])) {
			$data['t1_category_mask_toggle'] = $setting_info['t1_category_mask_toggle'];
		} else {
			$data['t1_category_mask_toggle'] = false;
		}

		if (isset($this->request->post['t1_category_third_level_toggle'])) {
			$data['t1_category_third_level_toggle'] = $this->request->post['t1_category_third_level_toggle'];
		} elseif (isset($setting_info['t1_category_third_level_toggle'])) {
			$data['t1_category_third_level_toggle'] = $setting_info['t1_category_third_level_toggle'];
		} else {
			$data['t1_category_third_level_toggle'] = false;
		}

		if (isset($this->request->post['t1_category_third_level_limit'])) {
			$data['t1_category_third_level_limit'] = $this->request->post['t1_category_third_level_limit'];
		} elseif (isset($setting_info['t1_category_third_level_limit'])) {
			$data['t1_category_third_level_limit'] = $setting_info['t1_category_third_level_limit'];
		} else {
			$data['t1_category_third_level_limit'] = 5;
		}

		if (isset($this->request->post['t1_category_no_full_height_submenu'])) {
			$data['t1_category_no_full_height_submenu'] = $this->request->post['t1_category_no_full_height_submenu'];
		} elseif (isset($setting_info['t1_category_no_full_height_submenu'])) {
			$data['t1_category_no_full_height_submenu'] = $setting_info['t1_category_no_full_height_submenu'];
		} else {
			$data['t1_category_no_full_height_submenu'] = false;
		}

		if (isset($this->request->post['t1_add_cat_links_toggle'])) {
			$data['t1_add_cat_links_toggle'] = $this->request->post['t1_add_cat_links_toggle'];
		} elseif (isset($setting_info['t1_add_cat_links_toggle'])) {
			$data['t1_add_cat_links_toggle'] = $setting_info['t1_add_cat_links_toggle'];
		} else {
			$data['t1_add_cat_links_toggle'] = false;
		}

		if (isset($this->request->post['t1_add_cat_links_item'])) {
			$results = $this->request->post['t1_add_cat_links_item'];
		} elseif (isset($setting_info['t1_add_cat_links_item'])) {
			$results = $setting_info['t1_add_cat_links_item'];
		} else {
			$results = array();
		}

		$data['t1_add_cat_links_items'] = array();

		foreach ($results as $result) {

			if (is_file(DIR_IMAGE . $result['image_peace'])) {
				$image_peace = $result['image_peace'];
				$thumb_peace = $result['image_peace'];
			} else {
				$image_peace = '';
				$thumb_peace = 'no_image.png';
			}

			if (is_file(DIR_IMAGE . $result['image_hover'])) {
				$image_hover = $result['image_hover'];
				$thumb_hover = $result['image_hover'];
			} else {
				$image_hover = '';
				$thumb_hover = 'no_image.png';
			}

			$data['t1_add_cat_links_items'][] = array(
				'image_peace' => $image_peace,
				'image_hover' => $image_hover,
				'thumb_peace' => $this->model_tool_image->resize($thumb_peace, 60, 60),
				'thumb_hover' => $this->model_tool_image->resize($thumb_hover, 60, 60),
				'title'			  => $result['title'],
				'link'  			=> $result['link'],
				'position'		=> $result['position'],
				'html'  			=> $result['html'],
				'sort'  			=> $result['sort']
			);
		}



		$this->load->model('catalog/category');

		$data['categories'] = array();

		$filter_data = array(
			'sort'  => 'name',
			'order' => 'ASC'
		);

		$results = $this->model_catalog_category->getCategories($filter_data);


		if (isset($this->request->post['t1_category_icon'])) {
			$data['t1_category_icon'] = $this->request->post['t1_category_icon'];
			$category_icon = $this->request->post['t1_category_icon'];
		} elseif (isset($setting_info['t1_category_icon'])) {
			$data['t1_category_icon'] = $setting_info['t1_category_icon'];
			$category_icon = $setting_info['t1_category_icon'];
		}


		foreach ($results as $result) {

			$path = $this->model_catalog_category->getCategoryPath($result['category_id']);

			if (!isset($path[2])) {

				$data['categories'][] = array(
					'category_id' => $result['category_id'],
					'name'        => $result['name'],
				);

				if (isset($category_icon[$result['category_id']]['peace']) && is_file(DIR_IMAGE . $category_icon[$result['category_id']]['peace'])) {
					$data['category_icon_thumb'][$result['category_id']]['peace'] = $this->model_tool_image->resize($category_icon[$result['category_id']]['peace'], 100, 100);
					$data['t1_category_icon'][$result['category_id']]['peace'] = $category_icon[$result['category_id']]['peace'];
				} else {
					$data['category_icon_thumb'][$result['category_id']]['peace'] = $this->model_tool_image->resize('no_image.png', 100, 100);
					$data['t1_category_icon'][$result['category_id']]['peace'] = '';
				}

				if (isset($category_icon[$result['category_id']]['hover']) && is_file(DIR_IMAGE . $category_icon[$result['category_id']]['hover'])) {
					$data['category_icon_thumb'][$result['category_id']]['hover'] = $this->model_tool_image->resize($category_icon[$result['category_id']]['hover'], 100, 100);
					$data['t1_category_icon'][$result['category_id']]['hover'] = $category_icon[$result['category_id']]['hover'];
				} else {
					$data['category_icon_thumb'][$result['category_id']]['hover'] = $this->model_tool_image->resize('no_image.png', 100, 100);
					$data['t1_category_icon'][$result['category_id']]['hover'] = '';
				}

			}
		}







		// footer

		if (isset($this->request->post['t1_footer_map_toggle'])) {
			$data['t1_footer_map_toggle'] = $this->request->post['t1_footer_map_toggle'];
		} elseif (isset($setting_info['t1_footer_map_toggle'])) {
			$data['t1_footer_map_toggle'] = $setting_info['t1_footer_map_toggle'];
		} else {
			$data['t1_footer_map_toggle'] = false;
		}

		if (isset($this->request->post['t1_footer_map_code'])) {
			$data['t1_footer_map_code'] = $this->request->post['t1_footer_map_code'];
		} elseif (isset($setting_info['t1_footer_map_code'])) {
			$data['t1_footer_map_code'] = $setting_info['t1_footer_map_code'];
		} else {
			$data['t1_footer_map_code'] = '';
		}


		if (isset($this->request->post['t1_footer_map_geocode'])) {
			$data['t1_footer_map_geocode'] = $this->request->post['t1_footer_map_geocode'];
		} elseif (isset($setting_info['t1_footer_map_geocode'])) {
			$data['t1_footer_map_geocode'] = $setting_info['t1_footer_map_geocode'];
		} else {
			$data['t1_footer_map_geocode'] = false;
		}

		if (isset($this->request->post['t1_pay_icons_toggle'])) {
			$data['t1_pay_icons_toggle'] = $this->request->post['t1_pay_icons_toggle'];
		} elseif (isset($setting_info['t1_pay_icons_toggle'])) {
			$data['t1_pay_icons_toggle'] = $setting_info['t1_pay_icons_toggle'];
		} else {
			$data['t1_pay_icons_toggle'] = false;
		}

		if (isset($this->request->post['t1_pay_icons_banner_id'])) {
			$data['t1_pay_icons_banner_id'] = $this->request->post['t1_pay_icons_banner_id'];
		} elseif (isset($setting_info['t1_pay_icons_banner_id'])) {
			$data['t1_pay_icons_banner_id'] = $setting_info['t1_pay_icons_banner_id'];
		} else {
			$data['t1_pay_icons_banner_id'] = -1;
		}

		$this->load->model('design/banner');
		$data['banners'] = $this->model_design_banner->getBanners();


		if (isset($this->request->post['t1_livesearch_toggle'])) {
			$data['t1_livesearch_toggle'] = $this->request->post['t1_livesearch_toggle'];
		} elseif (isset($setting_info['t1_livesearch_toggle'])) {
			$data['t1_livesearch_toggle'] = $setting_info['t1_livesearch_toggle'];
		} else {
			$data['t1_livesearch_toggle'] = false;
		}

		if (isset($this->request->post['t1_livesearch_subcat_search'])) {
			$data['t1_livesearch_subcat_search'] = $this->request->post['t1_livesearch_subcat_search'];
		} elseif (isset($setting_info['t1_livesearch_subcat_search'])) {
			$data['t1_livesearch_subcat_search'] = $setting_info['t1_livesearch_subcat_search'];
		} else {
			$data['t1_livesearch_subcat_search'] = false;
		}

		if (isset($this->request->post['t1_livesearch_description_search'])) {
			$data['t1_livesearch_description_search'] = $this->request->post['t1_livesearch_description_search'];
		} elseif (isset($setting_info['t1_livesearch_description_search'])) {
			$data['t1_livesearch_description_search'] = $setting_info['t1_livesearch_description_search'];
		} else {
			$data['t1_livesearch_description_search'] = false;
		}

		if (isset($this->request->post['t1_livesearch_show_description'])) {
			$data['t1_livesearch_show_description'] = $this->request->post['t1_livesearch_show_description'];
		} elseif (isset($setting_info['t1_livesearch_show_description'])) {
			$data['t1_livesearch_show_description'] = $setting_info['t1_livesearch_show_description'];
		} else {
			$data['t1_livesearch_show_description'] = false;
		}

		if (isset($this->request->post['t1_livesearch_mask'])) {
			$data['t1_livesearch_mask'] = $this->request->post['t1_livesearch_mask'];
		} elseif (isset($setting_info['t1_livesearch_mask'])) {
			$data['t1_livesearch_mask'] = $setting_info['t1_livesearch_mask'];
		} else {
			$data['t1_livesearch_mask'] = false;
		}

		if (isset($this->request->post['t1_livesearch_characters'])) {
			$data['t1_livesearch_characters'] = $this->request->post['t1_livesearch_characters'];
		} elseif (isset($setting_info['t1_livesearch_characters'])) {
			$data['t1_livesearch_characters'] = $setting_info['t1_livesearch_characters'];
		} else {
			$data['t1_livesearch_characters'] = 1;
		}

		if (isset($this->request->post['t1_livesearch_results'])) {
			$data['t1_livesearch_results'] = $this->request->post['t1_livesearch_results'];
		} elseif (isset($setting_info['t1_livesearch_results'])) {
			$data['t1_livesearch_results'] = $setting_info['t1_livesearch_results'];
		} else {
			$data['t1_livesearch_results'] = 3;
		}


		if (isset($this->request->post['t1_category_description_position'])) {
			$data['t1_category_description_position'] = $this->request->post['t1_category_description_position'];
		} elseif (isset($setting_info['t1_category_description_position'])) {
			$data['t1_category_description_position'] = $setting_info['t1_category_description_position'];
		} else {
			$data['t1_category_description_position'] = false;
		}

		if (isset($this->request->post['t1_sub_cat_img_status'])) {
			$data['t1_sub_cat_img_status'] = $this->request->post['t1_sub_cat_img_status'];
		} elseif (isset($setting_info['t1_sub_cat_img_status'])) {
			$data['t1_sub_cat_img_status'] = $setting_info['t1_sub_cat_img_status'];
		} else {
			$data['t1_sub_cat_img_status'] = false;
		}

    if (isset($this->request->post['t1_catalog_page_lazy'])) {
			$data['t1_catalog_page_lazy'] = $this->request->post['t1_catalog_page_lazy'];
		} elseif (isset($setting_info['t1_catalog_page_lazy'])) {
			$data['t1_catalog_page_lazy'] = $setting_info['t1_catalog_page_lazy'];
		} else {
			$data['t1_catalog_page_lazy'] = false;
		}

		if (isset($this->request->post['t1_catalog_stok_status'])) {
			$data['t1_catalog_stok_status'] = $this->request->post['t1_catalog_stok_status'];
		} elseif (isset($setting_info['t1_catalog_stok_status'])) {
			$data['t1_catalog_stok_status'] = $setting_info['t1_catalog_stok_status'];
		} else {
			$data['t1_catalog_stok_status'] = false;
		}

		if (isset($this->request->post['t1_view_default'])) {
			$data['t1_view_default'] = $this->request->post['t1_view_default'];
		} elseif (isset($setting_info['t1_view_default'])) {
			$data['t1_view_default'] = $setting_info['t1_view_default'];
		} else {
			$data['t1_view_default'] = 'grid';
		}


		if (isset($this->request->post['t1_product_additional_fields'])) {
			$data['t1_product_additional_fields'] = $this->request->post['t1_product_additional_fields'];
		} elseif (isset($setting_info['t1_product_additional_fields'])) {
			$data['t1_product_additional_fields'] = $setting_info['t1_product_additional_fields'];
		} else {
			$data['t1_product_additional_fields'] = array('model');
		}


		if (isset($this->request->post['t1_product_add_images_limit'])) {
			$data['t1_product_add_images_limit'] = $this->request->post['t1_product_add_images_limit'];
		} elseif (isset($setting_info['t1_product_add_images_limit'])) {
			$data['t1_product_add_images_limit'] = $setting_info['t1_product_add_images_limit'];
		} else {
			$data['t1_product_add_images_limit'] = 4;
		}

		if (isset($this->request->post['t1_product_short_description'])) {
			$data['t1_product_short_description'] = $this->request->post['t1_product_short_description'];
		} elseif (isset($setting_info['t1_product_short_description'])) {
			$data['t1_product_short_description'] = $setting_info['t1_product_short_description'];
		} else {
			$data['t1_product_short_description'] = true;
		}

		if (isset($this->request->post['t1_product_short_attributes'])) {
			$data['t1_product_short_attributes'] = $this->request->post['t1_product_short_attributes'];
		} elseif (isset($setting_info['t1_product_short_attributes'])) {
			$data['t1_product_short_attributes'] = $setting_info['t1_product_short_attributes'];
		} else {
			$data['t1_product_short_attributes'] = true;
		}

		if (isset($this->request->post['t1_product_short_attributes_limit'])) {
			$data['t1_product_short_attributes_limit'] = $this->request->post['t1_product_short_attributes_limit'];
		} elseif (isset($setting_info['t1_product_short_attributes_limit'])) {
			$data['t1_product_short_attributes_limit'] = $setting_info['t1_product_short_attributes_limit'];
		} else {
			$data['t1_product_short_attributes_limit'] = 5;
		}

		if (isset($this->request->post['t1_product_social_likes'])) {
			$data['t1_product_social_likes'] = $this->request->post['t1_product_social_likes'];
		} elseif (isset($setting_info['t1_product_social_likes'])) {
			$data['t1_product_social_likes'] = $setting_info['t1_product_social_likes'];
		} else {
			$data['t1_product_social_likes'] = true;
		}

		if (isset($this->request->post['t1_product_social_likes_code'])) {
			$data['t1_product_social_likes_code'] = $this->request->post['t1_product_social_likes_code'];
		} elseif (isset($setting_info['t1_product_social_likes_code'])) {
			$data['t1_product_social_likes_code'] = $setting_info['t1_product_social_likes_code'];
		} else {
			$data['t1_product_social_likes_code'] = '';
		}

		if (isset($this->request->post['t1_related_product_position'])) {
			$data['t1_related_product_position'] = $this->request->post['t1_related_product_position'];
		} elseif (isset($setting_info['t1_related_product_position'])) {
			$data['t1_related_product_position'] = $setting_info['t1_related_product_position'];
		} else {
			$data['t1_related_product_position'] = false;
		}

		if (isset($this->request->post['t1_related_product_buttons'])) {
			$data['t1_related_product_buttons'] = $this->request->post['t1_related_product_buttons'];
		} elseif (isset($setting_info['t1_related_product_buttons'])) {
			$data['t1_related_product_buttons'] = $setting_info['t1_related_product_buttons'];
		} else {
			$data['t1_related_product_buttons'] = false;
		}


		if (isset($this->request->post['t1_extra_tab_status'])) {
			$data['t1_extra_tab_status'] = $this->request->post['t1_extra_tab_status'];
		} elseif (isset($setting_info['t1_extra_tab_status'])) {
			$data['t1_extra_tab_status'] = $setting_info['t1_extra_tab_status'];
		} else {
			$data['t1_extra_tab_status'] = false;
		}

		if (isset($this->request->post['t1_extra_tab_heading'])) {
			$data['t1_extra_tab_heading'] = $this->request->post['t1_extra_tab_heading'];
		} elseif (isset($setting_info['t1_extra_tab_heading'])) {
			$data['t1_extra_tab_heading'] = $setting_info['t1_extra_tab_heading'];
		} else {
			$data['t1_extra_tab_heading'] = false;
		}

		if (isset($this->request->post['t1_extra_tab_content'])) {
			$data['t1_extra_tab_content'] = $this->request->post['t1_extra_tab_content'];
		} elseif (isset($setting_info['t1_extra_tab_content'])) {
			$data['t1_extra_tab_content'] = $setting_info['t1_extra_tab_content'];
		} else {
			$data['t1_extra_tab_content'] = false;
		}




		if (isset($this->request->post['t1_qview_status'])) {
			$data['t1_qview_status'] = $this->request->post['t1_qview_status'];
		} elseif (isset($setting_info['t1_qview_status'])) {
			$data['t1_qview_status'] = $setting_info['t1_qview_status'];
		} else {
			$data['t1_qview_status'] = false;
		}

		if (isset($this->request->post['t1_fastorder_status'])) {
			$data['t1_fastorder_status'] = $this->request->post['t1_fastorder_status'];
		} elseif (isset($setting_info['t1_fastorder_status'])) {
			$data['t1_fastorder_status'] = $setting_info['t1_fastorder_status'];
		} else {
			$data['t1_fastorder_status'] = false;
		}

		if (isset($this->request->post['t1_fastorder_quantity_status'])) {
			$data['t1_fastorder_quantity_status'] = $this->request->post['t1_fastorder_quantity_status'];
		} elseif (isset($setting_info['t1_fastorder_quantity_status'])) {
			$data['t1_fastorder_quantity_status'] = $setting_info['t1_fastorder_quantity_status'];
		} else {
			$data['t1_fastorder_quantity_status'] = false;
		}

		if (isset($this->request->post['t1_fastorder_mail'])) {
			$data['t1_fastorder_mail'] = $this->request->post['t1_fastorder_mail'];
		} elseif (isset($setting_info['t1_fastorder_mail'])) {
			$data['t1_fastorder_mail'] = $setting_info['t1_fastorder_mail'];
		} else {
			$data['t1_fastorder_mail'] = false;
		}
		if (isset($this->request->post['t1_fastorder_phone_mask'])) {
			$data['t1_fastorder_phone_mask'] = $this->request->post['t1_fastorder_phone_mask'];
		} elseif (isset($setting_info['t1_fastorder_phone_mask'])) {
			$data['t1_fastorder_phone_mask'] = $setting_info['t1_fastorder_phone_mask'];
		} else {
			$data['t1_fastorder_phone_mask'] = '8 (999) 999-99-99';
		}

		if (isset($this->request->post['t1_modal_status'])) {
			$data['t1_modal_status'] = $this->request->post['t1_modal_status'];
		} elseif (isset($setting_info['t1_modal_status'])) {
			$data['t1_modal_status'] = $setting_info['t1_modal_status'];
		} else {
			$data['t1_modal_status'] = false;
		}

		if (isset($this->request->post['t1_modal_size'])) {
			$data['t1_modal_size'] = $this->request->post['t1_modal_size'];
		} elseif (isset($setting_info['t1_modal_size'])) {
			$data['t1_modal_size'] = $setting_info['t1_modal_size'];
		} else {
			$data['t1_modal_size'] = false;
		}

		if (isset($this->request->post['t1_modal_cookie_days'])) {
			$data['t1_modal_cookie_days'] = $this->request->post['t1_modal_cookie_days'];
		} elseif (isset($setting_info['t1_modal_cookie_days'])) {
			$data['t1_modal_cookie_days'] = $setting_info['t1_modal_cookie_days'];
		} else {
			$data['t1_modal_cookie_days'] = 0;
		}

		if (isset($this->request->post['t1_modal_heading'])) {
			$data['t1_modal_heading'] = $this->request->post['t1_modal_heading'];
		} elseif (isset($setting_info['t1_modal_heading'])) {
			$data['t1_modal_heading'] = $setting_info['t1_modal_heading'];
		} else {
			$data['t1_modal_heading'] = false;
		}

		if (isset($this->request->post['t1_modal_content'])) {
			$data['t1_modal_content'] = $this->request->post['t1_modal_content'];
		} elseif (isset($setting_info['t1_modal_content'])) {
			$data['t1_modal_content'] = $setting_info['t1_modal_content'];
		} else {
			$data['t1_modal_content'] = false;
		}

		if (isset($this->request->post['t1_stikers'])) {
			$data['t1_stikers'] = $this->request->post['t1_stikers'];
		} elseif (isset($setting_info['t1_stikers'])) {
			$data['t1_stikers'] = $setting_info['t1_stikers'];
		} else {
			$data['t1_stikers'] = array(
				'special' => array(
					'status'    => false,
					'bg_color'  => '#dc3545',
					'txt_color' => '#ffffff'
				),
				'upc' => array(
					'status'    => false,
					'bg_color'  => '#007bff',
					'txt_color' => '#ffffff'
				),
				'ean' => array(
					'status'    => false,
					'bg_color'  => '#28a745',
					'txt_color' => '#ffffff'
				),
				'jan' => array(
					'status'    => false,
					'bg_color'  => '#ffc107',
					'txt_color' => '#343a40'
				),
				'isbn' => array(
					'status'    => false,
					'bg_color'  => '#17a2b8',
					'txt_color' => '#ffffff'
				),
				'mpn' => array(
					'status'    => false,
					'bg_color'  => '#343a40',
					'txt_color' => '#ffffff'
				)
			);
		}

		if (isset($this->request->post['t1_cust_code_top'])) {
			$data['t1_cust_code_top'] = $this->request->post['t1_cust_code_top'];
		} elseif (isset($setting_info['t1_cust_code_top'])) {
			$data['t1_cust_code_top'] = $setting_info['t1_cust_code_top'];
		} else {
			$data['t1_cust_code_top'] = false;
		}

		if (isset($this->request->post['t1_cust_code_bottom'])) {
			$data['t1_cust_code_bottom'] = $this->request->post['t1_cust_code_bottom'];
		} elseif (isset($setting_info['t1_cust_code_bottom'])) {
			$data['t1_cust_code_bottom'] = $setting_info['t1_cust_code_bottom'];
		} else {
			$data['t1_cust_code_bottom'] = false;
		}

		if (isset($this->request->post['t1_cust_css'])) {
			$data['t1_cust_css'] = $this->request->post['t1_cust_css'];
		} elseif (isset($setting_info['t1_cust_css'])) {
			$data['t1_cust_css'] = $setting_info['t1_cust_css'];
		} else {
			$data['t1_cust_css'] = false;
		}

    if (isset($this->request->post['t1_bs_bp_xxl'])) {
			$data['t1_bs_bp_xxl'] = $this->request->post['t1_bs_bp_xxl'];
		} elseif (isset($setting_info['t1_bs_bp_xxl'])) {
			$data['t1_bs_bp_xxl'] = $setting_info['t1_bs_bp_xxl'];
		} else {
			$data['t1_bs_bp_xxl'] = true;
		}

    if (isset($this->request->post['t1_bs_radius'])) {
			$data['t1_bs_radius'] = $this->request->post['t1_bs_radius'];
		} elseif (isset($setting_info['t1_bs_radius'])) {
			$data['t1_bs_radius'] = $setting_info['t1_bs_radius'];
		} else {
			$data['t1_bs_radius'] = 0.35;
		}

		if (isset($this->request->post['t1_color_schema'])) {
			$data['t1_color_schema'] = $this->request->post['t1_color_schema'];
		} elseif (isset($setting_info['t1_color_schema'])) {
			$data['t1_color_schema'] = $setting_info['t1_color_schema'];
		} else {
			$data['t1_color_schema'] = array(
				'white' 	 => '#ffffff',
				'black' 	 => '#000000',
				'gray_100' => '#f8f9fa',
				'gray_150' => '#f1f3f5',
				'gray_200' => '#e9ecef',
				'gray_300' => '#dee2e6',
				'gray_400' => '#ced4da',
				'gray_500' => '#adb5bd',
				'gray_600' => '#6c757d',
				'gray_700' => '#495057',
				'gray_800' => '#343a40',
				'gray_900' => '#212529',
				'blue' 		 => '#007bff',
				'indigo' 	 => '#6610f2',
				'purple' 	 => '#6f42c1',
				'pink' 	 	 => '#e83e8c',
				'red' 		 => '#dc3545',
				'orange' 	 => '#fd7e14',
				'yellow' 	 => '#ffc107',
				'green' 	 => '#28a745',
				'teal' 		 => '#20c997',
				'cyan' 		 => '#17a2b8',
			);
		}


    if (isset($this->request->post['t1_bs_settings'])) {
			$data['t1_bs_settings'] = $this->request->post['t1_bs_settings'];
		} elseif (isset($setting_info['t1_bs_settings'])) {
			$data['t1_bs_settings'] = $setting_info['t1_bs_settings'];
		} else {
			$data['t1_bs_settings'] = array(
        'main_colors' => array(
          'primary'                  => 'blue',
          'secondary'                => 'gray_600',
          'success'                  => 'green',
          'info'                     => 'cyan',
          'warning'                  => 'yellow',
          'danger'                   => 'red',
          'light'                    => 'gray_100',
          'dark'                     => 'gray_800',
        ),
        'main_settings' => array(
          'body_bg'                  => 'white',
          'body_color'               => 'gray_900',
          'link_color'               => 'primary',
          'border_color'             => 'gray_300',
          'component_active_color'   => 'white',
          'component_active_bg'      => 'primary'
        ),
			);
		}



    if (isset($this->request->post['t1_bs_settings_scss'])) {
			$data['t1_bs_settings_scss'] = $this->request->post['t1_bs_settings_scss'];
		} elseif (isset($setting_info['t1_bs_settings_scss'])) {
			$data['t1_bs_settings_scss'] = $setting_info['t1_bs_settings_scss'];
		} else {
			$data['t1_bs_settings_scss'] = '';
		}

    $data['t1_bs_all_components'] = array(
      'mixins',
      'root',
      'reboot',
      'type',
      'images',
      'code',
      'grid',
      'tables',
      'forms',
      'buttons',
      'transitions',
      'dropdown',
      'button-group',
      'input-group',
      'custom-forms',
      'nav',
      'navbar',
      'card',
      'breadcrumb',
      'pagination',
      'badge',
      'jumbotron',
      'alert',
      'progress',
      'media',
      'list-group',
      'close',
      'toasts',
      'modal',
      'tooltip',
      'popover',
      'carousel',
      'spinners',
      'utilities',
      'print',
    );

    if (isset($this->request->post['t1_bs_components'])) {
			$data['t1_bs_components'] = $this->request->post['t1_bs_components'];
		} elseif (isset($setting_info['t1_bs_components'])) {
			$data['t1_bs_components'] = $setting_info['t1_bs_components'];
		} else {
			$data['t1_bs_components'] = $data['t1_bs_all_components'];
		}

		if (isset($this->request->post['t1_minify_request'])) {
			$data['t1_minify_request'] = $this->request->post['t1_minify_request'];
		} elseif (isset($setting_info['t1_minify_request'])) {
			$data['t1_minify_request'] = $setting_info['t1_minify_request'];
		} else {
			$data['t1_minify_request'] = false;
		}

    if (isset($this->request->post['t1_show_version'])) {
			$data['t1_show_version'] = $this->request->post['t1_show_version'];
		} elseif (isset($setting_info['t1_show_version'])) {
			$data['t1_show_version'] = $setting_info['t1_show_version'];
		} else {
			$data['t1_show_version'] = false;
		}

    if (isset($this->request->post['t1_preload_ss'])) {
			$data['t1_preload_ss'] = $this->request->post['t1_preload_ss'];
		} elseif (isset($setting_info['t1_preload_ss'])) {
			$data['t1_preload_ss'] = $setting_info['t1_preload_ss'];
		} else {
			$data['t1_preload_ss'] = false;
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/theme/framefree', $data));
	}

	protected function validate() {

		if (!$this->user->hasPermission('modify', 'extension/theme/framefree')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['theme_framefree_image_category_width'] || !$this->request->post['theme_framefree_image_category_height']) {
			$this->error['image_category'] = $this->language->get('error_image_category');
		}

    if (!$this->request->post['theme_framefree_image_manufacturer_width'] || !$this->request->post['theme_framefree_image_manufacturer_height']) {
			$this->error['image_manufacturer'] = $this->language->get('error_image_manufacturer');
		}

		if (!$this->request->post['theme_framefree_image_thumb_width'] || !$this->request->post['theme_framefree_image_thumb_height']) {
			$this->error['image_thumb'] = $this->language->get('error_image_thumb');
		}

		if (!$this->request->post['theme_framefree_image_popup_width'] || !$this->request->post['theme_framefree_image_popup_height']) {
			$this->error['image_popup'] = $this->language->get('error_image_popup');
		}

		if (!$this->request->post['theme_framefree_image_product_width'] || !$this->request->post['theme_framefree_image_product_height']) {
			$this->error['image_product'] = $this->language->get('error_image_product');
		}

		if (!$this->request->post['theme_framefree_image_additional_width'] || !$this->request->post['theme_framefree_image_additional_height']) {
			$this->error['image_additional'] = $this->language->get('error_image_additional');
		}

		if (!$this->request->post['theme_framefree_image_related_width'] || !$this->request->post['theme_framefree_image_related_height']) {
			$this->error['image_related'] = $this->language->get('error_image_related');
		}

		if (!$this->request->post['theme_framefree_image_compare_width'] || !$this->request->post['theme_framefree_image_compare_height']) {
			$this->error['image_compare'] = $this->language->get('error_image_compare');
		}

		if (!$this->request->post['theme_framefree_image_wishlist_width'] || !$this->request->post['theme_framefree_image_wishlist_height']) {
			$this->error['image_wishlist'] = $this->language->get('error_image_wishlist');
		}

		if (!$this->request->post['theme_framefree_image_cart_width'] || !$this->request->post['theme_framefree_image_cart_height']) {
			$this->error['image_cart'] = $this->language->get('error_image_cart');
		}

		if (!$this->request->post['theme_framefree_image_location_width'] || !$this->request->post['theme_framefree_image_location_height']) {
			$this->error['image_location'] = $this->language->get('error_image_location');
		}

		return !$this->error;
	}
}
