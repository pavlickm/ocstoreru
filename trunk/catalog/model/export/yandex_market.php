<?php
class ModelExportYandexMarket extends Model {
	public function getCategory() {
		$query = $this->db->query("SELECT cd.name, c.category_id, c.parent_id FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  AND c.status = '1' AND c.sort_order <> '-1'");

		return $query->rows;
	}

	public function getProduct($allowed_categories, $out_of_stock_id, $vendor_required = true) {
		$query = $this->db->query("SELECT p.product_id, p.model, p.sku, p.image, p.quantity, p.stock_status_id, p.tax_class_id, pd.name, pd.description, m.name AS manufacturer, (SELECT category_id FROM " . DB_PREFIX . "product_to_category WHERE product_id = p.product_id ORDER BY main_category DESC LIMIT 1) AS category_id, IFNULL((SELECT price FROM " . DB_PREFIX . "product_special WHERE product_id = p.product_id AND customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND date_start < NOW() AND (date_end = '0000-00-00' OR date_end > NOW()) ORDER BY priority ASC, price ASC LIMIT 1), p.price) AS price FROM " . DB_PREFIX . "product p " . ($vendor_required ? '' : 'LEFT ') . "JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.product_id IN (SELECT product_id FROM " . DB_PREFIX . "product_to_category WHERE category_id IN (" . $this->db->escape($allowed_categories) . ")) AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.date_available <= NOW() AND p.status = '1' AND (p.quantity > '0' OR p.stock_status_id != '" . (int)$out_of_stock_id . "')");

		return $query->rows;
	}
}
?>
