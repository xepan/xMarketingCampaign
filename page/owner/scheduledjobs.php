<?php

class page_xMarketingCampaign_page_owner_scheduledjobs extends page_componentBase_page_owner_main{
	
	function page_index(){

		$tabs = $this->add('Tabs');
		$email_tab = $tabs->addTabURL('./email',"Email Jobs");
		$social_tab = $tabs->addTabURL('./social',"Social Jobs");



	}

	function page_email(){
		$jobs_model = $this->add('xEnquiryNSubscription/Model_EmailJobs');

		$jobs_model->addExpression('pending_emails')->set(function($m,$q){
			return $m->refSQL('xEnquiryNSubscription/EmailQueue')->addCondition('is_sent',false)->count();
		});

		$grid = $this->add('Grid');
		$grid->setModel($jobs_model);

		$grid->addColumn('expander','email_list');

		$grid->addPaginator(100);
		$grid->addQuickSearch(array('newsletter'));

	}

	function page_email_email_list(){
		$this->api->stickyGET('xEnquiryNSubscription_EmailJobs_id');
		
		$emails = $this->add('xEnquiryNSubscription/Model_EmailQueue');
		$emails->addCondition('emailjobs_id',$_GET['xEnquiryNSubscription_EmailJobs_id']);

		// $emails = $email_job->ref('xEnquiryNSubscription/EmailQueue');

		$grid = $this->add('Grid');
		$grid->setModel($emails);
		$grid->addPaginator(100);
		// $grid->addQuickSearch(array(''));

	}

	function page_social(){

	}
}
