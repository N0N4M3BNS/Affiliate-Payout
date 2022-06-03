<?php
class ControllerMarketingAffiliatePayout extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('marketing/affiliate_payout');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('marketing/affiliate_payout');

		$this->getList();
	}

	public function edit() {
		$this->load->language('marketing/affiliate_payout');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('marketing/affiliate_payout');

		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

			$this->model_marketing_affiliate_payout->editPayout($this->request->get['payout_id'], $this->request->get['affiliate_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_payout_id'])) {
				$url .= '&filter_payout_id=' . urlencode(html_entity_decode($this->request->get['filter_payout_id'], ENT_QUOTES, 'UTF-8'));
			}


			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}


			if (isset($this->request->get['filter_date_added'])) {
				$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
			}


			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('marketing/affiliate_payout', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function cancel() {
		$this->load->language('marketing/affiliate_payout');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('marketing/affiliate_payout');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $payout_id) {
				$this->model_marketing_affiliate_payout->cancelPayout($payout_id);
			}

			$this->session->data['success'] = $this->language->get('text_delete_success');

			$url = '';

			if (isset($this->request->get['filter_payout_id'])) {
				$url .= '&filter_payout_id=' . urlencode(html_entity_decode($this->request->get['filter_payout_id'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}

			if (isset($this->request->get['filter_date_added'])) {
				$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('marketing/affiliate_payout', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}


		$this->getList();
	}

	protected function getList() {
		if (isset($this->request->get['filter_payout_id'])) {
			$filter_payout_id = $this->request->get['filter_payout_id'];
		} else {
			$filter_payout_id = null;
		}


		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = null;
		}


		if (isset($this->request->get['filter_date_added'])) {
			$filter_date_added = $this->request->get['filter_date_added'];
		} else {
			$filter_date_added = null;
		}


		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['filter_payout_id'])) {
			$url .= '&filter_payout_id=' . urlencode(html_entity_decode($this->request->get['filter_payout_id'], ENT_QUOTES, 'UTF-8'));
		}


		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}


		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}


		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('marketing/affiliate_payout', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['cancel'] = $this->url->link('marketing/affiliate_payout/cancel', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['payouts'] = array();

		$filter_data = array(
			'filter_payout_id'       => $filter_payout_id,
			'filter_status'     => $filter_status,
			'filter_date_added' => $filter_date_added,
			'start'             => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'             => $this->config->get('config_limit_admin')
		);

		$payout_total = $this->model_marketing_affiliate_payout->getTotalPayout($filter_data);

		$results = $this->model_marketing_affiliate_payout->getPayouts($filter_data);

		foreach ($results as $result) {

			$data['payouts'][] = array(
				'payout_id' => $result['payout_id'],
				'amount'      => $this->currency->format($result['payout_amount'], $result['payout_currency']),
				'status'       => $result['payout_status'],
				'payout_method'       => $result['payout_method'],
				'date_added'   => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'edit'         => $this->url->link('marketing/affiliate_payout/edit', 'user_token=' . $this->session->data['user_token'] . '&payout_id=' . $result['payout_id'] . $url, true)
			);
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_list'] = $this->language->get('text_list');
		$data['text_pending'] = $this->language->get('text_pending');
		$data['text_complete'] = $this->language->get('text_complete');
		$data['text_cancel'] = $this->language->get('text_cancel');
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_none'] = $this->language->get('text_none');
		$data['text_confirm'] = $this->language->get('text_confirm');

		$data['column_payout_id'] = $this->language->get('column_payout_id');
		$data['column_name'] = $this->language->get('column_name');
		$data['column_email'] = $this->language->get('column_email');
		$data['column_amount'] = $this->language->get('column_amount');
		$data['column_method'] = $this->language->get('column_method');
		$data['column_date_added'] = $this->language->get('column_date_added');
		$data['column_status'] = $this->language->get('column_status');
		$data['column_action'] = $this->language->get('column_action');

		$data['entry_payout_id'] = $this->language->get('entry_payout_id');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_date_added'] = $this->language->get('entry_date_added');

		$data['button_edit'] = $this->language->get('button_edit');
		$data['button_cancel'] = $this->language->get('button_cancel');
		$data['button_filter'] = $this->language->get('button_filter');

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';

		if (isset($this->request->get['filter_payout_id'])) {
			$url .= '&filter_payout_id=' . urlencode(html_entity_decode($this->request->get['filter_payout_id'], ENT_QUOTES, 'UTF-8'));
		}


		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}


		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}


		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}


		$url = '';

		if (isset($this->request->get['filter_payout_id'])) {
			$url .= '&filter_payout_id=' . urlencode(html_entity_decode($this->request->get['filter_payout_id'], ENT_QUOTES, 'UTF-8'));
		}


		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}


		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}


		$pagination = new Pagination();
		$pagination->total = $payout_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('marketing/affiliate_payout', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($payout_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($payout_total - $this->config->get('config_limit_admin'))) ? $payout_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $payout_total, ceil($payout_total / $this->config->get('config_limit_admin')));

		$data['filter_payout_id'] = $filter_payout_id;
		$data['filter_status'] = $filter_status;
		$data['filter_date_added'] = $filter_date_added;


		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('marketing/affiliate_payout_list', $data));
	}

	protected function getForm() {
		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_form'] = !isset($this->request->get['payout_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		$data['text_payout_id'] = $this->language->get('text_payout_id');
		$data['text_amount'] = $this->language->get('text_amount');
		$data['text_status'] = $this->language->get('text_status');
		$data['text_payout_method'] = $this->language->get('text_payout_method');
		$data['text_cheque'] = $this->language->get('text_cheque');
		$data['text_paypal'] = $this->language->get('text_paypal');
		$data['text_bank'] = $this->language->get('text_bank');
		$data['text_manage_payout'] = $this->language->get('text_manage_payout');
		$data['text_status_cancel'] = $this->language->get('text_status_cancel');

		$data['entry_notify'] = $this->language->get('entry_notify');
		$data['entry_transaction'] = $this->language->get('entry_transaction');

		$data['button_complete'] = $this->language->get('button_complete');
		$data['button_cancel'] = $this->language->get('button_cancel');
		$data['button_remove'] = $this->language->get('button_remove');



		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}


		if (isset($this->error['cheque'])) {
			$data['error_cheque'] = $this->error['cheque'];
		} else {
			$data['error_cheque'] = '';
		}

		if (isset($this->error['paypal'])) {
			$data['error_paypal'] = $this->error['paypal'];
		} else {
			$data['error_paypal'] = '';
		}

		if (isset($this->error['bank_account_name'])) {
			$data['error_bank_account_name'] = $this->error['bank_account_name'];
		} else {
			$data['error_bank_account_name'] = '';
		}

		if (isset($this->error['bank_account_number'])) {
			$data['error_bank_account_number'] = $this->error['bank_account_number'];
		} else {
			$data['error_bank_account_number'] = '';
		}


		$url = '';

		if (isset($this->request->get['filter_payout_id'])) {
			$url .= '&filter_payout_id=' . urlencode(html_entity_decode($this->request->get['filter_payout_id'], ENT_QUOTES, 'UTF-8'));
		}


		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}


		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}


		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('marketing/affiliate_payout', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);


		$payout_info = $this->model_marketing_affiliate_payout->getPayout($this->request->get['payout_id']);


		$data['action'] = $this->url->link('marketing/affiliate_payout/edit', 'user_token=' . $this->session->data['user_token'] . '&payout_id=' . $this->request->get['payout_id'] . '&affiliate_id=' . $payout_info['affiliate_id'] . $url, true);

		$data['cancel'] = $this->url->link('marketing/affiliate_payout', 'user_token=' . $this->session->data['user_token'] . $url, true);



		$data['user_token'] = $this->session->data['user_token'];


		$data['payout_id'] = $this->request->get['payout_id'];


		$data['amount'] = $this->currency->format($payout_info['payout_amount'], $payout_info['payout_currency']);
		$data['status'] = $payout_info['payout_status'];
		$data['payout_method'] = $payout_info['payout_method'];


		if ($data['payout_method'] == 'bank') {
			// Only for bank
			$payout_details = json_decode($payout_info['payment_detail']);

			$data['bank_details'] = array(
				'bank_name' 		=> $this->language->get('text_bank_name') .$payout_details[0],
				'bank_branch'       => $this->language->get('text_bank_branch_number') .$payout_details[1],
				'bank_swift'      	=> $this->language->get('text_bank_swift_code') .$payout_details[2],
				'account_name'      => $this->language->get('text_bank_account_name') .$payout_details[3],
				'account_number'    => $this->language->get('text_bank_account_number') .$payout_details[4]

			);
		} else {
			$data['payout_details'] = json_decode($payout_info['payment_detail']);
		}


		if (isset($this->request->post['transaction'])) {
			$data['transaction'] = $this->request->post['transaction'];
		} elseif (!empty($payout_info)) {
			$data['transaction'] = $payout_info['transaction'];
		} else {
			$data['transaction'] = '';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('marketing/affiliate_payout_form', $data));
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'marketing/affiliate_payout')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

}
