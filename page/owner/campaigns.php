<?php

class page_xMarketingCampaign_page_owner_campaigns extends page_xMarketingCampaign_page_owner_main{

	function init(){
		$this->rename('x');
		parent::init();
	}

	function page_index(){
		// parent::init();

		$bg=$this->app->layout->add('View_BadgeGroup');
		$data=$this->add('xEnquiryNSubscription/Model_NewsLetter')->count()->getOne();
		$v=$bg->add('View_Badge')->set('Total NewsLetters')->setCount($data)->setCountSwatch('ink');

		$data=$this->add('xEnquiryNSubscription/Model_NewsLetter')->addCondition('created_by','xMarketingCampaign')->count()->getOne();
		$v=$bg->add('View_Badge')->set('By This App')->setCount($data)->setCountSwatch('ink');

		$cat_toggle_btnset =$this->app->layout->add('ButtonSet');
		$hide_cat_btn = $cat_toggle_btnset->addButton('Hide Category');
		$show_cat_btn = $cat_toggle_btnset->addButton('Show Category');

		$cols = $this->app->layout->add('Columns');
		$cat_col = $cols->addColumn(3);
		$camp_col = $cols->addColumn(9);

		$hide_cat_btn->js('click',array($cat_col->js()->hide(),$camp_col->js()->addClass('atk-col-12')));
		$show_cat_btn->js('click',array($cat_col->js()->show(),$camp_col->js()->removeClass('atk-col-12')));

		$cat_crud = $cat_col->add('CRUD');
		$cat_model = $this->add('xMarketingCampaign/Model_CampaignCategory');
		$cat_crud->setModel($cat_model,array('name','campaigns'));
		
		if(!$cat_crud->isEditing()){
			$g=$cat_crud->grid;
			$g->addMethod('format_filtercampaign',function($g,$f)use($camp_col){
				$g->current_row_html[$f]='<a href="javascript:void(0)" onclick="'. $camp_col->js()->reload(array('category_id'=>$g->model->id)) .'">'.$g->current_row[$f].'</a>';
			});
			$g->addFormatter('name','filtercampaign');
			$g->add_sno();
		}

		$campaign_model = $this->add('xMarketingCampaign/Model_Campaign');


		//filter Campaigns as per selected category
		if($_GET['category_id']){
			$this->api->stickyGET('category_id');
			$filter_box = $camp_col->add('View_Box')->setHTML('Campaigns for <b>'. $cat_model->load($_GET['category_id'])->get('name').'</b>' );
			
			$filter_box->add('Icon',null,'Button')
            ->addComponents(array('size'=>'mega'))
            ->set('cancel-1')
            ->addStyle(array('cursor'=>'pointer'))
            ->on('click',function($js) use($filter_box,$camp_col) {
                $filter_box->api->stickyForget('category_id');
                return $filter_box->js(null,$camp_col->js()->reload())->hide()->execute();
            });

			$campaign_model->addCondition('category_id',$_GET['category_id']);
		}

		if($_GET['schedule']){
			$test_campaign_model= $this->add('xMarketingCampaign/Model_Campaign')->load($_GET['schedule']);
			if($test_campaign_model['effective_start_date']=='CampaignDate')
				$this->api->redirect($this->api->url('./campaignDate_based_schedule',array('xmarketingcampaign_campaigns_id'=>$_GET['schedule'])));
			else
				$this->api->redirect($this->api->url('./subscriptionDate_based_schedule',array('xmarketingcampaign_campaigns_id'=>$_GET['schedule'])));
		}

		$campaign_crud = $camp_col->add('CRUD');
		$campaign_crud->setModel($campaign_model,null,array('category','name','starting_date','ending_date','effective_start_date','is_active'));
		
		if(!$campaign_crud->isEditing()){
			$campaign_crud->grid->addColumn('Button','schedule');

			// $campaign_crud->grid->addColumn('expander','AddEmails','Add Subscription Category');
			// $campaign_crud->grid->addColumn('expander','NewsLetterSubCampaign','News Letters To send');
			// $campaign_crud->grid->addColumn('expander','social_campaigns','Social Posts To Include');
			// $btn=$campaign_crud->grid->addButton('Schedule Emails Now');
			// $btn->setIcon('ui-icon-seek-end');
			// $btn->js('click')->univ()->frameURL('Campaign Executing',$this->api->url('xMarketingCampaign_page_owner_campaignexec'));
	
			$campaign_crud->add_button->setIcon('ui-icon-plusthick');
			// $Campaign_crud->grid->addColumn('expander','BlogSubCampaign');
		}

		// $Campaign_crud->add('Controller_FormBeautifier');

	}

