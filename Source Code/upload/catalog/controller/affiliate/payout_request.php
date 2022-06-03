<?php
class ControllerAffiliatePayoutRequest extends Controller {
	private $error = array();

	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('affiliate/payout_request', '', true);

			$this->response->redirect($this->url->link('affiliate/login', '', true));
		}

		$this->load->language('affiliate/payout_request');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('affiliate/affiliate_payout');


		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_affiliate_affiliate_payout->addPayout($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('affiliate/payout_history', '', true));
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_payout_request'),
			'href' => $this->url->link('affiliate/payout_request', '', true)
		);

		$data['heading_title'] = $this->language->get('heading_title');

		//Default Currency
		$currency = $this->config->get('config_currency');

		$data['text_available_balance'] = $this->language->get('text_available_balance');
		$data['text_cheque'] = $this->language->get('text_cheque');
		$data['text_paypal'] = $this->language->get('text_paypal');
		$data['text_bank'] = $this->language->get('text_bank');
		$data['text_change_payment'] = $this->language->get('text_change_payment');
		$data['text_recent_payouts'] = $this->language->get('text_recent_payouts');
		$data['text_payout_history'] = $this->language->get('text_payout_history');
		$data['text_empty'] = $this->language->get('text_empty');

		$data['available_balance'] = $this->currency->format($this->model_affiliate_affiliate_payout->getBalance(), $currency);
		$data['currency'] = $currency;

		$data['entry_payout_amount'] = $this->language->get('entry_payout_amount');
		$data['entry_payment'] = $this->language->get('entry_payment');
		$data['entry_cheque'] = $this->language->get('entry_cheque');
		$data['entry_paypal'] = $this->language->get('entry_paypal');

		$data['entry_bank_name'] = $this->language->get('entry_bank_name');
		$data['entry_bank_branch_number'] = $this->language->get('entry_bank_branch_number');
		$data['entry_bank_swift_code'] = $this->language->get('entry_bank_swift_code');
		$data['entry_bank_account_name'] = $this->language->get('entry_bank_account_name');
		$data['entry_bank_account_number'] = $this->language->get('entry_bank_account_number');

		$data['column_payout_id'] = $this->language->get('column_payout_id');
		$data['column_date_added'] = $this->language->get('column_date_added');
		$data['column_amount'] = $this->language->get('column_amount');
		$data['column_method'] = $this->language->get('column_method');
		$data['column_status'] = $this->language->get('column_status');

		$data['button_request'] = $this->language->get('button_request');

		$data['change_payment'] = $this->url->link('account/affiliate/edit', '', true);
		$data['payout_history'] = $this->url->link('affiliate/payout_history', '', true);

		// Recent Payouts
		$data['payouts'] = array();

		$filter_data = array(
			'start' => 0,
			'limit' => 5
		);

		$results = $this->model_affiliate_affiliate_payout->getPayouts($filter_data);

		foreach ($results as $result) {
			$data['payouts'][] = array(
				'payout_id'      => $result['payout_id'],
				'amount'      => $this->currency->format($result['payout_amount'], $result['payout_currency']),
				'method' => $result['payout_method'],
				'status' => $result['payout_status'],
				'date_added'  => date($this->language->get('date_format_short'), strtotime($result['date_added']))
			);
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['payout_min_limit'])) {
			$data['error_payout_amount'] = $this->error['payout_min_limit'];
		} else {
			$data['error_payout_amount'] = '';
		}

		$data['action'] = $this->url->link('affiliate/payout_request', '', true);

		$affiliate_info = $this->model_affiliate_affiliate_payout->getAffiliate($this->customer->getId());

		if (isset($this->request->post['payout_amount'])) {
			$data['payout_amount'] = $this->request->post['payout_amount'];
		} else {
			$data['payout_amount'] = '';
		}

		if (isset($this->request->post['payment'])) {
			$data['payment'] = $this->request->post['payment'];
		} elseif (!empty($affiliate_info)) {
			$data['payment'] = $affiliate_info['payment'];
		} else {
			$data['payment'] = '';
		}


		$data['cheque'] = $affiliate_info['cheque'];
		$data['paypal'] = $affiliate_info['paypal'];
		$data['bank_name'] = $affiliate_info['bank_name'];
		$data['bank_branch_number'] = $affiliate_info['bank_branch_number'];
		$data['bank_swift_code'] = $affiliate_info['bank_swift_code'];
		$data['bank_account_name'] = $affiliate_info['bank_account_name'];
		$data['bank_account_number'] = $affiliate_info['bank_account_number'];

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('affiliate/payout_request', $data));
	}

	protected function validate() {

		$this->load->model('affiliate/affiliate_payout');


		if ($this->request->post['payout_amount'] > $this->model_affiliate_affiliate_payout->getBalance() || $this->request->post['payout_amount'] == 0 || $this->request->post['payout_amount'] < $this->config->get('affiliate_payout_limit')) {
			$this->error['payout_min_limit'] = sprintf($this->language->get('error_min_limit'),$this->currency->format($this->config->get('affiliate_payout_limit'), $this->config->get('config_currency')));
		}

		if ($this->request->post['payout_amount'] > $this->model_affiliate_affiliate_payout->getBalance() || $this->request->post['payout_amount'] <= 0 || $this->request->post['payout_amount'] < $this->config->get('affiliate_payout_limit')) {
			$this->error['payout_min_limit'] = sprintf($this->language->get('error_min_limit'),$this->currency->format($this->config->get('affiliate_payout_limit'), $this->config->get('config_currency')));
		}



		return !$this->error;
	}

}
