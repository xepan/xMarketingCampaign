<?php

namespace xMarketingCampaign;

class Model_SocialPost extends \Model_Table {
	public $table ="xMarketingCampaign_SocialPosts";
	
	function init(){
		parent::init();

		$this->hasOne('Epan','epan_id');
		$this->addCondition('epan_id',$this->api->current_website->id);

		$f=$this->addField('name')->mandatory(true)->group('a~10');
		$f->icon='fa fa-adn~red';
		$f=$this->addField('is_active')->type('boolean')->defaultValue(true)->group('a~2');
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


		// $this->addHook('beforeSave',$this);

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
    	$this->hasMany('xMarketingCampaign/CampaignSocialPost','socialpost_id');
    	$this->addHook('beforeDelete',$this);
		// $this->add('dynamic_model/Controller_AutoCreator');

	}

	// function beforeSave(){
	// 	if($this['message_160_chars'] and strlen($this['message_160_chars']) > 160){
	// 		throw $this->exception('Length Exceeding','ValidityCheck')->setField('message_160_chars');
	// 	}
	// }
	function beforeDelete(){
		$temp=$this->ref('xMarketingCampaign/CampaignSocialPost');
		foreach ($temp as $junk) {
			$temp->delete();
		}
	}
}