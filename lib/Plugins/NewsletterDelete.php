<?php

namespace xMarketingCampaign;


class Plugins_NewsletterDelete extends \componentBase\Plugin {

	function init(){
		parent::init();
		$this->addHook('xenq_n_subs_newletter_before_delete',array($this,'Plugins_NewsletterDelete'));
	}

	function Plugins_NewsletterDelete($obj, $newsletter){
		
		$campnews=$this->add('xMarketingCampaign/Model_CampaignNewsLetter');
		$campnews->addCondition('newsletter_id',$newsletter->id);
		$campnews->deleteAll();
	}
}
