<?php
class ControllerExtensionModuleAffiliatePayout extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/affiliate_payout');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('affiliate_payout', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true));
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');

		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_admin_notification'] = $this->language->get('entry_admin_notification');
		$data['entry_min_withdraw'] = $this->language->get('entry_min_withdraw');
		
		$data['help_admin_notification'] = $this->language->get('help_admin_notification');
		$data['help_min_withdraw'] = $this->language->get('help_min_withdraw');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/affiliate_payout', 'token=' . $this->session->data['token'], true)
		);

		$data['action'] = $this->url->link('extension/module/affiliate_payout', 'token=' . $this->session->data['token'], true);

		$data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true);

		if (isset($this->request->post['affiliate_payout_status'])) {
			$data['affiliate_payout_status'] = $this->request->post['affiliate_payout_status'];
		} else {
			$data['affiliate_payout_status'] = $this->config->get('affiliate_payout_status');
		}
		
		if (isset($this->request->post['affiliate_payout_email'])) {
			$data['affiliate_payout_email'] = $this->request->post['affiliate_payout_email'];
		} else {
			$data['affiliate_payout_email'] = $this->config->get('affiliate_payout_email');
		}
		
		if (isset($this->request->post['affiliate_payout_limit'])) {
			$data['affiliate_payout_limit'] = $this->request->post['affiliate_payout_limit'];
		} else if ($this->config->get('affiliate_payout_limit')){
			$data['affiliate_payout_limit'] = $this->config->get('affiliate_payout_limit');
		} else {
			$data['affiliate_payout_limit'] = 0;
		}
		

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/affiliate_payout', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/affiliate_payout')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
	
	public function install() {
		
		if (!$this->user->hasPermission('modify', 'extension/extension/module')) {
			return;
		}
		
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "affiliate_payout` (
		  `payout_id` int(11) NOT NULL AUTO_INCREMENT,
		  `affiliate_id` int(11) NOT NULL,
		  `payout_amount` decimal(15,4) NOT NULL,
		  `payout_currency` varchar(11) NOT NULL,
		  `payout_method` varchar(11) NOT NULL,
		  `payout_status` varchar(15) NOT NULL,
		  `payment_detail` varchar(480) NOT NULL,
		  `transaction` varchar(255) NOT NULL,
		  `date_added` datetime NOT NULL,
		  `date_updated` datetime NOT NULL,
		  PRIMARY KEY (`payout_id`)
		) DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;");
		
	}
}
