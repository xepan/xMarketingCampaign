<?php

namespace xMarketingCampaign;


class Model_CampaignSubscriptionCategory extends \Model_Table {
	public $table ='xmarketingcampaign_cp_sub_cat';

	function init(){
		parent::init();
		$this->hasOne('Epan','epan_id');
		$this->addCondition('epan_id',$this->api->current_website->id);
		$this->hasOne('xMarketingCampaign/Campaign','campaign_id');
		$this->hasOne('xEnquiryNSubscription/SubscriptionCategories','category_id');

		// $this->addField('is_associate')->caption('is_associate')->type('boolean');

		$this->addExpression('name')->set(function($m,$q){
			return $m->refSQL('category_id')->fieldQuery('name');
		});

		// $this->add('dynamic_model/Controller_AutoCreator');
	}

	function createNew($cat_id,$Campaign_id){
		$this['category_id']=$cat_id;		
		$this['campaign_id']=$Campaign_id;		
		// $this['is_associate']=true;		
		$this->save();

	}

	function swapActive($status){
		if($status)
			$this->delete();
	}

	function getStatus($cat_id,$Campaign_id){
		$this->addCondition('category_id',$cat_id);
		$this->addCondition('campaign_id',$Campaign_id);
		$this->tryLoadAny();
		if($this->count()->getOne()){
			return true;
		}
		else
			return false;
	}

}	
