<?php

namespace xMarketingCampaign;


class Model_Config extends \Model_Table {
	public $table ='xmarketingcampaign_config';

	function init(){
		parent::init();

		$this->hasOne('Epan','epan_id');
		$this->addCondition('epan_id',$this->api->current_website->id);

		// $this->addField('Campaign_type')->setValueList(array('email'=>'Email','blog'=>'Blogs','social'=>'Social'));
		$this->addField('email_transport')->setValueList(array('smtp'=>'SMTP Transport','sendmail'=>'SendMail','mail'=>'PHP Mail function'))->defaultValue('smtp');
		$this->addField('encryption')->enum(array('none','SSL','TLS'))->mandatory(true);
		$this->addField('email_host');
		$this->addField('email_port');
		$this->addField('email_username');
		$this->addField('email_password')->type('password');
		$this->addField('email_reply_to');
		$this->addField('email_reply_to_name');
		$this->addField('sender_email');
		$this->addField('sender_name');
		$this->addField('smtp_auto_reconnect')->type('int')->hint('Auto Reconnect by n number of emails');
		$this->addField('email_threshold')->type('int')->hint('Threshold To send emails with this Email Configuration PER MINUTE');
		$this->addField('emails_in_BCC')->type('int')->hint('Emails to be sent by bunch of Bcc emails, to will be used same as From, 0 to send each email in to field');
		$this->addField('use_for_domains')->hint('Reserver This Configuration for emails from the domains like "gmail,yahoo,live,hotmail,aol"')->system(true); // Not implemented yet, todo
		$this->addField('is_active')->type('boolean')->defaultValue(true);
		// $this->addField('matter')->type('text')->display(array('form'=>'RichText'))->defaultValue('<p></p>');
			
		$this->add('dynamic_model/Controller_AutoCreator');
	}
}