<?php

class page_xMarketingCampaign_page_socialexec extends Page{

	function init(){
		parent::init();

		$all_postable_contents = $this->add('xMarketingCampaign/Model_CampaignSocialPost');
		$all_postable_contents->addCondition('is_posted',false);
		$all_postable_contents->addCondition('post_on_datetime','<=',date('Y-m-d H:i:s'));

		$socials=array();
		$objects = scandir($plug_path = getcwd().DS.'epan-components'.DS.'xMarketingCampaign'.DS.'lib'.DS.'Controller'.DS.'SocialPosters');
	    	foreach ($objects as $object) {
	    		if ($object != "." && $object != "..") {
	        		if (filetype($plug_path.DS.$object) != "dir"){
	        			$socials[] = str_replace(".php", "", $object);
	        		}
	        	}
	        }

		foreach ($all_postable_contents as $junk) {
			foreach ($socials as $social) {
				if($all_postable_contents[$social]){
					$this->add('xMarketingCampaign/Controller_SocialPosters_'.$social)->post($all_postable_contents->ref('socialpost_id'));
				}
			}	
			$all_postable_contents['is_posted']=true;
			$all_postable_contents->saveAndUnload();
		}

	}
}
	