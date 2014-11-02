<?php

namespace xMarketingCampaign;


class Model_Config extends \Model_Table {
	public $table ='xmarketingcampaign_config';

	function init(){
		parent::init();

		$this->hasOne('Epan','epan_id');
		$this->addCondition('epan_id',$this->api->current_website->id);

		// $this->addField('Campaign_type')->setValueList(array('email'=>'Email','blog'=>'Blogs','social'=>'Social'));
		$this->addField('email_transport')->setValueList(array('SmtpTransport'=>'SMTP Transport','SendmailTransport'=>'SendMail','MailTransport'=>'PHP Mail function'))->defaultValue('smtp');
		$this->addField('encryption')->enum(array('none','ssl','tls'))->mandatory(true);
		$this->addField('email_host');
		$this->addField('email_port');
		$this->addField('email_username');
		$this->addField('email_password')->type('password');
		$this->addField('email_reply_to');
		$this->addField('email_reply_to_name');
		$this->addField('from_email');
		$this->addField('from_name');
		$this->addField('sender_email');
		$this->addField('sender_name');
		$this->addField('return_path');
		$this->addField('smtp_auto_reconnect')->type('int')->hint('Auto Reconnect by n number of emails');
		$this->addField('email_threshold')->type('int')->hint('Threshold To send emails with this Email Configuration PER MINUTE');
		$this->addField('emails_in_BCC')->type('int')->hint('Emails to be sent by bunch of Bcc emails, to will be used same as From, 0 to send each email in to field')->defaultValue(0)->system(true);
		$this->addField('use_for_domains')->hint('Reserver This Configuration for emails from the domains like "gmail,yahoo,live,hotmail,aol"')->system(true); // Not implemented yet, todo
		$this->addField('is_active')->type('boolean')->defaultValue(true);

		$this->addField('last_engaged_at')->type('datetime')->system(true);
		$this->addField('email_sent_in_this_minute')->type('int')->system(true);

		// $this->addField('matter')->type('text')->display(array('form'=>'RichText'))->defaultValue('<p></p>');
			
		$this->addHook('beforeSave',$this);

		// $this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeSave(){
		if($this['email_transport']=='SmtpTransport'){
			if(!$this['email_host']) throw $this->exception('Host is must','ValidityCheck')->setField('email_host');
			if(!$this['email_port']) throw $this->exception('Host is must','ValidityCheck')->setField('email_port');
			if(!$this['email_username']) throw $this->exception('Host is must','ValidityCheck')->setField('email_username');
			if(!$this['email_password']) throw $this->exception('Host is must','ValidityCheck')->setField('email_password');
			if(!$this['email_reply_to']) throw $this->exception('Host is must','ValidityCheck')->setField('email_reply_to');
			if(!$this['email_reply_to_name']) throw $this->exception('Host is must','ValidityCheck')->setField('email_reply_to_name');
			if(!$this['sender_email']) throw $this->exception('Host is must','ValidityCheck')->setField('sender_email');
			if(!$this['sender_name']) throw $this->exception('Host is must','ValidityCheck')->setField('sender_name');
		}
	}

}