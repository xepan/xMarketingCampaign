<?php

class page_xMarketingCampaign_page_owner_main extends page_componentBase_page_owner_main {

	function page_index(){
		$this->h1->setHTML('<i class="fa fa-slideshare"></i> '.$this->component_name. '<small>Email & Social Campaign Manager</small>');
	}

	function initMainPage(){
		
		$tabs = $this->add('Tabs');	
		$tabs->addTabUrl('xMarketingCampaign/page_owner_dashboard','<i class="fa fa-dashboard"></i> Dashboard');
		$tabs->addTabUrl('xMarketingCampaign/page_owner_emailcontacts','<i class="fa fa-users"></i> Manage Contacts');
		$tabs->addTabUrl('xMarketingCampaign/page_owner_newsletters','<i class="fa fa-envelope"></i> Manage NewsLetters');
		$tabs->addTabUrl('xMarketingCampaign/page_owner_socialcontents','<i class="fa fa-share-alt-square"></i> Add SocialContent');
		$tabs->addTabUrl('xMarketingCampaign/page_owner_campaigns','<i class="fa fa-calendar-o"></i> Campaigns');
		$tabs->addTabUrl('xMarketingCampaign/page_owner_scheduledjobs','<i class="fa fa-tasks"></i> Scheduled Jobs');
		$tabs->addTabUrl('xMarketingCampaign/page_owner_config','<i class="fa fa-cogs"></i> Configurations');

	}

 
	function page_config(){
		$this->add('H1')->set('Default Config Page');
	}
}