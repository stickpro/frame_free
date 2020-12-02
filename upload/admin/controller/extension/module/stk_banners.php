<?php
class ControllerExtensionModuleSTKBanners extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/stk_banners');

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));

		$this->document->addStyle('view/stylesheet/framefreetheme/framefreetheme.css');
		$this->document->addStyle('view/javascript/codemirror/lib/codemirror.css');
		$this->document->addStyle('view/javascript/codemirror/theme/github.css');

		$this->document->addScript('view/javascript/codemirror/lib/codemirror.js');
		$this->document->addScript('view/javascript/codemirror/lib/xml.js');
		$this->document->addScript('view/javascript/codemirror/lib/formatting.js');

		$this->load->model('setting/module');

    if (!isset($this->request->get['module_id'])) {
			$data['apply_button'] = false;
		} else {
			$data['apply_button'] = true;
		}

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('stk_banners', $this->request->post);
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		$data['heading_title'] = strip_tags($this->language->get('heading_title'));

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}



		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => strip_tags($this->language->get('heading_title')),
				'href' => $this->url->link('extension/module/stk_banners', 'user_token=' . $this->session->data['user_token'], 'SSL')
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => strip_tags($this->language->get('heading_title')),
				'href' => $this->url->link('extension/module/stk_banners', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], 'SSL')
			);
		}

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/stk_banners', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/stk_banners', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($module_info)) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info)) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = '';
		}

    if (isset($this->request->post['title'])) {
      $data['title'] = $this->request->post['title'];
    } elseif (!empty($module_info)) {
      $data['title'] = $module_info['title'];
    } else {
      $data['title'] = '';
    }

    if (isset($this->request->post['type'])) {
			$data['type'] = $this->request->post['type'];
		} elseif (!empty($module_info)) {
			$data['type'] = $module_info['type'];
		} else {
			$data['type'] = 'slideshow';
		}

		if (isset($this->request->post['items'])) {
			$data['items'] = $this->request->post['items'];
		} elseif (!empty($module_info) && isset($module_info['items'])) {
			$data['items'] = $module_info['items'];
		} else {
			$data['items'] = 1;
		}

		if (isset($this->request->post['responsive_items'])) {
			$data['responsive_items'] = $this->request->post['responsive_items'];
		} elseif (!empty($module_info) && isset($module_info['responsive_items'])) {
			$data['responsive_items'] = $module_info['responsive_items'];
		} else {
			$data['responsive_items'] = array();
		}

		if (isset($this->request->post['responsive_base'])) {
			$data['responsive_base'] = $this->request->post['responsive_base'];
		} elseif (!empty($module_info) && isset($module_info['responsive_base'])) {
			$data['responsive_base'] = $module_info['responsive_base'];
		} else {
			$data['responsive_base'] = 'viewport';
		}

		if (isset($this->request->post['responsive_base_selector'])) {
			$data['responsive_base_selector'] = $this->request->post['responsive_base_selector'];
		} elseif (!empty($module_info) && isset($module_info['responsive_base'])) {
			$data['responsive_base_selector'] = $module_info['responsive_base_selector'];
		} else {
			$data['responsive_base_selector'] = '';
		}

    if (isset($this->request->post['animation_in'])) {
			$data['animation_in'] = $this->request->post['animation_in'];
		} elseif (!empty($module_info)) {
			$data['animation_in'] = $module_info['animation_in'];
		} else {
			$data['animation_in'] = '';
		}

    if (isset($this->request->post['animation_out'])) {
			$data['animation_out'] = $this->request->post['animation_out'];
		} elseif (!empty($module_info)) {
			$data['animation_out'] = $module_info['animation_out'];
		} else {
			$data['animation_out'] = '';
		}

    if (isset($this->request->post['controls'])) {
			$data['controls'] = $this->request->post['controls'];
		} elseif (!empty($module_info) && isset($module_info['controls'])) {
			$data['controls'] = $module_info['controls'];
		} else {
			$data['controls'] = array();
		}

		if (isset($this->request->post['loop'])) {
			$data['loop'] = $this->request->post['loop'];
		} elseif (isset($module_info['loop'])) {
			$data['loop'] = $module_info['loop'];
		} else {
			$data['loop'] = false;
		}

    if (isset($this->request->post['autoplay'])) {
			$data['autoplay'] = $this->request->post['autoplay'];
		} elseif (isset($module_info['autoplay'])) {
			$data['autoplay'] = $module_info['autoplay'];
		} else {
			$data['autoplay'] = false;
		}

    if (isset($this->request->post['autoplay_speed'])) {
			$data['autoplay_speed'] = $this->request->post['autoplay_speed'];
		} elseif (isset($module_info['autoplay_speed'])) {
			$data['autoplay_speed'] = $module_info['autoplay_speed'];
		} else {
			$data['autoplay_speed'] = 2500;
		}

		if (isset($this->request->post['hd_images'])) {
			$data['hd_images'] = $this->request->post['hd_images'];
		} elseif (!empty($module_info)) {
			$data['hd_images'] = $module_info['hd_images'];
		} else {
			$data['hd_images'] = false;
		}

    if (isset($this->request->post['lazyload'])) {
			$data['lazyload'] = $this->request->post['lazyload'];
		} elseif (!empty($module_info)) {
			$data['lazyload'] = $module_info['lazyload'];
		} else {
			$data['lazyload'] = false;
		}



		$this->load->model('tool/image');

		if (isset($this->request->post['cust_blocks_item'])) {
			$results = $this->request->post['cust_blocks_item'];
		} elseif (!empty($module_info) & isset($module_info['cust_blocks_item'])) {
			$results = $module_info['cust_blocks_item'];
		} else {
			$results = array();
		}

		$data['cust_blocks_items'] = array();

		foreach ($results as $result) {

      foreach ($this->model_localisation_language->getLanguages() as $language) {

  			if (is_file(DIR_IMAGE . $result['image'][$language['language_id']])) {
  				$image[$language['language_id']] = $result['image'][$language['language_id']];
          $thumb[$language['language_id']] = $this->model_tool_image->resize($result['image'][$language['language_id']], 100, 100);
  			} else {
  				$image[$language['language_id']] = '';
  				$thumb[$language['language_id']] = $this->model_tool_image->resize('no_image.png', 100, 100);
  			}

      }


			$data['cust_blocks_items'][] = array(
				'image' 			=> $image,
				'thumb' 			=> $thumb,
				'type' 				=> isset($result['type']) ? $result['type'] : array(),
				'img_width' 	=> isset($result['img_width']) ? $result['img_width'] : array(),
				'img_height' 	=> isset($result['img_height']) ? $result['img_height'] : array(),
				'alt'					=> isset($result['alt']) ? $result['alt'] : array(),
				'description' => isset($result['description']) ? $result['description'] : array(),
				'link'  			=> isset($result['link']) ? $result['link'] : array(),
				'target'			=> isset($result['target']) ? $result['target'] : array(),
				'sort'  			=> $result['sort']
			);
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/stk_banners', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/stk_banners')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		return !$this->error;
	}
}
