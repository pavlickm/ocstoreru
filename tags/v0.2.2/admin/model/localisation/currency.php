<?php
class ModelLocalisationCurrency extends Model {
	public function addCurrency($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "currency (title, code, symbol_left, symbol_right, decimal_place, value, status, date_modified) VALUES ('" . $this->db->escape($data['title']) . "', '" . $this->db->escape($data['code']) . "', '" . $this->db->escape($data['symbol_left']) . "', '" . $this->db->escape($data['symbol_right']) . "', '" . $this->db->escape($data['decimal_place']) . "', '" . $this->db->escape($data['value']) . "', '" . (int)$data['status'] . "', NOW())");

		$this->cache->delete('currency');
	}

	public function editCurrency($currency_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "currency SET title = '" . $this->db->escape($data['title']) . "', code = '" . $this->db->escape($data['code']) . "', symbol_left = '" . $this->db->escape($data['symbol_left']) . "', symbol_right = '" . $this->db->escape($data['symbol_right']) . "', decimal_place = '" . $this->db->escape($data['decimal_place']) . "', value = '" . $this->db->escape($data['value']) . "', status = '" . (int)$data['status'] . "', date_modified = NOW() WHERE currency_id = '" . (int)$currency_id . "'");

		$this->cache->delete('currency');
	}

	public function deleteCurrency($currency_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "currency WHERE currency_id = '" . (int)$currency_id . "'");

		$this->cache->delete('currency');
	}

	public function changeStatusCurrencies($currencies, $status) {
		function check_int($a) { return (int)$a; }
		$arr_currencies = array_map('check_int', $currencies);
		$currencies = implode("' OR currency_id = '", $arr_currencies);
		$this->db->query("UPDATE " . DB_PREFIX . "currency SET status = '" . (int)(bool)$status . "' WHERE currency_id = '" . $currencies . "'");

		$this->cache->delete('currency');
	}

	public function getCurrency($currency_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "currency WHERE currency_id = '" . (int)$currency_id . "'");

		return $query->row;
	}

	public function getCurrencies($data = array()) {
		if ($data) {
			$sql = "SELECT * FROM " . DB_PREFIX . "currency";

			$sort_data = array(
				'title',
				'code',
				'value',
				'date_modified'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY title";
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
			$currency_data = $this->cache->get('currency');

			if (!$currency_data) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "currency ORDER BY title ASC");

				foreach ($query->rows as $result) {
      				$currency_data[$result['code']] = array(
        				'currency_id'   => $result['currency_id'],
        				'title'         => $result['title'],
        				'code'          => $result['code'],
						'symbol_left'   => $result['symbol_left'],
						'symbol_right'  => $result['symbol_right'],
						'decimal_place' => $result['decimal_place'],
						'value'         => $result['value'],
						'status'        => $result['status'],
						'date_modified' => $result['date_modified']
      				);
    			}

				$this->cache->set('currency', $currency_data);
			}

			return $currency_data;
		}
	}

	public function updateCurrencies() {
		if (extension_loaded('curl')) {
			$data = array();

			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "currency WHERE code != '" . $this->db->escape($this->config->get('config_currency')) . "' AND date_modified < '" . date('Y-m-d H:i:s', strtotime('-1 day')) . "'");

			foreach ($query->rows as $result) {
				$data[] = $this->config->get('config_currency') . $result['code'] . '=X';
			}

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'http://download.finance.yahoo.com/d/quotes.csv?s=' . implode(',', $data) . '&f=sl1&e=.csv');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

			$content = curl_exec($ch);

			curl_close($ch);

			$lines = explode("\n", trim($content));

			foreach ($lines as $line) {
				$currency = substr($line, 4, 3);
				$value = substr($line, 11, 6);

				if ((float)$value) {
					$this->db->query("UPDATE " . DB_PREFIX . "currency SET value = '" . (float)$value . "', date_modified = NOW() WHERE code = '" . $this->db->escape($currency) . "'");
				}
			}

			$this->db->query("UPDATE " . DB_PREFIX . "currency SET value = '1.00000', date_modified = NOW() WHERE code = '" . $this->db->escape($this->config->get('config_currency')) . "'");

			$this->cache->delete('currency');
		}
	}

	public function getTotalCurrencies() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "currency");

		return $query->row['total'];
	}
}
?>