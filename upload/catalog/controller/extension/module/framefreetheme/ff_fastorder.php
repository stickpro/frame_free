<?php
class ControllerExtensionModuleFramefreethemeFFFastorder extends Controller {

	private $error = array();

	public function index() {
		$this->load->language('extension/module/framefreetheme/ff_fastorder');

		$this->load->model('setting/setting');

		$ff_settings = array();
		$ff_settings = $this->model_setting_setting->getSetting('theme_framefree', $this->config->get('config_store_id'));
		$language_id = $this->config->get('config_language_id');

		if (isset($ff_settings['t1_high_definition_imgs']) && $ff_settings['t1_high_definition_imgs']){
			$hd_imgs = $ff_settings['t1_high_definition_imgs'];
		} else {
			$hd_imgs = false;
		}

		if (isset($ff_settings['t1_fastorder_phone_mask']) && $ff_settings['t1_fastorder_phone_mask']) {
			$data['phone_mask'] = $ff_settings['t1_fastorder_phone_mask'];
		} else {
			$data['phone_mask'] = '';
		}

		if (isset($ff_settings['t1_fastorder_quantity_status']) && $ff_settings['t1_fastorder_quantity_status']) {
			$data['fastorder_quantity_status'] = $ff_settings['t1_fastorder_quantity_status'];
		} else {
			$data['fastorder_quantity_status'] = false;
		}

		$data['theme_dir'] = $this->config->get('theme_framefree_directory');

		if (isset($this->request->get['product_id'])) {
			$product_id = (int)$this->request->get['product_id'];
		} else {
			$product_id = 0;
		}

		if (isset($this->request->post['customer_name'])) {
			$data['customer_name'] = $this->request->post['customer_name'];
		} else {
			$data['customer_name'] = $this->customer->getFirstName();
		}

		if (isset($this->request->post['customer_email'])) {
			$data['customer_email'] = $this->request->post['customer_email'];
		} else {
			$data['customer_email'] = $this->customer->getEmail();
		}

		if (isset($this->request->post['customer_telephone'])) {
			$data['customer_telephone'] = $this->request->post['customer_telephone'];
		} else {
			$data['customer_telephone'] = $this->customer->getTelephone();
		}

		$data['show_phone'] = true;

		$data['show_mail'] = false;


		$this->load->model('catalog/product');

		$product_info = $this->model_catalog_product->getProduct($product_id);

		$data['product_isset'] = false;

		if ($product_info) {

			$data['product_isset'] = true;

			$data['heading_title'] = $product_info['name'];

			$data['product_href'] = $this->url->link('product/product&product_id=' . $product_info['product_id'], '', true);

			$data['product_id'] = (int)$this->request->get['product_id'];

			$data['model'] = $product_info['model'];


			$this->load->model('tool/image');

			$data['thumb_holder'] = $this->model_tool_image->resize('src_holder.png', 220, 220);

			if ($product_info['image']) {
				$data['thumb']   = $this->model_tool_image->resize($product_info['image'], 220, 220);
				$data['thumb2x'] = $hd_imgs ? $this->model_tool_image->resize($product_info['image'], 220*2, 220*2) : NULL;
				$data['thumb3x'] = $hd_imgs ? $this->model_tool_image->resize($product_info['image'], 220*3, 220*3) : NULL;
				$data['thumb4x'] = $hd_imgs ? $this->model_tool_image->resize($product_info['image'], 220*4, 220*4) : NULL;
			} else {
				$data['thumb'] = '';
			}

			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$data['price'] = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$data['price'] = false;
			}

			if ((float)$product_info['special']) {
				$data['special'] = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$data['special'] = false;
			}

			if ($this->config->get('config_tax')) {
				$data['tax'] = $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price'], $this->session->data['currency']);
			} else {
				$data['tax'] = false;
			}

			if ($product_info['minimum']) {
				$data['minimum'] = $product_info['minimum'];
			} else {
				$data['minimum'] = 1;
			}

		}

		$this->response->setOutput($this->load->view('extension/module/framefreetheme/ff_fastorder', $data));
	}


	public function fastorder() {

		$this->load->language('extension/module/framefreetheme/ff_fastorder');

		$this->load->model('setting/setting');

		$ff_settings = array();
		$ff_settings = $this->model_setting_setting->getSetting('theme_framefree', $this->config->get('config_store_id'));
		$language_id = $this->config->get('config_language_id');


		if (isset($ff_settings['t1_fastorder_mail']) && $ff_settings['t1_fastorder_mail']) {
			$recipient_mail = $ff_settings['t1_fastorder_mail'];
		} else {
			$recipient_mail = $this->config->get('config_email');
		}

		$show_phone = true;
		$show_email = false;

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate($show_phone, $show_email)) {

			$name = $this->request->post['f_name'];
			$phone = $this->request->post['f_phone'];

			if (isset($this->request->post['f_email'])) {
				$email = $this->request->post['f_email'];
			} else {
				$email = '';
			}



			$product_id = $this->request->post['product_id'];
			$comment = $this->request->post['f_comment'];
			$quantity = $this->request->post['f_quantity'];

			$this->load->model('catalog/product');

			$product_info = $this->model_catalog_product->getProduct($product_id);

			if ($product_info) {
				$product_name = $product_info['name'];
				$product_href = $this->url->link('product/product&product_id=' . $product_info['product_id'], '', true);
			} else {
				$product_name = '';
				$product_href = '';
			}

			$sender = $name;

			$sender_mail = 'robot@' . preg_replace('#^www\.(.+)#i', '$1', $_SERVER['HTTP_HOST']);


			$subject = sprintf($this->language->get('text_mail_subject'), $this->config->get('config_name'));

			$message = sprintf($this->language->get('text_message_name'), $name);

			if ($email) {
				$message .= sprintf($this->language->get('text_message_email'), $email);
			}

			if ($phone) {
				$message .= sprintf($this->language->get('text_message_phone'), $phone);
			}

			if ($comment) {
				$message .= sprintf($this->language->get('text_message_comment'), $comment);
			}

			$message .= sprintf($this->language->get('text_maessage_product'), $product_href, $product_name, $quantity);


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

			$json['success'] = $this->language->get('text_success');

		}

		if (isset($this->error['name'])) {
			$json['error']['name'] = $this->error['name'];
		}

		if (isset($this->error['phone'])) {
			$json['error']['phone'] = $this->error['phone'];
		}

		if (isset($this->error['email'])) {
			$json['error']['email'] = $this->error['email'];
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}


	protected function validate($show_phone, $show_email) {
		if ((utf8_strlen($this->request->post['f_name']) < 2) || (utf8_strlen($this->request->post['f_name']) > 32)) {
			$this->error['name'] = $this->language->get('error_name');
		}


		// if (!filter_var($this->request->post['f_email'], FILTER_VALIDATE_EMAIL) && $show_email) {
			// $this->error['email'] = $this->language->get('error_email');
		// }

		if (!$this->request->post['f_phone'] && $show_phone) {
			$this->error['phone'] = $this->language->get('error_phone');
		}

		return !$this->error;
	}

}
