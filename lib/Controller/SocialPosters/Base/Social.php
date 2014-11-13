<?php

namespace xMarketingCampaign;

class Controller_SocialPosters_Base_Social extends \AbstractController{

	function login_status(){
		return "Oops";
	}

	function config_page(){
		echo "Oops";
	}

	function get_post_fields_using(){
		return array('title','image','255');
	}

	function post($params){
		
	}

}