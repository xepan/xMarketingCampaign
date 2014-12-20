<?php

namespace xMarketingCampaign;

class Model_SocialPost extends \Model_Table {
	public $table ="xMarketingCampaign_SocialPosts";
	
	function init(){
		parent::init();

		$this->hasOne('Epan','epan_id');
		$this->addCondition('epan_id',$this->api->current_website->id);

		$this->hasOne('xMarketingCampaign/SocialPostCategory','category_id')->sortable(true);

		$f=$this->addField('name')->mandatory(true)->group('a~10')->sortable(true);
		$f->icon='fa fa-adn~red';
		$f=$this->addField('is_active')->type('boolean')->defaultValue(true)->group('a~2')->sortable(true);
		$f->icon='fa fa-exclamation~blue';

		$f=$field_title = $this->addField('post_title')->display(array('grid'=>'shorttext,wrap'))->group('b~12~<i class="fa fa-share-alt"></i> The Post')->mandatory(true);
		$f->icon ='fa fa-header~red';
		$f=$field_url = $this->addField('url')->group('b~12~bl');
		$f->icon = 'fa fa-globe~blue';
		$f=$field_image = $this->addField('image')->display(array('form'=>'ElImage'))->group('b~12~bl');
		$f->icon='fa fa-image~blue';
		
		$f=$field_160 = $this->addField('message_160_chars')->group('c~12~<i class="fa fa-paragraph"></i> The Message');
		$field_255 = $this->addField('message_255_chars')->display(array('grid'=>'shorttext,wrap'))->group('c~12~bl');
		$field_3000 = $this->addField('message_3000_chars')->type('text')->group('c~12~bl');
		$field_blog = $this->addField('message_blog')->type('text')->display(array('form'=>'RichText'))->group('c~12~bl');

		$this->addField('post_leg_allowed')->hint('No of days allowed to delay post')->system(true);

		$this->addField('created_at')->type('datetime')->defaultValue(date('Y-m-d H:i:s'))->system(true);
		$this->addField('updated_at')->type('datetime')->defaultValue(date('Y-m-d H:i:s'))->system(true);

		$this->addHook('beforeSave',$this);

		$objects = scandir($plug_path = getcwd().DS.'epan-components'.DS.'xMarketingCampaign'.DS.'lib'.DS.'Controller'.DS.'SocialPosters');
    	foreach ($objects as $object) {
    		if ($object != "." && $object != "..") {
        		if (filetype($plug_path.DS.$object) != "dir"){
        			$object = str_replace(".php", "", $object);
        			$social = $this->add('xMarketingCampaign/Controller_SocialPosters_'.$object);
        			$used_fields = $social->get_post_fields_using();
        			foreach ($used_fields as $fld) {
        				$temp_field = 'field_'.$fld;
        				if(isset(${$temp_field}))
	        				${$temp_field}->hint(${$temp_field}->hint(). ' ' . $object);
        			}
        		}
    		}
    	}

    	$this->addExpression('total_posts')->set(function($m,$q){
    		return $m->refSQL('xMarketingCampaign/SocialPosting')->count();
    	})->sortable(true);

    	$this->addExpression('total_likes')->set(function($m,$q){
    		return $m->refSQL('xMarketingCampaign/SocialPosting')->sum('likes');
    	})->sortable(true);

    	$this->addExpression('total_share')->set(function($m,$q){
    		return $m->refSQL('xMarketingCampaign/SocialPosting')->sum('share');
    	})->sortable(true);

    	$this->addExpression('total_comments')->set(function($m,$q){
    		$act_m = $m->add('xMarketingCampaign/Model_Activity',array('table_alias'=>'temp'));
    		$posting_j = $act_m->join('xMarketingCampaign_SocialPostings','posting_id');
    		$posting_j->addField('post_id');

    		$act_m->addCondition('post_id',$q->getField('id'));

    		return $act_m->count();
    	})->sortable(true);

    	$this->addExpression('unread_comment')->set(function($m,$q){
    		$act_m = $m->add('xMarketingCampaign/Model_Activity',array('table_alias'=>'temp'));
    		$posting_j = $act_m->join('xMarketingCampaign_SocialPostings','posting_id');
    		$posting_j->addField('post_id');
    		$act_m->addCondition('post_id',$q->getField('id'));	
    		
    		$act_m->addCondition('is_read',false);

    		return $act_m->count();
    	})->sortable(true);

    	$this->hasMany('xMarketingCampaign/SocialPosting','post_id');
    	$this->hasMany('xMarketingCampaign/CampaignSocialPost','socialpost_id');
    	$this->addHook('beforeDelete',$this);
		$this->add('dynamic_model/Controller_AutoCreator');

	}

	function beforeSave(){
		// if($this['message_160_chars'] and strlen($this['message_160_chars']) > 160){
		// 	throw $this->exception('Length Exceeding','ValidityCheck')->setField('message_160_chars');
		// }

		$this['updated_at'] = date('Y-m-d H:i:s');

	}
	function beforeDelete(){
		$temp=$this->ref('xMarketingCampaign/CampaignSocialPost');
		foreach ($temp as $junk) {
			$temp->delete();
		}
	}
}