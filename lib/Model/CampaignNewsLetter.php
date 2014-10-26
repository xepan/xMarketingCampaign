<?php

namespace xMarketingCampaign;


class Model_CampaignNewsLetter extends \Model_Table {
	public $table ='xmarketingcampaign_campaignnewsletter';

	function init(){
		parent::init();

		$this->hasOne('Epan','epan_id');
		$this->addCondition('epan_id',$this->api->current_website->id);

		$this->hasOne('xMarketingCampaign/Campaign','campaign_id')->defaultValue('Null');
		$this->hasOne('xEnquiryNSubscription/NewsLetter','newsletter_id')->defaultValue('Null');

		$this->addField('name')->caption('is_associate')->type('boolean')->defaultValue(true);
		$this->addField('duration')->hint('duration in days')->type('Number');

		$this->add('dynamic_model/Controller_AutoCreator');
	}

}	
