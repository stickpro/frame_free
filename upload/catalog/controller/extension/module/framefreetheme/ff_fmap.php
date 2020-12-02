<?php
class ControllerExtensionModuleFramefreethemeFFFmap extends Controller {
	public function index() {
		$this->load->language('extension/module/framefreetheme/ff_fmap');

		$this->load->model('setting/setting');

		$ff_settings = array();
		$ff_settings = $this->model_setting_setting->getSetting('theme_framefree', $this->config->get('config_store_id'));
		$language_id = $this->config->get('config_language_id');

		if (isset($ff_settings['t1_footer_map_toggle']) && $ff_settings['t1_footer_map_toggle']){
			$data['fmap_status'] = $ff_settings['t1_footer_map_toggle'];
		} else {
			$data['fmap_status'] = '';
		}

		if (isset($ff_settings['t1_footer_map_code']) && $ff_settings['t1_footer_map_code']){
			$data['fmap_code'] = html_entity_decode($ff_settings['t1_footer_map_code'], ENT_QUOTES, 'UTF-8');
		} else {
			$data['fmap_code'] = '';
		}

		if (isset($ff_settings['t1_footer_map_geocode']) && $ff_settings['t1_footer_map_geocode']){
			$data['geocode'] = $ff_settings['t1_footer_map_geocode'];
		} else {
			$data['geocode'] = $this->config->get('config_geocode');
		}

		$this->load->model('tool/image');

		$data['map_bg'] = $this->model_tool_image->resize('catalog/framefreetheme/fmap_bg.png', 1120, 84);
   	 	$data['map_bg_src_holder'] = $this->model_tool_image->resize('catalog/framefreetheme/src_holder.png', 1120, 84);

		if (isset($this->request->get['get_map_code']) && $this->request->get['get_map_code']) {

			$data['get_map_code'] = true;

			$this->response->setOutput($this->load->view('extension/module/framefreetheme/ff_fmap', $data));

		} else {

			$data['get_map_code'] = false;

			return $this->load->view('extension/module/framefreetheme/ff_fmap', $data);
		}
	}
}
