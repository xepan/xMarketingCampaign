<?php


class page_xMarketingCampaign_page_owner_mrkt_dtgrb_dtgrb extends page_componentBase_page_owner_main {
	
	function page_index(){
		// parent::init();
			
		$crud = $this->add('CRUD');
		$crud->setModel('xMarketingCampaign/DataGrabber');

		if($g = $crud->grid){
			$g->addColumn('expander','phrases');
		}

	}

	function page_phrases(){
		$this->api->stickyGET('xMarketingCampaign_data_grabber_id');

		$data_grabber_model = $this->add('xMarketingCampaign/Model_DataGrabber');
		$data_grabber_model->load($_GET['xMarketingCampaign_data_grabber_id']);

		$crud = $this->add('CRUD');
		$crud->setModel($data_grabber_model->ref('xMarketingCampaign/DataSearchPhrase')->setOrder('id'),null,array('subscription_category','name','max_record_visit','max_domain_depth','max_page_depth','is_active','last_page_checked_at'));

	}
}