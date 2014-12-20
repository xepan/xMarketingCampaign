<?php

namespace xMarketingCampaign;

class Model_DataSearchPhrase extends \Model_Table{
	public $table = "xMarketingCampaign_data_search_phrase";

	function init(){
		parent::init();

		$this->hasOne('xMarketingCampaign/DataGrabber','data_grabber_id');
		$f=$this->hasOne('xEnquiryNSubscription/Model_SubscriptionCategories','subscription_category_id')->caption('Category To Save Data');
		$f->group="a~10~Store Grabbed Emails";
		$f=$this->addField('is_grabbed')->type('boolean')->defaultValue(false)->group('a~2')->sortable(true);

		$this->addField('name')->caption('Search Phrase')->group('b~10~Search What (For Direct Search)')->hint('to grab data direct from provided url, YOUR IP CAN GET BLOCKED BY SEARCH ENGINE')->sortable(true);
		$this->addField('max_record_visit')->hint('How many search results to visit')->group('b~2')->sortable(true);

		$this->addField('content_provided')->type('longtext')->display(array('grid'=>'shorttext','form'=>'text'))->hint('Html to parsed, no need to fetch url')->group('c~12~For Block Proof Search');
		$this->addField('max_domain_depth')->hint('No of domains to hop from result websites')->defaultValue(1)->system(true)->sortable(true);
		$this->addField('max_page_depth')->hint('Depth Of pages in websites')->defaultValue(2)->system(true);


		$this->addField('page_parameter_start_value')->system(true)->defaultValue(0);
		$this->addField('page_parameter_max_value')->system(true);
		$this->addField('last_page_checked_at')->type('datetime')->system(true);

		$this->hasMany('xEnquiryNSubscription/Model_Subscription','from_id');

		$this->addHook('beforeSave',$this);

		// $this->add('dynamic_model/Controller_AutoCreator');

	}


	function beforeSave(){
		$this['last_page_checked_at'] = date('Y-m-d H:i:s');
		// if($this->ref('data_grabber_id')->get('paginator_based_on')=='records')
		// 	$this['page_parameter_max_value'] = $this['max_record_visit'] - $this->ref('data_grabber_id')->get('records_per_page');
		// else
		// 	$this['page_parameter_max_value'] = $this['max_record_visit'] / $this->ref('data_grabber_id')->get('records_per_page');
	}

}