	function page_subscriptionDate_based_schedule(){
		$page = $this->api->layout?$this->api->layout: $this;

		$campaign_id = $this->api->stickyGET('xmarketingcampaign_campaigns_id');
		$campaign = $this->add('xMarketingCampaign/Model_Campaign')->load($_GET['xmarketingcampaign_campaigns_id']);

		$preview_newsletetr_vp = $this->add('VirtualPage');
		$preview_newsletetr_vp->set(function($p){
			$m=$p->add('xEnquiryNSubscription/Model_NewsLetter')->load($_GET['newsletter_id']);
			$p->add('View')->set('Created '. $this->add('xDate')->diff(Carbon::now(),$m['created_at']) .', Last Modified '. $this->add('xDate')->diff(Carbon::now(),$m['updated_at']) )->addClass('atk-size-micro pull-right')->setStyle('color','#555');
			$p->add('HR');
			$p->add('View')->setHTML($m['matter']);
		});

		$cols = $page->add('Columns');
		$emails_col = $cols->addColumn(6);
		$calendar_col = $cols->addColumn(6);

		// Subscriber Categories
		$emails_col_cols = $emails_col->add('Columns');

		$category_col = $emails_col_cols->addColumn(6);
		$newsletter_col = $emails_col_cols->addColumn(6);

		$category_col->add('H4')->set('Subscription Categories')->addClass('text-center');
		$form=$category_col->add('Form');
		$selected = $campaign->ref('xMarketingCampaign/CampaignSubscriptionCategory')->_dsql()->del('fields')->field('category_id')->getAll();
		$form->addField('hidden','campaign_id')->set($_GET['xmarketingcampaign_campaigns_id']);
		$campaign_category_select_field=$form->addField('hidden','categories')->set(json_encode(iterator_to_array(new RecursiveIteratorIterator(new RecursiveArrayIterator($selected)),false)));
		$campaign_category_select_reset_field=$form->addField('hidden','reset')->set(json_encode(iterator_to_array(new RecursiveIteratorIterator(new RecursiveArrayIterator($selected)),false)));
		
		$category_grid = $category_col->add('Grid');
		$category_grid->setModel('xEnquiryNSubscription/Model_SubscriptionCategories',array('name'));
		$category_grid->template->tryDel('Pannel');

		$category_grid->addSelectable($campaign_category_select_field);
		$all_btn=$form->add('Button')->set(array('All'));
		$none_btn=$form->add('Button')->set('None');
		$reset_btn=$form->add('Button')->set(array('Reset','icon'=>'retweet','swatch'=>'red'));
		$reset_btn->js('click',$category_col->js()->reload(array('xmarketingcampaign_campaigns_id'=>$_GET['xmarketingcampaign_campaigns_id'])));
		$apply_btn=$form->add('Button')->set(array('Apply','swatch'=>'green'));
		$apply_btn->js('click',$form->js()->submit());
		
		$all_btn->js('click',array($category_grid->js()->atk4_checkboxes('select_all'),$apply_btn->js()->effect('highlight')));
		$none_btn->js('click',array($category_grid->js()->atk4_checkboxes('unselect_all'),$apply_btn->js()->effect('highlight')));
		
		if($form->isSubmitted()){
			$campaign_id = $form['campaign_id'];
			$categories = json_decode($form['categories'],true);

			$campaign_m = $this->add('xMarketingCampaign/Model_Campaign')->load($campaign_id);
			$campaign_m->ref('xMarketingCampaign/CampaignSubscriptionCategory')->deleteAll();

			$assos = $this->add('xMarketingCampaign/Model_CampaignSubscriptionCategory');
			foreach ($categories as $cat_id) {
				$assos['campaign_id'] = $campaign_id;
				$assos['category_id'] = $cat_id;
				$assos->saveAndUnload();
			}

			$category_col->js()->reload(array('xmarketingcampaign_campaigns_id'=>$form['campaign_id']))->execute();
		}

		// News letters
		$newsletter_col->add('H4')->set('News Letters')->addClass('text-center');

		$form = $newsletter_col->add('Form');
		$news_cat_field = $form->addField('DropDown','category','')->setEmptyText('All Categories');
		$news_cat_field->setModel('xEnquiryNSubscription/NewsLetterCategory');
		$news_cat_field->afterField()->add('Button')->set(array('','icon'=>'user'))->js('click',$form->js()->submit());

		$newsletter_grid = $newsletter_col->add('xMarketingCampaign/View_DroppableNewsLetters',array('preview_vp'=>$preview_newsletetr_vp));

		$newsletter_model =$this->add('xEnquiryNSubscription/Model_NewsLetter');
		if($_GET['newsletter_category_filter_id']){
			$newsletter_model->addCondition('category_id',$_GET['newsletter_category_filter_id']);
		}
		$newsletter_grid->setModel($newsletter_model,array('name','email_subject'));
		$newsletter_grid->removeColumn('email_subject');

		if($form->isSubmitted()){
			$newsletter_grid->js()->reload(array('newsletter_category_filter_id'=>$form['category']))->execute();
		}

		$CALANDER = $calendar_col->add('xMarketingCampaign/View_SubscriptionScheduler');
		$CALANDER->setModel($campaign);

	}

