<?php

namespace xMarketingCampaign;
class Model_Lead extends \SQL_Model{
	public $table="xmarketingcampaign_leads";
	
	function init(){
		parent::init();


		$this->hasOne('Epan','epan_id');
		$this->addCondition('epan_id',$this->api->current_website->id);
		$this->hasOne('xHR/Employee','created_by_id')->defaultValue($this->api->current_employee->id)->system(true);

		$this->addField('name');
		$this->addField('organization_name');
		$this->addField('email_id');
		$this->addField('status');
		$this->addField('source');
		$this->addField('source_id');
		$this->addField('phone');
		$this->addField('mobile_no');
		$this->addField('fax');
		$this->addField('website');
		$this->addField('lead_type');

		$this->hasMany('xShop/Model_Oppertunity','lead_id');
		$this->hasMany('xShop/Model_Quotation','lead_id');

		$this->add('Controller_Validator');
		$this->is(array(
			'name|to_trim|to_alpha|len|>3?Length must be more then 30 chars',
			'email_id|email|unique?Email not perfact or already used'
			)
		);

		$this->add('dynamic_model/Controller_AutoCreator');
	}
}