<?php

class page_xMarketingCampaign_page_owner_leads extends page_xMarketingCampaign_page_owner_main{
	
	function page_index(){
			
		// Add Badges

		// filter line if filter is there

		$leads=$this->add('xMarketingCampaign/Model_Lead');
		$crud=$this->app->layout->add('CRUD');
		$crud->setModel($leads);

		$crud->add('xHR/Controller_Acl');		

	}
}