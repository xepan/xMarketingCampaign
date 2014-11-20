<?php

namespace xMarketingCampaign;


class Model_Config extends \Model_Table {
	public $table ='xmarketingcampaign_config';

	function init(){
		parent::init();

		$this->hasOne('Epan','epan_id');
		$this->addCondition('epan_id',$this->api->current_website->id);

		// $this->addField('Campaign_type')->setValueList(array('email'=>'Email','blog'=>'Blogs','social'=>'Social'));
		$f=$this->addField('email_transport')->setValueList(array('SmtpTransport'=>'SMTP Transport','SendmailTransport'=>'SendMail','MailTransport'=>'PHP Mail function'))->defaultValue('SmtpTransport')->group('a~3~Transport Setting');
		$this->addField('encryption')->enum(array('none','ssl','tls'))->mandatory(true)->group('a~3');
		$this->addField('email_host')->group('a~3');
		$this->addField('email_port')->group('a~3');
		$this->addField('email_username')->group('b~6~Login Credentails');
		$this->addField('email_password')->type('password')->group('b~6');

		$this->addField('email_reply_to')->group('c~4~Header Settings')->hint('email id to be set in reply to');
		$this->addField('email_reply_to_name')->group('c~4~bl');
		$this->addField('from_email')->group('c~4')->hint('email id to show as from');
		$this->addField('from_name')->group('c~4~bl');
		$this->addField('sender_email')->group('c~4')->hint('actual sender email id');
		$this->addField('sender_name')->group('c~4~bl');
		
		$this->addField('return_path')->group('d~3~Throttling')->hint('return email id to collect server responses and bounces');
		$this->addField('smtp_auto_reconnect')->type('int')->hint('Auto Reconnect by n number of emails')->group('d~3');
		$this->addField('email_threshold')->type('int')->hint('Threshold To send emails with this Email Configuration PER MINUTE')->group('d~3');
		$this->addField('emails_in_BCC')->type('int')->hint('Emails to be sent by bunch of Bcc emails, to will be used same as From, 0 to send each email in to field')->defaultValue(0)->system(true)->group('d~3');
		$this->addField('use_for_domains')->hint('Reserver This Configuration for emails from the domains like "gmail,yahoo,live,hotmail,aol"')->system(true); // Not implemented yet, todo
		$this->addField('is_active')->type('boolean')->defaultValue(true)->group('d~3');

		$this->addField('last_engaged_at')->type('datetime')->system(true)->defaultValue(date('Y-m-md'));
		$this->addField('email_sent_in_this_minute')->type('int')->system(true)->defaultValue(0);

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