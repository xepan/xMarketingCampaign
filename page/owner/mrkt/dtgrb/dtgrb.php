<?php


class page_xMarketingCampaign_page_owner_mrkt_dtgrb_dtgrb extends page_componentBase_page_owner_main {
	
	function page_index(){
		// parent::init();
			
		$crud = $this->add('CRUD');
		$crud->setModel('xMarketingCampaign/DataGrabber',null,array('name','site_url','is_active','last_run_time','is_runnable'));

		if($g = $crud->grid){
			$crud->add_button->setIcon('ui-icon-plusthick');
			$g->addColumn('expander','phrases');
		}

		$crud->add('Controller_FormBeautifier');

	}

	function page_phrases(){
		$this->api->stickyGET('xMarketingCampaign_data_grabber_id');

		$data_grabber_model = $this->add('xMarketingCampaign/Model_DataGrabber');
		$data_grabber_model->load($_GET['xMarketingCampaign_data_grabber_id']);

		$crud = $this->add('CRUD');

		$phrases = $data_grabber_model->ref('xMarketingCampaign/DataSearchPhrase');
		$phrases->addExpression('emails_count')->set(function($m,$q){
			$subs = $m->add('xEnquiryNSubscription/Model_Subscription',array('table_alias'=>'emc'));
			$subs->addCondition('from_app','DataGrabberPhrase');
			$subs->addCondition('from_id',$q->getField('id'));

			return $subs->count();
		});

		$phrases->setOrder('id','desc');

		$crud->setModel($phrases ,null,array('subscription_category','name','is_active','last_page_checked_at','emails_count'));

		if($crud->grid){
			$crud->grid->addPaginator(10);
			$crud->add_button->setIcon('ui-icon-plusthick');
		}

		$crud->add('Controller_FormBeautifier');

	}
}