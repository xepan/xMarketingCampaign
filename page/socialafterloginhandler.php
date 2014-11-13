<?php


class page_xMarketingCampaign_page_socialafterloginhandler extends Page{
	
	function init(){
		parent::init();

		if(!$_GET['xfrom']){
			$this->add('View')->set('no xfrom found');
			return;
		}

		$cont = $this->add('xMarketingCampaign/Controller_SocialPosters_'.$_GET['xfrom']);
		$cont->after_login_handler();
		$this->add('View_Info')->set('Access Token Updated');
	}
}