<?php
class ControllerExtensionModuleSTKCustomBlocks extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/stk_custom_blocks');

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));

		$this->document->addStyle('view/stylesheet/framefreetheme/framefreetheme.css');

		$this->load->model('setting/module');

		if (!isset($this->request->get['module_id'])) {
			$data['apply_button'] = false;
		} else {
			$data['apply_button'] = true;
		}


		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('stk_custom_blocks', $this->request->post);
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
				'href' => $this->url->link('extension/module/stk_custom_blocks', 'user_token=' . $this->session->data['user_token'], 'SSL')
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => strip_tags($this->language->get('heading_title')),
				'href' => $this->url->link('extension/module/stk_custom_blocks', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], 'SSL')
			);
		}

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/stk_custom_blocks', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/stk_custom_blocks', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
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

		if (isset($this->request->post['columns'])) {
			$data['columns'] = $this->request->post['columns'];
		} elseif (!empty($module_info)) {
			$data['columns'] = $module_info['columns'];
		} else {
			$data['columns'] = 1;
		}

		if (isset($this->request->post['columns_sm'])) {
			$data['columns_sm'] = $this->request->post['columns_sm'];
		} elseif (!empty($module_info)) {
			$data['columns_sm'] = $module_info['columns_sm'];
		} else {
			$data['columns_sm'] = 1;
		}

		if (isset($this->request->post['columns_md'])) {
			$data['columns_md'] = $this->request->post['columns_md'];
		} elseif (!empty($module_info)) {
			$data['columns_md'] = $module_info['columns_md'];
		} else {
			$data['columns_md'] = 1;
		}

		if (isset($this->request->post['columns_lg'])) {
			$data['columns_lg'] = $this->request->post['columns_lg'];
		} elseif (!empty($module_info)) {
			$data['columns_lg'] = $module_info['columns_lg'];
		} else {
			$data['columns_lg'] = 1;
		}

		if (isset($this->request->post['columns_xl'])) {
			$data['columns_xl'] = $this->request->post['columns_xl'];
		} elseif (!empty($module_info)) {
			$data['columns_xl'] = $module_info['columns_xl'];
		} else {
			$data['columns_xl'] = 1;
		}

		if (isset($this->request->post['columns_xxl'])) {
			$data['columns_xxl'] = $this->request->post['columns_xxl'];
		} elseif (!empty($module_info)) {
			$data['columns_xxl'] = $module_info['columns_xxl'];
		} else {
			$data['columns_xxl'] = 1;
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

			if (is_file(DIR_IMAGE . $result['image'])) {
				$image = $result['image'];
				$thumb = $result['image'];
			} else {
				$image = '';
				$thumb = 'no_image.png';
			}

			$data['cust_blocks_items'][] = array(
				'image' => $image,
				'thumb' => $this->model_tool_image->resize($thumb, 100, 100),
				'title' => $result['title'],
				'description' => $result['description'],
				'link'  => $result['link'],
				'html'  => $result['html'],
				'sort'  => intval($result['sort'])
			);
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/stk_custom_blocks', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/stk_custom_blocks')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		return !$this->error;
	}
}
