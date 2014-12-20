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
		$this->addField('duration')->hint('Duration in days, starts from 0 as campaign start day')->type('Number');

		$this->addExpression('posting_date')->set(function($m,$q){
			$td=$m->add('xMarketingCampaign/Model_Campaign',array('table_alias'=>'td'));
			$td->addCondition('id',$q->getField('campaign_id'));
			$td->_dsql()->del('field');
			$td->_dsql()->field('starting_date');

    		return 'DATE_ADD(('.$td->_dsql()->render().'),INTERVAL duration DAY)';
   		})->type('datetime');
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

	function createNew($newsletter_id,$campaign_id,$duration){
		if($this->loaded())
			$this->unload();
		$this['campaign_id'] = $campaign_id;
		$this['newsletter_id'] = $newsletter_id;
		$this['duration'] = $duration;
		$this->save();
		return true;
	}


	function isExist($newsletter_id,$campaign_id,$duration){
		$this->addCondition('campaign_id',$campaign_id);
		$this->addCondition('newsletter_id',$newsletter_id);
		$this->addCondition('duration',$duration);
		$this->tryLoadAny();
		if($this->loaded()){
			return true;
		}
		return false;
	}

}	
