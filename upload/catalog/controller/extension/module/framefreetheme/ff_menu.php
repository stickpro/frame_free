<?php
class ControllerExtensionModuleFramefreethemeFFMenu extends Controller {
	public function index() {
		$this->load->language('extension/module/framefreetheme/ff_menu');

		$this->load->model('setting/setting');

		$ff_settings = array();
		$ff_settings = $this->model_setting_setting->getSetting('theme_framefree', $this->config->get('config_store_id'));
		$language_id = $this->config->get('config_language_id');

		if (isset($ff_settings['t1_category_mask_toggle']) && $ff_settings['t1_category_mask_toggle']){
			$data['mask_status'] = $ff_settings['t1_category_mask_toggle'];
		} else {
			$data['mask_status'] = false;
		}

		if (isset($ff_settings['t1_category_third_level_toggle']) && $ff_settings['t1_category_third_level_toggle']){
			$data['third_level_status'] = $ff_settings['t1_category_third_level_toggle'];
		} else {
			$data['third_level_status'] = false;
		}

		if (isset($ff_settings['t1_category_third_level_limit']) && $ff_settings['t1_category_third_level_limit']){
			$data['third_level_limit'] = $ff_settings['t1_category_third_level_limit'];
		} else {
			$data['third_level_limit'] = 5;
		}

		if (isset($ff_settings['t1_category_no_full_height_submenu']) && $ff_settings['t1_category_no_full_height_submenu']){
			$data['alt_view_submenu'] = $ff_settings['t1_category_no_full_height_submenu'];
		} else {
			$data['alt_view_submenu'] = false;
		}

		if (isset($ff_settings['t1_add_cat_links_toggle']) && $ff_settings['t1_add_cat_links_toggle']){
			$data['add_cat_links_status'] = $ff_settings['t1_add_cat_links_toggle'];
		} else {
			$data['add_cat_links_status'] = false;
		}

		if (isset($ff_settings['t1_category_icon']) && $ff_settings['t1_category_icon']){
			$category_icons = $ff_settings['t1_category_icon'];
		} else {
			$category_icons = array();
		}

		if (isset($ff_settings['t1_add_cat_links_item']) && !empty($ff_settings['t1_add_cat_links_item'])) {
			$results = $ff_settings['t1_add_cat_links_item'];
		} else {
			$results = array();
		}

		$data['add_category_menu'] = array();

		foreach ($results as $result) {

			if (is_file(DIR_IMAGE . $result['image_peace'])) {
				$image_peace = $result['image_peace'];
			} else {
				$image_peace = '';
			}

			if (is_file(DIR_IMAGE . $result['image_hover'])) {
				$image_hover = $result['image_hover'];
			} else {
				$image_hover = '';
			}

			$data['add_category_menu'][] = array(
				'image_peace' => $this->model_tool_image->resize($image_peace, 24, 24),
				'image_hover' => $this->model_tool_image->resize($image_hover, 24, 24),
				'title'			  => html_entity_decode($result['title'][$language_id], ENT_QUOTES, 'UTF-8'),
				'link'  			=> $result['link'][$language_id],
				'position'		=> $result['position'],
				'html'  			=> html_entity_decode($result['html'], ENT_QUOTES, 'UTF-8'),
				'sort'  			=> $result['sort']
			);

		}

		if (!empty($data['add_category_menu'])){
			foreach ($data['add_category_menu'] as $key => $value) {
				$sort_add_category_menu[$key] = $value['sort'];
			}
			array_multisort($sort_add_category_menu, SORT_ASC, $data['add_category_menu']);
		}


		// Menu
		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

		$data['categories'] = array();

		$categories = $this->model_catalog_category->getCategories(0);


		if (isset($this->request->get['route'])) {
			$route = $this->request->get['route'];
		} else {
			$route = 'common/home';
		}


		if (isset($ff_settings['t1_category_shown_pages']) && !empty($ff_settings['t1_category_shown_pages'])) {
			$shown_menu_routes = $ff_settings['t1_category_shown_pages'];
		} else {
			$shown_menu_routes = array('product/category', 'common/home');
		}

		$data['menu_show'] = false;

		if (in_array($route, $shown_menu_routes)) {
			$data['menu_show'] = true;
		}

		$parts = array();
		if (isset($this->request->get['path'])) {
			$parts = explode('_', (string)$this->request->get['path']);
		}

		foreach ($categories as $category) {




			if ($category['top']) {

				if (isset($category_icons[$category['category_id']]['html'])) {
					$html_icon = html_entity_decode($category_icons[$category['category_id']]['html'], ENT_QUOTES, 'UTF-8');
				} else {
					$html_icon = '';
				}

				if (isset($category_icons[$category['category_id']]['peace']) && is_file(DIR_IMAGE . $category_icons[$category['category_id']]['peace'])) {
					$icon_peace = $this->model_tool_image->resize($category_icons[$category['category_id']]['peace'], 24, 24);
				} else {
					$icon_peace = '';
				}

				if (isset($category_icons[$category['category_id']]['hover']) && is_file(DIR_IMAGE . $category_icons[$category['category_id']]['hover'])) {
					$icon_hover = $this->model_tool_image->resize($category_icons[$category['category_id']]['hover'], 24, 24);
				} else {
					$icon_hover = '';
				}

				// Level 2
				$children_data = array();

				$children = $this->model_catalog_category->getCategories($category['category_id']);

				foreach ($children as $child) {

					// Level 3
					$children2_data = array();

					$children2 = $this->model_catalog_category->getCategories($child['category_id']);

					foreach ($children2 as $child2) {

						$filter_data3 = array(
							'filter_category_id'  => $child2['category_id'],
							'filter_sub_category' => true
						);

						$active = false;
						if (isset($parts[2]) && $parts[2] == $child2['category_id']) {
							$active = true;
						}

						$children2_data[] = array(
							'active' => $active,
							'name'  => $child2['name'] . ($this->config->get('config_product_count') ? ' <small class="count">' . $this->model_catalog_product->getTotalProducts($filter_data3) . '</small>' : ''),
							'href'  => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id'].'_'. $child2['category_id'])
						);
					}

					if (isset($category_icons[$child['category_id']]['html'])) {
						$child_html_icon = html_entity_decode($category_icons[$child['category_id']]['html'], ENT_QUOTES, 'UTF-8');
					} else {
						$child_html_icon = '';
					}

					if (isset($category_icons[$child['category_id']]['peace']) && is_file(DIR_IMAGE . $category_icons[$child['category_id']]['peace'])) {
						$child_icon_peace = $this->model_tool_image->resize($category_icons[$child['category_id']]['peace'], 24, 24);
					} else {
						$child_icon_peace = '';
					}

					if (isset($category_icons[$child['category_id']]['hover']) && is_file(DIR_IMAGE . $category_icons[$child['category_id']]['hover'])) {
						$child_icon_hover = $this->model_tool_image->resize($category_icons[$child['category_id']]['hover'], 24, 24);
					} else {
						$child_icon_hover = '';
					}

					$filter_data2 = array(
						'filter_category_id'  => $child['category_id'],
						'filter_sub_category' => true
					);

					$active = false;
					if (isset($parts[1]) && $parts[1] == $child['category_id']) {
						$active = true;
					}

					$children_data[] = array(
						'category_id' => $child['category_id'],
						'icon_peace' => $child_icon_peace,
						'icon_hover' => $child_icon_hover,
						'html_icon' => $child_html_icon,
						'active' => $active,
						'children2'    => $children2_data,
						'name'  => $child['name'] . ($this->config->get('config_product_count') ? ' <small class="count">' . $this->model_catalog_product->getTotalProducts($filter_data2) . '</small>' : ''),
						'href'  => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id'])
					);
				}

				// Level 1
				$filter_data = array(
					'filter_category_id'  => $category['category_id'],
					'filter_sub_category' => true
				);

				$active = false;
				if (isset($parts[0]) && $parts[0] == $category['category_id']) {
					$active = true;
				}

				if ((int)$category['column'] < 2) {
					$column = 1;
				} elseif ((int)$category['column'] == 2) {
					$column = 2;
				} else {
					$column = 3;
				}

				$data['categories'][] = array(
					'category_id'     => $category['category_id'],
					'icon_peace' => $icon_peace,
					'icon_hover' => $icon_hover,
					'html_icon' => $html_icon,
					'active' => $active,
					'name'  => $category['name'] . ($this->config->get('config_product_count') ? ' <small class="count">' . $this->model_catalog_product->getTotalProducts($filter_data) . '</small>' : ''),
					'children' => $children_data,
					'column'   => $column,
					'href'     => $this->url->link('product/category', 'path=' . $category['category_id'])
				);
			}
		}

		return $this->load->view('extension/module/framefreetheme/ff_menu', $data);
	}
}
