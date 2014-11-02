<?php

namespace xMarketingCampaign;


class Model_Campaign extends \Model_Table {
	public $table ='xmarketingcampaign_campaigns';

	function init(){
		parent::init();

		$this->hasOne('Epan','epan_id');
		$this->addCondition('epan_id',$this->api->current_website->id);

		// $this->addField('Campaign_type')->setValueList(array('email'=>'Email','blog'=>'Blogs','social'=>'Social'));
		$this->addField('name')->mandatory(true);
		$this->addField('starting_date')->type('datetime')->defaultValue(date('Y-m-d H:i:s'));
		$this->addField('ending_date')->type('datetime');
		$this->addField('effective_start_date')->enum(array('CampaignDate','SubscriptionDate'));
		$this->addField('is_active')->type('boolean');
		// $this->addField('matter')->type('text')->display(array('form'=>'RichText'))->defaultValue('<p></p>');
		$this->hasMany('xMarketingCampaign/CampaignSubscriptionCategory','campaign_id');

		// $this->add('dynamic_model/Controller_AutoCreator');
	}
}