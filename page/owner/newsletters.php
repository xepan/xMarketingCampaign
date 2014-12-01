<?php

class page_xMarketingCampaign_page_owner_newsletters extends page_componentBase_page_owner_main{

	function init(){
		parent::init();

		$newsletter_model = $this->add('xEnquiryNSubscription/Model_NewsLetter');

		$crud = $this->add('CRUD');
		$crud->setModel($newsletter_model,null,null);
		$crud->add('Controller_FormBeautifier');
		
		if($crud->grid){
			$crud->add_button->setIcon('ui-icon-plusthick');
		}

	}
}		