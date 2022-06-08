<?php
class ControllerAffiliatePayoutHistory extends Controller {
	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('affiliate/payout_history', '', true);

			$this->response->redirect($this->url->link('affiliate/login', '', true));
		}

		$this->load->language('affiliate/payout_history');

		$this->document->setTitle($this->language->get('heading_title'));

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
			'text' => $this->language->get('text_payout_history'),
			'href' => $this->url->link('affiliate/payout_history', '', true)
		);

		$this->load->model('affiliate/affiliate_payout');

		$data['heading_title'] = $this->language->get('heading_title');

		$data['column_payout_id'] = $this->language->get('column_payout_id');
		$data['column_date_added'] = $this->language->get('column_date_added');
		$data['column_amount'] = $this->language->get('column_amount');
		$data['column_method'] = $this->language->get('column_method');
		$data['column_status'] = $this->language->get('column_status');


		$data['text_empty'] = $this->language->get('text_empty');

		$data['button_continue'] = $this->language->get('button_continue');

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['payouts'] = array();

		$filter_data = array(
			'start' => ($page - 1) * 10,
			'limit' => 10
		);

		$payout_total = $this->model_affiliate_affiliate_payout->getTotalPayouts();

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

		$pagination = new Pagination();
		$pagination->total = $payout_total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->url = $this->url->link('affiliate/payout_history', 'page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($payout_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($payout_total - 10)) ? $payout_total : ((($page - 1) * 10) + 10), $payout_total, ceil($payout_total / 10));

		$data['continue'] = $this->url->link('account/account', '', true);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('affiliate/payout_history', $data));
	}
}
