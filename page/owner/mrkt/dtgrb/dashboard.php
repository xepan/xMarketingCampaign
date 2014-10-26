<?php

class page_xMarketingCampaign_page_owner_mrkt_dtgrb_dashboard extends page_componentBase_page_owner_main {

	function init(){
		parent::init();

		$tabs = $this->add('Tabs');
		$data_grabber_tab = $tabs->addTabURL('xMarketingCampaign_page_owner_mrkt_dtgrb_dtgrb','Data Graber Instances');

	}

}