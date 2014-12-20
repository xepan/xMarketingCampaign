<?php


class page_xMarketingCampaign_page_owner_mrkt_dtgrb_dtgrb extends page_xMarketingCampaign_page_owner_main {
	
	function page_index(){
		// parent::init();
		
		$bg=$this->app->layout->add('View_BadgeGroup');
		$data =$this->add('xMarketingCampaign/Model_DataSearchPhrase')->count()->getOne();
		$v=$bg->add('View_Badge')->set('Total Phrases')->setCount($data)->setCountSwatch('ink');

		$data =$this->add('xMarketingCampaign/Model_DataSearchPhrase')->addCondition('is_grabbed',false)->count()->getOne();
		$v=$bg->add('View_Badge')->set('Un Grabbed Phrases')->setCount($data)->setCountSwatch('red');
				
		$crud = $this->app->layout->add('CRUD');
		$m = $this->add('xMarketingCampaign/Model_DataGrabber');
		$m->addExpression('total_phrases')->set(function($m,$q){
			return $m->refSQL('xMarketingCampaign/DataSearchPhrase')->count();
		});

		$m->addExpression('ungrabbed_phrases')->set(function($m,$q){
			return $m->refSQL('xMarketingCampaign/DataSearchPhrase')->addCondition('is_grabbed',false)->count();
		});

		$crud->setModel($m,null,array('name','site_url','is_active','last_run_time','total_phrases','ungrabbed_phrases'));

		if(!$crud->isEditing()){
			$g = $crud->grid;
			$crud->add_button->setIcon('ui-icon-plusthick');
			$g->addColumn('expander','phrases');
		}

		// $crud->add('Controller_FormBeautifier');

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

		$crud->setModel($phrases ,null,array('subscription_category','name','is_grabbed','last_page_checked_at','emails_count'));

		if(!$crud->isEditing()){
			$crud->grid->addPaginator(10);
			$crud->add_button->setIcon('ui-icon-plusthick');
		}

		// $crud->add('Controller_FormBeautifier');

	}
}