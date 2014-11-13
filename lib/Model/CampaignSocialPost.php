<?php

namespace xMarketingCampaign;


class Model_CampaignSocialPost extends \Model_Table {
	public $table ='xmarketingcampaign_campaignsocialposts';

	function init(){
		parent::init();

		$this->hasOne('Epan','epan_id');
		$this->addCondition('epan_id',$this->api->current_website->id);

		$this->hasOne('xMarketingCampaign/Campaign','campaign_id')->defaultValue('Null')->mandatory(true);
		$this->hasOne('xMarketingCampaign/SocialPost','socialpost_id')->defaultValue('Null')->mandatory(true);

		// $this->addField('post_to_socials')->type('boolean')->defaultValue(false);
		$this->addField('post_on')->type('date');

		$this->addField('at_hour')->enum(array(00,01,02,03,04,05,06,07,08,09,10,11,12,13,14,15,16,17,18,19,20,21,22,23));
		$this->addField('at_minute')->enum(array(00,05,10,15,20,25,30,35,40,45,50,55));

		$this->addField('is_posted')->type('boolean')->defaultValue(false)->system(true);
		$this->addExpression('is_poting_done','is_posted')->type('boolean');

		$objects = scandir($plug_path = getcwd().DS.'epan-components'.DS.'xMarketingCampaign'.DS.'lib'.DS.'Controller'.DS.'SocialPosters');
    	foreach ($objects as $object) {
    		if ($object != "." && $object != "..") {
        		if (filetype($plug_path.DS.$object) != "dir"){
        			$object = str_replace(".php", "", $object);
        			$this->addField($object)->type('boolean')->defaultValue(true);
        		}
    		}
    	}

		$this->addExpression('post_on_datetime')->set('CONCAT(post_on," ",at_hour,":",at_minute,":00")');


		$this->addHook('beforeSave',$this);
	
		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeSave(){
				
	}

}	
