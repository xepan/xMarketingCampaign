<?php

namespace xMarketingCampaign;

class Model_FacebookConfig extends \Model_Table{
	public $table='xMarketingCampaign_FacebookConfig';

	function init(){
		parent::init();

		$this->addField('name');
		$this->addField('userid');
		$this->addField('userid_returned');
		$this->addField('appId');
		$this->addField('secret');
		$this->addField('access_token')->system(false);
		$this->addField('is_access_token_valid')->type('boolean')->defaultValue(false)->system(true);

		$this->add('dynamic_model/Controller_AutoCreator');
	}
}

class Controller_SocialPosters_Facebook extends Controller_SocialPosters_Base_Social{
	public $fb=null;
	public $config=null;

	function init(){
		parent::init();
		require_once('epan-components/xMarketingCampaign/lib/Controller/SocialPosters/Facebook/facebook.php');
		
		$this->config = $fb_config = $this->add('xMarketingCampaign/Model_FacebookConfig')->tryLoadAny();
		
		if(!$this->config->loaded()) $this->config->save();

		if(!$fb_config->loaded()) return;

		$config = array(
		      'appId' => $fb_config['appId'],
		      'secret' => $fb_config['secret'],
		      'fileUpload' => true, // optional
		      'allowSignedRequest' => false, // optional, but should be set to false for non-canvas apps
		  );

		$this->fb = $facebook = new \Facebook($config);
		
		if($_GET['facebook_logout']){
			$this->fb->destroySession();
		}
	}

	function login_status(){
		if(!$this->fb){
			return "Configuration Problem";
		}
		$user_id = $this->fb->getUser();
		if(!$user_id){
			$login_url = $this->fb->getLoginUrl(array('scope'=>'publish_actions,status_update,publish_stream,user_groups','redirect_uri'=>'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?page=xMarketingCampaign_page_socialafterloginhandler&xfrom=Facebook'));
		  	return '<a class="btn btn-danger btn-xs" href="'.$login_url.'">Login</a>';
		}else{
			$this->config['userid_returned'] = $user_id;
			$this->config->save();
		  	return '<a class="btn btn-success btn-xs" href="#" onclick="javascript:'.$this->owner->js()->reload(array('facebook_logout'=>1)).'">Logout</a>';
		}

	}

	function after_login_handler(){
		if(!$this->fb){
			return "Configuration Problem";
		}

		$user_id = $this->fb->getUser();
		$this->fb->setExtendedAccessToken();

		$new_token = $this->fb->getAccessToken();
		$this->config['access_token'] = $new_token;
		$this->config['is_access_token_valid']= true;
		$this->config->save();
	}


	function config_page(){
		$c=$this->owner->add('CRUD',array('allow_add'=>false,'allow_del'=>false));
		$c->setModel('xMarketingCampaign/FacebookConfig');
	}

	function post($params){ // all social post row as hash array

	  	try{



	  		$post_content=array();

	  		$api='feed';

	  		if($params['post_title']) $post_content['title'] = $params['post_title'];
	  		if($params['url']) $post_content['link'] = $params['url'];
	  		if($params['image']){
	  			$api='photos';
	  			$this->fb->setFileUploadSupport(true);
	  			$post_content['ImageSource'] = '@'.realpath($params['image']);
	  		} 
	  		if($params['message_255_chars']) $post_content['message'] = $params['message_255_chars'];

          	$post_content['access_token'] = $this->config['access_token'];

	  		$ret_obj = $this->fb->api('/'. $this->config['userid_returned'] .'/'.$api, 'POST',
	  								$post_content
                                 );

	  		// Now posting to groups as well
	  		// get all groups
	  		$groups = $this->fb->api('/'. $this->config['userid_returned'] .'/groups', 'GET',array('access_token'=>$this->config['access_token']));
	  		// $groups = json_decode($groups,true);
	  		// print_r($groups);

	  		foreach ($groups['data'] as $grp) {
	  			// print_r($grp);
		  		$ret_obj = $this->fb->api('/'. $grp['id'] .'/'.$api, 'POST',$post_content);
		  		// print_r($ret_obj);
	  		}

	  	}catch(\Exception $e){

	  		echo "<h2>".$e->getMessage()."</h2>";
	  		// print_r($post_content);
	  	}
	  	
	}

	function get_post_fields_using(){
		return array('title','url','image','255');
	}
}

