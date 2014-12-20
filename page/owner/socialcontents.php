<?php

class page_xMarketingCampaign_page_owner_socialcontents extends page_xMarketingCampaign_page_owner_main{




	function init(){
		$this->rename('y');
		parent::init();

		$total_posts_vp = $this->total_posts_vp();
	
		$preview_vp = $this->add('VirtualPage');
		$preview_vp->set(function($p){


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

		$this->app->layout->template->trySetHTML('page_title','<i class="fa fa-bullhorn"></i> '.$this->component_name. '<small> Social Posts </small>');

		$bg=$this->app->layout->add('View_BadgeGroup');
		$data=$this->add('xMarketingCampaign/Model_SocialPost')->count()->getOne();
		$v=$bg->add('View_Badge')->set('Total Post')->setCount($data)->setCountSwatch('ink');

		$data=$this->add('xMarketingCampaign/Model_SocialPosting')->count()->getOne();
		$v=$bg->add('View_Badge')->set('Total Postings')->setCount($data)->setCountSwatch('ink');
		
		$data=$this->add('xMarketingCampaign/Model_SocialPosting')->sum('likes');
		$v=$bg->add('View_Badge')->set('Total Likes')->setCount($data)->setCountSwatch('ink');	

		$data=$this->add('xMarketingCampaign/Model_SocialPosting')->sum('share');
		$v=$bg->add('View_Badge')->set('Total Share')->setCount($data)->setCountSwatch('ink');

		$data=$this->add('xMarketingCampaign/Model_Activity')->count()->getOne();
		$v=$bg->add('View_Badge')->set('Total Comments')->setCount($data)->setCountSwatch('ink');		

		$data=$this->add('xMarketingCampaign/Model_Activity')->addCondition('is_read',false)->count()->getOne();
		if($data)
			$v=$bg->add('View_Badge')->set('Total Un-Read Comments')->setCount($data)->setCountSwatch('red');		

		$cols = $this->app->layout->add('Columns');
		$cat_col = $cols->addColumn(3);
		$social_col = $cols->addColumn(9);

		$social_category_model = $this->add('xMarketingCampaign/Model_SocialPostCategory');

		$cat_crud=$cat_col->add('CRUD');

		$cat_crud->setModel($social_category_model,array('name','posts'));

		if(!$cat_crud->isEditing()){
			$g=$cat_crud->grid;
			$g->addMethod('format_filtersocial',function($g,$f)use($social_col){
				$g->current_row_html[$f]='<a href="javascript:void(0)" onclick="'. $social_col->js()->reload(array('category_id'=>$g->model->id)) .'">'.$g->current_row[$f].'</a>';
			});
			$g->addFormatter('name','filtersocial');
			$g->add_sno();
		}

		$social_model = $this->add('xMarketingCampaign/Model_SocialPost');

		// filter social letter as per selected category
		if($_GET['category_id']){
			$this->api->stickyGET('category_id');
			$filter_box = $social_col->add('View_Box')->setHTML('Social Posts for <b>'. $social_category_model->load($_GET['category_id'])->get('name').'</b>' );
			
			$filter_box->add('Icon',null,'Button')
            ->addComponents(array('size'=>'mega'))
            ->set('cancel-1')
            ->addStyle(array('cursor'=>'pointer'))
            ->on('click',function($js) use($filter_box,$social_col) {
                $filter_box->api->stickyForget('category_id');
                return $filter_box->js(null,$social_col->js()->reload())->hide()->execute();
            });

			$social_model->addCondition('category_id',$_GET['category_id']);
		}

		$social_crud = $social_col->add('CRUD');
		
		$cols_array = array('category','name','is_active','total_posts','total_likes','total_share','total_comments','unread_comment');

		if($_GET['sort_by']){
			$this->api->stickyGET('sort_by');
			$this->api->stickyGET('order');
			// $social_crud->grid->add('View_Box',null,'grid_buttons')->set($_GET['sort_by']);

			switch ($_GET['sort_by']) {
				case 'created_at':
					$social_model->_dsql()->del('order');
					$social_model->setOrder('created_at',$_GET['order']);
					break;
				case 'updated_at':
					$social_model->_dsql()->del('order');
					$social_model->setOrder('updated_at',$_GET['order']);
					break;
				case 'recent_posted':
					$posting_j=$social_model->join('xMarketingCampaign_SocialPostings.post_id');
					$posting_j->addField('posted_on');
					$social_model->setOrder('posted_on',$_GET['order']);
					$social_model->_dsql()->group('post_id');
					$cols_array=array_merge(array('posted_on'),$cols_array);
					break;
				case 'recent_commented':
					$posting_j=$social_model->join('xMarketingCampaign_SocialPostings.post_id');
					$posting_j->addField('posted_on');

					$activity_j = $posting_j->join('xMarketingCampaign_SocialPostings_Activities.posting_id');
					$activity_j->addField('activity_on')->caption('Comment On');

					$social_model->setOrder('activity_on',$_GET['order']);
					$social_model->_dsql()->group('post_id');
					$cols_array=array_merge(array('activity_on'),$cols_array);
					break;
				default:
					# code...
					break;
			}

		}

		$social_crud->setModel($social_model,null,$cols_array);
		// $social_crud->add('Controller_FormBeautifier');
		if(!$social_crud->isEditing()){
			$g=$social_crud->grid;
			$g->add_sno();

			$sort_form = $g->buttonset->add('Form');
			$sort_form->addClass('atk-form atk-move-right');
        	// $sort_form->template->trySet('fieldset', 'atk-row');
        	$sort_form->template->tryDel('button_row');
			$sort_form_field= $sort_form->addField('DropDown','sort_by')->setValueList(array(0=>'Default','created_at'=>'Created Date','updated_at'=>'Updated Date','recent_posted'=>'Recent Posted On','recent_scheduled'=>'Recent Scheduled On','recent_commented'=>'Recent Commented On'))->set($_GET['sort_by']?:"Default");
			$btn=$sort_form_field->beforeField()->add('Button')->set(array('','icon'=>'sort-alt-up'));
			$btn->js('click',$g->js()->reload(array('sort_by'=>$sort_form_field->js()->val(),'order'=>'asc')));
			$btn=$sort_form_field->afterField()->add('Button')->set(array('','icon'=>'sort-alt-down'));
			$btn->js('click',$g->js()->reload(array('sort_by'=>$sort_form_field->js()->val(),'order'=>'desc')));


			$g->addQuickSearch(array('name'));
			$g->addClass('social_grid');
			$g->js('reload')->reload();

			$g->addMethod('format_total_posts',function($g,$f)use($total_posts_vp){
				$g->current_row_html[$f]= '<a href="javascript:void(0)" onclick="'.$g->js()->univ()->frameURL('Total Posts Of  "'.$g->model['name'].'"',$g->api->url($total_posts_vp->getURL(),array('socialpost_id'=>$g->model->id))).'">'.$g->current_row[$f].'</a>';
			});
			$g->addFormatter('total_posts','total_posts');

			$g->addMethod('format_preview',function($g,$f)use($preview_vp){
				$g->current_row_html[$f]='<a href="javascript:void(0)" onclick="'. $g->js()->univ()->frameURL($g->model['name'],$g->api->url($preview_vp->getURL(),array('socialpost_id'=>$g->model->id))) .'">'.$g->current_row[$f].'</a>';
			});
			$g->addFormatter('name','preview');

			$g->addMethod('format_unread_comment',function($g,$f){
				if($g->current_row[$f])
					$g->current_row_html[$f] = '<i class="atk-label atk-swatch-ink" style="background-color:red"/>'.$g->current_row[$f];
			});
			$g->addFormatter('unread_comment','unread_comment');
			
			$g->addColumn('Expander','post');
			$social_crud->add_button->setIcon('ui-icon-plusthick');
			
		}
	
	}

	function total_posts_vp(){

		$comments_vp=$this->add('VirtualPage');
		$comments_vp->set(function($p){
			$p->add('xMarketingCampaign/Controller_SocialPosters_Base_Social'); // Need it just for Models to be accesed defined in this file

			$p->api->stickyGET('posting_id');
			
			$comment_form = $p->add('Form');
			$crud = $p->add('CRUD',array('allow_edit'=>false,'allow_add'=>false,'allow_del'=>false));

			$comment_form->addClass('stacked');
			$comment_form->addField('text','comment');
			$comment_form->addField('hidden','posting_id')->set($_GET['posting_id']);
			$comment_form->addSubmit('Comment');
			
			if($comment_form->isSubmitted()){
				$posting_model = $p->add('xMarketingCampaign/Model_SocialPosting')->load($comment_form['posting_id']);
				$cont = $p->add('xMarketingCampaign/Controller_SocialPosters_'.$posting_model['social_app']);
				$cont->comment($posting_model,$comment_form['comment']);
				$crud->grid->js(null,array($comment_form->js()->reload(),$comment_form->js()->univ()->successMessage('Commented')))->reload()->execute();
			}


			$activity_m = $p->add('xMarketingCampaign/Model_Activity');
			$activity_m->addCondition('posting_id',$_GET['posting_id']);

			$p->api->db->dsql()->table('xMarketingCampaign_SocialPostings_Activities')->set('is_read',1)->where('posting_id',$_GET['posting_id'])->update();

			$activity_m->getElement('name')->caption('Comment');
			$activity_m->getElement('activity_by')->caption('Comment By');
			$activity_m->getElement('activity_on')->caption('Comment On');

			$crud->setModel($activity_m,array('name','activity_by','activity_on','action_allowed'));
			

			if(!$crud->isEditing()){
				$g= $crud->grid;
				$g->removeColumn('action_allowed');

				$g->addMethod('format_activity_by',function($g,$f){
					$posting = $g->model->ref('posting_id');
					$social_app_cont = $g->add('xMarketingCampaign/Controller_SocialPosters_'.$posting['social_app']);
					$profile_detail = $social_app_cont->profileURL(0,$g->model[$f]);
					$g->current_row_html[$f]='<a href="'.$profile_detail['url'].'" target="_blank">'.$profile_detail['name'].'</a>';
				});
				$g->addFormatter('activity_by','activity_by');


			}

		});

		$total_posts_vp = $this->add('VirtualPage');
		$total_posts_vp->set(function($p)use($comments_vp){
			$p->api->stickyGET('socialpost_id');

			$p->add('xMarketingCampaign/Controller_SocialPosters_Base_Social'); // Need it just for Models to be accesed defined in this file

			$crud= $p->add('CRUD',array('allow_add'=>false,'allow_del'=>true,'allow_edit'=>false));
	    	
	    	$postings_m = $p->add('xMarketingCampaign/Model_SocialPosting');
	    	$postings_m->addCondition('post_id',$_GET['socialpost_id']);
	    	$postings_m->setOrder('posted_on','desc');
	    	
	    	$crud->setModel($postings_m,array('social_app','user','posted_on','postid_returned','post_type','likes','share','unread_comments','total_comments','group_name','campaign','is_monitoring','force_monitor'));

	    	$crud->addAction('keep_monitoring',array('toolbar'=>false));

	    	if(!$crud->isEditing()){
	    		$grid= $crud->grid;
		    	$grid->addMethod('format_socialIcon',function($g,$f){
		    		if($g->current_row[$f])
			    		$g->current_row_html[$f]=$g->add('xMarketingCampaign/Controller_SocialPosters_'.$g->current_row[$f])->icon();

		    	});
		    	$grid->addFormatter('social_app','socialIcon');

		    	$grid->addMethod('format_profileurl',function($g,$f){
		    		if(!$g->current_row['social_app']) return;
		    		if($url=$g->add('xMarketingCampaign/Controller_SocialPosters_'.$g->current_row['social_app'])->profileURL($g->model['user_id'])){
		    			$url=$url['url'];
		    			$g->current_row_html[$f] ="<a href='$url' target='_blank'>".$g->current_row[$f]. "</a>";
		    		}

		    	});
		    	$grid->addFormatter('user','profileurl');

		    	$grid->addMethod('format_posturl',function($g,$f){
		    		if(!$g->current_row['social_app']) return;
		    		if($url=$g->add('xMarketingCampaign/Controller_SocialPosters_'.$g->current_row['social_app'])->postURL($g->model['postid_returned'])){
		    			if($g->current_row['post_type']=='Group Post')
		    				$g->current_row[$f] = $g->current_row[$f] . ' : '. $g->current_row['group_name'];
		    			$g->current_row_html[$f] ="<a href='$url' target='_blank'>".$g->current_row[$f]. "</a>";
		    		}

		    	});
		    	$grid->addFormatter('post_type','posturl');

		    	$grid->addMethod('format_total_comments',function($g,$f)use($comments_vp){
		    		// if(!$g->current_row['social_app']) return;
		    		// if($url=$g->add('xMarketingCampaign/Controller_SocialPosters_'.$g->current_row['social_app'])->postURL($g->model['postid_returned'])){
		    			// if($g->current_row['post_type']=='Group Post')
		    				// $g->current_row[$f] = $g->current_row[$f] . ' : '. $g->current_row['group_name'];
		    			$g->current_row_html[$f] ='<a href="javascript:void(0)" onclick="'.$g->js()->univ()->frameURL('Comments',$g->api->url($comments_vp->getURL(),array('posting_id'=>$g->model->id))).'">'.$g->current_row[$f]. "</a>";
		    		// }

		    	});
		    	$grid->addFormatter('total_comments','total_comments');
		    	
		    	$grid->addMethod('format_unread_comments',function($g,$f)use($comments_vp){
		 			if($g->current_row[$f])
						$g->current_row_html[$f] = '<i class="atk-label atk-swatch-ink" style="background-color:red">'.$g->current_row[$f].'</i>';	
		    	});

		    	$grid->addFormatter('unread_comments','unread_comments');

		    	$grid->addPaginator(50);
		    	$grid->addQuickSearch(array('social_app','user','campaign','group_name'));
		    	$grid->add_sno();
		    	$grid->removeColumn('postid_returned');
		    	$grid->removeColumn('group_name');
	    	}
		});
		return $total_posts_vp;
	}

	function page_post(){

		$this->api->stickyGET('xMarketingCampaign_SocialPosts_id');
		$post_m = $this->add('xMarketingCampaign/Model_SocialPost')->load($_GET['xMarketingCampaign_SocialPosts_id']);

		$v= $this->add('View')->addClass('panel panel-default');
		$v->setStyle('padding','20px');

		$btn_set = $v->add('ButtonSet');

		$objects = scandir($plug_path = getcwd().DS.'epan-components'.DS.'xMarketingCampaign'.DS.'lib'.DS.'Controller'.DS.'SocialPosters');
    	foreach ($objects as $object) {
    		if ($object != "." && $object != "..") {
        		if (filetype($plug_path.DS.$object) != "dir"){
        			$object = str_replace(".php", "", $object);
        			$btn = $btn_set->addButton($object);
        			if($btn->isClicked()){
        				$btn->js()->univ()->frameURL('Post on '. $object, $this->api->url('./single',array('social'=>$object,'socialpost_id'=>$_GET['xMarketingCampaign_SocialPosts_id'])))->execute();
        			}
        		}
    		}
    	}

    	


	}

	function page_post_single(){
		$this->api->stickyGET('social');
		$this->api->stickyGET('socialpost_id');

		$object =$_GET['social'];
		$social_cont = $this->add('xMarketingCampaign/Controller_SocialPosters_'.$object);

		$config = $this->add('xMarketingCampaign/Model_'.$object.'Config');

		$crud= $this->add('CRUD',array('allow_add'=>false,'allow_del'=>false,'allow_edit'=>false));
		$crud->setModel($config);

		$users_crud = $crud->addRef('xMarketingCampaign/SocialUsers',array('label'=>'Users','view_options'=>array('allow_add'=>false,'allow_del'=>false,'allow_edit'=>false),'grid_fields'=>array('name')));

		if($users_crud and !$users_crud->isEditing()){
			$g= $users_crud->grid;
			$g->addColumn('Button','status_update_only');
			if($crud->model['post_in_groups']){
				$g->addColumn('Button','status_with_groups');
			}
		}

		$do_post=false;
		$in_groups=false;
		$user_id=null;
		if($user_id = $_GET['status_with_groups']){
			$do_post=true;
			$in_groups=true;
		}

		if($_GET['status_update_only'] OR $do_post){
			if(!$user_id) $user_id = $_GET['status_update_only'];

				$post = $this->add('xMarketingCampaign/Model_SocialPost')->load($_GET['socialpost_id']);			
				$user= $this->add('xMarketingCampaign/Model_SocialUsers')->load($user_id);
			$social_cont->postSingle($user,$post,$in_groups);
			$this->js()->univ()->successMessage('Posted Sucessfully')->execute();
		}


	}

}		