	function page_campaignDate_based_schedule(){
		$campaign_id = $this->api->stickyGET('xmarketingcampaign_campaigns_id');
		$campaign = $this->add('xMarketingCampaign/Model_Campaign')->load($_GET['xmarketingcampaign_campaigns_id']);

		$preview_newsletetr_vp = $this->add('VirtualPage');
		$preview_newsletetr_vp->set(function($p){
			$m=$p->add('xEnquiryNSubscription/Model_NewsLetter')->load($_GET['newsletter_id']);
			$p->add('View')->set('Created '. $this->add('xDate')->diff(Carbon::now(),$m['created_at']) .', Last Modified '. $this->add('xDate')->diff(Carbon::now(),$m['updated_at']) )->addClass('atk-size-micro pull-right')->setStyle('color','#555');
			$p->add('HR');
			$p->add('View')->setHTML($m['matter']);
		});

		$preview_social_vp = $this->add('VirtualPage');
		$preview_social_vp->set(function($p){


			$m=$p->add('xMarketingCampaign/Model_SocialPost')->load($_GET['socialpost_id']);
			$p->add('View')->set('Created '. $this->add('xDate')->diff(Carbon::now(),$m['created_at']) .', Last Modified '. $this->add('xDate')->diff(Carbon::now(),$m['updated_at']) )->addClass('atk-size-micro pull-right')->setStyle('color','#555');
			$p->add('HR');
			$p=$p->add('View')->addClass('panel panel-default')->setStyle('padding','20px');
			
			$cols = $p->add('Columns');
			$share_col =$cols->addColumn(4);
			$title_col =$cols->addColumn(8);

			$share_col->addClass('text-center');
			$share_col->add('View')->setElement('a')->setAttr(array('href'=>$m['url'],'target'=>'_blank'))->set($m['url']);
			$share_col->add('View')->setElement('img')->setAttr('src',$m['image'])->setStyle('max-width','100%');



			$title_col->add('H4')->set($m['post_title']);

			$cols_hrs=$p->add('Columns');
			$l_c= $cols_hrs->addColumn(4);
			$l_c->add('View')->set('Share URL')->addClass('atk-size-micro pull-right')->setStyle('color','#555');
			$l_c->add('HR');
			
			$r_c= $cols_hrs->addColumn(8);
			$r_c->add('View')->set('Post Title')->addClass('atk-size-micro pull-right')->setStyle('color','#555');
			$r_c->add('HR');

			if($m['message_160_chars']){
				$p->add('View')->set($m['message_160_chars']);
				$p->add('View')->set('Message in 160 Characters')->addClass('atk-size-micro pull-right')->setStyle('color','#555');
				$p->add('HR');
			}

			if($m['message_255_chars']){
				$p->add('View')->set($m['message_255_chars']);
				$p->add('View')->set('Message in 255 Characters')->addClass('atk-size-micro pull-right')->setStyle('color','#555');
				$p->add('HR');
			}

			if($m['message_3000_chars']){
				$p->add('View')->set($m['message_3000_chars']);
				$p->add('View')->set('Message in 3000 Characters')->addClass('atk-size-micro pull-right')->setStyle('color','#555');
				$p->add('HR');
			}

			if($m['message_blog']){
				$p->add('View')->setHTML($m['message_blog']);
				$p->add('View')->set('Message for Blogs')->addClass('atk-size-micro pull-right')->setStyle('color','#555');
				$p->add('HR');
			}

		});

		$page = $this->api->layout?$this->api->layout: $this;

		$cols = $page->add('Columns');
		$emails_col = $cols->addColumn(4);
		$calendar_col = $cols->addColumn(4);
		$social_col = $cols->addColumn(4);


		// Subscriber Categories
		$emails_col_cols = $emails_col->add('Columns');

		$category_col = $emails_col_cols->addColumn(6);
		$newsletter_col = $emails_col_cols->addColumn(6);

		$category_col->add('H4')->set('Subscription Categories')->addClass('text-center');
		$form=$category_col->add('Form');
		$selected = $campaign->ref('xMarketingCampaign/CampaignSubscriptionCategory')->_dsql()->del('fields')->field('category_id')->getAll();
		$form->addField('hidden','campaign_id')->set($_GET['xmarketingcampaign_campaigns_id']);
		$campaign_category_select_field=$form->addField('hidden','categories')->set(json_encode(iterator_to_array(new RecursiveIteratorIterator(new RecursiveArrayIterator($selected)),false)));
		$campaign_category_select_reset_field=$form->addField('hidden','reset')->set(json_encode(iterator_to_array(new RecursiveIteratorIterator(new RecursiveArrayIterator($selected)),false)));
		
		$category_grid = $category_col->add('Grid');
		$category_grid->setModel('xEnquiryNSubscription/Model_SubscriptionCategories',array('name'));
		$category_grid->template->tryDel('Pannel');

		$category_grid->addSelectable($campaign_category_select_field);
		$all_btn=$form->add('Button')->set(array('All'));
		$none_btn=$form->add('Button')->set('None');
		$reset_btn=$form->add('Button')->set(array('Reset','icon'=>'retweet','swatch'=>'red'));
		$reset_btn->js('click',$category_col->js()->reload(array('xmarketingcampaign_campaigns_id'=>$_GET['xmarketingcampaign_campaigns_id'])));
		$apply_btn=$form->add('Button')->set(array('Apply','swatch'=>'green'));
		$apply_btn->js('click',$form->js()->submit());
		
		$all_btn->js('click',array($category_grid->js()->atk4_checkboxes('select_all'),$apply_btn->js()->effect('highlight')));
		$none_btn->js('click',array($category_grid->js()->atk4_checkboxes('unselect_all'),$apply_btn->js()->effect('highlight')));
		
		if($form->isSubmitted()){
			$campaign_id = $form['campaign_id'];
			$categories = json_decode($form['categories'],true);

			$campaign_m = $this->add('xMarketingCampaign/Model_Campaign')->load($campaign_id);
			$campaign_m->ref('xMarketingCampaign/CampaignSubscriptionCategory')->deleteAll();

			$assos = $this->add('xMarketingCampaign/Model_CampaignSubscriptionCategory');
			foreach ($categories as $cat_id) {
				$assos['campaign_id'] = $campaign_id;
				$assos['category_id'] = $cat_id;
				$assos->saveAndUnload();
			}

			$category_col->js()->reload(array('xmarketingcampaign_campaigns_id'=>$form['campaign_id']))->execute();
		}

		// News letters
		$newsletter_col->add('H4')->set('News Letters')->addClass('text-center');

		$form = $newsletter_col->add('Form');
		$news_cat_field = $form->addField('DropDown','category','')->setEmptyText('All Categories');
		$news_cat_field->setModel('xEnquiryNSubscription/NewsLetterCategory');
		$news_cat_field->afterField()->add('Button')->set(array('','icon'=>'user'))->js('click',$form->js()->submit());

		$newsletter_grid = $newsletter_col->add('xMarketingCampaign/View_DroppableNewsLetters',array('preview_vp'=>$preview_newsletetr_vp));

		$newsletter_model =$this->add('xEnquiryNSubscription/Model_NewsLetter');
		if($_GET['newsletter_category_filter_id']){
			$newsletter_model->addCondition('category_id',$_GET['newsletter_category_filter_id']);
		}
		$newsletter_grid->setModel($newsletter_model,array('name','email_subject'));
		$newsletter_grid->removeColumn('email_subject');

		if($form->isSubmitted()){
			$newsletter_grid->js()->reload(array('newsletter_category_filter_id'=>$form['category']))->execute();
		}


		// calander
		$calendar_col->add('View')->set($campaign['name'])->addClass('atk-size-peta text-center');
		$range = $calendar_col->add('View');
		$range->add('View')->set($campaign['starting_date'])->addClass('atk-size-milli atk-move-left');
		$range->add('View')->set($campaign['ending_date'])->addClass('atk-size-milli atk-move-right');
		$calendar_col->add('HR');
		$CALANDER = $calendar_col->add('xMarketingCampaign/View_CampaignScheduler');
		$CALANDER->setModel($campaign);

		// Social Section
		$social_col_cols = $social_col->add('Columns');
		$social_posts_col = $social_col_cols->addColumn(6);
		$social_users_col = $social_col_cols->addColumn(6);

		// social posts
		$social_posts_col->add('H4')->set('Social Posts')->addClass('text-center');
		$form = $social_posts_col->add('Form');
		$social_post_cat_field = $form->addField('DropDown','category','')->setEmptyText('All Categories');
		$social_post_cat_field->setModel('xMarketingCampaign/SocialPostCategory');
		$social_post_cat_field->afterField()->add('Button')->set(array('','icon'=>'user'))->js('click',$form->js()->submit());

		$social_post_model = $this->add('xMarketingCampaign/Model_SocialPost');
		if($_GET['social_category_filter_id']){
			$social_post_model->addCondition('category_id',$_GET['social_category_filter_id']);
		}

		$social_posts_grid = $social_posts_col->add('xMarketingCampaign/View_DroppableSocialPosts',array('preview_vp'=>$preview_social_vp));
		$social_posts_grid->setModel($social_post_model,array('name'));
		$social_posts_grid->template->tryDel('Pannel');

		if($form->isSubmitted()){
			$social_posts_grid->js()->reload(array('social_category_filter_id'=>$form['category']))->execute();
		}

		// social users
		$social_users_col->add('H4')->set('Social Users')->addClass('text-center');
		$form=$social_users_col->add('Form');
		$selected = $campaign->ref('xMarketingCampaign/CampaignSocialUser')->_dsql()->del('fields')->field('socialuser_id')->getAll();
		$form->addField('hidden','campaign_id')->set($_GET['xmarketingcampaign_campaigns_id']);
		$campaign_social_user_select_field=$form->addField('hidden','socialusers')->set(json_encode(iterator_to_array(new RecursiveIteratorIterator(new RecursiveArrayIterator($selected)),false)));
		$campaign_social_user_select_reset_field=$form->addField('hidden','reset')->set(json_encode(iterator_to_array(new RecursiveIteratorIterator(new RecursiveArrayIterator($selected)),false)));

		$social_user_grid = $social_users_col->add('Grid');
		$social_user_grid->setModel('xMarketingCampaign/SocialUsers',array('name'));
		$social_user_grid->template->tryDel('Pannel');

		$social_user_grid->addMethod('format_add_social',function($g,$f){
			$cont = $g->add('xMarketingCampaign/Controller_SocialPosters_'.$g->model->ref('config_id')->get('social_app'));
			$g->current_row_html[$f] = $cont->icon() . ' '.$g->current_row[$f];
		});

		$social_user_grid->addFormatter('name','add_social');

		$social_user_grid->addSelectable($campaign_social_user_select_field);
		$all_btn=$form->add('Button')->set('All');
		$none_btn =$form->add('Button')->set('None');
		
		$reset_btn=$form->add('Button')->set(array('Reset','swatch'=>'red'));
		$apply_btn=$form->add('Button')->set(array('Apply','swatch'=>'green'));
		$apply_btn->js('click',$form->js()->submit());

		$none_btn->js('click',array($social_user_grid->js()->atk4_checkboxes('unselect_all'),$apply_btn->js()->effect('highlight')));
		$all_btn->js('click',array($social_user_grid->js()->atk4_checkboxes('select_all'),$apply_btn->js()->effect('highlight')));
		$reset_btn->js('click',array($social_users_col->js()->reload(array('xmarketingcampaign_campaigns_id'=>$_GET['xmarketingcampaign_campaigns_id']))));

		if($form->isSubmitted()){
			$campaign_id = $form['campaign_id'];
			$socialusers = json_decode($form['socialusers'],true);

			$campaign_m = $this->add('xMarketingCampaign/Model_Campaign')->load($campaign_id);
			$campaign_m->ref('xMarketingCampaign/CampaignSocialUser')->deleteAll();

			$assos = $this->add('xMarketingCampaign/Model_CampaignSocialUser');
			foreach ($socialusers as $user_id) {
				$assos['campaign_id'] = $campaign_id;
				$assos['socialuser_id'] = $user_id;
				$assos->saveAndUnload();
			}

			$social_users_col->js()->reload(array('xmarketingcampaign_campaigns_id'=>$form['campaign_id']))->execute();
		}

	}


