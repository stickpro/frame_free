<?php
class ControllerExtensionModuleSTKNewsCarousel extends Controller {
	public function index($setting) {
		static $module = 0;

		$this->load->model('localisation/language');

		$languages = $this->model_localisation_language->getLanguages();
		$language_id = $this->config->get('config_language_id');

		$this->load->language('extension/module/stk_news_carousel');
		$this->load->language('extension/module/framefreetheme/ff_global');

		$this->load->model('blog/article');

		$this->load->model('tool/image');

		$this->load->model('setting/setting');

		$ff_settings = array();
		$ff_settings = $this->model_setting_setting->getSetting('theme_framefree', $this->config->get('config_store_id'));

		if (isset($ff_settings['t1_high_definition_imgs']) && $ff_settings['t1_high_definition_imgs']){
			$hd_imgs = $ff_settings['t1_high_definition_imgs'];
		} else {
			$hd_imgs = false;
		}

    if (isset($ff_settings['t1_catalog_page_lazy']) && $ff_settings['t1_catalog_page_lazy']){
			$data['lazyload_imgs'] = $ff_settings['t1_catalog_page_lazy'];
		} else {
			$data['lazyload_imgs'] = false;
		}

    $data['review_status'] = $this->config->get('configblog_review_status');

		$this->document->addStyle('catalog/view/theme/' . $this->config->get('theme_framefree_directory') . '/javascript/owl-carousel/owl.carousel.min.css');
		$this->document->addScript('catalog/view/theme/' . $this->config->get('theme_framefree_directory') . '/javascript/owl-carousel/owl.carousel.min.js');


		if (!$setting['limit']) {
			$setting['limit'] = 4;
		}

		if ($setting['title']) {
			$data['heading_title'] = $setting['title'][$language_id];
		}

		$data['controls'] = isset($setting['controls']) ? $setting['controls'] : array();

		$data['module_type'] = $setting['module_type'];
		$data['autoplay'] = $setting['autoplay'];
		$data['autoplay_speed'] = $setting['autoplay_speed'];

		$data['items'] = $setting['items'] ? $setting['items'] : 1;
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

		$data['articles'] = array();

    $results = array();

		if ($setting['module_type'] == 'latest') {

			$filter_data = array(
				'sort'  => 'p.date_added',
				'order' => 'DESC',
				'start' => 0,
				'limit' => $setting['limit']
			);

			$results = $this->model_blog_article->getArticles($filter_data);

  		if ($results) {
  			foreach ($results as $result) {
  				if ($result['image']) {
  					$image = $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height']);
            if ($hd_imgs) {
              $image2x = $this->model_tool_image->resize($result['image'], $setting['width']*2, $setting['height']*2);
              $image3x = $this->model_tool_image->resize($result['image'], $setting['width']*3, $setting['height']*3);
              $image4x = $this->model_tool_image->resize($result['image'], $setting['width']*4, $setting['height']*4);
            }
  				} else {
  					$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
            if ($hd_imgs) {
              $image2x = $this->model_tool_image->resize('placeholder.png', $setting['width']*2, $setting['height']*2);
              $image3x = $this->model_tool_image->resize('placeholder.png', $setting['width']*3, $setting['height']*3);
              $image4x = $this->model_tool_image->resize('placeholder.png', $setting['width']*4, $setting['height']*4);
            }
  				}

  				if ($this->config->get('configblog_review_status')) {
  					$rating = $result['rating'];
  				} else {
  					$rating = false;
  				}

  				$data['articles'][] = array(
  					'article_id'  => $result['article_id'],
  					'thumb'       => $image,
						'img_width'   => $setting['width'] . 'px',
						'img_height'   => $setting['height'] . 'px',
						'thumb_holder'  => $this->model_tool_image->resize('catalog/framefreetheme/src_holder.png', $setting['width'], $setting['height']),
            'thumb2x'       => $hd_imgs ? $image2x : NULL,
            'thumb3x'       => $hd_imgs ? $image3x : NULL,
            'thumb4x'       => $hd_imgs ? $image4x : NULL,
  					'name'        => $result['name'],
  					'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('configblog_article_description_length')) . '..',
  					'rating'      => $rating,
  					'date_added'  => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
  					'viewed'      => $result['viewed'],
  					'href'        => $this->url->link('blog/article', 'article_id=' . $result['article_id'])
  				);
  			}
      }
    }


    if ($setting['module_type'] == 'featured') {

      if (!empty($setting['article'])) {
  			$articles = array_slice($setting['article'], 0, (int)$setting['limit']);

  			foreach ($articles as $article_id) {
  				$article_info = $this->model_blog_article->getArticle($article_id);

  				if ($article_info) {
  					if ($article_info['image']) {
  						$image = $this->model_tool_image->resize($article_info['image'], $setting['width'], $setting['height']);
              if ($hd_imgs) {
                $image2x = $this->model_tool_image->resize($article_info['image'], $setting['width']*2, $setting['height']*2);
                $image3x = $this->model_tool_image->resize($article_info['image'], $setting['width']*3, $setting['height']*3);
                $image4x = $this->model_tool_image->resize($article_info['image'], $setting['width']*4, $setting['height']*4);
              }
  					} else {
  						$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
              if ($hd_imgs) {
                $image2x = $this->model_tool_image->resize('placeholder.png', $setting['width']*2, $setting['height']*2);
                $image3x = $this->model_tool_image->resize('placeholder.png', $setting['width']*3, $setting['height']*3);
                $image4x = $this->model_tool_image->resize('placeholder.png', $setting['width']*4, $setting['height']*4);
              }
  					}

  					if ($this->config->get('configblog_review_status')) {
  						$rating = $article_info['rating'];
  					} else {
  						$rating = false;
  					}

  					$data['articles'][] = array(
  						'article_id'  => $article_info['article_id'],
  						'thumb'       => $image,
							'img_width'   => $setting['width'] . 'px',
							'img_height'   => $setting['height'] . 'px',
              'thumb2x'       => $hd_imgs ? $image2x : NULL,
              'thumb3x'       => $hd_imgs ? $image3x : NULL,
              'thumb4x'       => $hd_imgs ? $image4x : NULL,
  						'name'        => $article_info['name'],
  						'description' => utf8_substr(strip_tags(html_entity_decode($article_info['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('configblog_article_description_length')) . '..',
  						'rating'      => $rating,
  						'date_added'  => date($this->language->get('date_format_short'), strtotime($article_info['date_added'])),
  						'viewed'      => $article_info['viewed'],
  						'href'        => $this->url->link('blog/article', 'article_id=' . $article_info['article_id'])
  					);
  				}
  			}
  		}
    }




		$data['module'] = $module++;

		if ($data['articles']) {
			return $this->load->view('extension/module/stk_news_carousel', $data);
		}
	}
}
