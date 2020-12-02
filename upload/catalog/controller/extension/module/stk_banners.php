<?php
class ControllerExtensionModuleSTKBanners extends Controller {
	public function index($setting) {
		static $module = 0;

		$this->load->model('tool/image');

		$this->load->model('setting/setting');

	    if ($setting['type'] != 'single_banner') {
	      $this->document->addStyle('catalog/view/theme/' . $this->config->get('theme_framefree_directory') . '/javascript/owl-carousel/owl.carousel.min.css');
	  		$this->document->addScript('catalog/view/theme/' . $this->config->get('theme_framefree_directory') . '/javascript/owl-carousel/owl.carousel.min.js');

	      if ($setting['animation_in'] || $setting['animation_in']) {
	        $this->document->addStyle('catalog/view/theme/' . $this->config->get('theme_framefree_directory') . '/stylesheet/animate.css');
	      }
	    }

		$language_id = $this->config->get('config_language_id');
    	$hd_imgs = $setting['hd_images'];

		$data['heading_title'] = $setting['title'][$language_id];

    	$data['banners'] = array();

		$results = isset($setting['cust_blocks_item']) ? $setting['cust_blocks_item'] : array();

    	$data['lazyload_imgs'] = $setting['lazyload'];

		$data['img_sizes'] = array();

		foreach ($results as $result) {

			$width = $result['img_width'][$language_id] ? $result['img_width'][$language_id] : 200;
	    $height = $result['img_height'][$language_id] ? $result['img_height'][$language_id] : 200;

			$data['img_sizes'][] = array(
				'width' => $width,
				'height' => $height,
			);


			$image = $this->model_tool_image->resize($result['image'][$language_id], $width, $height);
			$image2x = $hd_imgs ? $this->model_tool_image->resize($result['image'][$language_id], $width*2, $height*2) : NULL;
			$image3x = $hd_imgs ? $this->model_tool_image->resize($result['image'][$language_id], $width*3, $height*3) : NULL;
			$image4x = $hd_imgs ? $this->model_tool_image->resize($result['image'][$language_id], $width*4, $height*4) : NULL;

			$target = isset($result['target'][$language_id]) ? '_blank' : '_self';
			$alt = isset($result['alt'][$language_id]) ? $result['alt'][$language_id] : '';
			$link = isset($result['link'][$language_id]) ? $result['link'][$language_id] : '';

			$aliases = array(
				'{\{image\}}',
				'{\{image2x\}}',
				'{\{image3x\}}',
				'{\{image4x\}}',
				'{\{width\}}',
				'{\{height\}}',
				'{\{alt\}}',
				'{\{link\}}',
				'{\{target\}}'
			);

			$values = array(
				$image,
				$image2x,
				$image3x,
				$image4x,
				$width,
				$height,
				$alt,
				$link,
				$target
			);

			$description = html_entity_decode(preg_replace($aliases, $values, $result['description'][$language_id]), ENT_QUOTES, 'UTF-8');

			$data['banners'][] = array(
				'type' 				=> $result['type'][$language_id],
				'src_holder'	=> $this->model_tool_image->resize('catalog/framefreetheme/src_holder.png', $width, $height),
				'img_width' 	=> $width . 'px',
				'img_height' 	=> $height . 'px',
				'image' 			=> $image,
				'image2x' 		=> $image2x,
				'image3x' 		=> $image3x,
				'image4x' 		=> $image4x,
				'alt'  				=> $alt,
				'description' => $description,
				'link'  			=> $link,
				'sort'  			=> $result['sort']
			);
		}

		if (!empty($data['banners'])){
			foreach ($data['banners'] as $key => $value) {
				$sort[$key] = $value['sort'];
			}
			array_multisort($sort, SORT_ASC, $data['banners']);
		}

    $data['type'] = $setting['type'];
    $data['controls'] = isset($setting['controls']) ? $setting['controls'] : array();
    $data['title'] = html_entity_decode($setting['title'][$language_id], ENT_QUOTES, 'UTF-8');
		$data['items'] = $setting['items'] && $setting['type'] == 'carousel' ? $setting['items'] : 1;

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

		$data['loop'] = $setting['loop'];
	    $data['autoplay'] = $setting['autoplay'];
	    $data['autoplay_speed'] = $setting['autoplay_speed'] ? $setting['autoplay_speed'] : 2500;
	    $data['animation_in'] = $setting['animation_in'];
	    $data['animation_out'] = $setting['animation_out'];

	    $data['module'] = $module++;

		return $this->load->view('extension/module/stk_banners', $data);
	}
}
