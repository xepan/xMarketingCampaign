<?php

namespace xMarketingCampaign;

class View_CampaignScheduler extends \View{
	public $calendar_options = array();
	
	function init(){
		parent::init();
		$this->calendar_options = array("editable"=> true,'header'=>array('left'=>'prev,next today','center'=> 'title','right'=> 'month,agendaWeek,agendaDay'));
		// $this->app->layout->add('View_Error')->set('hello');
		if($what=$_GET[$this->name.'_event_type']){

			$func=$_GET[$this->name.'_event_act'].$what;

			$this->$func($_GET[$this->name.'_event_id'], $_GET[$this->name.'_ondate']);
			$s=array();
			$s[] = $this->js()->univ()->successMessage("Done");
			// $s[] = $this->js()->fullCalendar('removeEvents',array($_GET[$this->name.'_event_jsid']));
			echo implode(";", $s);
			exit;
		}
	}

	function RemoveSocialPost($socialpost_id,$on_date){
		$save = 0;
		$fromdate = $_GET[$this->name.'_fromdate'];
		$campaign = $this->add('xMarketingCampaign/Model_Campaign')->load($_GET['campaign_id']);
		$campaign_start_date = strtotime($campaign['starting_date']);
		$campaign_end_date = strtotime($campaign['ending_date']);
		$on_date_only = date('Y-m-d',strtotime($on_date));
		$time  = date("H:i:s",strtotime($on_date));
		$duration = $this->add('xDate')->diff(date('Y-m-d 00:00:00',strtotime($on_date)),$campaign['starting_date'],'days');
		$hour = $this->add('xDate')->getHour(date('Y-m-d H:i:s',strtotime($on_date)));
		$minute = $this->add('xDate')->getMinute(date('Y-m-d H:i:s',strtotime($on_date)));

		// throw new \Exception("c=".$_GET['campaign_id']."s=".$socialpost_id."on".$on_date_only."h".$hour."m".$minute);
		$campaign_socialpost_model = $this->add('xMarketingCampaign/Model_CampaignSocialPost');
		// $campaign_socialpost_model->addCondition('is_posted',false);
		$campaign_socialpost_model->addCondition('campaign_id',$_GET['campaign_id']);
		$campaign_socialpost_model->addCondition('socialpost_id',$socialpost_id);
		$campaign_socialpost_model->addCondition('post_on',$on_date_only);
		$campaign_socialpost_model->addCondition('at_hour',$hour);
		$campaign_socialpost_model->addCondition('at_minute',$minute);
		$campaign_socialpost_model->tryLoadAny();
		if($campaign_socialpost_model->loaded()){
			$campaign_socialpost_model->delete();
			return true;	
		}
		
	}

	function MoveSocialPost($socialpost_id,$on_date){
		$save = 0;
		$fromdate = $_GET[$this->name.'_fromdate'];
		$campaign = $this->add('xMarketingCampaign/Model_Campaign')->load($_GET['campaign_id']);
		$campaign_start_date = strtotime($campaign['starting_date']);
		$campaign_end_date = strtotime($campaign['ending_date']);
		$today = strtotime(date('Y-m-d'));
		$on_date_only = date('Y-m-d',strtotime($on_date));
		$time  = date("H:i:s",strtotime($on_date));
		$duration = $this->add('xDate')->diff(date('Y-m-d 00:00:00',strtotime($on_date)),$campaign['starting_date'],'days');
		$hour = $this->add('xDate')->getHour(date('Y-m-d H:i:s',strtotime($on_date)));
		$minute = $this->add('xDate')->getMinute(date('Y-m-d H:i:s',strtotime($on_date)));
		
		if($campaign['effective_start_date'] == "CampaignDate"){
			// throw new \Exception("c.".$_GET['campaign_id']."post".$socialpost_id."date".$on_date_only."h-".$hour."m".$minute."=ondate..".$on_date);
			// $_GET['campaign_id'],$socialpost_id,$on_date_only,$hour,$minute
			if(strtotime($on_date_only) < $today){
				$s=array();
				$s[]= "revertFunc();";
				$s[]= $this->js()->univ()->errorMessage('Could Not Saved');
				echo implode(";", $s);
				exit;		
			}

			if(strtotime($on_date_only) > $campaign_start_date and $campaign_end_date >= strtotime($on_date_only)){
				$campaign_socialpost_model = $this->add('xMarketingCampaign/Model_CampaignSocialPost');
				if(!$campaign_socialpost_model->isExist($_GET['campaign_id'],$socialpost_id,$on_date_only,$hour,$minute)){
					$save = 1;
				}				
			}
		}
			
		if($save){
			$from_date_only = date('Y-m-d',strtotime($fromdate));
			$from_hour = $this->add('xDate')->getHour(date('Y-m-d H:i:s',strtotime($fromdate)));
			$from_minute = $this->add('xDate')->getMinute(date('Y-m-d H:i:s',strtotime($fromdate)));

			$campaign_socialpost_model = $this->add('xMarketingCampaign/Model_CampaignSocialPost');
			$campaign_socialpost_model->addCondition('is_posted',false);
			$campaign_socialpost_model->addCondition('campaign_id',$_GET['campaign_id']);
			$campaign_socialpost_model->addCondition('socialpost_id',$socialpost_id);
			$campaign_socialpost_model->addCondition('post_on',$from_date_only);
			$campaign_socialpost_model->addCondition('at_hour',$from_hour);
			$campaign_socialpost_model->addCondition('at_minute',$from_minute);
			$campaign_socialpost_model->tryLoadAny();
			if($campaign_socialpost_model->loaded()){
				if($hour==0)
					$hour = '00';
				if($minute==0)
					$minute = '00';
				$campaign_socialpost_model['post_on'] = $on_date_only;
				$campaign_socialpost_model['at_hour'] = $hour;
				$campaign_socialpost_model['at_minute'] = $minute;
				$campaign_socialpost_model->saveAndUnload();
			}
			return true;
		}else{
			$s=array();
			$s[]= "revertFunc();";
			$s[]= $this->js()->univ()->errorMessage('Could Not Saved');
			echo implode(";", $s);
			exit;
		}

	}

