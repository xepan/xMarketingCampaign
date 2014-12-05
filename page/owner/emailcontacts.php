<?php

class page_xMarketingCampaign_page_owner_emailcontacts extends page_componentBase_page_owner_main{

	function page_index(){
		// parent::init();
	
		$email_category_model = $this->add('xEnquiryNSubscription/Model_SubscriptionCategories');
		$email_category_model->hasMany('xMarketingCampaign/DataSearchPhrase','subscription_category_id');

		$email_category_model->addExpression('total_phrases')->set(function($m,$q){
			return $m->refSQL('xMarketingCampaign/DataSearchPhrase')->count();
		})->type('int');

		$email_category_model->addExpression('active_phrases')->set(function($m,$q){
			return $m->refSQL('xMarketingCampaign/DataSearchPhrase')->addCondition('is_active',true)->count();
		})->type('int');

		$crud = $this->add('CRUD');
		$crud->setModel($email_category_model,array('name','is_active','total_phrases','active_phrases','total_emails'));

		if($g=$crud->grid){
			$crud->add_button->setIcon('ui-icon-plusthick');
			$btn = $g->addButton('Manage Data Grabber');
			$btn->setIcon('ui-icon-contact');
			$btn->js('click',$g->js()->univ()->frameURL('Data Grabber',$this->api->url('xMarketingCampaign_page_owner_mrkt_dtgrb_dtgrb')));

			$btn1 = $g->addButton('Exec Grabber');
			$btn1->setIcon('ui-icon-seek-end');
			$btn1->js('click',$g->js()->univ()->frameURL('Execute Data Grabber',$this->api->url('xMarketingCampaign_page_owner_mrkt_dtgrb_exec')));

			$g->addTotals(array('total_phrases','active_phrases','total_emails'));	
		}

		$crud->add('Controller_FormBeautifier');
		if($crud and $g = $crud->grid){
			$g->addColumn('expander','emails');
		}
		
	}

	function page_emails(){
		$group_id = $this->api->stickyGET('xEnquiryNSubscription_Subscription_Categories_id');
		$subs_crud = $this->add('CRUD');
		$cat_sub_model = $this->add('xEnquiryNSubscription/Model_SubscriptionCategoryAssociation')->addCondition('category_id',$group_id);

		$tmp = $cat_sub_model->getElement('subscriber_id')->getModel();
		$tmp->getElement('from_app')->defaultValue('xMarketingCampaign');

		$subs_crud->setModel($cat_sub_model);

		if($subs_crud){
			$subs_crud->add('Controller_FormBeautifier');			
			// ->getElement('from_app')->defaultValue('xMarketingCampaign');
		}
		if($subs_crud and $g=$subs_crud->grid){
			$subs_crud->add_button->setIcon('ui-icon-plusthick');
			$g->add_sno();
			$g->addPaginator(100);
			$g->addQuickSearch(array('email'));
		}
	}
}