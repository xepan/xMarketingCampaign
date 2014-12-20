<?php

namespace xMarketingCampaign;


class Model_Campaign extends \Model_Table {
	public $table ='xmarketingcampaign_campaigns';

	function init(){
		parent::init();

		$this->add('xMarketingCampaign/Controller_SocialPosters_Base_Social'); // Just in case of needed files

		$this->hasOne('Epan','epan_id');
		$this->addCondition('epan_id',$this->api->current_website->id);

		$this->hasOne('xMarketingCampaign/CampaignCategory','category_id');
		// $this->addField('Campaign_type')->setValueList(array('email'=>'Email','blog'=>'Blogs','social'=>'Social'));
		$f=$this->addField('name')->mandatory(true)->group('a~6~<i class="fa fa-slideshare"></i> The Campaign');
		$f->icon='fa fa-adn~red';
		$f=$this->addField('effective_start_date')->enum(array('CampaignDate','SubscriptionDate'))->group('a~4')->caption('Campaign Start date...');
		$f=$this->addField('is_active')->type('boolean')->group('a~2');
		$f->icon='fa fa-exclamation~blue';
		
		$f=$this->addField('starting_date')->type('datetime')->defaultValue(date('Y-m-d H:i:s'))->group('b~6~<i class="fa fa-calendar"></i> Limit Duration')->mandatory(true);
		$f->icon='fa fa-calendar~red';
		$f=$this->addField('ending_date')->type('datetime')->group('b~6')->mandatory(true);
		$f->icon='fa fa-calendar~red';
		// $this->addField('matter')->type('text')->display(array('form'=>'RichText'))->defaultValue('<p></p>');
		$this->hasMany('xMarketingCampaign/CampaignSubscriptionCategory','campaign_id');
		$this->hasMany('xMarketingCampaign/CampaignNewsLetter','campaign_id');
		$this->hasMany('xMarketingCampaign/CampaignSocialUser','campaign_id');
		$this->hasMany('xMarketingCampaign/CampaignSocialPost','campaign_id');

		$this->add('dynamic_model/Controller_AutoCreator');
	}
}