<?php

class page_xMarketingCampaign_page_owner_emailcontacts extends page_xMarketingCampaign_page_owner_main{

	function init(){
		parent::init();
		
		$total_phrases_vp = $this->total_phrases_vp();
		$un_grabbed_phrases_vp = $this->un_grabbed_phrases_vp();
		$total_assos_emails_vp = $this->total_assos_emails_vp();
		$grabbed_emails_vp = $this->grabbed_emails_vp();
		$other_email_by_other_apps_vp = $this->other_email_by_other_apps_vp();
		$bounced_email_vp = $this->bounced_email_vp();

		$this->app->layout->template->trySetHTML('page_title','<i class="fa fa-slideshare"></i> '.$this->component_name. '<small> Emails Data Management</small>');

		$email_category_model = $this->add('xEnquiryNSubscription/Model_SubscriptionCategories');
		$email_category_model->hasMany('xMarketingCampaign/DataSearchPhrase','subscription_category_id');



		$bg=$this->app->layout->add('View_BadgeGroup');
		$data =$this->add('xMarketingCampaign/Model_DataSearchPhrase')->count()->getOne();
		$v=$bg->add('View_Badge')->set('Total Phrases')->setCount($data)->setCountSwatch('ink');
		
		$data =$this->add('xMarketingCampaign/Model_DataSearchPhrase')->addCondition('is_grabbed',false)->count()->getOne();
		$v=$bg->add('View_Badge')->set('Un Grabbed Phrases')->setCount($data)->setCountSwatch('red');
		
		$data =$this->add('xEnquiryNSubscription/Model_Subscription')->count()->getOne();
		$v=$bg->add('View_Badge')->set('Total Emails')->setCount($data)->setCountSwatch('ink');
		
		$data =$this->add('xEnquiryNSubscription/Model_Subscription')->addCondition('from_app','DataGrabberPhrase')->count()->getOne();
		$v=$bg->add('View_Badge')->set('Total Grabbed Emails')->setCount($data)->setCountSwatch('green');

		$data =$this->add('xEnquiryNSubscription/Model_Subscription')->addCondition('from_app','DataGrabberPhrase')->addCondition('is_ok',false)->count()->getOne();
		$v=$bg->add('View_Badge')->set('Total Bounced Emails')->setCount($data)->setCountSwatch('red');

		$data =$this->add('xEnquiryNSubscription/Model_Subscription')->addCondition('from_app','<>','DataGrabberPhrase')->count()->getOne();
		$v=$bg->add('View_Badge')->set('Total Other Emails')->setCount($data)->setCountSwatch('ink');

		$email_category_model->addExpression('total_phrases')->set(function($m,$q){
			return $m->refSQL('xMarketingCampaign/DataSearchPhrase')->count();
		})->type('int')->sortable(true);

		$email_category_model->addExpression('un_grabbed_phrases')->set(function($m,$q){
			return $m->refSQL('xMarketingCampaign/DataSearchPhrase')->addCondition('is_grabbed',false)->count();
		})->type('int');

		$email_category_model->addExpression('grabbed_emails')->set(function($m,$q){

			$emails = $m->add('xEnquiryNSubscription/Model_Subscription',array('table_alias'=>'subs'));
			$emails->addCondition('from_app','DataGrabberPhrase');
			$phrase_j = $emails->leftJoin('xMarketingCampaign_data_search_phrase','from_id');
			$phrase_j->addField('subscription_category_id');
			$emails->addCondition('subscription_category_id',$q->getField('id'));

			return $emails->count();
		});

		$email_category_model->addExpression('emails_by_other_apps')->set(function($m,$q){
			
			$mq=$m->add('xEnquiryNSubscription/Model_Subscription',array('table_alias'=>'tmqa'));
			// $s_j=$mq->join('xEnquiryNSubscription_Subscription','subscriber_id');
			$as_j=$mq->join('xEnquiryNSubscription_SubsCatAss.subscriber_id');
			$as_j->addField('category_id');

			$mq->addCondition('category_id',$q->getField('id'));
			$mq->addCondition('from_app','<>','DataGrabberPhrase');
			return $mq->count();

		});

		$email_category_model->addExpression('bounced_emails')->set(function($m,$q){
			
			$mq=$m->add('xEnquiryNSubscription/Model_Subscription',array('table_alias'=>'tmqa'));
			// $s_j=$mq->join('xEnquiryNSubscription_Subscription','subscriber_id');
			$as_j=$mq->join('xEnquiryNSubscription_SubsCatAss.subscriber_id');
			$as_j->addField('category_id');
			$mq->addCondition('category_id',$q->getField('id'));
			$mq->addCondition('is_ok',false);
			return $mq->count();

		});

		$crud = $this->app->layout->add('CRUD');
		$crud->setModel($email_category_model,array('name','is_active','total_phrases','un_grabbed_phrases','total_emails','grabbed_emails','emails_by_other_apps','bounced_emails'));

		if(!$crud->isEditing()){
			$g=$crud->grid;	
			$g->addButton('Jump to Data Grabber ...')->js('click')->redirect($this->api->url('xMarketingCampaign_page_owner_mrkt_dtgrb_dtgrb'));
			$crud->add_button->setIcon('ui-icon-plusthick');

			$g->addMethod('format_total_phrases',function($g,$f)use($total_phrases_vp){
				$g->current_row_html[$f]= '<a href="javascript:void(0)" onclick="'.$g->js()->univ()->frameURL('Phrases For "'.$g->model['name'].'"',$g->api->url($total_phrases_vp->getURL(),array('category_id'=>$g->model->id))).'">'.$g->current_row[$f].'</a>';
			});
			$g->addFormatter('total_phrases','total_phrases');

			$g->addMethod('format_ungrabbed_phrases',function($g,$f)use($un_grabbed_phrases_vp){
				$g->current_row_html[$f]= '<a href="javascript:void(0)" onclick="'.$g->js()->univ()->frameURL('Phrases For "'.$g->model['name'].'"',$g->api->url($un_grabbed_phrases_vp->getURL(),array('category_id'=>$g->model->id))).'">'.$g->current_row[$f].'</a>';
			});
			$g->addFormatter('un_grabbed_phrases','ungrabbed_phrases');

			$g->addMethod('format_total_assos_emails',function($g,$f)use($total_assos_emails_vp){
				$g->current_row_html[$f]= '<a href="javascript:void(0)" onclick="'.$g->js()->univ()->frameURL('Emails Associated in "'.$g->model['name'].'"',$g->api->url($total_assos_emails_vp->getURL(),array('category_id'=>$g->model->id))).'">'.$g->current_row[$f].'</a>';
			});
			$g->addFormatter('total_emails','total_assos_emails');

			$g->addMethod('format_grabbed_emails',function($g,$f)use($grabbed_emails_vp){
				$g->current_row_html[$f]= '<a href="javascript:void(0)" onclick="'.$g->js()->univ()->frameURL('Grabbed Emails in "'.$g->model['name'].'"',$g->api->url($grabbed_emails_vp->getURL(),array('category_id'=>$g->model->id))).'">'.$g->current_row[$f].'</a>';
			});
			$g->addFormatter('grabbed_emails','grabbed_emails');

			$g->addMethod('format_other_email_by_other_apps',function($g,$f)use($other_email_by_other_apps_vp){
				$g->current_row_html[$f]= '<a href="javascript:void(0)" onclick="'.$g->js()->univ()->frameURL('Other Emails in "'.$g->model['name'].'"',$g->api->url($other_email_by_other_apps_vp->getURL(),array('category_id'=>$g->model->id))).'">'.$g->current_row[$f].'</a>';
			});
			$g->addFormatter('emails_by_other_apps','other_email_by_other_apps');

			$g->addMethod('format_bounced_emails',function($g,$f)use($bounced_email_vp){
				$g->current_row_html[$f]= '<a href="javascript:void(0)" onclick="'.$g->js()->univ()->frameURL('Total Bounced Emails in "'.$g->model['name'].'"',$g->api->url($bounced_email_vp->getURL(),array('category_id'=>$g->model->id))).'">'.$g->current_row[$f].'</a>';
			});
			$g->addFormatter('bounced_emails','bounced_emails');


		}

		// $crud->add('Controller_FormBeautifier');
		
	}