	function page_AddEmails(){
		$campaign_id = $this->api->StickyGET('xmarketingcampaign_campaigns_id');

		// $v=$this->add('View');
		// $v->addClass('panel panel-default');
		// $v->setStyle('padding','20px');
		
		$grid = $this->add('Grid');

		$cat_model = $this->add('xEnquiryNSubscription/Model_SubscriptionCategories');
		$cat_model->addCondition('is_active',true);

		$cat_model->addExpression('status')->set(function($m,$q)use($campaign_id){
			$category_campaign_model = $m->add('xMarketingCampaign/Model_CampaignSubscriptionCategory',array('table_alias'=>'c'));
			$category_campaign_model->addCondition('category_id',$q->getField('id'));
			$category_campaign_model->addCondition('campaign_id',$campaign_id);
			return $category_campaign_model->count();
		})->type('boolean');

		$grid->setModel($cat_model,array('name','is_associate','status'));
		$grid->addColumn('Button','save','Swap Select');

		if($_GET['save']){
			$campaignemail_model = $this->add('xMarketingCampaign/Model_CampaignSubscriptionCategory');
			$status=$campaignemail_model->getStatus($_GET['save'],$campaign_id);
			if($status){
				$campaignemail_model->swapActive($status);
			}
			else{
				$campaignemail_model->createNew($_GET['save'],$campaign_id);
			}

			$grid->js(null,$this->js()->univ()->successMessage('Save Changes'))->reload()->execute();	
		}
	}	

