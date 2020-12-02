<?php
class ControllerExtensionModuleSTKCategoryWall extends Controller {
	public function index($setting) {
		static $module = 0;

		$this->load->model('localisation/language');

		$languages = $this->model_localisation_language->getLanguages();
		$language_id = $this->config->get('config_language_id');

		$this->load->language('extension/module/stk_category_wall');
		$this->load->language('extension/module/framefreetheme/ff_global');

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


		$this->document->addStyle('catalog/view/theme/' . $this->config->get('theme_framefree_directory') . '/javascript/owl-carousel/owl.carousel.min.css');
		$this->document->addScript('catalog/view/theme/' . $this->config->get('theme_framefree_directory') . '/javascript/owl-carousel/owl.carousel.min.js');


		if (!$setting['limit']) {
			$setting['limit'] = 4;
		}

		if ($setting['title']) {
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

    	if (isset($this->request->get['path'])) {
			$parts = explode('_', (string)$this->request->get['path']);
		} else {
			$parts = array();
		}

    	if (isset($this->request->get['path'])) {
			$parts = explode('_', (string)$this->request->get['path']);
		} else {
			$parts = array();
		}

		if (isset($parts[0])) {
			$data['category_id'] = $parts[0];
		} else {
			$data['category_id'] = 0;
		}

		if (isset($parts[1])) {
			$data['child_id'] = $parts[1];
		} else {
			$data['child_id'] = 0;
		}

		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

		$data['categories'] = array();

		$categories_list = $setting['category'];

    	$categories = array();

    	foreach ($categories_list as $category_id) {
			$category_info = $this->model_catalog_category->getCategory($category_id);

			if ($category_info) {
				$categories[] = $category_info;
			}
		}

		foreach ($categories as $category) {
			$children_data = array();

				//if ($category['category_id'] == $data['category_id']) {	}

				$children = $this->model_catalog_category->getCategories($category['category_id']);

        $children = array_slice($children, 0, $setting['limit']);

				foreach($children as $child) {
					$filter_data = array('filter_category_id' => $child['category_id'], 'filter_sub_category' => true);

					$children_data[] = array(
						'category_id' => $child['category_id'],
						'name' => $child['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
						'href' => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id'])
					);
				}




			$filter_data = array(
				'filter_category_id'  => $category['category_id'],
				'filter_sub_category' => true
			);

		      if ($category['image']) {
		        $image = $this->model_tool_image->resize($category['image'], $setting['width'], $setting['height']);
		        if ($hd_imgs) {
		          $image2x = $this->model_tool_image->resize($category['image'], $setting['width']*2, $setting['height']*2);
		          $image3x = $this->model_tool_image->resize($category['image'], $setting['width']*3, $setting['height']*3);
		          $image4x = $this->model_tool_image->resize($category['image'], $setting['width']*4, $setting['height']*4);
		        }

		      } else {
		        $image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
		        if ($hd_imgs) {
		          $image2x = $this->model_tool_image->resize('placeholder.png', $setting['width']*2, $setting['height']*2);
		          $image3x = $this->model_tool_image->resize('placeholder.png', $setting['width']*3, $setting['height']*3);
		          $image4x = $this->model_tool_image->resize('placeholder.png', $setting['width']*4, $setting['height']*4);
		        }
		      }


			$data['categories'][] = array(
				'category_id' => $category['category_id'],
        		'thumb'       => $image,
				'img_width'   => $setting['width'] . 'px',
				'img_height'  => $setting['height'] . 'px',
				'thumb_holder'=> $this->model_tool_image->resize('catalog/framefreetheme/src_holder.png', $setting['width'], $setting['height']),
		        'thumb2x'     => $hd_imgs ? $image2x : NULL,
		        'thumb3x'     => $hd_imgs ? $image3x : NULL,
		        'thumb4x'     => $hd_imgs ? $image4x : NULL,
				'active'     	=> ($category['category_id'] == $data['category_id']) ? true : false,
				'name'        => $category['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
				'children'    => $children_data,
				'href'        => $this->url->link('product/category', 'path=' . $category['category_id'])
			);


		}

		$data['module'] = $module++;

		if ($data['categories']) {
			return $this->load->view('extension/module/stk_category_wall', $data);
		}
	}
}
