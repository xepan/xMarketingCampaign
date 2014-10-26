<?php

class page_xMarketingCampaign_page_owner_campaignexec extends page_componentBase_page_owner_main{

	public $today=null;

	function init(){
		parent::init();

		$this->today  =date('Y-m-d');
		if($_GET['today']){
			$this->today=$_GET['today'];
			
		}

		$this->emailExec();

	}

	function emailExec(){
		$this->add('View_Info')->set('Comapign Executing.......wait');

		$news_letters_model = $this->add('xMarketingCampaign/Model_CampaignNewsLetter');
		$campaign_j = $news_letters_model->join('xmarketingcampaign_campaigns','campaign_id');
		$campaign_j->addField('is_active');
		$campaign_j->addField('starting_date');
		$campaign_j->addField('ending_date');
		$campaign_j->addField('effective_start_date');

		$news_letters_model->addExpression('efective_date')->set(function($m,$q){
			return "(DATE_ADD(_x.starting_date, INTERVAL ".$q->getField('duration')." DAY))";
		});

		$news_letters_model->addCondition('is_active',true);
		$news_letters_model->addCondition('ending_date','>', date('Y-m-d H:i:s'));

		foreach ($news_letters_model as $junk) {
			if($news_letters_model['effective_start_date'] == 'CampaignDate' AND  strtotime($news_letters_model['efective_date']) < strtotime($this->today) ) continue;
			// Get all categories of xEnq Subscription to check subscriptions
			$campain_categories = $this->add('xMarketingCampaign/Model_CampaignSubscriptionCategory');
			$campain_categories->addCondition('campaign_id',$news_letters_model['campaign_id']);
			$categories = array();
			foreach ($campain_categories as $junk2) {
				$categories[] = $junk2['category_id'];
			}

			$candidate_subscribers = $this->add('xEnquiryNSubscription/Model_Subscription');
			$candidate_subscribers->addExpression('is_this_newsletter_sent')->set(function($m,$q)use($junk){
				$email_job = $m->add('xEnquiryNSubscription/Model_EmailJobs',array('table_alias'=>'ej'));
				$email_que_j = $email_job->join('xEnquiryNSubscription_EmailQueue.emailjobs_id');
				$email_que_j->addField('subscriber_id');
				$email_job->addCondition('subscriber_id',$q->getField('id'));
				$email_job->addCondition('newsletter_id',$junk['newsletter_id']);
				return $email_job->count();

			});

			if($news_letters_model['effective_start_date'] == 'CampaignDate'){
				$candidate_subscribers->addExpression('age_of_registration')->set('DATEDIFF("'.$this->today.'","'.$news_letters_model['starting_date'].'")');
				$candidate_subscribers->addCondition('subscribed_on','<=',date("Y-m-d", strtotime(date("Y-m-d", strtotime($this->today)) . " +1 DAY")));
			}else{
				$candidate_subscribers->addExpression('age_of_registration')->set('DATEDIFF("'.$this->today.'",subscribed_on)');
			}

			$candidate_subscribers->addCondition('age_of_registration','>=',$news_letters_model['duration']);
			$candidate_subscribers->addCondition('category_id',$categories);
			$candidate_subscribers->addCondition('is_this_newsletter_sent',0);

			$i=0;
			$q=$this->add('xEnquiryNSubscription/Model_EmailQueue');
			foreach ($candidate_subscribers->debug() as $junk) {
				if($i==0){
					$new_email_job = $this->add('xEnquiryNSubscription/Model_EmailJobs');
					$new_email_job['newsletter_id'] = $news_letters_model['newsletter_id'];
					$new_email_job['job_posted_at'] = $this->today;
					$new_email_job->save();
				}

				$q['subscriber_id'] = $candidate_subscribers->id;
				$q['emailjobs_id'] = $new_email_job->id;
				$q->saveAndUnload();

				$i=1;
				
			}

		}

	}

	// function emailExec(){
	// 	$this->add('View_Info')->set('Comapign Executing.......wait');

	// 	$news_letters_model = $this->add('xMarketingCampaign/Model_CampaignNewsLetter');
	// 	$campaign_j = $news_letters_model->join('xmarketingCampaign_Campaigns','Campaign_id');
	// 	$campaign_j->addField('is_active');
	// 	$campaign_j->addField('starting_date');
	// 	$campaign_j->addField('ending_date');
	// 	$campaign_j->addField('effective_start_date');

