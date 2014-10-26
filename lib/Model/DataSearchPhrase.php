<?php

namespace xMarketingCampaign;

class Model_DataSearchPhrase extends \Model_Table{
	public $table = "xMarketingCampaign_data_search_phrase";

	function init(){
		parent::init();

		$this->hasOne('xMarketingCampaign/DataGrabber','data_grabber_id');
		$this->hasOne('xEnquiryNSubscription/Model_SubscriptionCategories','subscription_category_id')->caption('Subscription category to save Data in');

		$this->addField('name')->caption('Search Phrase');

		$this->addField('content_provided')->type('longtext')->display(array('grid'=>'shorttext','form'=>'text'))->hint('Html to parsed, no need to fetch url');
		$this->addField('max_record_visit')->hint('How many search results to visit');
		$this->addField('max_domain_depth')->hint('No of domains to hop from result websites');
		$this->addField('max_page_depth')->hint('Depth Of pages in websites');

		$this->addField('is_active')->type('boolean')->defaultValue(true);

		$this->addField('page_parameter_start_value')->system(false)->defaultValue(0);
		$this->addField('page_parameter_max_value')->system(false);
		$this->addField('last_page_checked_at')->type('datetime')->system(false);

		$this->addHook('beforeSave',$this);

		$this->add('dynamic_model/Controller_AutoCreator');

	}


	function beforeSave(){
		$this['last_page_checked_at'] = date('Y-m-d H:i:s');
		// if($this->ref('data_grabber_id')->get('paginator_based_on')=='records')
		// 	$this['page_parameter_max_value'] = $this['max_record_visit'] - $this->ref('data_grabber_id')->get('records_per_page');
		// else
		// 	$this['page_parameter_max_value'] = $this['max_record_visit'] / $this->ref('data_grabber_id')->get('records_per_page');
	}

}