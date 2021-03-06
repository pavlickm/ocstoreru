<?php
class ModelSettingSetting extends Model {
	public function getSetting($group) {
		$data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE `group` = '" . $this->db->escape($group) . "'");

		foreach ($query->rows as $result) {
			$data[$result['key']] = $result['value'];
		}

		return $data;
	}

	public function editSetting($group, $data) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `group` = '" . $this->db->escape($group) . "'");

		foreach ($data as $key => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "setting (`group`, `key`, `value`) VALUES ('" . $this->db->escape($group) . "', '" . $this->db->escape($key) . "', '" . $this->db->escape($value) . "')");
		}
		$this->cache->delete('*');
	}

	public function deleteSetting($group) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `group` = '" . $this->db->escape($group) . "'");
		$this->cache->delete('*');
	}
}
?>