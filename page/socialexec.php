<?php

class page_xMarketingCampaign_page_socialexec extends Page{

	function init(){
		parent::init();

		$all_postable_contents = $this->add('xMarketingCampaign/Model_CampaignSocialPost');
		$all_postable_contents->addCondition('is_posted',false);
		$all_postable_contents->addCondition('post_on_datetime','<=',date('Y-m-d H:i:s'));

		foreach ($all_postable_contents as $junk) {
			$campaign = $all_postable_contents->ref('campaign_id');
			$social_users = $campaign->ref('xMarketingCampaign/CampaignSocialUser');

			foreach ($social_users as $junk) {
				$config = $social_users->ref('config_id');
				$social_app = $config->get('social_app');
				$this->add('xMarketingCampaign/Controller_SocialPosters_'.$social_app)->postSingle($social_users,$all_postable_contents->ref('socialpost_id'),$config['post_in_groups', $groups_posted=array(),$under_campaign_id=$all_postable_contents['campaign_id']);
			}	
			$all_postable_contents['is_posted']=true;
			$all_postable_contents->saveAndUnload();
		}

	}
}
	