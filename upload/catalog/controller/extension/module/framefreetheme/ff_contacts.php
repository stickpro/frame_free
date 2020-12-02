<?php
class ControllerExtensionModuleFramefreethemeFFContacts extends Controller {
	public function index() {

		$this->load->language('extension/module/framefreetheme/ff_contacts');

		$data['theme_dir'] = $this->config->get('theme_framefree_directory');

		$this->load->model('tool/image');

		$this->load->model('setting/setting');

		$ff_settings = array();
		$ff_settings = $this->model_setting_setting->getSetting('theme_framefree', $this->config->get('config_store_id'));
		$language_id = $this->config->get('config_language_id');

		if (isset($ff_settings['t1_contact_main_toggle']) && $ff_settings['t1_contact_main_toggle']){
			$data['contacts_status'] = $ff_settings['t1_contact_main_toggle'];
		} else {
			$data['contacts_status'] = false;
		}

		if (isset($ff_settings['t1_high_definition_imgs']) && $ff_settings['t1_high_definition_imgs']){
			$hd_imgs = $ff_settings['t1_high_definition_imgs'];
		} else {
			$hd_imgs = false;
		}

		$data['all_contacts_link'] = $this->url->link('information/contact', '', true);

		if (isset($ff_settings['t1_contact_main_phone'][$language_id]) && $ff_settings['t1_contact_main_phone'][$language_id]){
			$data['main_phone'] = $ff_settings['t1_contact_main_phone'][$language_id];
		} else {
			$data['main_phone'] = $this->config->get('config_telephone');
		}

		if (isset($ff_settings['t1_contact_main_phone_link'][$language_id]) && $ff_settings['t1_contact_main_phone_link'][$language_id]){
			$data['main_phone_href'] = $ff_settings['t1_contact_main_phone_link'][$language_id];
		} else {
			$data['main_phone_href'] = '';
		}

		if (isset($ff_settings['t1_contact_hint'][$language_id]) && $ff_settings['t1_contact_hint'][$language_id]){
			$data['main_hint'] = $ff_settings['t1_contact_hint'][$language_id];
		} else {
			$data['main_hint'] = '';
		}

		if (isset($ff_settings['t1_cb_link']) && $ff_settings['t1_cb_link']){
			$data['cb_link'] = $ff_settings['t1_cb_link'];
		} else {
			$data['cb_link'] = false;
		}

		if (isset($ff_settings['t1_cb_link_text'][$language_id]) && $ff_settings['t1_cb_link_text'][$language_id]){
			$data['cb_link_text'] = $ff_settings['t1_cb_link_text'][$language_id];
		} else {
			$data['cb_link_text'] = '';
		}

		if (isset($ff_settings['t1_callback_form_toggle']) && $ff_settings['t1_callback_form_toggle']){
			$data['callback_status'] = $ff_settings['t1_callback_form_toggle'];
		} else {
			$data['callback_status'] = false;
		}

		if (isset($ff_settings['t1_contact_add_toggle']) && $ff_settings['t1_contact_add_toggle']){
			$data['additional_contacts_status'] = $ff_settings['t1_contact_add_toggle'];
		} else {
			$data['additional_contacts_status'] = false;
		}

		if (isset($ff_settings['t1_callback_form_button_text'][$language_id]) && $ff_settings['t1_callback_form_button_text'][$language_id]){
			$data['callback_button_text'] = $ff_settings['t1_callback_form_button_text'][$language_id];
		} else {
			$data['callback_button_text'] = $this->language->get('ff_callback_tab');
		}

		if (isset($ff_settings['t1_callback_hint'][$language_id]) && $ff_settings['t1_callback_hint'][$language_id]){
			$data['callback_hint'] = $ff_settings['t1_callback_hint'][$language_id];
		} else {
			$data['callback_hint'] = '';
		}

		if (isset($ff_settings['t1_callback_phone_mask']) && $ff_settings['t1_callback_phone_mask']){
			$data['phone_mask'] = $ff_settings['t1_callback_phone_mask'];
		} else {
			$data['phone_mask'] = '';
		}


		if (isset($this->request->post['customer_name'])) {
			$data['customer_name'] = $this->request->post['customer_name'];
		} else {
			$data['customer_name'] = $this->customer->getFirstName();
		}

		if (isset($this->request->post['customer_telephone'])) {
			$data['customer_telephone'] = $this->request->post['customer_telephone'];
		} else {
			$data['customer_telephone'] = $this->customer->getTelephone();
		}

		if (isset($ff_settings['t1_additional_number']) && !empty($ff_settings['t1_additional_number'])) {
			$additional_phones = $ff_settings['t1_additional_number'];
		} else {
			$additional_phones = array();
		}

		$data['other_contacts_show'] = false;

		if (isset($ff_settings['t1_additional_contact']) && !empty($ff_settings['t1_additional_contact'])) {
			$other_contacts = $ff_settings['t1_additional_contact'];
		} else {
			$other_contacts = array();
		}

		if (!empty($additional_phones)){
			foreach ($additional_phones as $key => $value) {
				$additional_phones_sorted[$key] = $value['sort'];
			}
			array_multisort($additional_phones_sorted, SORT_ASC, $additional_phones);
		}

		foreach ($additional_phones as $phone) {

			$data['additional_phones'][] = array(
				'title'        => $phone['title'][$language_id],
				'href'         => $phone['link'][$language_id],
				'hint_text'    => $phone['hint'][$language_id],
				'hint_show'    => false,
				'image' 		   => $this->model_tool_image->resize($phone['image'], 24, 24) ,
				'image2x' 		 => $hd_imgs ? $this->model_tool_image->resize($phone['image'], 24*2, 24*2) : NULL,
				'image3x'      => $hd_imgs ? $this->model_tool_image->resize($phone['image'], 24*3, 24*3) : NULL,
				'image4x'      => $hd_imgs ? $this->model_tool_image->resize($phone['image'], 24*4, 24*4) : NULL,
				'sort'         => $phone['sort'],
			);

		}

		if (!empty($other_contacts)){
			foreach ($other_contacts as $key => $value) {
				$other_contacts_sorted[$key] = $value['sort'];
			}
			array_multisort($other_contacts_sorted, SORT_ASC, $other_contacts);
		}

		foreach ($other_contacts as $contact) {
			$data['other_contacts'][] = array(
				'title'        => $contact['title'][$language_id],
				'href'         => $contact['link'][$language_id],
				'target'       => false,
				'hint'         => $contact['hint'][$language_id],
				'image' 		   => $this->model_tool_image->resize($contact['image'], 16*1, 16*1),
				'image2x' 	   => $hd_imgs ? $this->model_tool_image->resize($contact['image'], 16*2, 16*2) : NULL,
				'image3x' 	   => $hd_imgs ? $this->model_tool_image->resize($contact['image'], 16*3, 16*3) : NULL,
				'image4x' 	   => $hd_imgs ? $this->model_tool_image->resize($contact['image'], 16*4, 16*4) : NULL,
				'sort'         => $contact['sort'],
			);
		}

		if ($data['contacts_status']) {
			return $this->load->view('extension/module/framefreetheme/ff_contacts', $data);
		}

	}

