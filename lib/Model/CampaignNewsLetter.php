<?php

namespace xMarketingCampaign;


class Model_CampaignNewsLetter extends \Model_Table {
	public $table ='xmarketingcampaign_campaignnewsletter';

	function init(){
		parent::init();

		$this->hasOne('Epan','epan_id');
		$this->addCondition('epan_id',$this->api->current_website->id);

		$this->hasOne('xMarketingCampaign/Campaign','campaign_id')->defaultValue('Null')->mandatory(true);
		$this->hasOne('xEnquiryNSubscription/NewsLetter','newsletter_id')->defaultValue('Null')->mandatory(true);

		// $this->addField('post_to_socials')->type('boolean')->defaultValue(false);
		$this->addField('duration')->hint('duration in days')->type('Number');

		$this->addHook('beforeSave',$this);
	
		// $this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeSave(){
		
		$campaign = $this->add('xMarketingCampaign/Model_Campaign');
		$campaign->load($this['campaign_id']);

		if($campaign['effective_start_date'] == 'SubscriptionDate' AND $this['post_to_socials']){
			throw $this->exception('Social Posts are not applicable on Subscribers Subscription Date Based Campaigns','ValidityCheck')->setField('post_to_socials');
		}
	}

}	
