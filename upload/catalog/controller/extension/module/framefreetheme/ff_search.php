<?php
class ControllerExtensionModuleFramefreethemeFFSearch extends Controller {
	public function index() {
		$this->load->language('extension/module/framefreetheme/ff_search');

		$this->load->model('catalog/category');

		$data['text_search'] = $this->language->get('text_search');
		$data['text_anywhere'] = $this->language->get('text_anywhere');

		if (isset($this->request->get['category_id'])) {

			$category_id = $this->request->get['category_id'];
			$category_info = $this->model_catalog_category->getCategory($category_id);

			if (isset($category_info['parent_id'])){
				while ($category_info['parent_id'] != 0) {
					$category_id = $category_info['parent_id'];
					$category_info = $this->model_catalog_category->getCategory($category_id);
				}
			}

			$data['category_id'] = $category_id;

			if (isset($category_info['name'])){
				$data['category_id'] = $category_info['category_id'];
				$data['category_name'] = $category_info['name'];
			}

		} else {
			$data['category_id'] = 0;
			$data['category_name'] = $this->language->get('text_anywhere');
		}

		$categories = array();

		$data['categories'] = array();

		$categories = $this->model_catalog_category->getCategories(0);

		foreach ($categories as $category) {
			$data['categories'][] = array(
				'category_id' => $category['category_id'],
				'name'        => $category['name']
			);
		}

		if (isset($this->request->get['search'])) {
			$data['search'] = $this->request->get['search'];
		} else {
			$data['search'] = '';
		}


		$this->load->model('setting/setting');

		$ff_settings = array();
		$ff_settings = $this->model_setting_setting->getSetting('theme_framefree', $this->config->get('config_store_id'));
		$language_id = $this->config->get('config_language_id');

		if (isset($ff_settings['t1_livesearch_toggle']) && $ff_settings['t1_livesearch_toggle']){
			$data['livesearch_toggle'] = $ff_settings['t1_livesearch_toggle'];
		} else {
			$data['livesearch_toggle'] = false;
		}

		if (isset($ff_settings['t1_livesearch_subcat_search']) && $ff_settings['t1_livesearch_subcat_search']){
			$data['subcat_search'] = $ff_settings['t1_livesearch_subcat_search'];
		} else {
			$data['subcat_search'] = false;
		}

		if (isset($ff_settings['t1_livesearch_description_search']) && $ff_settings['t1_livesearch_description_search']){
			$data['description_search'] = $ff_settings['t1_livesearch_description_search'];
		} else {
			$data['description_search'] = false;
		}

		if (isset($ff_settings['t1_livesearch_show_description']) && $ff_settings['t1_livesearch_show_description']){
			$data['show_description'] = $ff_settings['t1_livesearch_show_description'];
		} else {
			$data['show_description'] = false;
		}

		if (isset($ff_settings['t1_livesearch_mask']) && $ff_settings['t1_livesearch_mask']){
			$data['mask'] = $ff_settings['t1_livesearch_mask'];
		} else {
			$data['mask'] = false;
		}

		if (isset($ff_settings['t1_livesearch_characters']) && $ff_settings['t1_livesearch_characters']){
			$data['characters'] = $ff_settings['t1_livesearch_characters'];
		} else {
			$data['characters'] = false;
		}

		return $this->load->view('extension/module/framefreetheme/ff_search', $data);
	}

	public function livesearch() {

		$json = array();

		$this->load->language('extension/module/framefreetheme/ff_search');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');


		$this->load->model('setting/setting');

		$ff_settings = array();
		$ff_settings = $this->model_setting_setting->getSetting('theme_framefree', $this->config->get('config_store_id'));
		$language_id = $this->config->get('config_language_id');

		if (isset($ff_settings['t1_livesearch_results']) && $ff_settings['t1_livesearch_results']){
			$limit = $ff_settings['t1_livesearch_results'];
		} else {
			$limit = 3;
		}

		if (isset($this->request->get['search'])) {
			$search = $this->request->get['search'];
		} else {
			$search = '';
		}

    if (isset($this->request->get['tag'])) {
			$tag = $this->request->get['tag'];
		} elseif (isset($this->request->get['search'])) {
			$tag = $this->request->get['search'];
		} else {
			$tag = '';
		}

		if (isset($this->request->get['description'])) {
			$description = $this->request->get['description'];
		} else {
			$description = '';
		}

		if (isset($this->request->get['category_id'])) {
			$category_id = $this->request->get['category_id'];
		} else {
			$category_id = 0;
		}

		if (isset($this->request->get['sub_category'])) {
			$sub_category = $this->request->get['sub_category'];
		} else {
			$sub_category = '';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'p.sort_order';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		$url = '';

		if (isset($this->request->get['search'])) {
			$url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['description'])) {
			$url .= '&description=' . $this->request->get['description'];
		}

		if (isset($this->request->get['category_id'])) {
			$url .= '&category_id=' . $this->request->get['category_id'];
		}

		if (isset($this->request->get['sub_category'])) {
			$url .= '&sub_category=' . $this->request->get['sub_category'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$json['products'] = array();

		if (isset($this->request->get['search']) || isset($this->request->get['tag'])) {
			$filter_data = array(
				'filter_name'         => $search,
				'filter_tag'          => $tag,
				'filter_description'  => $description,
				'filter_category_id'  => $category_id,
				'filter_sub_category' => $sub_category,
				'sort'                => $sort,
				'order'               => $order,
				'start'               => 0,
				'limit'               => $limit
			);

			$results = $this->model_catalog_product->getProducts($filter_data);

			$json['total'] = $this->model_catalog_product->getTotalProducts($filter_data);

			foreach ($results as $result) {

				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], 50, 50);
					$image2x = $this->model_tool_image->resize($result['image'], 50*2, 50*2);
					$image3x = $this->model_tool_image->resize($result['image'], 50*3, 50*3);
					$image4x = $this->model_tool_image->resize($result['image'], 50*4, 50*4);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', 50, 50);
					$image2x = $this->model_tool_image->resize('placeholder.png', 50*2, 50*2);
					$image3x = $this->model_tool_image->resize('placeholder.png', 50*3, 50*3);
					$image4x = $this->model_tool_image->resize('placeholder.png', 50*4, 50*4);
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
					$rating = (int)$result['rating'];
				} else {
					$rating = false;
				}

				$json['products'][] = array(
					'product_id'  => $result['product_id'],
					'thumb'       => $image,
					'thumb2x'     => $image2x,
					'thumb3x'     => $image3x,
					'thumb4x'     => $image4x,
					'name'        => $result['name'],
					'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'       => $price,
					'special'     => $special,
					'tax'         => $tax,
					'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
					'rating'      => $result['rating'],
					'href'        => html_entity_decode($this->url->link('product/product', 'product_id=' . $result['product_id']), ENT_QUOTES, 'UTF-8')
				);
			}

		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));

	}
}
