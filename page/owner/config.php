<?php

class page_xMarketingCampaign_page_owner_config extends page_componentBase_page_owner_main{

	function init(){
		parent::init();

		$tab = $this->add('Tabs');
		$email_tab = $tab->addTab('Email Config');
		$api_tab = $tab->addTab('Api Config');

		//Email Tab
		$config_model = $email_tab->add('xMarketingCampaign/Model_Config');
		$email_tab->add('View_Info')->set('Email Configuration');
		$crud = $email_tab->add('CRUD');
		$crud->setModel($config_model);
		//End of Email Tab

		//Api Tab
		$api_tab->add('View_Info')->set('Api Config');
		//End of Api Tab

	}
}		