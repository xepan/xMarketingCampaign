<?php

namespace xMarketingCampaign;


class View_SubscriptionScheduler extends \View{
	
	public $calendar_options=array();

	function init(){
		parent::init();

		if($func = $_GET[$this->name.'_event_act']){
			// throw new \Exception("event_act=".$_GET[$this->name.'_event_act']."event_id=".$_GET[$this->name.'_event_id']."ondate=".$_GET[$this->name.'_onday']."fromdate=".$_GET[$this->name.'_fromday']."campaign_id=".$_GET['campaign_id']);
			if($this->$func($_GET[$this->name.'_event_id'],$_GET[$this->name.'_onday']))		
				$this->js()->univ()->successMessage($_GET[$this->name.'_event_act']. ' ' . $_GET[$this->name.'_onday'])->execute();
			else
				$this->js()->univ()->errorMessage($_GET[$this->name.'_event_act']. 'not save on ' . $_GET[$this->name.'_onday'])->execute();
			exit;
		}
		
	}

	function setModel($model){
		$events = $this->getEvents($model);
		$this->calendar_options = array_merge($this->calendar_options,array('events'=>$events));
		parent::setModel($model);
	}


	function getEvents($campaign){
		$events = array();
		$news_letters_events = $campaign->ref('xMarketingCampaign/CampaignNewsLetter');
		foreach ($news_letters_events as $junk) {
			$events[] = array('title'=>$news_letters_events['newsletter'],'day'=>$news_letters_events['duration'], 'color'=>'#922', "_eventtype"=> "NewsLetter", "_nid"=> $news_letters_events['newsletter_id']);
		}

		// print_r($events);
		return $events;
	}

	function render(){
		// $this->app->jui->addInclude('subscriptioncalendar');
		$this->calendar_options['url']=$this->api->url(null);
		$this->calendar_options['schedular_name']=$this->name;
		$this->calendar_options['campaign_id']=$this->model->id;

		$this->js(true)->xepan_subscriptioncalander($this->calendar_options);
		parent::render();
	}

	//Subscription Campaign Add Event 
	function AddEvent($newsletter_id,$on_day){
		$campaign = $this->add('xMarketingCampaign/Model_Campaign')->load($_GET['campaign_id']);
		$campaign_start_date = strtotime($campaign['starting_date']);
		$campaign_end_date = strtotime($campaign['ending_date']);
		$duration = $on_day;

		switch ($campaign['effective_start_date']) {
			case 'SubscriptionDate':
				$campaign_newsletter_model = $this->add('xMarketingCampaign/Model_CampaignNewsLetter');
				if(!$campaign_newsletter_model->isExist($newsletter_id,$_GET['campaign_id'],$duration))
					return $campaign_newsletter_model->createNew($newsletter_id,$_GET['campaign_id'],$duration);
				
			break;

			case 'CampaignDate':	
				$s=array();
				$s[]= $this->js()->fullCalendar('removeEvents',array($_GET[$this->name.'_event_jsid']));
				$s[]= $this->js()->univ()->errorMessage('Campaign start from Subscription Date ');
				echo implode(";", $s);
				break;		
		}
	}

	//Subscription Campaign Move Event
	function MoveEvent($newsletter_id,$on_day){
		$from_day = $_GET[$this->name.'_fromday'];
		$campaign = $this->add('xMarketingCampaign/Model_Campaign')->load($_GET['campaign_id']);	
		$campaign_start_date = strtotime($campaign['starting_date']);
		$campaign_end_date = strtotime($campaign['ending_date']);		
		
		if($campaign['effective_start_date'] == "SubscriptionDate"){
			$campaign_newsletter_model = $this->add('xMarketingCampaign/Model_CampaignNewsLetter');
			if($campaign_newsletter_model->isExist($newsletter_id,$_GET['campaign_id'],$from_day)){
				$campaign_newsletter_model['duration'] = $on_day;
				$campaign_newsletter_model->saveAndUnload();
				return true;
			}
		}

	}

	function RemoveEvent($newsletter_id,$on_day){

		$campaign_newsletter_model = $this->add('xMarketingCampaign/Model_CampaignNewsLetter');
		$campaign_newsletter_model->addCondition('campaign_id',$_GET['campaign_id']);
		$campaign_newsletter_model->addCondition('newsletter_id',$newsletter_id);
		$campaign_newsletter_model->addCondition('duration',$on_day);
		$campaign_newsletter_model->tryLoadAny();
		if($campaign_newsletter_model->loaded()){
			$campaign_newsletter_model->delete();
			return true;
		}

		return false;
	}

	function defaultTemplate(){
		$this->app->pathfinder->base_location->addRelativeLocation(
		    'epan-components/'.__NAMESPACE__, array(
		        'php'=>'lib',
		        'template'=>'templates',
		        'css'=>'templates/css',
		        'js'=>'templates/js',
		    )
		);

		return array('view/subscriptioncalendar');
	}
}