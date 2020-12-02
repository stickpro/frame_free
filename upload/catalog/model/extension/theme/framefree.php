<?php
class ModelExtensionThemeFramefree extends Model {

	public function getSpecialEndDate($product_id) {
		if ($this->customer->isLogged()){
			$customer_group_id = $this->customer->getGroupId();
		} else {
			$customer_group_id = 0;
		}

		$sql = "SELECT date_end FROM " . DB_PREFIX . "product_special WHERE product_id ='" . (int)$product_id . "' AND date_start <= NOW() AND date_end >= NOW() ORDER BY priority ";
		if ($customer_group_id){
			$sql .= " AND customer_group_id ='" . $customer_group_id . "'";
		}

		$sql .= "LIMIT 0,1";

		$query = $this->db->query($sql);

		if ($query->num_rows){
			return $query->row['date_end'];
		} else {
			return false;
		}
	}

	public function getLastReviews($limit = 20, $rating = 0) {

		if ($limit < 1) {
			$limit = 20;
		}

		if ($rating < 0) {
			$rating = 0;
		}

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "review WHERE status = 1 AND rating > " . (int)$rating .  " ORDER BY date_added DESC LIMIT " . (int)$limit);

		return $query->rows;
	}

}