	function bounced_email_vp(){
		$other_email_by_other_apps = $this->add('VirtualPage')->set(function($p){
			$cat_id = $p->api->stickyGET('category_id');

			$emails = $p->add('xEnquiryNSubscription/Model_Subscription',array('table_alias'=>'subs'));
			$assos_j = $emails->join('xEnquiryNSubscription_SubsCatAss.subscriber_id');
			$assos_j->addField('category_id');
			$emails->addCondition('category_id',$_GET['category_id']);
			$emails->addCondition('is_ok',false);

			$grid = $p->add('Grid');
			$grid->setModel($emails,array('email','is_ok','created_at'));
			$grid->addPaginator(100);
			$grid->addQuickSearch(array('email'));
			$grid->add_sno();

			$grid->addMethod('format_weblink',function($g,$f){
				preg_match_all("/@(.*)$/", $g->current_row[$f],$weblink);
				// $g->current_row_html[$f] = print_r($weblink[1],true);
				$g->current_row_html[$f]= '<a href="http://'.$weblink[1][0].'" target="_blank"> '.$g->current_row[$f].' </a>';
			});
			$grid->addFormatter('email','weblink');


		});	
		return $other_email_by_other_apps;		
	}

	function other_email_by_other_apps_vp(){
		$other_email_by_other_apps = $this->add('VirtualPage')->set(function($p){
			$cat_id = $p->api->stickyGET('category_id');

			$emails = $p->add('xEnquiryNSubscription/Model_Subscription',array('table_alias'=>'subs'));
			$emails->addCondition('from_app','<>','DataGrabberPhrase');
			$assos_j = $emails->join('xEnquiryNSubscription_SubsCatAss.subscriber_id');
			$assos_j->addField('category_id');
			$emails->addCondition('category_id',$_GET['category_id']);

			$grid = $p->add('Grid');
			$grid->setModel($emails,array('email','is_ok','created_at'));
			$grid->addPaginator(100);
			$grid->addQuickSearch(array('email'));
			$grid->add_sno();

			$grid->addMethod('format_weblink',function($g,$f){
				preg_match_all("/@(.*)$/", $g->current_row[$f],$weblink);
				// $g->current_row_html[$f] = print_r($weblink[1],true);
				$g->current_row_html[$f]= '<a href="http://'.$weblink[1][0].'" target="_blank"> '.$g->current_row[$f].' </a>';
			});
			$grid->addFormatter('email','weblink');


		});	
		return $other_email_by_other_apps;		
	}

