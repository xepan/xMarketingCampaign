<?php

namespace xMarketingCampaign;


class Model_Config extends \Model_Table {
	public $table ='xmarketingcampaign_config';

	function init(){
		parent::init();

		$this->hasOne('Epan','epan_id');
		$this->addCondition('epan_id',$this->api->current_website->id);

		// $this->addField('Campaign_type')->setValueList(array('email'=>'Email','blog'=>'Blogs','social'=>'Social'));
		$this->addField('email_host');
		$this->addField('email_port');
		$this->addField('email_username');
		$this->addField('email_password');
		$this->addField('email_reply_to');
		$this->addField('email_reply_to_name');
		$this->addField('sender_email');
		$this->addField('sender_name');
		$this->addField('email_threshold');
		$this->addField('apply_to_all')->type('boolean');
		// $this->addField('matter')->type('text')->display(array('form'=>'RichText'))->defaultValue('<p></p>');
			
		$this->add('dynamic_model/Controller_AutoCreator');
	}
}