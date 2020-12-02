<?php
class ControllerExtensionModuleSTKRecentReviews extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/stk_recent_reviews');

		$this->document->addStyle('view/stylesheet/framefreetheme/framefreetheme.css');

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));

		$this->load->model('setting/module');

		if (!isset($this->request->get['module_id'])) {
			$data['apply_button'] = false;
		} else {
			$data['apply_button'] = true;
		}

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('stk_recent_reviews', $this->request->post);
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
			'href' => $this->url->link('extension/module', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => strip_tags($this->language->get('heading_title')),
				'href' => $this->url->link('extension/module/stk_recent_reviews', 'user_token=' . $this->session->data['user_token'], 'SSL')
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => strip_tags($this->language->get('heading_title')),
				'href' => $this->url->link('extension/module/stk_recent_reviews', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], 'SSL')
			);
		}

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/stk_recent_reviews', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/stk_recent_reviews', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
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

		if (isset($this->request->post['title'])) {
			$data['title'] = $this->request->post['title'];
		} elseif (!empty($module_info)) {
			$data['title'] = $module_info['title'];
		} else {
			$data['title'] = '';
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info)) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = '';
		}

    if (isset($this->request->post['limit'])) {
			$data['limit'] = $this->request->post['limit'];
		} elseif (!empty($module_info)) {
			$data['limit'] = $module_info['limit'];
		} else {
			$data['limit'] = 20;
		}

		if (isset($this->request->post['sort'])) {
			$data['sort'] = $this->request->post['sort'];
		} elseif (!empty($module_info)) {
			$data['sort'] = $module_info['sort'];
		} else {
			$data['sort'] = 0;
		}

    if (isset($this->request->post['rating'])) {
			$data['rating'] = $this->request->post['rating'];
		} elseif (!empty($module_info)) {
			$data['rating'] = $module_info['rating'];
		} else {
			$data['rating'] = 0;
		}

    if (isset($this->request->post['image_width'])) {
      $data['image_width'] = $this->request->post['image_width'];
    } elseif (!empty($module_info)) {
      $data['image_width'] = $module_info['image_width'];
    } else {
      $data['image_width'] = 90;
    }

    if (isset($this->request->post['image_height'])) {
      $data['image_height'] = $this->request->post['image_height'];
    } elseif (!empty($module_info)) {
      $data['image_height'] = $module_info['image_height'];
    } else {
      $data['image_height'] = 90;
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

		if (isset($this->request->post['controls'])) {
			$data['controls'] = $this->request->post['controls'];
		} elseif (!empty($module_info) && isset($module_info['controls'])) {
			$data['controls'] = $module_info['controls'];
		} else {
			$data['controls'] = array();
		}



		if (isset($this->request->post['autoplay'])) {
			$data['autoplay'] = $this->request->post['autoplay'];
		} elseif (!empty($module_info)) {
			$data['autoplay'] = $module_info['autoplay'];
		} else {
			$data['autoplay'] = '';
		}

		if (isset($this->request->post['autoplay_speed'])) {
			$data['autoplay_speed'] = $this->request->post['autoplay_speed'];
		} elseif (!empty($module_info)) {
			$data['autoplay_speed'] = $module_info['autoplay_speed'];
		} else {
			$data['autoplay_speed'] = 2500;
		}


		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/stk_recent_reviews', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/stk_recent_reviews')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		return !$this->error;
	}
}
