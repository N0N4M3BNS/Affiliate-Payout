<?php
class ModelMarketingAffiliatePayout extends Model {

	public function getPayout($payout_id) {

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "affiliate_payout WHERE payout_id = '" . (int)$payout_id . "'");

		return $query->row;


	}

	public function editPayout($payout_id, $affiliate_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "affiliate_payout SET payout_status = '" . 'Complete' . "', transaction = '" . $this->db->escape($data['transaction']) . "', date_updated = NOW() WHERE payout_id = '" . (int)$payout_id . "'");

		if (isset($data['notify'])) {
			$affiliate_info = $this->getAffiliate($affiliate_id);

			$this->load->language('marketing/affiliate_payout');

			$subject = sprintf($this->language->get('email_subject'), html_entity_decode($payout_id, ENT_QUOTES, 'UTF-8'));

			$message = sprintf($this->language->get('email_payout_inform'), $payout_id) . "\n\n";

			if ($data['transaction']) {
				$message .= sprintf($this->language->get('email_payout_follow'), $data['transaction']);
			}

			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo($affiliate_info['email']);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$mail->setSubject($subject);
			$mail->setText($message);
			$mail->send();
		}
	}

	public function getPayouts($data = array()) {

		$sql = "SELECT * FROM " . DB_PREFIX . "affiliate_payout";

		$implode = array();

		if (!empty($data['filter_payout_id'])) {
			$implode[] = "payout_id = '" . (int)$data['filter_payout_id'] . "'";
		}


		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$implode[] = "payout_status = '" . $data['filter_status'] . "'";
		}


		if (!empty($data['filter_date_added'])) {
			$implode[] = "DATE(date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$sql .= " ORDER BY payout_id";


		$sql .= " DESC";

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
	}

	public function getTotalPayout($data = array()) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "affiliate_payout";

		$implode = array();

		if (!empty($data['filter_payout_id'])) {
			$implode[] = "payout_id = '" . (int)$data['filter_payout_id'] . "'";
		}


		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$implode[] = "payout_status = '" . $data['filter_status'] . "'";
		}


		if (!empty($data['filter_date_added'])) {
			$implode[] = "DATE(date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function cancelPayout($payout_id) {

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "affiliate_payout WHERE payout_id = '" . (int)$payout_id . "'");

		if($query->row['payout_status'] != "Canceled"){

            $description = "Payout Return #".$payout_id;

            $this->db->query("INSERT INTO " . DB_PREFIX . "customer_transaction SET customer_id = '" . (int)$query->row['affiliate_id'] . "', order_id = '" . '0' . "', description = '" . $this->db->escape($description) . "', amount = '" . (float)$query->row['payout_amount'] . "', date_added = NOW()");

            $this->db->query("UPDATE " . DB_PREFIX . "affiliate_payout SET payout_status = 'Canceled', date_updated = NOW() WHERE payout_id = '" . (int)$payout_id . "'");

		}

	}

	public function getAffiliate($affiliate_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "affiliate WHERE affiliate_id = '" . (int)$affiliate_id . "'");

		return $query->row;
	}


}
