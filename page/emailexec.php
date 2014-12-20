<?php

class page_xMarketingCampaign_page_emailexec extends Page {

	function init(){
		parent::init();

		include_once('lib/Swift/swift_init.php');

		$email_job = $this->add('xEnquiryNSubscription/Model_EmailJobs');
		$email_job->addExpression('un_processed_job')->set(function($m,$q){
			return $m->refSQL('xEnquiryNSubscription/EmailQueue')->addCondition('is_sent',false)->count(true);
		});

		$email_job->addCondition('un_processed_job','>',0);
		$email_job->setOrder('job_posted_at','asc');

		$email_job->addCondition('process_via','xMarketingCampaign');

		$email_job->tryLoadAny();

		if(!$email_job->loaded()){
			$this->add('View_Info')->set('No Job Due');
			return;
		}



		$email_setting_for_this_minute = $this->add('xMarketingCampaign/Model_Config');
		$email_setting_for_this_minute->addExpression('remaining_emails_in_this_minute')->set('(IF(TIMESTAMPDIFF(MINUTE,last_engaged_at,"'. date('Y-m-d H:i:s') .'") > 0, email_threshold, email_threshold - email_sent_in_this_minute))');
		$email_setting_for_this_minute->addCondition('remaining_emails_in_this_minute','>',0);
		$email_setting_for_this_minute->addCondition('is_active',true);
		$email_setting_for_this_minute->setOrder('remaining_emails_in_this_minute','desc');
		$email_setting_for_this_minute->tryLoadAny();

		// Visual patch
		// send start_next=1 from cron url always
		if($email_setting_for_this_minute->loaded() and !$_GET['start_next']){
			$this->add('H3')->set('Running Next');
			$this->js(true)->reload(array('start_next'=>1));
			return;
		}

		if(!$email_setting_for_this_minute->loaded()){
			$this->add('View_Error')->set('All Email Server Used for this minute, Trying a bit later');
			$this->js(true)->reload();
			return;
		}

		$news_letter = $email_job->ref('newsletter_id');
		$job_queue_count = $email_job['un_processed_job'];
		$email_queue = $this->add('xEnquiryNSubscription/Model_EmailQueue');
		$email_queue->addCondition('emailjobs_id',$email_job->id);
		$email_queue->addCondition('is_sent',false);
		
		// $email_queue->addCondition('subscriber','not like',"%@gmail.");
		// $email_queue->addCondition('subscriber','not like',"%@yahoo.");
		// $email_queue->addCondition('subscriber','not like',"%@aol.");
		// $email_queue->addCondition('subscriber','not like',"%@live.");
		// $email_queue->addCondition('subscriber','not like',"%@hotmail.");
		$email_queue->setLimit($email_setting_for_this_minute['remaining_emails_in_this_minute']);


		echo "Email Setting taken " . $email_setting_for_this_minute['email_username'];

		switch ($email_setting_for_this_minute['email_transport']) {
			case 'SmtpTransport':
				$transport = Swift_SmtpTransport::newInstance($email_setting_for_this_minute['email_host'],$email_setting_for_this_minute['email_port'],$email_setting_for_this_minute['encryption']!='none'?$email_setting_for_this_minute['encryption']:null);
				$transport->setUsername($email_setting_for_this_minute['email_username']);
				$transport->setPassword($email_setting_for_this_minute['email_password']);


				break;
			case 'SendmailTransport':
				$transport = Swift_SendmailTransport::newInstance();
				break;
			case 'MailTransport':
				$transport = Swift_MailTransport::newInstance();
				break;
			
			default:
				# code...
				break;
		}

		$mailer = Swift_Mailer::newInstance($transport);

		if($email_setting_for_this_minute['smtp_auto_reconnect']){
			$mailer->registerPlugin(new Swift_Plugins_AntiFloodPlugin($email_setting_for_this_minute['smtp_auto_reconnect']));
		}

		if($email_setting_for_this_minute['email_threshold']){
			$mailer->registerPlugin(new Swift_Plugins_ThrottlerPlugin($email_setting_for_this_minute['email_threshold'] , Swift_Plugins_ThrottlerPlugin::MESSAGES_PER_MINUTE));
		}


		$message = Swift_Message::newInstance($news_letter['email_subject'])
		  ->setFrom(array($email_setting_for_this_minute['sender_email'] => $email_setting_for_this_minute['sender_name']))
		  ->setReplyTo(array($email_setting_for_this_minute['email_reply_to'] => $email_setting_for_this_minute['email_reply_to_name']))
		  ;

		  if($email_setting_for_this_minute['return_path']){
		  	$message->setReturnPath($email_setting_for_this_minute['return_path']);
		  }

		$email_body = $news_letter['matter'];
		$email_body = $this->convertImagesInline($message,$email_body);


		$failed =array();
		$sent =0;
		$i=1;
		foreach ($email_queue as $junk) {	
		  	
		  	$to_email = $junk['email']?:$junk['subscriber'];

		  	$email_body = str_replace("{{email}}", $to_email, $email_body);

		  	try{
		  		$message->setTo($to_email);
		  	}catch(Exception $e){
		  		// continue;
		  	}
		  	$message->setBody($email_body,'text/html');

		  	// $start = microtime(true);
			$sent_this =  $mailer->send($message, $failed);
			if(!$sent_this){
				// This is not actually bounced. keep it a separate portion
				$email_queue->ref('subscriber_id')->set('is_bounced',true)->saveAndUnload();
			}else{
				$sent += $sent_this;
			}
			// echo "Sent $i th email in ". (microtime(true) - $start) . ' seconds <br/>';

			$email_queue['is_sent']=true;
			$email_queue->ref('emailjobs_id')->set('processed_on',date('Y-m-d H:i:s'))->save();
			$email_queue->saveAndUnload();
			print_r($failed);
			// $bogus_emails = $this->add('xEnquiryNSubscription/Model_Subscription')->addCondition('email',$failed)->tryDeleteAll();
		}

		if(strtotime(date('Y-m-d H:i:0',strtotime($email_setting_for_this_minute['last_engaged_at']))) == strtotime(date('Y-m-d H:i:0',strtotime(date('Y-m-d H:i:s'))))){
			$email_setting_for_this_minute['email_sent_in_this_minute'] = $email_setting_for_this_minute['email_sent_in_this_minute'] + $sent;
		}else{
			$email_setting_for_this_minute['email_sent_in_this_minute'] = $sent;
		}

		$email_setting_for_this_minute['last_engaged_at'] = date('Y-m-d H:i:s');
		$email_setting_for_this_minute->saveAndUnload();

		if($sent)
			$this->add('View_Info')->set($sent.' Emailes Sent');
		else
			$this->add('View_Error');

		$this->js(true)->reload();

	}

	function convertImagesInline(&$message, &$body){
        // get all img tags
        preg_match_all('/<img.*?>/', $body, $matches);
        if (!isset($matches[0])) return;
        // foreach tag, create the cid and embed image
        foreach ($matches[0] as $img)
        {
            // make cid
            // $id = 'img'.($i++);
            // replace image web path with local path
            preg_match('/src="(.*?)"/', $img, $m);
            if (!isset($m[1])) continue;
            $arr = parse_url($m[1]);
            if (isset($arr['host'])) continue;
            // add
            $cid = $message->embed(Swift_Image::fromPath(getcwd().'/'.$arr['path']));
            $body = str_replace($img, '<img alt="" src="'.$cid.'" style="border: none;" />', $body); 
        }
        return $body;
    }

}
	