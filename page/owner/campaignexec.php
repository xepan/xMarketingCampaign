<?php

class page_xMarketingCampaign_page_owner_campaignexec extends page_xMarketingCampaign_page_owner_main{

	public $today=null;

	function init(){
		parent::init();

		$this->today  = date('Y-m-d');

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
			$sent_to=array();
			if($news_letters_model['effective_start_date'] == 'CampaignDate' AND  strtotime($news_letters_model['efective_date']) < strtotime($this->today) ) continue;
			// Get all categories of xEnq Subscription to check subscriptions
			$campain_categories = $this->add('xMarketingCampaign/Model_CampaignSubscriptionCategory');
			$campain_categories->addCondition('campaign_id',$news_letters_model['campaign_id']);
			$categories = array();
			foreach ($campain_categories as $junk2) {
				$categories[] = $junk2['category_id'];
			}

			$candidate_subscribers = $this->add('xEnquiryNSubscription/Model_Subscription');
			$asso_j = $candidate_subscribers->join('xEnquiryNSubscription_SubsCatAss.subscriber_id');
			$asso_j->addField('subscribed_on');

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
			$candidate_subscribers->addCondition('is_this_newsletter_sent',0);
			
			$asso_j = $candidate_subscribers->join('xEnquiryNSubscription_SubsCatAss.subscriber_id');
			$asso_j->addField('category_id');
			$asso_j->addField('send_news_letters');

			$candidate_subscribers->addCondition('category_id',$categories);
			$candidate_subscribers->addCondition('send_news_letters',true);

			$i=0;
			$q=$this->add('xEnquiryNSubscription/Model_EmailQueue');
			foreach ($candidate_subscribers as $junk) {
				if($i==0){
					$new_email_job = $this->add('xEnquiryNSubscription/Model_EmailJobs');
					$new_email_job['newsletter_id'] = $news_letters_model['newsletter_id'];
					$new_email_job['job_posted_at'] = $this->today;
					$new_email_job['process_via'] = 'xMarketingCampaign';

					$new_email_job->save();
				}

				if(in_array($candidate_subscribers['email'], $sent_to)) continue;

				$q['subscriber_id'] = $candidate_subscribers->id;
				$q['emailjobs_id'] = $new_email_job->id;
				$q->saveAndUnload();

				$sent_to[] = $candidate_subscribers['email'];

				$i=1;
				
			}

		}

	}

}		