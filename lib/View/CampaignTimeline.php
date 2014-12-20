<?php

namespace xMarketingCampaign;

class view_CampaignTimeline extends \View{

	function init(){
		parent::init();

	}

	function render(){
		// $this->js(true)->_load('timeline/assets/js/timeline-min')->_load('timelinescript');//($this,$this->calendar_options, $this->api->url(null), $this->name, $this->model->id);
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

		return array('view/timeline');
	}
}