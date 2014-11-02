<?php

class page_xMarketingCampaign_page_install extends page_componentBase_page_install {
	function init(){
		parent::init();

		// 
		// Code To run before installing

		$mrkt_place=$this->add('Model_MarketPlace');
		$mrkt_place->addCondition('namespace','xEnquiryNSubscription');
		$mrkt_place->tryLoadAny();
		if($mrkt_place->loaded()){
			$this->install();
		}else{
		$this->add('View_Error')->set('First Install xEnquiryNSubscription Application');
		return;
		}		
		// Code to run after installation
	}
}