	function AddSocialPost($socialpost_id,$on_date){
		$save = 0;
		$campaign = $this->add('xMarketingCampaign/Model_Campaign')->load($_GET['campaign_id']);
		$campaign_start_date = strtotime($campaign['starting_date']);
		$campaign_end_date = strtotime($campaign['ending_date']);
		$on_date_only = date('Y-m-d',strtotime($on_date));
		$time  = date("H:i:s",strtotime($on_date));
		$duration = $this->add('xDate')->diff(date('Y-m-d 00:00:00',strtotime($on_date)),$campaign['starting_date'],'days');
		$hour = $this->add('xDate')->getHour(date('Y-m-d H:i:s',strtotime($on_date)));
		$minute = $this->add('xDate')->getMinute(date('Y-m-d H:i:s',strtotime($on_date)));
		// throw new \Exception("Error Processing Request".$hour."m".$minute);		

		switch ($campaign['effective_start_date']) {
			case 'SubscriptionDate':
				$s=array();
				$s[]= $this->js()->fullCalendar('removeEvents',array($_GET[$this->name.'_event_jsid']));
				$s[]= $this->js()->univ()->errorMessage('Campaign start from Subscription Date ');
				echo implode(";", $s);
				exit;				
				break;

			case 'CampaignDate':
				if(strtotime($on_date_only) > $campaign_start_date and $campaign_end_date >= strtotime($on_date_only)){
					$campaign_scialpost_model = $this->add('xMarketingCampaign/Model_CampaignSocialPost');
					if(!$campaign_scialpost_model->isExist($_GET['campaign_id'],$socialpost_id,$on_date_only,$hour,$minute))
						$save = 1;						
				}	
				break;		
		}

		if($save){
			if($hour==0)
				$hour = '00';
			if($minute==0)
				$minute = '00';

			$campaign_scialpost_model = $this->add('xMarketingCampaign/Model_CampaignSocialPost');
			return $campaign_scialpost_model->createNew($_GET['campaign_id'],$socialpost_id,$on_date_only,$hour,$minute);
		}

		$s=array();
		$s[]= $this->js()->fullCalendar('removeEvents',array($_GET[$this->name.'_event_jsid']));
		$s[]= $this->js()->univ()->errorMessage('Could Not Saved');
		echo implode(";", $s);
		exit;

	}	

	function RemoveNewsletter($newsletter_id,$on_date){
		$campaign = $this->add('xMarketingCampaign/Model_Campaign')->load($_GET['campaign_id']);	
		$campaign_start_date = strtotime($campaign['starting_date']);
		$campaign_end_date = strtotime($campaign['ending_date']);
		$duration = $this->add('xDate')->diff(date('Y-m-d 00:00:00',strtotime($on_date)),$campaign['starting_date'],'days');

		
		$campaign_newsletter_model = $this->add('xMarketingCampaign/Model_CampaignNewsLetter');	
		$campaign_newsletter_model->addCondition('newsletter_id',$newsletter_id);
		$campaign_newsletter_model->addCondition('campaign_id',$_GET['campaign_id']);
		$campaign_newsletter_model->addCondition('duration',$duration);
		$campaign_newsletter_model->tryLoadAny();
		if($campaign_newsletter_model->loaded()){
			$campaign_newsletter_model->delete();
			// throw new \Exception("ne".$newsletter_id."d".$duration."cam".$_GET['campaign_id']);
			return true;
		}
	}

