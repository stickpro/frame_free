<?php
class ControllerExtensionModuleSTKProductsCarousel extends Controller {
	public function index($setting) {
		static $module = 0;

		$this->load->model('localisation/language');

		$languages = $this->model_localisation_language->getLanguages();
		$language_id = $this->config->get('config_language_id');

		$this->load->language('extension/module/stk_products_carousel');
		$this->load->language('extension/module/framefreetheme/ff_global');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');

		$this->load->model('setting/setting');

		$ff_settings = array();
		$ff_settings = $this->model_setting_setting->getSetting('theme_framefree', $this->config->get('config_store_id'));

		if (isset($ff_settings['t1_high_definition_imgs']) && $ff_settings['t1_high_definition_imgs']){
			$hd_imgs = $ff_settings['t1_high_definition_imgs'];
		} else {
			$hd_imgs = false;
		}

    if (isset($ff_settings['t1_catalog_page_lazy']) && $ff_settings['t1_catalog_page_lazy']){
			$data['lazyload_imgs'] = $ff_settings['t1_catalog_page_lazy'];
		} else {
			$data['lazyload_imgs'] = false;
		}

		if (isset($ff_settings['t1_stikers']) && !empty($ff_settings['t1_stikers'])) {
			$ff_stikers = $ff_settings['t1_stikers'];
		} else {
			$ff_stikers = array();
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

		$this->document->addStyle('catalog/view/theme/' . $this->config->get('theme_framefree_directory') . '/javascript/owl-carousel/owl.carousel.min.css');
		$this->document->addScript('catalog/view/theme/' . $this->config->get('theme_framefree_directory') . '/javascript/owl-carousel/owl.carousel.min.js');

		$data['products'] = array();

		if (!$setting['limit']) {
			$setting['limit'] = 4;
		}

		if ($setting['title']) {
			$data['heading_title'] = html_entity_decode($setting['title'][$language_id], ENT_QUOTES, 'UTF-8');
		}

		$data['controls'] = isset($setting['controls']) ? $setting['controls'] : array();
		$data['module_type'] = $setting['module_type'];
		$data['autoplay'] = $setting['autoplay'];
		$data['autoplay_speed'] = $setting['autoplay_speed'];
		$data['hide_out_of_stock_products'] = $setting['hide_out_of_stock_products'];
		$data['show_buttons'] = $setting['show_buttons'];

		$data['items'] = $setting['items'] ? $setting['items'] : 1;
		$data['responsive_items'] = array();

		$responsive_items = isset($setting['responsive_items']) ? $setting['responsive_items'] : array();
		foreach ($responsive_items as $item) {
			if ($item['breakpoint'] && $item['amount']) {
				$data['responsive_items'][] = array(
					'breakpoint' => $item['breakpoint'],
					'amount' => $item['amount']
				);
			}
		}

		if (isset($setting['hide_noimage_products'])) {
			$hide_noimage_products = $setting['hide_noimage_products'];
		} else {
			$hide_noimage_products = false;
		}

		$results = array();

		if ($setting['module_type'] == 'latest') {
			$filter_data = array(
				'sort'  => 'p.date_added',
				'order' => 'DESC',
				'start' => 0,
				'limit' => $setting['limit']
			);

			$results = $this->model_catalog_product->getProducts($filter_data);
		}

		if ($setting['module_type'] == 'special') {
			$filter_data = array(
				'sort'  => 'pd.name',
				'order' => 'ASC',
				'start' => 0,
				'limit' => $setting['limit']
			);

			$results = $this->model_catalog_product->getProductSpecials($filter_data);
		}

		if ($setting['module_type'] == 'bestseller') {
			$results = $this->model_catalog_product->getBestSellerProducts($setting['limit']);
		}


		if ($results) {
			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height']);
					if ($hd_imgs) {
						$image2x = $this->model_tool_image->resize($result['image'], $setting['width']*2, $setting['height']*2);
						$image3x = $this->model_tool_image->resize($result['image'], $setting['width']*3, $setting['height']*3);
						$image4x = $this->model_tool_image->resize($result['image'], $setting['width']*4, $setting['height']*4);
					}

				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
					if ($hd_imgs) {
						$image2x = $this->model_tool_image->resize('placeholder.png', $setting['width']*2, $setting['height']*2);
						$image3x = $this->model_tool_image->resize('placeholder.png', $setting['width']*3, $setting['height']*3);
						$image4x = $this->model_tool_image->resize('placeholder.png', $setting['width']*4, $setting['height']*4);
					}
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ((float)$result['special']) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
				} else {
					$tax = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = $result['rating'];
				} else {
					$rating = false;
				}

				$stickers = array();

				if ($result['price'] && $result['special'] && $ff_stikers['special']['status']) {
					$stickers['special'] = array(
						'text'  => round(100 - ($result['special'] / $result['price']) * 100) * (-1) . '%',
						'class' => 'badge stiker-special'
					);
				}

				if ($result['upc'] && $ff_stikers['upc']['status']) {
					$stickers['upc'] = array(
						'text'  => $result['upc'],
						'class' => 'badge stiker-upc'
					);
				}

				if ($result['ean'] && $ff_stikers['ean']['status']) {
					$stickers['ean'] = array(
						'text'  => $result['ean'],
						'class' => 'badge stiker-ean'
					);
				}

				if ($result['jan'] && $ff_stikers['jan']['status']) {
					$stickers['jan'] = array(
						'text'  => $result['jan'],
						'class' => 'badge stiker-jan'
					);
				}

				if ($result['isbn'] && $ff_stikers['isbn']['status']) {
					$stickers['isbn'] = array(
						'text'  => $result['isbn'],
						'class' => 'badge stiker-isbn'
					);
				}

				if ($result['mpn'] && $ff_stikers['mpn']['status']) {
					$stickers['mpn'] = array(
						'text'  => $result['mpn'],
						'class' => 'badge stiker-mpn'
					);
				}

				if (!($hide_noimage_products & !$result['image'])) {
					$data['products'][] = array(
						'product_id'  => $result['product_id'],
						'thumb'       => $image,
						'img_width'   =>  $setting['width'] . 'px',
						'img_height'  =>  $setting['height'] . 'px',
						'thumb_holder'    => $this->model_tool_image->resize('catalog/framefreetheme/src_holder.png', $setting['width'], $setting['height']),
						'thumb2x'         => $hd_imgs ? $image2x : NULL,
						'thumb3x'         => $hd_imgs ? $image3x : NULL,
						'thumb4x'         => $hd_imgs ? $image4x : NULL,
						'stickers'  			=> $stickers,
						'name'        => $result['name'],
						'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
						'quantity'    => $result['quantity'],
						'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
						'price'       => $price,
						'special'     => $special,
						'tax'         => $tax,
						'rating'      => $rating,
						'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'])
					);
				}
			}

		}

		if ($setting['module_type'] == 'featured' || $setting['module_type'] == 'viewed') {

			$products = array();

			if (!empty($setting['product']) && $setting['module_type'] == 'featured') {
				$products = array_slice($setting['product'], 0, (int)$setting['limit']);
			}

			if ($setting['module_type'] == 'viewed') {

				if (isset($this->request->cookie['stk_viewed_products'])) {
					$products = json_decode($this->request->cookie['stk_viewed_products'], true);
				}

				if (!empty($products)) {
					$products = array_unique($products);
					krsort($products);
					$products = array_slice($products, 0, (int)$setting['limit']);
				}

			}

			if (!empty($products)) {
				foreach ($products as $product_id) {
					$product_info = $this->model_catalog_product->getProduct($product_id);

					if ($product_info) {
						if ($product_info['image']) {
							$image = $this->model_tool_image->resize($product_info['image'], $setting['width'], $setting['height']);
							$image2x = $this->model_tool_image->resize($product_info['image'], $setting['width']*2, $setting['height']*2);
							$image3x = $this->model_tool_image->resize($product_info['image'], $setting['width']*3, $setting['height']*3);
							$image4x = $this->model_tool_image->resize($product_info['image'], $setting['width']*4, $setting['height']*4);
						} else {
							$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
							$image2x = $this->model_tool_image->resize('placeholder.png', $setting['width']*2, $setting['height']*2);
							$image3x = $this->model_tool_image->resize('placeholder.png', $setting['width']*3, $setting['height']*3);
							$image4x = $this->model_tool_image->resize('placeholder.png', $setting['width']*4, $setting['height']*4);
						}

						if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
							$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
						} else {
							$price = false;
						}

						if ((float)$product_info['special']) {
							$special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
						} else {
							$special = false;
						}

						if ($this->config->get('config_tax')) {
							$tax = $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price'], $this->session->data['currency']);
						} else {
							$tax = false;
						}

						if ($this->config->get('config_review_status')) {
							$rating = $product_info['rating'];
						} else {
							$rating = false;
						}

						$stickers = array();

						if ($product_info['price'] && $product_info['special'] && $ff_stikers['special']['status']) {
							$stickers['special'] = array(
								'text'  => round(100 - ($product_info['special'] / $product_info['price']) * 100) * (-1) . '%',
								'class' => 'badge stiker-special'
							);
						}

						if ($product_info['upc'] && $ff_stikers['upc']['status']) {
							$stickers['upc'] = array(
								'text'  => $product_info['upc'],
								'class' => 'badge stiker-upc'
							);
						}

						if ($product_info['ean'] && $ff_stikers['ean']['status']) {
							$stickers['ean'] = array(
								'text'  => $product_info['ean'],
								'class' => 'badge stiker-ean'
							);
						}

						if ($product_info['jan'] && $ff_stikers['jan']['status']) {
							$stickers['jan'] = array(
								'text'  => $product_info['jan'],
								'class' => 'badge stiker-jan'
							);
						}

						if ($product_info['isbn'] && $ff_stikers['isbn']['status']) {
							$stickers['isbn'] = array(
								'text'  => $product_info['isbn'],
								'class' => 'badge stiker-isbn'
							);
						}

						if ($product_info['mpn'] && $ff_stikers['mpn']['status']) {
							$stickers['mpn'] = array(
								'text'  => $product_info['mpn'],
								'class' => 'badge stiker-mpn'
							);
						}

						if (!($hide_noimage_products & !$product_info['image'])) {
							$data['products'][] = array(
								'product_id'  => $product_info['product_id'],
								'thumb'       => $image,
								'img_width'   =>  $setting['width'] . 'px',
								'img_height'  =>  $setting['height'] . 'px',
								'thumb_holder'    => $this->model_tool_image->resize('catalog/framefreetheme/src_holder.png', $setting['width'], $setting['height']),
								'thumb2x'         => $image2x,
								'thumb3x'         => $image3x,
								'thumb4x'         => $image4x,
								'stickers'  			=> $stickers,
								'name'        => $product_info['name'],
								'description' => utf8_substr(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
								'quantity'    => $product_info['quantity'],
								'minimum'     => $product_info['minimum'] > 0 ? $product_info['minimum'] : 1,
								'price'       => $price,
								'special'     => $special,
								'tax'         => $tax,
								'rating'      => $rating,
								'href'        => $this->url->link('product/product', 'product_id=' . $product_info['product_id'])
							);
						}
					}
				}
			}
		}

		if ($setting['shufle_products']) {
			shuffle($data['products']);
		}

		$data['module'] = $module++;

		if ($data['products']) {
			return $this->load->view('extension/module/stk_products_carousel', $data);
		}
	}
}