	function page_NewsLetterSubCampaign(){
		$campaign_id = $this->api->StickyGET('xmarketingcampaign_campaigns_id');

		// $v=$this->add('View');
		// $v->addClass('panel panel-default');
		// $v->setStyle('padding','20px');

		$campaign_newsletter_model = $this->add('xMarketingCampaign/Model_CampaignNewsLetter');
		$campaign_newsletter_model->addCondition('campaign_id',$campaign_id);
		$crud = $this->add('CRUD');
		// $crud->add('Controller_FormBeautifier');	
		if(!$crud->isEditing()){
			$crud->add_button->setIcon('ui-icon-plusthick');
		}
		
		$crud->setModel($campaign_newsletter_model);
	}

	function page_social_campaigns(){
		$campaign_id = $this->api->StickyGET('xmarketingcampaign_campaigns_id');
		
		// $v=$this->add('View');
		// $v->addClass('panel panel-default');
		// $v->setStyle('padding','20px');

		$campaign_socialpost_model = $this->add('xMarketingCampaign/Model_CampaignSocialPost');
		$campaign_socialpost_model->addCondition('campaign_id',$campaign_id);
		$crud = $this->add('CRUD');
		$crud->setModel($campaign_socialpost_model);
		if($crud->form){
			$crud->form->getElement('socialpost_id')->setEmptyText('Please Select Post to Post');
		}

		// $crud->add('Controller_FormBeautifier');
		
		if(!$crud->isEditing()){
			$crud->add_button->setIcon('ui-icon-plusthick');
		}
	}


}		