<?php

namespace xMarketingCampaign;

class Model_GoogleBloggerConfig extends \Model_Table{
	public $table='xMarketingCampaign_GoogleBloggerConfig';

	function init(){
		parent::init();

		$this->addField('name');
		$this->addField('userid');
		$this->addField('userid_returned');
		$this->addField('blogid');
		$this->addField('appId')->display(array('grid'=>'shorttext,wrap'));
		$this->addField('secret');
		$this->addField('access_token')->system(false)->type('text');
		$this->addField('access_token_secret')->system(false)->type('text');
		$this->addField('refresh_token')->system(false)->type('text');
		$this->addField('is_access_token_valid')->type('boolean')->defaultValue(false)->system(true);
		$this->addField('is_active')->type('boolean')->defaultValue(true);

		$this->add('dynamic_model/Controller_AutoCreator');
	}
}

class Controller_SocialPosters_GoogleBlogger extends Controller_SocialPosters_Base_Social{
	public $client=null;
	public $client_config=null;

	function init(){
		parent::init();

		require_once('epan-components/xMarketingCampaign/lib/Controller/SocialPosters/Base/http.php');
		require_once('epan-components/xMarketingCampaign/lib/Controller/SocialPosters/Base/oauth/client/class.php');
		
		$this->client_config = $client_config = $this->add('xMarketingCampaign/Model_GoogleBloggerConfig')->tryLoadAny();
		
		if(!$this->client_config->loaded()) $this->client_config->save();

		if(!$client_config->loaded()) return;

		$this->client = $client = new \oauth_client_class;
		$client->debug = 1;
		$client->offline = true;
		$client->debug_http = 1;
		$client->server = 'Google';
		$client->redirect_uri = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?page=xMarketingCampaign_page_socialafterloginhandler&xfrom=GoogleBlogger';

		$client->client_id = $this->client_config['appId']; $application_line = __LINE__;
		$client->client_secret = $this->client_config['secret'];
		$client->refresh_token = $this->client_config['refresh_token'];
		// $client->access_token = $this->client_config['access_token'];

		/*  API permission scopes
		 *  Separate scopes with a space, not with +
		 */
		$client->scope = 'https://www.googleapis.com/auth/blogger';

		// if($_GET['facebook_logout']){
		// 	$this->fb->destroySession();
		// }
	}

	function login_status(){
		$client = $this->client;

		if(($success = $client->Initialize()))
		{
			if(($success = $client->Process()))
			{
				if(strlen($client->access_token))
				{
					// $success = $client->CallAPI(
					// 	'http://api.linkedin.com/v1/people/~', 
					// 	'GET', array(
					// 		'format'=>'json'
					// 	), array('FailOnAccessError'=>true), $user);
				}
			}
			$success = $client->Finalize($success);
		}
		if($client->exit){
			exit;
		}
		if(strlen($client->authorization_error))
		{
			$client->error = $client->authorization_error;
			$success = false;
		}

		if($success){
			// echo $this->client->access_token;
			// $this->client_config['access_token'] = $this->client->access_token;
			// $this->client_config->save();
		}

		// echo $user->name;

		return "https://www.linkedin.com/uas/oauth2/authorization?response_type=code&client_id=".$this->client_config['appId']."&scope=".urlencode($this->client->scope)."&state=XAVOCXEPANCODE123&redirect_uri=". urlencode($this->client->redirect_uri);

	}

	function after_login_handler(){
		if(!$this->client){
			return "Configuration Problem";
		}

		$client = $this->client;

		if(($success = $client->Initialize()))
		{
			if(($success = $client->Process()))
			{
				if(strlen($client->access_token))
				{
					// $success = $client->CallAPI(
					// 	'http://api.linkedin.com/v1/people/~', 
					// 	'GET', array(
					// 		'format'=>'json'
					// 	), array('FailOnAccessError'=>true), $user);
				}
			}
			$success = $client->Finalize($success);
		}
		if($client->exit)
			exit;
		if(strlen($client->authorization_error))
		{
			$client->error = $client->authorization_error;
			$success = false;
		}

		if($success){
			// echo $this->client->access_token;
			$li_user['access_token'] = $this->client->access_token;
			$li_user['access_token_secret'] = $this->client->access_token_secret;
			$li_user['access_token_expiry'] = $this->client->access_token_expiry;
			$this->client_config['refresh_token'] = $this->client->refresh_token;
			$this->client_config->save();
		}else{
			echo "oops:".$client->error;
		}
	}

	function post($params){ // all social post row as hash array
		// return;
	  	try{
	  		$client = $this->client;
	  		
	  		if(!$client['is_active']) return;
	  		
	  		if(!$client->Initialize())
	  			echo "not init";
	  		if(!$client->Process())
	  			echo "not process";

	  		$client->access_token = $this->client_config['access_token'];
	  		$client->access_token_secret = $users['access_token_secret'];
	  		
	  		echo "posting to ". $client->access_token;

	  		$post= new \stdClass;
	  		$post->kind='blogger#post';
	  		$post->blog = new \stdClass;
	  		$post->blog->id = $this->client_config['blogid'];
	  		$post->title = $params['post_title'];
	  		$post->content = $params['message_blog'];

			$success = $client->CallAPI(
				'https://www.googleapis.com/blogger/v3/blogs/'.$this->client_config['blogid'].'/posts/',
				'POST', $post , array('FailOnAccessError'=>true, 'RequestContentType'=>'application/json'), $user);

			if($client->access_token != $this->client_config['access_token']){
				$client_config['access_token'] = $client->access_token;
				$this->client_config->save();
			}

			$success = $client->Finalize($success);

			if(!$success) throw $this->exception('not posted'.$client->error);


	  	}catch(\Exception $e){

	  		echo "<h2>Error: ".$e->getMessage()."</h2>";
	  		// print_r($post_content);
	  	}
	  	
	}

	function config_page(){
		$c=$this->owner->add('CRUD',array('allow_add'=>false,'allow_del'=>false));
		$c->setModel('xMarketingCampaign/GoogleBloggerConfig');

		if($c->grid){
			$f=$c->addFrame('Login URL');
			if($f){
				$f->add('View')->setElement('a')->setAttr('href','index.php?page=xMarketingCampaign_page_socialloginmanager&social_login_to=GoogleBlogger')->setAttr('target','_blank')->set('index.php?page=xMarketingCampaign_page_socialloginmanager&social_login_to=GoogleBlogger');
			}
		}

		$c->add('Controller_FormBeautifier');
	}
}