<?php

class page_xMarketingCampaign_page_owner_emailcontacts extends page_componentBase_page_owner_main{

	function init(){
		parent::init();
	
		$email_category_model = $this->add('xEnquiryNSubscription/Model_SubscriptionCategories');
		$crud = $this->add('CRUD');
		$crud->setModel($email_category_model,array('name','is_active'));

		if($g=$crud->grid){
			$btn = $g->addButton('Manage Data Grabber');
			$btn->js('click',$g->js()->univ()->frameURL('Data Grabber',$this->api->url('xMarketingCampaign_page_owner_mrkt_dtgrb_dtgrb')));

			$btn1 = $g->addButton('Exec Grabber');
			$btn1->js('click',$g->js()->univ()->frameURL('Execute Data Grabber',$this->api->url('xMarketingCampaign_page_owner_mrkt_dtgrb_exec')));
		}

		$subs_crud = $crud->addRef('xEnquiryNSubscription/Subscription',array('label'=>'Emails','fields'=>array('category','email','subscribed_on','send_news_letters')));		
		if($subs_crud and $g=$subs_crud->grid){
			$g->sno=1;
			$g->addMethod('format_sno',function($grid,$field){
				$skip=0;
				foreach ($_GET as $key => $value) {
					if(strpos($key, '_paginator_skip') !== false) $skip = $_GET[$key];
				}
				$grid->current_row[$field] = $grid->sno + $skip;
				$grid->sno++;
			});

			$g->addColumn('sno','sno');
			$g->addOrder()->move('sno','first')->now();
		}

	}

}