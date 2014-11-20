<?php

class page_xMarketingCampaign_page_owner_scheduledjobs extends page_componentBase_page_owner_main{
	
	function page_index(){

		$tabs = $this->add('Tabs');
		$email_tab = $tabs->addTabURL('./email','<i class="fa fa-reorder"></i> Email Jobs');
		$social_tab = $tabs->addTabURL('./social','<i class="fa fa-reorder"></i> Social Jobs');



	}

	function page_email(){
		$jobs_model = $this->add('xEnquiryNSubscription/Model_EmailJobs');

		$jobs_model->addExpression('pending_emails')->set(function($m,$q){
			return $m->refSQL('xEnquiryNSubscription/EmailQueue')->addCondition('is_sent',false)->count();
		});

		$btn= $this->add('Button')->set('Execute Sending Emails Now');
		$btn->setIcon('ui-icon-seek-end');

		if($btn->isClicked()){
			$this->js()->univ()->frameURL('Sending Emails ... Do not close this frame, unless specified',$this->api->url('xMarketingCampaign_page_emailexec'))->execute();
		}

		$grid = $this->add('Grid');
		$grid->setModel($jobs_model);

		$grid->addColumn('expander','email_list');

		$grid->addPaginator(100);
		$grid->addQuickSearch(array('newsletter'));

	}

	function page_email_email_list(){
		$this->api->stickyGET('xEnquiryNSubscription_EmailJobs_id');
		
		$v= $this->add('View');
		$v->addClass('panel panel-default');
		$v->setStyle('padding','20px');

		$emails = $this->add('xEnquiryNSubscription/Model_EmailQueue');
		$emails->addCondition('emailjobs_id',$_GET['xEnquiryNSubscription_EmailJobs_id']);

		// $emails = $email_job->ref('xEnquiryNSubscription/EmailQueue');

		$grid = $v->add('Grid');
		$grid->setModel($emails);
		$grid->addPaginator(100);
		$grid->addQuickSearch(array('emailjobs','subscriber','email'));

	}

	function page_social(){

		$btn= $this->add('Button')->set('Execute Posting Posts Now');
		$btn->setIcon('ui-icon-seek-end');
		
		if($btn->isClicked()){
			$this->js()->univ()->frameURL('Posting Social ... Do not close this frame, unless specified',$this->api->url('xMarketingCampaign_page_socialexec'))->execute();
		}

		$model = $this->add('xMarketingCampaign/Model_CampaignSocialPost');
		$grid = $this->add('Grid');
		$grid->setModel($model);

	}
}
