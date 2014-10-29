<?php

namespace xMarketingCampaign;

class Model_DataGrabber extends \Model_Table{
	public $table='xMarketingCampaign_data_grabber';

	function init(){
		parent::init();

		$this->hasOne('Epan','epan_id');

		$this->addField('name');
		$this->addField('site_url')->hint('Website URL, from where to grab data');
		
		
		$this->addField('query_parameter')->hint('variable name that searches on site like \'q\' in google');
		$this->addField('paginator_parameter')->hint('variable name that searches on site like \'q\' in google');
		$this->addField('paginator_initial_value')->hint('It should either be 0 or 1 depends on site or your own value to start with');
		$this->addField('records_per_page')->defaultValue(10)->hint('Value to be increased every time a page is searched');
		$this->addField('paginator_based_on')->setValueList(array('records'=>'Records Count','pages'=>'Page Number'))->hint('What paginator system is used by site');
		$this->addField('extra_url_parameters');

		$this->addField('required_pause_between_hits')->hint('Time interval between two consicutive requests in seconds');
		$this->addField('result_selector')->hint('jQuery style selector to look for only, Comma seperated for multiple regions');
		$this->addField('result_format')->enum(array('JSON','HTML'));
		$this->addField('json_url_key')->hint('unescapedUrl');
		$this->addField('reg_ex_on_href')->hint('to strip out extra info a search wngine applying');

		$this->addField('created_at')->type('datetime')->defaultValue(date('Y-m-d H:i:s'))->system(true);
		$this->addField('last_run_at')->type('datetime')->system(true);

		$this->addField('is_active')->type('boolean')->defaultValue(true);
		$this->addExpression('last_run_time')->set('last_run_at');

		$this->addExpression('is_runnable')->set(function($m,$q){
			return "IF((UNIX_TIMESTAMP('".date('Y-m-d H:i:s')."') - UNIX_TIMESTAMP(last_run_at)) > required_pause_between_hits,1,0)";
		});

		$this->hasMany('xMarketingCampaign/DataSearchPhrase','data_grabber_id');
		

		$this->addHook('beforeSave',$this);


		$this->addCondition('epan_id',$this->api->current_website->id);
		$this->add('dynamic_model/Controller_AutoCreator');

	}


	function beforeSave(){
		$this['last_run_at'] = date('Y-m-d H:i:s');
		// if(!$this['required_pause_between_hits'] or $this['required_pause_between_hits'] < 10) $this['required_pause_between_hits'] = 10;
	}
}