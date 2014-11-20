<?php

class page_xMarketingCampaign_page_owner_socialcontents extends page_componentBase_page_owner_main{

	function init(){
		parent::init();

		$this->rename('y');
		
		$crud = $this->add('CRUD');

		if($_GET['delete']){
			// managing delete mannulaly for suhosin
			$social_model_delete = $this->add('xMarketingCampaign/Model_SocialPost');
			$social_model_delete->delete($_GET['delete']);
			$crud->grid->js()->reload()->execute();	
		}		

		$social_model = $this->add('xMarketingCampaign/Model_SocialPost');

		$crud->setModel($social_model);
		
		$crud->add('Controller_FormBeautifier');
		if($crud->grid){
			$crud->add_button->setIcon('ui-icon-plusthick');
		}

	}
}		