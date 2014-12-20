<?php

namespace xMarketingCampaign;

class Model_CampaignSocialUser extends \Model_Table{
	public $table ='xmarketingcampaign_cp_socialuser_cat';

	function init(){
		parent::init();

		$this->hasOne('Epan','epan_id');
		$this->addCondition('epan_id',$this->api->current_website->id);
		$this->hasOne('xMarketingCampaign/Campaign','campaign_id');
		$this->hasOne('xMarketingCampaign/SocialUsers','socialuser_id');



		$this->add('dynamic_model/Controller_AutoCreator');
	}

}