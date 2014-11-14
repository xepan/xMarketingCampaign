<?php


class page_xMarketingCampaign_page_socialloginmanager extends Page{
	
	function init(){
		parent::init();

		if($social = $_GET['social_login_to']){
			$this->api->stickyGET('social_login_to');
			$this->add('xMarketingCampaign/Controller_SocialPosters_'.$_GET['social_login_to'])->login_status();

		}

	}
}