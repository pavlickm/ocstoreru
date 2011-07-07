<?php
class ModelCatalogProduct extends Model {
	public function addProduct($data) {
	    $this->db->query("INSERT INTO " . DB_PREFIX . "product (model, sku, location, quantity, minimum, subtract, stock_status_id, date_available, manufacturer_id, shipping, price, cost, weight, weight_class_id, length, width, height, length_class_id, status, tax_class_id, sort_order, date_added, main_category_id) VALUES ('" . $this->db->escape($data['model']) . "', '" . $this->db->escape($data['sku']) . "', '" . $this->db->escape($data['location']) . "', '" . (int)$data['quantity'] . "', '" . (int)$data['minimum'] . "', '" . (int)$data['subtract'] . "', '" . (int)$data['stock_status_id'] . "', '" . $this->db->escape($data['date_available']) . "', '" . (int)$data['manufacturer_id'] . "', '" . (int)$data['shipping'] . "', '" . (float)$data['price'] . "', '" . (float)$data['cost'] . "', '" . (float)$data['weight'] . "', '" . (int)$data['weight_class_id'] . "', '" . (float)$data['length'] . "', '" . (float)$data['width'] . "', '" . (float)$data['height'] . "', '" . (int)$data['length_class_id'] . "', '" . (int)$data['status'] . "', '" . (int)$data['tax_class_id'] . "', '" . (int)$data['sort_order'] . "', NOW(), " . ((int)$data['main_category_id'] > 0 ? (int)$data['main_category_id'] : 'NULL') . ")");

		$product_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape($data['image']) . "' WHERE product_id = '" . (int)$product_id . "'");
		}

		foreach ($data['product_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_description (product_id, language_id, name, meta_keywords, meta_description, description, title, h1) VALUES ('" . (int)$product_id . "', '" . (int)$language_id . "', '" . $this->db->escape($value['name']) . "', '" . $this->db->escape($value['meta_keywords']) . "', '" . $this->db->escape($value['meta_description']) . "', '" . $this->db->escape($value['description']) . "', '" . $this->db->escape($value['title']) . "', '" . $this->db->escape($value['h1']) . "')");
		}

		if (isset($data['product_store'])) {
			foreach ($data['product_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store (product_id, store_id) VALUES ('" . (int)$product_id . "', '" . (int)$store_id . "')");
			}
		}

		if (isset($data['product_option'])) {
			foreach ($data['product_option'] as $product_option) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_option (product_id, sort_order) VALUES ('" . (int)$product_id . "', '" . (int)$product_option['sort_order'] . "')");

				$product_option_id = $this->db->getLastId();

				foreach ($product_option['language'] as $language_id => $language) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_description (product_option_id, language_id, product_id, name) VALUES ('" . (int)$product_option_id . "', '" . (int)$language_id . "', '" . (int)$product_id . "', '" . $this->db->escape($language['name']) . "')");
				}

				if (isset($product_option['product_option_value'])) {
					foreach ($product_option['product_option_value'] as $product_option_value) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value (product_option_id, product_id, quantity, subtract, price, prefix, sort_order) VALUES ('" . (int)$product_option_id . "', '" . (int)$product_id . "', '" . (int)$product_option_value['quantity'] . "', '" . (int)$product_option_value['subtract'] . "', '" . (float)$product_option_value['price'] . "', '" . $this->db->escape($product_option_value['prefix']) . "', '" . (int)$product_option_value['sort_order'] . "')");

						$product_option_value_id = $this->db->getLastId();

						foreach ($product_option_value['language'] as $language_id => $language) {
						    $this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value_description (product_option_value_id, language_id, product_id, name) VALUES ('" . (int)$product_option_value_id . "', '" . (int)$language_id . "', '" . (int)$product_id . "', '" . $this->db->escape($language['name']) . "')");
						}
					}
				}
			}
		}

		if (isset($data['product_discount'])) {
			foreach ($data['product_discount'] as $value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_discount (product_id, customer_group_id, quantity, priority, price, date_start, date_end) VALUES ('" . (int)$product_id . "', '" . (int)$value['customer_group_id'] . "', '" . (int)$value['quantity'] . "', '" . (int)$value['priority'] . "', '" . (float)$value['price'] . "', '" . $this->db->escape($value['date_start']) . "', '" . $this->db->escape($value['date_end']) . "')");
			}
		}

		if (isset($data['product_special'])) {
			foreach ($data['product_special'] as $value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_special (product_id, customer_group_id, priority, price, date_start, date_end) VALUES ('" . (int)$product_id . "', '" . (int)$value['customer_group_id'] . "', '" . (int)$value['priority'] . "', '" . (float)$value['price'] . "', '" . $this->db->escape($value['date_start']) . "', '" . $this->db->escape($value['date_end']) . "')");
			}
		}

		if (isset($data['product_image'])) {
			foreach ($data['product_image'] as $image) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_image (product_id, image) VALUES ('" . (int)$product_id . "', '" . $this->db->escape($image) . "')");
			}
		}

		if (isset($data['product_download'])) {
			foreach ($data['product_download'] as $download_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_download (product_id, download_id) VALUES ('" . (int)$product_id . "', '" . (int)$download_id . "')");
			}
		}

		if (isset($data['product_category'])) {
			if (is_numeric($data['main_category_id']) && $data['main_category_id'] > 0) {
				array_push($data['product_category'], $data['main_category_id']);
				$data['product_category'] = array_unique($data['product_category']);
			}
			foreach ($data['product_category'] as $category_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category (product_id, category_id) VALUES ('" . (int)$product_id . "', '" . (int)$category_id . "')");
			}
		}

		if (isset($data['product_related'])) {
			foreach ($data['product_related'] as $related_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_related (product_id, related_id) VALUES ('" . (int)$product_id . "', '" . (int)$related_id . "')");
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_related (product_id, related_id) VALUES ('" . (int)$related_id . "', '" . (int)$product_id . "')");
			}
		}

		if ($data['keyword']) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias (query, keyword) VALUES ('product_id=" . (int)$product_id . "', '" . $this->db->escape($data['keyword']) . "')");
		}

		foreach ($data['product_tags'] as $language_id => $value) {
			$tags = explode(',', $value);
			foreach ($tags as $tag) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_tags (product_id, language_id, tag) VALUES ('" . (int)$product_id . "', '" . (int)$language_id . "', '" . $this->db->escape(trim($tag)) . "')");
			}
		}


		$this->cache->delete('product');

		// Возвращает индентификатор продукта. Нужно для работы модуля 1C
		return $product_id;
	}

	public function editProduct($product_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "product SET model = '" . $this->db->escape($data['model']) . "', sku = '" . $this->db->escape($data['sku']) . "', location = '" . $this->db->escape($data['location']) . "', quantity = '" . (int)$data['quantity'] . "', minimum = '" . (int)$data['minimum'] . "', subtract = '" . (int)$data['subtract'] . "', stock_status_id = '" . (int)$data['stock_status_id'] . "', date_available = '" . $this->db->escape($data['date_available']) . "', manufacturer_id = '" . (int)$data['manufacturer_id'] . "', shipping = '" . (int)$data['shipping'] . "', price = '" . (float)$data['price'] . "', cost = '" . (float)$data['cost'] . "', weight = '" . (float)$data['weight'] . "', weight_class_id = '" . (int)$data['weight_class_id'] . "', length = '" . (float)$data['length'] . "', width = '" . (float)$data['width'] . "', height = '" . (float)$data['height'] . "', length_class_id = '" . (int)$data['length_class_id'] . "', status = '" . (int)$data['status'] . "', tax_class_id = '" . (int)$data['tax_class_id'] . "', sort_order = '" . (int)$data['sort_order'] . "', date_modified = NOW(), main_category_id = " . ((int)$data['main_category_id'] > 0 ? (int)$data['main_category_id'] : 'NULL') . " WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape($data['image']) . "' WHERE product_id = '" . (int)$product_id . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");

		foreach ($data['product_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_description (product_id, language_id, name, meta_keywords, meta_description, description, title, h1) VALUES ('" . (int)$product_id . "', '" . (int)$language_id . "', '" . $this->db->escape($value['name']) . "', '" . $this->db->escape($value['meta_keywords']) . "', '" . $this->db->escape($value['meta_description']) . "', '" . $this->db->escape($value['description']) . "', '" . $this->db->escape($value['title']) . "', '" . $this->db->escape($value['h1']) . "')");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_store'])) {
			foreach ($data['product_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store (product_id, store_id) VALUES ('" . (int)$product_id . "', '" . (int)$store_id . "')");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "'");
                $this->db->query("DELETE FROM " . DB_PREFIX . "product_option_description WHERE product_id = '" . (int)$product_id . "'");
	        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "'");
    		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value_description WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_option'])) {
			foreach ($data['product_option'] as $product_option) {

				$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', sort_order = '" . (int)$product_option['sort_order'] . "'");

				$product_option_id = $this->db->getLastId();

				foreach ($product_option['language'] as $language_id => $language) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_description SET product_option_id = '" . (int)$product_option_id . "', language_id = '" . (int)$language_id . "', product_id = '" . (int)$product_id . "', name = '" . $this->db->escape($language['name']) . "'");
				}

				if (isset($product_option['product_option_value'])) {
					foreach ($product_option['product_option_value'] as $product_option_value) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$product_id . "', quantity = '" . (int)$product_option_value['quantity'] . "', subtract = '" . (int)$product_option_value['subtract'] . "', price = '" . (float)$product_option_value['price'] . "', prefix = '" . $this->db->escape($product_option_value['prefix']) . "', sort_order = '" . (int)$product_option_value['sort_order'] . "'");

						$product_option_value_id = $this->db->getLastId();

						foreach ($product_option_value['language'] as $language_id => $language) {
							$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value_description SET product_option_value_id = '" . (int)$product_option_value_id . "', language_id = '" . (int)$language_id . "', product_id = '" . (int)$product_id . "', name = '" . $this->db->escape($language['name']) . "'");
						}
					}
				}
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_discount'])) {
			foreach ($data['product_discount'] as $value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_discount (product_id, customer_group_id, quantity, priority, price, date_start, date_end) VALUES ('" . (int)$product_id . "', '" . (int)$value['customer_group_id'] . "', '" . (int)$value['quantity'] . "', '" . (int)$value['priority'] . "', '" . (float)$value['price'] . "', '" . $this->db->escape($value['date_start']) . "', '" . $this->db->escape($value['date_end']) . "')");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_special'])) {
			foreach ($data['product_special'] as $value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_special (product_id, customer_group_id, priority, price, date_start, date_end) VALUES ('" . (int)$product_id . "', '" . (int)$value['customer_group_id'] . "', '" . (int)$value['priority'] . "', '" . (float)$value['price'] . "', '" . $this->db->escape($value['date_start']) . "',  '" . $this->db->escape($value['date_end']) . "')");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_image'])) {
			foreach ($data['product_image'] as $image) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_image (product_id, image) VALUES ('" . (int)$product_id . "', '" . $this->db->escape($image) . "')");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_download WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_download'])) {
			foreach ($data['product_download'] as $download_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_download (product_id, download_id) VALUES ('" . (int)$product_id . "', '" . (int)$download_id . "')");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_category'])) {
			if (is_numeric($data['main_category_id']) && $data['main_category_id'] > 0) {
				array_push($data['product_category'], $data['main_category_id']);
				$data['product_category'] = array_unique($data['product_category']);
			}
			foreach ($data['product_category'] as $category_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category (product_id, category_id) VALUES ('" . (int)$product_id . "', '" . (int)$category_id . "')");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_related'])) {
			foreach ($data['product_related'] as $related_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_related (product_id, related_id) VALUES ('" . (int)$product_id . "', '" . (int)$related_id . "')");
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$related_id . "' AND related_id = '" . (int)$product_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_related (product_id, related_id) VALUES ('" . (int)$related_id . "', '" . (int)$product_id . "')");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'product_id=" . (int)$product_id. "'");

		if ($data['keyword']) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias (query, keyword) VALUES ('product_id=" . (int)$product_id . "', '" . $this->db->escape($data['keyword']) . "')");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_tags WHERE product_id = '" . (int)$product_id. "'");

		foreach ($data['product_tags'] as $language_id => $value) {
			$tags = explode(',', $value);
			foreach ($tags as $tag) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_tags (product_id, language_id, tag) VALUES ('" . (int)$product_id . "', '" . (int)$language_id . "', '" . $this->db->escape(trim($tag)) . "')");
			}
		}

		$this->cache->delete('product');
		$this->cache->delete('category.seo');
	}

	public function copyProduct($product_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		if ($query->num_rows) {
			$data = array();

			$data = $query->row;

			$data = array_merge($data, array('product_description' => $this->getProductDescriptions($product_id)));
			$data = array_merge($data, array('product_option' => $this->getProductOptions($product_id)));

			$data['keyword'] = '';

			$data['status'] = '0';

            foreach(array_keys($data['product_description']) as $key) {
                $data['product_description'][$key]['name'] = $data['product_description'][$key]['name'] . '*';
            }

			$data['product_image'] = array();

			$results = $this->getProductImages($product_id);

			foreach ($results as $result) {
				$data['product_image'][] = $result['image'];
			}

			$data = array_merge($data, array('product_discount' => $this->getProductDiscounts($product_id)));
			$data = array_merge($data, array('product_special' => $this->getProductSpecials($product_id)));
			$data = array_merge($data, array('product_download' => $this->getProductDownloads($product_id)));
			$data = array_merge($data, array('product_category' => $this->getProductCategories($product_id)));
			$data = array_merge($data, array('product_store' => $this->getProductStores($product_id)));
			$data = array_merge($data, array('product_related' => $this->getProductRelated($product_id)));
			$data = array_merge($data, array('product_tags' => $this->getProductTags($product_id)));

			$this->addProduct($data);
		}
	}

	public function deleteProduct($product_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_description WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value_description WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_download WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "review WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'product_id=" . (int)$product_id. "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_tags WHERE product_id='" . (int)$product_id. "'");

		$this->cache->delete('product');
	}

	public function changeStatusProducts($products, $status) {
		function check_int($a) { return (int)$a; }
		$arr_products = array_map('check_int', $products);
		$products = implode("' OR product_id = '", $arr_products);
		$this->db->query("UPDATE " . DB_PREFIX . "product SET status = '" . (int)(bool)$status . "' WHERE product_id = '" . $products . "'");

		$this->cache->delete('product');
	}

	public function getProduct($product_id) {
		$query = $this->db->query("SELECT DISTINCT *, (SELECT keyword FROM " . DB_PREFIX . "url_alias WHERE query = 'product_id=" . (int)$product_id . "') AS keyword FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getProducts($data = array()) {
		if ($data) {
			$sql = "SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

			if (isset($data['filter_name']) && !is_null($data['filter_name'])) {
				$sql .= " AND LCASE(pd.name) LIKE '%" . $this->db->escape(strtolower($data['filter_name'])) . "%'";
			}

			if (isset($data['filter_model']) && !is_null($data['filter_model'])) {
				$sql .= " AND LCASE(p.model) LIKE '%" . $this->db->escape(strtolower($data['filter_model'])) . "%'";
			}

			if (isset($data['filter_price']) && !is_null($data['filter_price'])) {
				$sql .= " AND LCASE(p.price) LIKE '" . $this->db->escape(strtolower($data['filter_price'])) . "%'";
			}

			if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
				$sql .= " AND p.quantity = '" . $this->db->escape($data['filter_quantity']) . "'";
			}

			if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
				$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
			}

			$sort_data = array(
				'pd.name',
				'p.model',
				'p.price',
				'p.quantity',
				'p.status',
				'p.sort_order'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY pd.name";
			}

			if (isset($data['order']) && ($data['order'] == 'DESC')) {
				$sql .= " DESC";
			} else {
				$sql .= " ASC";
			}

			if (isset($data['start']) || isset($data['limit'])) {
				if ($data['start'] < 0) {
					$data['start'] = 0;
				}

				if ($data['limit'] < 1) {
					$data['limit'] = 20;
				}

				$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
			}

			$query = $this->db->query($sql);

			return $query->rows;
		} else {
			$product_data = $this->cache->get('product.' . $this->config->get('config_language_id'));

			if (!$product_data) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY pd.name ASC");

				$product_data = $query->rows;

				$this->cache->set('product.' . $this->config->get('config_language_id'), $product_data);
			}

			return $product_data;
		}
	}

	public function addFeatured($data) {
      	$this->db->query("DELETE FROM " . DB_PREFIX . "product_featured");

		if (isset($data['product_featured'])) {
      		foreach ($data['product_featured'] as $product_id) {
        		$this->db->query("INSERT INTO " . DB_PREFIX . "product_featured (product_id) VALUES ('" . (int)$product_id . "')");
      		}
		}
	}

	public function getFeaturedProducts() {
		$product_featured_data = array();

		$query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "product_featured");

		foreach ($query->rows as $result) {
			$product_featured_data[] = $result['product_id'];
		}
		return $product_featured_data;
	}

	public function getProductsByKeyword($keyword) {
		if ($keyword) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND (LCASE(pd.name) LIKE '%" . $this->db->escape(strtolower($keyword)) . "%' OR LCASE(p.model) LIKE '%" . $this->db->escape(strtolower($keyword)) . "%')");

			return $query->rows;
		} else {
			return array();
		}
	}

	public function getProductsByCategoryId($category_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p2c.category_id = '" . (int)$category_id . "' ORDER BY pd.name ASC");

		return $query->rows;
	}

	public function getProductDescriptions($product_id) {
		$product_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_description_data[$result['language_id']] = array(
				'name'             => $result['name'],
				'meta_keywords'    => $result['meta_keywords'],
				'meta_description' => $result['meta_description'],
				'description'      => $result['description'],
				'title'			   => $result['title'],
				'h1'			   => $result['h1']
			);
		}

		return $product_description_data;
	}

	public function getProductOptions($product_id) {
		$product_option_data = array();

		$product_option = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "' ORDER BY sort_order");

		foreach ($product_option->rows as $product_option) {
			$product_option_value_data = array();

			$product_option_value = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value WHERE product_option_id = '" . (int)$product_option['product_option_id'] . "' ORDER BY sort_order");

			foreach ($product_option_value->rows as $product_option_value) {
				$product_option_value_description_data = array();

				$product_option_value_description = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value_description WHERE product_option_value_id = '" . (int)$product_option_value['product_option_value_id'] . "'");

				foreach ($product_option_value_description->rows as $result) {
					$product_option_value_description_data[$result['language_id']] = array('name' => $result['name']);
				}

				$product_option_value_data[] = array(
					'product_option_value_id' => $product_option_value['product_option_value_id'],
					'language'                => $product_option_value_description_data,
         			'quantity'                => $product_option_value['quantity'],
					'subtract'                => $product_option_value['subtract'],
					'price'                   => $product_option_value['price'],
         			'prefix'                  => $product_option_value['prefix'],
					'sort_order'              => $product_option_value['sort_order']
				);
			}

			$product_option_description_data = array();

			$product_option_description = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_description WHERE product_option_id = '" . (int)$product_option['product_option_id'] . "'");

			foreach ($product_option_description->rows as $result) {
				$product_option_description_data[$result['language_id']] = array('name' => $result['name']);
			}

        	$product_option_data[] = array(
        		'product_option_id'    => $product_option['product_option_id'],
				'language'             => $product_option_description_data,
				'product_option_value' => $product_option_value_data,
				'sort_order'           => $product_option['sort_order']
        	);
      	}

		return $product_option_data;
	}

	public function getProductImages($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "'");

		return $query->rows;
	}

	public function getProductDiscounts($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "' ORDER BY quantity, priority, price");

		return $query->rows;
	}

	public function getProductSpecials($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "' ORDER BY priority, price");

		return $query->rows;
	}

	public function getProductDownloads($product_id) {
		$product_download_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_download WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_download_data[] = $result['download_id'];
		}

		return $product_download_data;
	}

	public function getProductStores($product_id) {
		$product_store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_store_data[] = $result['store_id'];
		}

		return $product_store_data;
	}

	public function getProductCategories($product_id) {
		$product_category_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_category_data[] = $result['category_id'];
		}

		return $product_category_data;
	}

	public function getProductRelated($product_id) {
		$product_related_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_related_data[] = $result['related_id'];
		}

		return $product_related_data;
	}

	public function getProductTags($product_id) {
		$product_tag_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_tags WHERE product_id = '" . (int)$product_id . "'");

		$tag_data = array();

		foreach ($query->rows as $result) {
			$tag_data[$result['language_id']][] = $result['tag'];
		}

		foreach ($tag_data as $language => $tags) {
			$product_tag_data[$language] = implode(',', $tags);
		}

		return $product_tag_data;
	}

	public function getTotalProducts($data = array()) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (isset($data['filter_name']) && !is_null($data['filter_name'])) {
			$sql .= " AND LCASE(pd.name) LIKE '%" . $this->db->escape(strtolower($data['filter_name'])) . "%'";
		}

		if (isset($data['filter_model']) && !is_null($data['filter_model'])) {
			$sql .= " AND LCASE(p.model) LIKE '%" . $this->db->escape(strtolower($data['filter_model'])) . "%'";
		}

		if (isset($data['filter_price']) && !is_null($data['filter_price'])) {
			$sql .= " AND LCASE(p.price) LIKE '" . $this->db->escape(strtolower($data['filter_price'])) . "%'";
		}

		if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
			$sql .= " AND p.quantity = '" . $this->db->escape($data['filter_quantity']) . "'";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getTotalProductsByStockStatusId($stock_status_id) {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE stock_status_id = '" . (int)$stock_status_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByImageId($image_id) {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE image_id = '" . (int)$image_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByTaxClassId($tax_class_id) {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE tax_class_id = '" . (int)$tax_class_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByWeightClassId($weight_class_id) {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE weight_class_id = '" . (int)$weight_class_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByLengthClassId($length_class_id) {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE length_class_id = '" . (int)$length_class_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByOptionId($option_id) {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_to_option WHERE option_id = '" . (int)$option_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByDownloadId($download_id) {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_to_download WHERE download_id = '" . (int)$download_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByManufacturerId($manufacturer_id) {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

		return $query->row['total'];
	}
}
?>