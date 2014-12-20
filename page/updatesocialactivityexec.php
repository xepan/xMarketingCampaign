<?php

class page_xMarketingCampaign_page_updatesocialactivityexec extends Page {
	
	function init(){
		parent::init();
		$dummy_cont = $this->add('xMarketingCampaign/Controller_SocialPosters_Base_Social');

		$posts_to_watch = $this->add('xMarketingCampaign/Model_SocialPosting');
		$posts_to_watch->addCondition('is_monitoring',true);

		foreach ($posts_to_watch as $junk) {
			$cont= $this->add('xMarketingCampaign/Controller_SocialPosters_'.$posts_to_watch['social_app']);
			$cont->updateActivities($posts_to_watch);
		}

	}	
}