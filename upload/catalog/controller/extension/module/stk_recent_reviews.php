<?php
class ControllerExtensionModuleSTKRecentReviews extends Controller {
	public function index($setting) {
    static $module = 0;

		$this->load->language('extension/module/stk_recent_reviews');

		$this->document->addStyle('catalog/view/theme/' . $this->config->get('theme_framefree_directory') . '/javascript/owl-carousel/owl.carousel.min.css');
		$this->document->addScript('catalog/view/theme/' . $this->config->get('theme_framefree_directory') . '/javascript/owl-carousel/owl.carousel.min.js');

		$this->load->model('tool/image');
    $this->load->model('extension/theme/frame');
    $this->load->model('catalog/product');

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

		$language_id = $this->config->get('config_language_id');

    $results = $this->model_extension_theme_framefree->getLastReviews($setting['limit'], $setting['rating']);

    if ($setting['title'][$language_id]) {
			$data['heading_title'] = $setting['title'][$language_id];
		}

		$data['controls'] = isset($setting['controls']) ? $setting['controls'] : array();

		$data['autoplay'] = $setting['autoplay'];
		$data['autoplay_speed'] = $setting['autoplay_speed'];

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

    foreach ($results as $result) {
      $product_info = $this->model_catalog_product->getProduct($result['product_id']);

      if ($product_info['image']) {
        $image = $this->model_tool_image->resize($product_info['image'], $setting['image_width'], $setting['image_height']);
				if ($hd_imgs) {
					$image2x = $this->model_tool_image->resize($product_info['image'], $setting['image_width']*2, $setting['image_height']*2);
					$image3x = $this->model_tool_image->resize($product_info['image'], $setting['image_width']*3, $setting['image_height']*3);
					$image4x = $this->model_tool_image->resize($product_info['image'], $setting['image_width']*4, $setting['image_height']*4);
				}
      } else {
        $image = '';
      }

      $data['reviews'][] = array(
        'review_id'     => $result['review_id'],
        'author'        => $result['author'],
        'date_added'    => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
        'rating'        => $result['rating'],
        'text'          => utf8_substr(trim(strip_tags(html_entity_decode($result['text'], ENT_QUOTES, 'UTF-8'))), 0, 170) . '...',
        'customer_id'   => $result['customer_id'],
        'product_name'  => $product_info['name'],
        'product_href'  => $this->url->link('product/product', '&product_id=' . $result['product_id']),
		'thumb_holder'    => $this->model_tool_image->resize('catalog/framefreetheme/src_holder.png', $setting['image_width'], $setting['image_height']),
        'product_image' => $image,
		'img_width'     => $setting['image_width'] . 'px',
		'img_height'     => $setting['image_height'] . 'px',
		'product_image2x' => $hd_imgs ? $image2x : NULL,
		'product_image3x' => $hd_imgs ? $image3x : NULL,
		'product_image4x' => $hd_imgs ? $image4x : NULL
      );

    }

		if ($setting['sort']) {
			shuffle($data['reviews']);
		}

    $data['module'] = $module++;

		return $this->load->view('extension/module/stk_recent_reviews', $data);
	}
}
