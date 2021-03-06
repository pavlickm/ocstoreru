<?php
class ModelAccountCustomer extends Model {
	public function addCustomer($data) {

		$data['firstname'] = mb_convert_case( trim( $data['firstname'] ), MB_CASE_TITLE, "UTF-8");
		$data['lastname'] = mb_convert_case( trim( $data['lastname'] ), MB_CASE_TITLE, "UTF-8");

		$data['company'] = mb_convert_case( trim( $data['company'] ), MB_CASE_TITLE, "UTF-8");
		$data['address_1'] = mb_convert_case( trim( $data['address_1'] ), MB_CASE_TITLE, "UTF-8");
		$data['address_2'] = mb_convert_case( trim( $data['address_2'] ), MB_CASE_TITLE, "UTF-8");
		$data['city'] = mb_convert_case( trim( $data['city'] ), MB_CASE_TITLE, "UTF-8");
		$data['postcode'] = strtoupper(trim($data['postcode']));

		$this->db->query("INSERT INTO " . DB_PREFIX . "customer (store_id, firstname, lastname, email, telephone, fax, password, newsletter, customer_group_id, status, date_added) VALUES ('" . (int)$this->config->get('config_store_id') . "', '" . $this->db->escape($data['firstname']) . "', '" . $this->db->escape($data['lastname']) . "', '" . $this->db->escape($data['email']) . "', '" . $this->db->escape($data['telephone']) . "', '" . $this->db->escape($data['fax']) . "', '" . $this->db->escape(md5($data['password'])) . "', '" . (int)$data['newsletter'] . "', '" . (int)$this->config->get('config_customer_group_id') . "', '1', NOW())");

		$customer_id = $this->db->getLastId();

		$this->db->query("INSERT INTO " . DB_PREFIX . "address (customer_id, firstname, lastname, company, address_1, address_2, city, postcode, country_id, zone_id) VALUES ('" . (int)$customer_id . "', '" . $this->db->escape($data['firstname']) . "', '" . $this->db->escape($data['lastname']) . "', '" . $this->db->escape($data['company']) . "', '" . $this->db->escape($data['address_1']) . "', '" . $this->db->escape($data['address_2']) . "', '" . $this->db->escape($data['city']) . "', '" . $this->db->escape($data['postcode']) . "', '" . (int)$data['country_id'] . "', '" . (int)$data['zone_id'] . "')");

		$address_id = $this->db->getLastId();

      	$this->db->query("UPDATE " . DB_PREFIX . "customer SET address_id = '" . (int)$address_id . "' WHERE customer_id = '" . (int)$customer_id . "'");

		if (!$this->config->get('config_customer_approval')) {
			$this->db->query("UPDATE " . DB_PREFIX . "customer SET approved = '1' WHERE customer_id = '" . (int)$customer_id . "'");
		}
	}

	public function editCustomer($data) {

		$data['firstname'] = mb_convert_case( trim( $data['firstname'] ), MB_CASE_TITLE, "UTF-8");
		$data['lastname'] = mb_convert_case( trim( $data['lastname'] ), MB_CASE_TITLE, "UTF-8");

		$this->db->query("UPDATE " . DB_PREFIX . "customer SET firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', fax = '" . $this->db->escape($data['fax']) . "' WHERE customer_id = '" . (int)$this->customer->getId() . "'");
	}

	public function editPassword($email, $password) {
      	$this->db->query("UPDATE " . DB_PREFIX . "customer SET password = '" . $this->db->escape(md5($password)) . "' WHERE email = '" . $this->db->escape($email) . "'");
	}

	public function editNewsletter($newsletter) {
		$this->db->query("UPDATE " . DB_PREFIX . "customer SET newsletter = '" . (int)$newsletter . "' WHERE customer_id = '" . (int)$this->customer->getId() . "'");
	}

	public function getCustomer($customer_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$customer_id . "'");

		return $query->row;
	}

	public function getTotalCustomersByEmail($email) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer WHERE email = '" . $this->db->escape($email) . "'");

		return $query->row['total'];
	}
}
?>