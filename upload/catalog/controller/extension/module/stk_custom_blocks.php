<?php
class ControllerExtensionModulestkCustomBlocks extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/stk_custom_blocks');

		$this->load->model('tool/image');

		$this->load->model('setting/setting');

		$ff_settings = array();
		$ff_settings = $this->model_setting_setting->getSetting('theme_framefree', $this->config->get('config_store_id'));

		if (isset($ff_settings['t1_high_definition_imgs']) && $ff_settings['t1_high_definition_imgs']){
			$hd_imgs = $ff_settings['t1_high_definition_imgs'];
		} else {
			$hd_imgs = false;
		}

		$language_id = $this->config->get('config_language_id');

		$data['columns'] = $setting['columns'];
		$data['columns_sm'] = $setting['columns_sm'];
		$data['columns_md'] = $setting['columns_md'];
		$data['columns_lg'] = $setting['columns_lg'];
		$data['columns_xl'] = $setting['columns_xl'];
		$data['columns_xxl'] = $setting['columns_xxl'];

		$results = $setting['cust_blocks_item'];

		foreach ($results as $result) {
			$data['blocks'][] = array(
				'image' => $this->model_tool_image->resize($result['image'], 50, 50),
				'image2x' => $hd_imgs ? $this->model_tool_image->resize($result['image'], 50*2, 50*2) : NULL,
				'image3x' => $hd_imgs ? $this->model_tool_image->resize($result['image'], 50*3, 50*3) : NULL,
				'image4x' => $hd_imgs ? $this->model_tool_image->resize($result['image'], 50*4, 50*4) : NULL,
				'title' => $result['title'][$language_id],
				'description' => $result['description'][$language_id],
				'link'  => $result['link'][$language_id],
				'html'  => html_entity_decode($result['html'], ENT_QUOTES, 'UTF-8'),
				'sort'  => $result['sort']
			);
		}

		if (!empty($data['blocks'])){
			foreach ($data['blocks'] as $key => $value) {
				$sort[$key] = $value['sort'];
			}
			array_multisort($sort, SORT_ASC, $data['blocks']);
		}

		return $this->load->view('extension/module/stk_custom_blocks', $data);
	}
}
