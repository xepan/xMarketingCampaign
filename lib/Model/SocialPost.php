<?php

namespace xMarketingCampaign;

class Model_SocialPost extends \Model_Table {
	public $table ="xMarketingCampaign_SocialPosts";
	
	function init(){
		parent::init();

		$this->hasOne('Epan','epan_id');
		$this->addCondition('epan_id',$this->api->current_website->id);

		$this->addField('name');

		$field_title = $this->addField('post_title');
		$field_url = $this->addField('url');
		$field_image = $this->addField('image')->display(array('form'=>'ElImage'));
		
		$field_160 = $this->addField('message_160_chars');
		$field_255 = $this->addField('message_255_chars');
		$field_3000 = $this->addField('message_3000_chars')->type('text');
		$field_blog = $this->addField('message_blog')->type('text')->display(array('form'=>'RichText'));

		$this->addField('post_leg_allowed')->hint('No of days allowed to delay post');

		$this->addField('is_active')->type('boolean')->defaultValue(true);

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

		$this->add('dynamic_model/Controller_AutoCreator');

	}

	// function beforeSave(){
	// 	if($this['message_160_chars'] and strlen($this['message_160_chars']) > 160){
	// 		throw $this->exception('Length Exceeding','ValidityCheck')->setField('message_160_chars');
	// 	}
	// }

}