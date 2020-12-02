<?php
class ControllerExtensionModuleFramefreethemeFFHlinks extends Controller {
	public function index() {

		// $this->load->model('tool/image');
		
		$this->load->model('setting/setting');
			
		$ff_settings = array();
		$ff_settings = $this->model_setting_setting->getSetting('theme_framefree', $this->config->get('config_store_id'));
		$language_id = $this->config->get('config_language_id');

		if (isset($ff_settings['t1_help_menu_toggle']) && $ff_settings['t1_help_menu_toggle']){
			$data['links_status'] = $ff_settings['t1_help_menu_toggle'];
		} else {
			$data['links_status'] = false;
		}
		
		if (isset($ff_settings['t1_help_menu_text'][$language_id]) && $ff_settings['t1_help_menu_text'][$language_id]){
			$data['links_title'] = $ff_settings['t1_help_menu_text'][$language_id];
		} else {
			$data['links_title'] = '';
		}
		
		if (isset($ff_settings['t1_help_menu_item']) && $ff_settings['t1_help_menu_item']){
			$links = $ff_settings['t1_help_menu_item'];
		} else {
			$links = array();
		}

		if (!empty($links)){
			foreach ($links as $key => $value) {
				$sorted_links[$key] = $value['sort'];
			} 
			array_multisort($sorted_links, SORT_ASC, $links);
		}
		
		$data['links'] = array();
		
		foreach ($links as $link) {
			$data['links'][] = array(
				'title'        => html_entity_decode($link['title'][$language_id], ENT_QUOTES, 'UTF-8'),
				'href'         => $link['link'][$language_id],
				'sort'         => $link['sort'],
			);
		}

		return $this->load->view('extension/module/framefreetheme/ff_hlinks', $data);
	}
}
