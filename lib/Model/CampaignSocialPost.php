<?php

namespace xMarketingCampaign;


class Model_CampaignSocialPost extends \Model_Table {
	public $table ='xmarketingcampaign_campaignsocialposts';

	function init(){
		parent::init();

		$this->hasOne('Epan','epan_id');
		$this->addCondition('epan_id',$this->api->current_website->id);

		$f=$this->hasOne('xMarketingCampaign/Campaign','campaign_id')->defaultValue('Null')->mandatory(true)->group('a~12');

		$f=$this->hasOne('xMarketingCampaign/SocialPost','socialpost_id')->defaultValue('Null')->mandatory(true)->group('b~12');

		// $this->addField('post_to_socials')->type('boolean')->defaultValue(false);
		$f=$this->addField('post_on')->type('date')->group('c~6~<i class="fa fa-calendar"></i> Posting Schedule');
		$f->icon='fa fa-calendar~red';
		
		$f=$this->addField('at_hour')->enum(array('00','01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18',"19",'20',"21",'22','23'))->group('c~3')->defaultValue('09');
		$f->icon="fa fa-clock-o~red";
		$f=$this->addField('at_minute')->enum(array('00','15','30','45'))->group('c~3');
		$f->icon="fa fa-clock-o~red";

		$this->addField('is_posted')->type('boolean')->defaultValue(false)->system(false);
		$this->addExpression('is_posting_done','is_posted')->type('boolean');
		$objects = scandir($plug_path = getcwd().DS.'epan-components'.DS.'xMarketingCampaign'.DS.'lib'.DS.'Controller'.DS.'SocialPosters');
    	foreach ($objects as $object) {
    		if ($object != "." && $object != "..") {
        		if (filetype($plug_path.DS.$object) != "dir"){
        			$object = str_replace(".php", "", $object);
        			$f=$this->addField($object)->type('boolean')->defaultValue(true);
    				$f->group('d~2');
        		}
    		}
    	}

		$this->addExpression('post_on_datetime')->set('CONCAT(post_on," ",at_hour,":",at_minute,":00")');

		$this->addHook('beforeSave',$this);
	
		// $this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeSave(){
				
	}

	function isExist($campaign_id,$socialpost_id,$date,$hour=00,$minute=00){
		$this->addCondition('is_posted',false);
		$this->addCondition('campaign_id',$campaign_id);
		$this->addCondition('socialpost_id',$socialpost_id);	
		$this->addCondition('post_on',$date);	
		$this->addCondition('at_hour',$hour);
		$this->addCondition('at_minute',$minute);
		$this->tryLoadAny();
		if($this->loaded()){
			return true;
		}
		return false;	
	}

	function createNew($campaign_id,$socialpost_id,$date,$hour=00,$minute=00){
		if($this->loaded())
			$this->unload();
		$this['campaign_id'] = $campaign_id;
		$this['socialpost_id'] = $socialpost_id;	
		$this['post_on'] = $date;	
		$this['at_hour'] = $hour;
		$this['at_minute'] = $minute;
		$this->save();
		return true;
	}

}	
