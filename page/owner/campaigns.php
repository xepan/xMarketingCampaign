<?php

class page_xMarketingCampaign_page_owner_campaigns extends page_componentBase_page_owner_main{

	function page_index(){
		
		$Campaign_crud = $this->add('CRUD');		
		
		$Campaign_crud->setModel('xMarketingCampaign/Campaign',array('name','starting_date','ending_date','effective_start_date','is_active'));
		if($Campaign_crud->grid){
			$Campaign_crud->grid->addColumn('expander','AddEmails','Add Subscription Category');
			$Campaign_crud->grid->addColumn('expander','NewsLetterSubCampaign','News Letters To send');
			$Campaign_crud->grid->addColumn('expander','social_campaigns','Social Posts To Include');
			$btn=$Campaign_crud->grid->addButton('Schedule Emails Now');
			$btn->setIcon('ui-icon-seek-end');
			$btn->js('click')->univ()->frameURL('Campaign Executing',$this->api->url('xMarketingCampaign_page_owner_campaignexec'));
			
			$Campaign_crud->add_button->setIcon('ui-icon-plusthick');
			// $Campaign_crud->grid->addColumn('expander','BlogSubCampaign');
		}

		$Campaign_crud->add('Controller_FormBeautifier');

	}	

	function page_AddEmails(){
		$campaign_id = $this->api->StickyGET('xmarketingcampaign_campaigns_id');

		$v=$this->add('View');
		$v->addClass('panel panel-default');
		$v->setStyle('padding','20px');
		
		$grid = $v->add('Grid');

		$cat_model = $this->add('xEnquiryNSubscription/Model_SubscriptionCategories');
		$cat_model->addCondition('is_active',true);

		$cat_model->addExpression('status')->set(function($m,$q)use($campaign_id){
			$category_campaign_model = $m->add('xMarketingCampaign/Model_CampaignSubscriptionCategory',array('table_alias'=>'c'));
			$category_campaign_model->addCondition('category_id',$q->getField('id'));
			$category_campaign_model->addCondition('campaign_id',$campaign_id);
			return $category_campaign_model->count();
		})->type('boolean');

		$grid->setModel($cat_model,array('name','is_associate','status'));
		$grid->addColumn('Button','save','Swap Select');

		if($_GET['save']){
			$campaignemail_model = $this->add('xMarketingCampaign/Model_CampaignSubscriptionCategory');
			$status=$campaignemail_model->getStatus($_GET['save'],$campaign_id);
			if($status){
				$campaignemail_model->swapActive($status);
			}
			else{
				$campaignemail_model->createNew($_GET['save'],$campaign_id);
			}

			$grid->js(null,$this->js()->univ()->successMessage('Save Changes'))->reload()->execute();	
		}
	}	

	function page_NewsLetterSubCampaign(){
		$campaign_id = $this->api->StickyGET('xmarketingcampaign_campaigns_id');

		$v=$this->add('View');
		$v->addClass('panel panel-default');
		$v->setStyle('padding','20px');

		$campaign_newsletter_model = $this->add('xMarketingCampaign/Model_CampaignNewsLetter');
		$campaign_newsletter_model->addCondition('campaign_id',$campaign_id);
		$crud = $v->add('CRUD');
		$crud->add('Controller_FormBeautifier');	
		if($crud->grid){
			$crud->add_button->setIcon('ui-icon-plusthick');
		}
		
		$crud->setModel($campaign_newsletter_model);
	}

	function page_social_campaigns(){
		$campaign_id = $this->api->StickyGET('xmarketingcampaign_campaigns_id');
		
		$v=$this->add('View');
		$v->addClass('panel panel-default');
		$v->setStyle('padding','20px');

		$campaign_socialpost_model = $this->add('xMarketingCampaign/Model_CampaignSocialPost');
		$campaign_socialpost_model->addCondition('campaign_id',$campaign_id);
		$crud = $v->add('CRUD');
		$crud->setModel($campaign_socialpost_model);
		if($crud->form){
			$crud->form->getElement('socialpost_id')->setEmptyText('Please Select Post to Post');
		}

		$crud->add('Controller_FormBeautifier');
		
		if($crud->grid){
			$crud->add_button->setIcon('ui-icon-plusthick');
		}
	}


}		