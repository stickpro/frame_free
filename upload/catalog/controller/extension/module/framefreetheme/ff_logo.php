<?php
class ControllerExtensionModuleFramefreethemeFFLogo extends Controller {
	public function index() {
		$this->load->model('setting/setting');

		$ff_settings = array();
		$ff_settings = $this->model_setting_setting->getSetting('theme_framefree', $this->config->get('config_store_id'));
		$language_id = $this->config->get('config_language_id');

		if (isset($ff_settings['t1_logo']) && $ff_settings['t1_logo']){
			$logo = $ff_settings['t1_logo'];
		} else {
			$logo = false;
		}

		if (isset($ff_settings['t1_high_definition_imgs']) && $ff_settings['t1_high_definition_imgs']){
			$hd_imgs = $ff_settings['t1_high_definition_imgs'];
		} else {
			$hd_imgs = false;
		}

		if (isset($ff_settings['t1_logo_width']) && $ff_settings['t1_logo_width']){
			$width = $ff_settings['t1_logo_width'];
		} else {
			$width = 0;
		}

		if (isset($ff_settings['t1_logo_height']) && $ff_settings['t1_logo_height']){
			$height = $ff_settings['t1_logo_height'];
		} else {
			$height = 0;
		}

		if (isset($ff_settings['t1_svg_logo_status']) && $ff_settings['t1_svg_logo_status']){
			$data['svg_logo_status'] = $ff_settings['t1_svg_logo_status'];
		} else {
			$data['svg_logo_status'] = false;
		}

		if (isset($ff_settings['t1_svg_logo']) && $ff_settings['t1_svg_logo']){
			$data['svg_logo'] = html_entity_decode($ff_settings['t1_svg_logo'][$this->config->get('config_language_id')], ENT_QUOTES, 'UTF-8');
		} else {
			$data['svg_logo'] = '';
		}

		if (isset($this->request->get['route'])) {
			$route = $this->request->get['route'];
		} else {
			$route = 'common/home';
		}

		if ($route == 'common/home') {
			$data['on_home'] = true;
		} else {
			$data['on_home'] = false;
		}


		$this->load->model('tool/image');

		$data['logo'] = $logo;
		$data['name'] = $this->config->get('config_name');
		$data['home'] = $this->url->link('common/home');

		$data['hd_imgs'] = $hd_imgs;
		$data['logo1x'] = $this->model_tool_image->resize($logo, $width, $height);

		if ($hd_imgs) {
			$data['logo2x'] = $this->model_tool_image->resize($logo, $width*2, $height*2);
			$data['logo3x'] = $this->model_tool_image->resize($logo, $width*3, $height*3);
			$data['logo4x'] = $this->model_tool_image->resize($logo, $width*4, $height*4);
		}

		return $this->load->view('extension/module/framefreetheme/ff_logo', $data);
	}
}