	private $error = array();

	public function callback() {

		$this->load->language('extension/module/framefreetheme/ff_contacts');

			if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

				$name = $this->request->post['c_name'];
				$phone = $this->request->post['c_phone'];
				$comment = $this->request->post['c_comment'];



				$this->load->model('setting/setting');

				$ff_settings = array();
				$ff_settings = $this->model_setting_setting->getSetting('theme_framefree', $this->config->get('config_store_id'));
				$language_id = $this->config->get('config_language_id');


				if (isset($ff_settings['t1_callback_mail']) && $ff_settings['t1_callback_mail']) {
					$recipient_mail = $ff_settings['t1_callback_mail'];
				} else {
					$recipient_mail = $this->config->get('config_email');
				}

				$sender = $this->config->get('config_name');

				$sender_mail = 'robot@' . preg_replace('#^www\.(.+)#i', '$1', $_SERVER['HTTP_HOST']);

				$subject = sprintf($this->language->get('ff_text_mail_subject'), $name);

				$message = sprintf($this->language->get('ff_text_mail_message'), $name, $phone);

				if ($comment) {
					$message .= sprintf($this->language->get('ff_text_message_comment'), $comment);
				}

				$mail = new Mail($this->config->get('config_mail_engine'));
				$mail->parameter = $this->config->get('config_mail_parameter');
				$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
				$mail->smtp_username = $this->config->get('config_mail_smtp_username');
				$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
				$mail->smtp_port = $this->config->get('config_mail_smtp_port');
				$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
				$mail->setTo($recipient_mail);
				// $mail->setFrom($sender_mail);
				$mail->setFrom($recipient_mail);
				$mail->setReplyTo($recipient_mail);
				$mail->setSender(html_entity_decode($sender, ENT_QUOTES, 'UTF-8'));
				$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
				$mail->setHtml(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
				$mail->send();

				if (isset($ff_settings['t1_callback_form_success_text'][$language_id]) && $ff_settings['t1_callback_form_success_text'][$language_id]){
					$json['success'] = $ff_settings['t1_callback_form_success_text'][$language_id];
				} else {
					$json['success'] = $this->language->get('ff_text_success');
				}

			}

			if (isset($this->error['name'])) {
				$json['error']['name'] = $this->error['name'];
			}

			if (isset($this->error['phone'])) {
				$json['error']['phone'] = $this->error['phone'];
			}

			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
	}

	protected function validate() {
		if ((utf8_strlen($this->request->post['c_name']) < 2) || (utf8_strlen($this->request->post['c_name']) > 32)) {
			$this->error['name'] = $this->language->get('ff_error_name');
		}

		if (!$this->request->post['c_phone']) {
			$this->error['phone'] = $this->language->get('ff_error_phone');
		}

		return !$this->error;
	}

}
