<?php

class page_xMarketingCampaign_page_owner_main extends page_componentBase_page_owner_main {
	function initMainPage(){
		
		$tabs = $this->add('Tabs');	
		$tabs->addTabUrl('xMarketingCampaign/page_owner_emailcontacts','Add Contacts');
		$tabs->addTabUrl('xMarketingCampaign/page_owner_newsletters','Add NewsLetter');
		$tabs->addTabUrl('xMarketingCampaign/page_owner_campaigns','Campaigns');
		$tabs->addTabUrl('xMarketingCampaign/page_owner_scheduledjobs','Scheduled Jobs');
		$tabs->addTabUrl('xMarketingCampaign/page_owner_config','Configuration');

	}

 
	function page_config(){
		$this->add('H1')->set('Default Config Page');
	}
}