<?php
class ControllerExtensionModuleFramefreethemeFFReviewsNoajax extends Controller {
	public function index($product_id) {

    if ($this->config->get('config_review_status')) {

      $this->load->language('product/product');

      $this->load->model('catalog/product');

  		$product_info = $this->model_catalog_product->getProduct($product_id);

      $this->load->model('catalog/review');

      if (isset($this->request->get['page'])) {
        $page = $this->request->get['page'];
      } else {
        $page = 1;
      }

      $data['reviews'] = array();

      $review_total = $this->model_catalog_review->getTotalReviewsByProductId($product_id);

      $results = $this->model_catalog_review->getReviewsByProductId($product_id, ($page - 1) * 5, 5);

      foreach ($results as $result) {
        $data['reviews'][] = array(
          'author'     => $result['author'],
          'text'       => nl2br($result['text']),
          'rating'     => (int)$result['rating'],
          'product_name' => $product_info['name'],
          'meta_date'  => date(strtotime($result['date_added'])),
          'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
        );
      }

      $pagination = new Pagination_ft();
      $pagination->total = $review_total;
      $pagination->page = $page;
      $pagination->limit = 5;
      $pagination->url = $this->url->link('product/product/review', 'product_id=' . $product_id . '&page={page}');

      $data['pagination'] = $pagination->render();

      $data['results'] = sprintf($this->language->get('text_pagination'), ($review_total) ? (($page - 1) * 5) + 1 : 0, ((($page - 1) * 5) > ($review_total - 5)) ? $review_total : ((($page - 1) * 5) + 5), $review_total, ceil($review_total / 5));

      if ($page == 1) {
        $this->document->addLink($this->url->link('product/product', 'product_id=' . $product_id), 'canonical');
      } else {
        $this->document->addLink($this->url->link('product/product', 'product_id=' . $product_id . '&page='. $page), 'canonical');
      }

      if ($page > 1) {
        $this->document->addLink($this->url->link('product/product', 'product_id=' . $product_id . (($page - 2) ? '&page='. ($page - 1) : '')), 'prev');
      }

      if (5 && ceil($review_total / 5) > $page) {
        $this->document->addLink($this->url->link('product/product', 'product_id=' . $product_id . '&page='. ($page + 1)), 'next');
      }

      return $this->load->view('product/review', $data);

    }
	}
}
