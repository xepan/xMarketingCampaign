<?php

namespace xMarketingCampaign;

class Controller_SocialPosters_Linkedin extends Controller_SocialPosters_Base_Social{
	
	function config_page(){
		$this->owner->add('View_Info');
	}
}