	function MoveNewsLetter($newsletter_id,$on_date){
		$fromdate = $_GET[$this->name.'_fromdate'];
		$save = 0;
		$today = strtotime(date('Y-m-d'));
		$campaign = $this->add('xMarketingCampaign/Model_Campaign')->load($_GET['campaign_id']);	
		$campaign_start_date = strtotime($campaign['starting_date']);
		$campaign_end_date = strtotime($campaign['ending_date']);
		$duration = $this->add('xDate')->diff(date('Y-m-d 00:00:00',strtotime($on_date)),$campaign['starting_date'],'days');
		$last_duration = $this->add('xDate')->diff(date('Y-m-d 00:00:00',strtotime($fromdate)),$campaign['starting_date'],'days');
		// throw new \Exception("Error Processing Request".$last_duration);
		
		// throw new \Exception("news = ".$newsletter_id."cam = ".$_GET['campaign_id']."dur =".$duration);
		
		if($campaign['effective_start_date'] == "CampaignDate"){
			if(strtotime($on_date) < $today){
				$s=array();
				$s[]= "revertFunc();";
				$s[]= $this->js()->univ()->errorMessage('Could Not Saved');
				echo implode(";", $s);
				exit;	
			}

			if(strtotime($on_date) > $campaign_start_date and $campaign_end_date >= strtotime($on_date)){
				$campaign_newsletter_model = $this->add('xMarketingCampaign/Model_CampaignNewsLetter');
				if(!$campaign_newsletter_model->isExist($newsletter_id,$_GET['campaign_id'],$duration)){
					$save = 1;
				}				
			}

		}
			
		if($save){
			$campaign_newsletter_model = $this->add('xMarketingCampaign/Model_CampaignNewsLetter');
			$campaign_newsletter_model->addCondition('newsletter_id',$newsletter_id);
			$campaign_newsletter_model->addCondition('campaign_id',$_GET['campaign_id']);
			$campaign_newsletter_model->addCondition('duration',$last_duration);
			$campaign_newsletter_model->tryLoadAny();
			if($campaign_newsletter_model->loaded()){
				$campaign_newsletter_model['duration'] = $duration;
				$campaign_newsletter_model->saveAndUnload();
			}
			return true;
		}else{
			$s=array();
			$s[]= "revertFunc();";
			$s[]= $this->js()->univ()->errorMessage('Could Not Saved');
			echo implode(";", $s);
			exit;
		}

	}

	function AddNewsLetter($newsletter_id,$on_date){
		$save = 0;
		$campaign = $this->add('xMarketingCampaign/Model_Campaign')->load($_GET['campaign_id']);	
		$campaign_start_date = strtotime($campaign['starting_date']);
		$campaign_end_date = strtotime($campaign['ending_date']);
		$duration = $this->add('xDate')->diff(date('Y-m-d 00:00:00',strtotime($on_date)),$campaign['starting_date'],'days');

		switch ($campaign['effective_start_date']) {
			case 'SubscriptionDate':
				$s=array();
				$s[]= $this->js()->fullCalendar('removeEvents',array($_GET[$this->name.'_event_jsid']));
				$s[]= $this->js()->univ()->errorMessage('Campaign start from Subscription Date ');
				echo implode(";", $s);
				exit;				
				break;

			case 'CampaignDate':
				if(strtotime($on_date) > $campaign_start_date and $campaign_end_date >= strtotime($on_date)){
					$campaign_newsletter_model = $this->add('xMarketingCampaign/Model_CampaignNewsLetter');
					if(!$campaign_newsletter_model->isExist($newsletter_id,$_GET['campaign_id'],$duration))
						$save = 1;						
				}	
				break;		
		}

		if($save){
			$campaign_newsletter_model = $this->add('xMarketingCampaign/Model_CampaignNewsLetter');
			return $campaign_newsletter_model->createNew($newsletter_id,$_GET['campaign_id'],$duration);
		}

		$s=array();
		$s[]= $this->js()->fullCalendar('removeEvents',array($_GET[$this->name.'_event_jsid']));
		$s[]= $this->js()->univ()->errorMessage('Could Not Saved');
		echo implode(";", $s);
		exit;
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
			$events[] = array('title'=>$news_letters_events['newsletter'],'start'=>$news_letters_events['posting_date'], 'color'=>'#922', "_eventtype"=> "NewsLetter", "_nid"=> $news_letters_events['newsletter_id']);
		}

		$social_events = $campaign->ref('xMarketingCampaign/CampaignSocialPost');
		foreach ($social_events as $junk) {
			$events[] = array('title'=>$social_events['socialpost'],'start'=>$social_events['post_on_datetime'],'color'=>'#7a7', "_eventtype"=> "SocialPost", "_nid"=> $social_events['socialpost_id']);
		}
		// print_r($events);
		return $events;
	}

	function render(){		
		$this->js(true)->_load('full-calendar/lib/moment.min')->_load('full-calendar/fullcalendar.min')->_load('campaigncalendar')->univ()->campaigncalendar($this,$this->calendar_options, $this->api->url(null), $this->name, $this->model->id);
		parent::render();
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

		return array('view/calendar');
	}

}