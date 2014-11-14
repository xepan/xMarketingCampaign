<?php

class page_xMarketingCampaign_page_owner_config extends page_componentBase_page_owner_main{

	function page_index(){

		$tab = $this->add('Tabs');
		$email_tab = $tab->addTab('Email Config');
		$api_tab = $tab->addTabURL('./social','Social Config');

		//Email Tab
		$config_model = $email_tab->add('xMarketingCampaign/Model_Config');
		$email_tab->add('View_Info')->set('Email Configuration');
		$crud = $email_tab->add('CRUD');
		$crud->setModel($config_model);
		//End of Email Tab
	}

	function page_social(){
		
		$tabs = $this->add('Tabs');

		$objects = scandir($plug_path = getcwd().DS.'epan-components'.DS.'xMarketingCampaign'.DS.'lib'.DS.'Controller'.DS.'SocialPosters');
    	foreach ($objects as $object) {
    		if ($object != "." && $object != "..") {
        		if (filetype($plug_path.DS.$object) != "dir"){
        			$object = str_replace(".php", "", $object);
        			$t=$tabs->addTab($object);
        			// $login_status_view =$t->add('View');
        			$social = $t->add('xMarketingCampaign/Controller_SocialPosters_'.$object);
        			$social->config_page();
        			// $login_status_view->setHTML($object. ' - '. $social->login_status());
        		}
    		}
    	}

	}
}		