	function grabbed_emails_vp(){

		$grabbed_emails = $this->add('VirtualPage')->set(function($p){
			$cat_id = $p->api->stickyGET('category_id');

			$emails = $p->add('xEnquiryNSubscription/Model_Subscription',array('table_alias'=>'subs'));
			$emails->addCondition('from_app','DataGrabberPhrase');
			$phrase_j = $emails->leftJoin('xMarketingCampaign_data_search_phrase','from_id');
			$phrase_j->addField('subscription_category_id');
			$emails->addCondition('subscription_category_id',$_GET['category_id']);

			$grid = $p->add('Grid');
			$grid->setModel($emails,array('email','is_ok','created_at'));
			$grid->addPaginator(100);
			$grid->addQuickSearch(array('email'));
			$grid->add_sno();

			$grid->addMethod('format_weblink',function($g,$f){
				preg_match_all("/@(.*)$/", $g->current_row[$f],$weblink);
				// $g->current_row_html[$f] = print_r($weblink[1],true);
				$g->current_row_html[$f]= '<a href="http://'.$weblink[1][0].'" target="_blank"> '.$g->current_row[$f].' </a>';
			});
			$grid->addFormatter('email','weblink');


		});	
		return $grabbed_emails;		
	}

	function total_phrases_vp(){

		$content_vp = $this->add('VirtualPage')->set(function($p){
			$p->api->stickyGET('phrase_id');
			$m=$p->add('xMarketingCampaign/Model_DataSearchPhrase')->load($_GET['phrase_id']);
			$p->add('View')->setHTML($m['content_provided']);
		});

		$total_phrases_vp = $this->add('VirtualPage')->set(function($p)use($content_vp){
			$p->api->stickyGET('category_id');

			$m=$p->add('xMarketingCampaign/Model_DataSearchPhrase');
			$m->addCondition('subscription_category_id',$_GET['category_id']);

			$m->addExpression('emails_fetched')->set(function($m,$q){
				return $m->refSQL('xEnquiryNSubscription/Model_Subscription')->addCondition('from_app','DataGrabberPhrase')->count();
			});

			$m->addExpression('bounced')->set(function($m,$q){
				return $m->refSQL('xEnquiryNSubscription/Model_Subscription')
					->addCondition('from_app','DataGrabberPhrase')
					->addCondition('is_ok',false)
					->count();
			});

			$un_grb_m=$p->add('xMarketingCampaign/Model_DataSearchPhrase');
			$un_grb_m->addCondition('subscription_category_id',$_GET['category_id']);
			$un_grb_m->addCondition('is_grabbed',false);

			$bg=$p->add('View_BadgeGroup');
			$v=$bg->add('View_Badge')->set('Un-Grabbed')->setCount((string)$un_grb_m->count())->setCountSwatch('red');

			$grid = $p->add('Grid');
			$grid->setModel($m,array('data_grabber','is_grabbed','name','content_provided','emails_fetched','bounced'));
			$grid->addPaginator(100);
			$grid->addQuickSearch(array('name'));

			$grid->addMethod('format_content',function($g,$f)use($content_vp){
				$g->current_row_html[$f]= '<a href="javascript:void(0)" onclick="'.$g->js()->univ()->frameURL('Content For "'.$g->model['name'].'"',$this->api->url($content_vp->getURL(),array('phrase_id'=>$g->model->id))).'"> Content </a>';
			});
			$grid->setFormatter('content_provided','content');

		});	
		return $total_phrases_vp;
	}

