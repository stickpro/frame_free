<?php
class ControllerExtensionModuleFramefreethemeFFQoptions extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/framefreetheme/ff_qoptions');
		$this->load->language('extension/module/framefreetheme/ff_global');

		if (isset($this->request->get['product_id'])) {
			$product_id = (int)$this->request->get['product_id'];
		} else {
			$product_id = 0;
		}

		$this->load->model('catalog/product');

		$this->load->model('setting/setting');

		$ff_settings = array();
		$ff_settings = $this->model_setting_setting->getSetting('theme_framefree', $this->config->get('config_store_id'));
		$language_id = $this->config->get('config_language_id');

    if (isset($ff_settings['t1_high_definition_imgs']) && $ff_settings['t1_high_definition_imgs']){
			$hd_imgs = $ff_settings['t1_high_definition_imgs'];
		} else {
			$hd_imgs = false;
		}

		if (isset($ff_settings['t1_buy_button_status']) && !empty($ff_settings['t1_buy_button_status'])) {
			$data['disable_btn_status'] = $ff_settings['t1_buy_button_status'];
		} else {
			$data['disable_btn_status'] = false;
		}

		if (isset($ff_settings['t1_buy_button_disabled_text'][$language_id]) && !empty($ff_settings['t1_buy_button_disabled_text'][$language_id])) {
			$data['disable_btn_text'] = $ff_settings['t1_buy_button_disabled_text'][$language_id];
		} else {
			$data['disable_btn_text'] = '';
		}



		$product_info = $this->model_catalog_product->getProduct($product_id);

		$data['product_isset'] = false;

		if ($product_info) {

			$data['product_isset'] = true;

			$data['heading_title'] = $product_info['name'];

			$data['product_href'] = $this->url->link('product/product&product_id=' . $product_info['product_id'], '', true);

			$data['text_minimum'] = sprintf($this->language->get('text_minimum'), $product_info['minimum']);

			$data['product_id'] = (int)$this->request->get['product_id'];
			$data['theme_dir'] 	= $this->config->get('theme_frame_directory');

			$data['quantity'] = $product_info['quantity'];

			$this->load->model('tool/image');

			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$data['price'] = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$data['price'] = false;
			}

			if ((float)$product_info['special']) {
				$data['special'] = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$data['special'] = false;
			}

			if ($this->config->get('config_tax')) {
				$data['tax'] = $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price'], $this->session->data['currency']);
			} else {
				$data['tax'] = false;
			}

			$discounts = $this->model_catalog_product->getProductDiscounts($this->request->get['product_id']);

			$data['discounts'] = array();

			foreach ($discounts as $discount) {
				$data['discounts'][] = array(
					'quantity' => $discount['quantity'],
					'price'    => $this->currency->format($this->tax->calculate($discount['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'])
				);
			}

			$data['options'] = array();

			foreach ($this->model_catalog_product->getProductOptions($this->request->get['product_id']) as $option) {
				$product_option_value_data = array();

				foreach ($option['product_option_value'] as $option_value) {
					if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
						if ((($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) && (float)$option_value['price']) {
							$price = $this->currency->format($this->tax->calculate($option_value['price'], $product_info['tax_class_id'], $this->config->get('config_tax') ? 'P' : false), $this->session->data['currency']);
						} else {
							$price = false;
						}

						$product_option_value_data[] = array(
							'product_option_value_id' => $option_value['product_option_value_id'],
							'option_value_id'         => $option_value['option_value_id'],
							'name'                    => $option_value['name'],
							'image'                   => $this->model_tool_image->resize($option_value['image'], 64, 64),
							'image2x'                 => $hd_imgs ? $this->model_tool_image->resize($option_value['image'], 64*2, 64*2) : NULL,
							'image3x'                 => $hd_imgs ? $this->model_tool_image->resize($option_value['image'], 64*3, 64*3) : NULL,
							'image4x'                 => $hd_imgs ? $this->model_tool_image->resize($option_value['image'], 64*4, 64*4) : NULL,
							'price'                   => $price,
							'price_prefix'            => $option_value['price_prefix']
						);
					}
				}

				$data['options'][] = array(
					'product_option_id'    => $option['product_option_id'],
					'product_option_value' => $product_option_value_data,
					'option_id'            => $option['option_id'],
					'name'                 => $option['name'],
					'type'                 => $option['type'],
					'value'                => $option['value'],
					'required'             => $option['required']
				);
			}

			if ($product_info['minimum']) {
				$data['minimum'] = $product_info['minimum'];
			} else {
				$data['minimum'] = 1;
			}


			$data['recurrings'] = $this->model_catalog_product->getProfiles($this->request->get['product_id']);

			$this->model_catalog_product->updateViewed($this->request->get['product_id']);

		}

		$this->response->setOutput($this->load->view('extension/module/framefreetheme/ff_qoptions', $data));
	}

}
