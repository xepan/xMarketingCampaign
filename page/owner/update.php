<?php

class page_xMarketingCampaign_page_owner_update extends page_componentBase_page_update {
		
	public $git_path="https://github.com/xepan/xMarketingCampaign"; // Put your components git path here

	function init(){
		parent::init();

		// 
		// Code To run before update
		
		$this->update($dynamic_model_update=false); // All modls will be dynamic executed in here
		$model_array=array(
			'Model_Campaign',
			'Model_CampaignNewsLetter',
			'Model_CampaignSubscriptionCategory',
			'Model_Config',
			'Model_DataGrabber',
			'Model_DataSearchPhrase',
			'Model_SocialPost',
			'Model_CampaignSocialPost'
			);

		foreach ($model_array as $md) {
			$model = $this->add('xMarketingCampaign/'.$md);
			$model->add('dynamic_model/Controller_AutoCreator');
			$model->tryLoadAny();
		}
		$this->add('View_Info')->set('Component Updated Successfully');
		// Code to run after update
	}
}