	function total_assos_emails_vp(){

		$content_vp = $this->add('VirtualPage')->set(function($p){
			$p->api->stickyGET('phrase_id');
			$m=$p->add('xMarketingCampaign/Model_DataSearchPhrase')->load($_GET['phrase_id']);
			$p->add('View')->setHTML($m['content_provided']);
		});

		$total_assos_emails_vp = $this->add('VirtualPage')->set(function($p)use($content_vp){
			$p->api->stickyGET('category_id');
			$m=$p->add('xEnquiryNSubscription/Model_SubscriptionCategoryAssociation');

			$m->addCondition('category_id',$_GET['category_id']);

			$grid = $p->add('Grid');
			$grid->setModel($m,array('subscriber','last_updated_on','send_news_letters','unsubscribed_on','from_app'));
			$grid->addPaginator(100);
			$grid->addQuickSearch(array('subscriber'));
			$grid->add_sno();

		});	
		return $total_assos_emails_vp;
	}

	function un_grabbed_phrases_vp(){

		$content_vp = $this->add('VirtualPage')->set(function($p){
			$p->api->stickyGET('phrase_id');
			$m=$p->add('xMarketingCampaign/Model_DataSearchPhrase')->load($_GET['phrase_id']);
			$p->add('View')->setHTML($m['content_provided']);
		});

		$un_grabbed_phrases_vp = $this->add('VirtualPage')->set(function($p)use($content_vp){
			$p->api->stickyGET('category_id');

			$m=$p->add('xMarketingCampaign/Model_DataSearchPhrase');
			$m->addCondition('subscription_category_id',$_GET['category_id']);
			$m->addCondition('is_grabbed',false);

			$m->addExpression('emails_fetched')->set(function($m,$q){
				return $m->refSQL('xEnquiryNSubscription/Model_Subscription')->addCondition('from_app','DataGrabberPhrase')->count();
			});

			$m->addExpression('bounced')->set(function($m,$q){
				return $m->refSQL('xEnquiryNSubscription/Model_Subscription')
					->addCondition('from_app','DataGrabberPhrase')
					->addCondition('is_ok',false)
					->count();
			});


			$grid = $p->add('Grid');
			$grid->setModel($m,array('data_grabber','is_grabbed','name','content_provided','emails_fetched','bounced'));
			$grid->addPaginator(100);
			$grid->addQuickSearch(array('name'));

			$grid->addMethod('format_content',function($g,$f)use($content_vp){
				$g->current_row_html[$f]= '<a href="javascript:void(0)" onclick="'.$g->js()->univ()->frameURL('Content For "'.$g->model['name'].'"',$this->api->url($content_vp->getURL(),array('phrase_id'=>$g->model->id))).'"> Content </a>';
			});
			$grid->setFormatter('content_provided','content');
		});	
		return $un_grabbed_phrases_vp;
	}

	function page_emails(){
		$group_id = $this->api->stickyGET('xEnquiryNSubscription_Subscription_Categories_id');
		$subs_crud = $this->add('CRUD');
		$cat_sub_model = $this->add('xEnquiryNSubscription/Model_SubscriptionCategoryAssociation')->addCondition('category_id',$group_id);

		$tmp = $cat_sub_model->getElement('subscriber_id')->getModel();
		$tmp->getElement('from_app')->defaultValue('xMarketingCampaign');

		$subs_crud->setModel($cat_sub_model);

		// if($subs_crud){
		// $subs_crud->add('Controller_FormBeautifier');			
		// ->getElement('from_app')->defaultValue('xMarketingCampaign');
		// }
		if($subs_crud and (!$subs_crud->isEditing())){
			$g=$subs_crud->grid;
			$subs_crud->add_button->setIcon('ui-icon-plusthick');
			$g->add_sno();
			$g->addPaginator(100);
			$g->addQuickSearch(array('email'));
		}
	}
}