	// 	$news_letters_model->addCondition('is_active',true);
	// 	$news_letters_model->addCondition('ending_date','>', date('Y-m-d H:i:s'));

	// 	$sent_in_here=array();

	// 	foreach ($news_letters_model as $junk) {
	// 		// Get all categories of xEnq Subscription to check subscriptions
	// 		$campain_categories = $this->add('xMarketingCampaign/Model_CampaignSubscriptionCategory');
	// 		$campain_categories->addCondition('Campaign_id',$news_letters_model['Campaign_id']);
	// 		$categories = array();
	// 		foreach ($campain_categories as $junk) {
	// 			$categories[] = $junk['category_id'];
	// 		}

	// 		// Get candidates from selected categories satsifing conditions
	// 		$candidate_subscribers = $this->add('xEnquiryNSubscription/Model_Subscription');
	// 		// If already has some emails sent to this ???
	// 		$sent_emails_to_this_j = $candidate_subscribers->leftJoin('xEnquiryNSubscription_EmailQueue.subscriber_id');
	// 		// Of ehich email job (newsletter_id to check)
	// 		$email_job_j = $sent_emails_to_this_j->leftJoin('xEnquiryNSubscription_EmailJobs','emailjobs_id');
	// 		// news letter must not be sent IF ONLY FROM THE SAME CAMPAIGN
	// 		// TODO ??? no id of campaign in email job

	// 		$email_job_j->addField('newsletter_id');

	// 		// $candidate_subscribers->setOrder('job_posted_at');
	// 		$candidate_subscribers->_dsql()->group('email,newsletter_id');
			
	// 		if($news_letters_model['effective_start_date'] == 'CampaignDate'){
	// 			$candidate_subscribers->addExpression('age_of_registration')->set('DATEDIFF("'.$this->today.'","'.$news_letters_model['starting_date'].'")');
	// 		}else{
	// 			$candidate_subscribers->addExpression('age_of_registration')->set('DATEDIFF("'.$this->today.'",subscribed_on)');
	// 		}
			
	// 		// $candidate_subscribers->addCondition('newsletter_id',$news_letters_model['newsletter_id']);
	// 		$candidate_subscribers->addCondition('category_id',$categories);
	// 		$candidate_subscribers->addCondition('age_of_registration','>=',$news_letters_model['duration']);
	// 		$candidate_subscribers->addCondition('newsletter_id','<>',$news_letters_model['newsletter_id']);

	// 		// $candidate_subscribers->_dsql()->where('((`job_posted_at` < "'.date('Y-m-d',strtotime($this->today.' -'.$news_letters_model['duration'] .' DAY')).'" AND`newsletter_id` = '.$news_letters_model['newsletter_id'] .') OR newsletter_id is null )');

	// 		if($candidate_subscribers->debug()->count()->getOne() > 0) {
	// 			$new_email_job = $this->add('xEnquiryNSubscription/Model_EmailJobs');
	// 			$new_email_job['newsletter_id'] = $news_letters_model['newsletter_id'];
	// 			$new_email_job['job_posted_at'] = $this->today;
	// 			$new_email_job->save();

	// 			$q=$this->add('xEnquiryNSubscription/Model_EmailQueue');
	// 			foreach ($candidate_subscribers as $junk) {
	// 				if(!in_array($candidate_subscribers['email'] .' - '. $news_letters_model->ref('newsletter_id')->get('name'),$sent_in_here)){
	// 					$q['subscriber_id'] = $candidate_subscribers->id;
	// 					$q['emailjobs_id'] = $new_email_job->id;
	// 					$q->saveAndUnload();
	// 					echo $news_letters_model->ref('newsletter_id')->get('name') .' to ' . $candidate_subscribers['email'] . '<br/>';
	// 					$sent_in_here[] = $candidate_subscribers['email'] .' - '. $news_letters_model->ref('newsletter_id')->get('name');
	// 				}
	// 			}

	// 			if($new_email_job->ref('xEnquiryNSubscription/EmailQueue')->count()->getOne() == 0)
	// 				$new_email_job->delete();

	// 		}


	// 	}
	// 		// print_r($sent_in_here);


	// }

}		