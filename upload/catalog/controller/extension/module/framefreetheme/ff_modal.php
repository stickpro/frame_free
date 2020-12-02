<?php
class ControllerExtensionModuleFrameFreethemeFFModal extends Controller {
	public function index() {
		$this->load->model('setting/setting');
			
		$ff_settings = array();
		$ff_settings = $this->model_setting_setting->getSetting('theme_framefree', $this->config->get('config_store_id'));
		$language_id = $this->config->get('config_language_id');

		if (isset($ff_settings['t1_modal_status']) && $ff_settings['t1_modal_status']){
			$data['status'] = $ff_settings['t1_modal_status'];
		} else {
			$data['status'] = false;
		}
		
		if (isset($ff_settings['t1_modal_cookie_days']) && $ff_settings['t1_modal_cookie_days']){
			$data['cookie_days'] = $ff_settings['t1_modal_cookie_days'];
		} else {
			$data['cookie_days'] = false;
		}
		
		if (isset($ff_settings['t1_modal_size']) && $ff_settings['t1_modal_size']){
			$data['size'] = $ff_settings['t1_modal_size'];
		} else {
			$data['size'] = false;
		}
		
		if (isset($ff_settings['t1_modal_heading']) && $ff_settings['t1_modal_heading']){
			$data['heading'] = html_entity_decode($ff_settings['t1_modal_heading'][$language_id], ENT_QUOTES, 'UTF-8');
		} else {
			$data['heading'] = false;
		}
		
		if (isset($ff_settings['t1_modal_content']) && $ff_settings['t1_modal_content']){
			$data['content'] = html_entity_decode($ff_settings['t1_modal_content'][$language_id], ENT_QUOTES, 'UTF-8');
		} else {
			$data['content'] = false;
		}

		return $this->load->view('extension/module/framefreetheme/ff_modal', $data);
	}
}