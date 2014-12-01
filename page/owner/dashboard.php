<?php

class page_xMarketingCampaign_page_owner_dashboard extends page_componentBase_page_owner_main {

	function page_index(){
		
		$this->add('View')->set('Total Emails Data');
		$this->add('View')->set('Total Total Newsletters');
		$this->add('View')->set('Recent Emails Jobs Completed - Process Via Others');
		$this->add('View')->set('Recent Emails Jobs Completed - Process Set Via Xmarketing Campaign');
		$this->add('View')->set('Recent Social Posts');
		$this->add('View')->set('Recent Social Posts Activities');
		
		$this->add('View')->set('Next Scheduled Email Job');
		$this->add('View')->set('Next Scheduled Social Job');



	}
}		