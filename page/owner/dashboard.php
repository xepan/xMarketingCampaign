<?php

class page_xMarketingCampaign_page_owner_dashboard extends page_xMarketingCampaign_page_owner_main {

	function init(){
		parent::init();	

		$sub_model = $this->add('xEnquiryNSubscription/Model_Subscription');
		$this->app->layout->add('View')->setHTML('Total Grabbed Emails = '.$sub_model->addCondition('from_app','DataGrabberPhrase')->count()->getOne());

		$sub_model = $this->add('xEnquiryNSubscription/Model_Subscription');
		$this->app->layout->add('View')->setHTML('Total Emails = '.$sub_model->addCondition('from_app','xMarketingCampaign')->count()->getOne());
		
		$newsletter_model = $this->add('xEnquiryNSubscription/Model_NewsLetter');
		$this->app->layout->add('View')->setHTML('Total Newsletters = '.$newsletter_model->count()->getOne());
		$this->app->layout->add('View')->setHTML('Total Active Newsletters = '.$newsletter_model->addCondition('is_active',true)->count()->getOne());
		$newsletter_unactive_model = $this->add('xEnquiryNSubscription/Model_NewsLetter');
		$this->app->layout->add('View')->setHTML('Total Unactive Newsletters = '.$newsletter_unactive_model->addCondition('is_active',false)->count()->getOne());
		
		$email_jobs = $this->add('xEnquiryNSubscription/Model_EmailJobs');
		$this->app->layout->add('View')->setHTML("Total Email Jobs = ".$email_jobs->count()->getOne());
		$email_jobs->addExpression('pending_emails')->set(function($m,$q){
			return $m->refSQL('xEnquiryNSubscription/EmailQueue')->addCondition('is_sent',false)->count();
		});
		$this->app->layout->add('View')->setHTML("Pending Email Jobs = ".$email_jobs->addCondition('pending_emails','>','0')->count()->getOne());
		
		//kab konsa newsletter send kara
		$email_queue = $this->add('xEnquiryNSubscription/Model_EmailQueue');
		$queue_job_j = $email_queue->join('xEnquiryNSubscription_EmailJobs','emailjobs_id');
		$queue_job_j->addField('newsletter_id')->system(true);
		$newsletter_j = $queue_job_j->join('xEnquiryNSubscription_NewsLetter','newsletter_id');
		$newsletter_j->addField('name')->caption('Newsletter Title');
		$email_queue->addCondition('sent_at','<',date('Y-m-d H:i:s'));
		$email_queue->addCondition('is_sent',true);
		$email_queue->getElement('email')->system(true);
		$email_queue->setOrder('sent_at','desc');	
		$email_queue->tryLoadAny();
		$email_queue->setLimit(5);
		$v = $this->app->layout->add('View');
		$v->add('View')->set('Kab Konsa NewsLetter kisko send kara')->addClass('label label-info');
		$grid = $v->add('Grid');
		if($email_queue->loaded()){
			$grid->addMethod('format_sentto',function($g,$f){
				$subscription_model = $g->add('xEnquiryNSubscription/Model_Subscription');
				$subscription_model->addCondition('id',$g->model['subscriber_id']);
				$subscription_model->tryLoadAny();
				$sent_to = $subscription_model['email'];
				if(!$g->model['subscriber_id'])
					$sent_to = $g->model['email'];
				$g->current_row_html[$f]=$sent_to; 
			});	

			$grid->addColumn('sentto','Send To');
		}
		$grid->setModel($email_queue,array('sent_to','name','sent_at'));

		//Recent Email Jobs Via xMarketingCampaign
		$email_jobs_model = $this->add('xEnquiryNSubscription/Model_EmailJobs');
		$email_jobs_model->addExpression('pending_emails')->set(function($m,$q){
			return $m->refSQL('xEnquiryNSubscription/EmailQueue')->addCondition('is_sent',false)->count();
		});
		$email_jobs_model->addCondition('pending_emails',0);			
		$email_jobs_model->addCondition('process_via','xMarketingCampaign');
		$email_jobs_model->setOrder('processed_on','desc');	
		$email_jobs_model->tryLoadAny();
		$email_jobs_model->setLimit(5);
		$v = $this->app->layout->add('View');
		$v->add('View')->set('Recent Emails Jobs Completed - Process Set Via Xmarketing Campaign')->addClass('label label-success');
		if($email_jobs_model->loaded()){
			$v->add('Grid')->setModel($email_jobs_model,array('name','job_posted_at','processed_on'));
		}
		
		//Recent Email Jobs via others
		$email_jobs_model = $this->add('xEnquiryNSubscription/Model_EmailJobs');
		$email_jobs_model->addExpression('pending_emails')->set(function($m,$q){
			return $m->refSQL('xEnquiryNSubscription/EmailQueue')->addCondition('is_sent',false)->count();
		});
		$p_email = $email_jobs_model->addCondition('pending_emails',0);
		$email_jobs_model->addCondition($p_email->dsql()->orExpr()
			->where('process_via','<>','xMarketingCampaign')
			->where('process_via',null)
			);
		$email_jobs_model->setOrder('processed_on','desc');	
		$email_jobs_model->setLimit(5);
		$email_jobs_model->tryLoadAny();
		$v = $this->app->layout->add('View');
		$v->add('View')->set('Recent Emails Jobs Completed - Process Set Via Others')->addClass('label label-warning');
		if($email_jobs_model->loaded()){
			$v->add('Grid')->setModel($email_jobs_model,array('name','job_posted_at','processed_on'));
		}
		
		//Recent Social Post
		$v = $this->app->layout->add('View');
		$v->add('View')->set('Recent Social Posts')->addClass('label label-danger');
		$csp_model = $this->add('xMarketingCampaign/Model_CampaignSocialPost');
		$csp_model->addCondition('is_posted',true);
		$csp_model->setOrder('post_on','desc');
		$csp_model->setLimit(5);
		$grid = $v->add('Grid');

		$grid->setModel($csp_model,array('socialpost','post_on_datetime'));
		if($grid->model){
			$grid->model->getElement('Facebook')->system(true);
			$grid->model->getElement('Linkedin')->system(true);
			$grid->model->getElement('GoogleBlogger')->system(true);
			// $grid->model->getElement('Twitter')->system(true);
		}
		$grid->addMethod('format_social',function($g,$f){
			$social_icon="";
			if($g->model['Facebook']){
				$social_icon .='<i class="fa fa-facebook" style="color:blue;"></i> ';
			}
			if($g->model['Linkedin']){
				$social_icon .='<i class="fa fa-linkedin" style="background-color:#00CCFF;color:white;padding:2px;"></i> ';
			}
			if($g->model['Twitter']){
				$social_icon .='<i class="fa fa-twitter" style="color:#55acee;"></i> ';
			}
			if($g->model['GoogleBlogger']){
				$social_icon .='<i class="glyphicon glyphicon-bold" style="background:orange;color:white;padding:2px 0;"></i> ';
			}	
			$g->current_row_html[$f]=$social_icon; 
		});

		$grid->addColumn('social','Post on');

		
		$this->app->layout->add('View')->set('Recent Social Posts Activities')->addClass('label label-default');

		//Next Scheduled Email Job
		$v = $this->app->layout->add('View');
		$v->add('View')->set('Next Scheduled Email Job')->addClass('label label-info');
		$cnl_model = $this->add('xMarketingCampaign/Model_CampaignNewsLetter');
		$cnl_model->_dsql()->having('posting_date','>',date('Y-m-d H:i:s'));
		$cnl_model->setOrder('posting_date','desc');
		$cnl_model->setLimit(5);
		$v->add('Grid')->setModel($cnl_model,array('posting_date','newsletter'));
		
		//Next Scheduled Social Job
		$v = $this->app->layout->add('View');
		$v->add('View')->set('Next Scheduled Social Job')->addClass('label label-success');
		$csp_model = $this->add('xMarketingCampaign/Model_CampaignSocialPost');	
		$csp_model->_dsql()->having('post_on_datetime','>',date('Y-m-d H:i:s'));
		$csp_model->setOrder('post_on_datetime','desc');
		$csp_model->setLimit(5);
		$grid = $v->add('Grid');
		$grid->setModel($csp_model,array('socialpost','post_on_datetime'));
		if($grid->model){
			$grid->model->getElement('Facebook')->system(true);
			$grid->model->getElement('Linkedin')->system(true);
			$grid->model->getElement('GoogleBlogger')->system(true);
			// $grid->model->getElement('Twitter')->system(true);
		}			
		$grid->addMethod('format_social',function($g,$f){
			$social_icon="";
			if($g->model['Facebook']){
				$social_icon .='<i class="fa fa-facebook" style="color:blue;"></i> ';
			}
			if($g->model['Linkedin']){
				$social_icon .='<i class="fa fa-linkedin" style="background-color:#00CCFF;color:white;padding:2px;"></i> ';
			}
			if($g->model['Twitter']){
				$social_icon .='<i class="fa fa-twitter" style="color:#55acee;"></i> ';
			}
			if($g->model['GoogleBlogger']){
				$social_icon .='<i class="glyphicon glyphicon-bold" style="background:orange;color:white;padding:2px 0;"></i> ';
			}	
			$g->current_row_html[$f]=$social_icon; 
		});
		$grid->addColumn('social','Post On');
	}
}		