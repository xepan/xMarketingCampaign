<?php

namespace xMarketingCampaign;

class Model_LinkedinConfig extends \Model_Table{
	public $table='xMarketingCampaign_LinkedinConfig';

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

class Controller_SocialPosters_Linkedin extends Controller_SocialPosters_Base_Social{
	public $client=null;
	public $client_config=null;

	function init(){
		parent::init();

		require_once('epan-components/xMarketingCampaign/lib/Controller/SocialPosters/Base/http.php');
		require_once('epan-components/xMarketingCampaign/lib/Controller/SocialPosters/Base/oauth/client/class.php');
		
		$this->client_config = $client_config = $this->add('xMarketingCampaign/Model_LinkedinConfig')->tryLoadAny();
		
		if(!$this->client_config->loaded()) $this->client_config->save();

		if(!$client_config->loaded()) return;

		$this->client = $client = new \oauth_client_class;
		$client->debug = 1;
		$client->debug_http = 1;
		$client->server = 'LinkedIn';
		$client->redirect_uri = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?page=xMarketingCampaign_page_socialafterloginhandler&xfrom=Linkedin';

		$client->client_id = $this->client_config['appId']; $application_line = __LINE__;
		$client->client_secret = $this->client_config['secret'];
		// $client->access_token = $this->client_config['access_token'];

		/*  API permission scopes
		 *  Separate scopes with a space, not with +
		 */
		$client->scope = 'rw_company_admin w_messages r_basicprofile r_contactinfo r_fullprofile r_network r_emailaddress rw_nus rw_groups';

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
					$success = $client->CallAPI(
						'http://api.linkedin.com/v1/people/~', 
						'GET', array(
							'format'=>'json'
						), array('FailOnAccessError'=>true), $user);
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
			$this->client_config['access_token'] = $this->client->access_token;
			$this->client_config->save();
		}

		echo $user->name;

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
					$success = $client->CallAPI(
						'http://api.linkedin.com/v1/people/~', 
						'GET', array(
							'format'=>'json'
						), array('FailOnAccessError'=>true), $user);
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
			echo $this->client->access_token;
			$this->client_config['access_token'] = $this->client->access_token;
			$this->client_config->save();
		}

	}


	function config_page(){
		$c=$this->owner->add('CRUD',array('allow_add'=>false,'allow_del'=>false));
		$c->setModel('xMarketingCampaign/LinkedinConfig');
	}

	function post($params){ // all social post row as hash array
	  	try{
	  		$client = $this->client;
	  		
	  		$client->access_token = $this->client_config['access_token'];
	  		
	  		if(!$client->Initialize())
	  			echo "not init";
	  		if(!$client->Process())
	  			echo "not process";
			
	  		
	  		echo "posting to ". $client->access_token;


	  		// echo $parameters->content->{'submitted-image-url'};

			if($params['url'] and $params['image']){
				// Its a share 

				/*
					<?xml version="1.0" encoding="UTF-8"?>
					<share>
					    <comment>83% of employers will use social media to hire: 78% LinkedIn, 55% Facebook, 45% Twitter [SF Biz Times] http://bit.ly/cCpeOD</comment>
					    <content>
					        <title>Survey: Social networks top hiring tool - San Francisco Business Times</title>
					        <submitted-url>http://sanfrancisco.bizjournals.com/sanfrancisco/stories/2010/06/28/daily34.html</submitted-url>
					        <submitted-image-url>http://images.bizjournals.com/travel/cityscapes/thumbs/sm_sanfrancisco.jpg</submitted-image-url>
					    </content>
					    <visibility>
					        <code>anyone</code>
					    </visibility>
					</share>
				*/

		  		$parameters = new \stdClass;
				$parameters->content = new \stdClass;
				$parameters->visibility = new \stdClass;
				$parameters->visibility->code = 'anyone';
		  		if($params['post_title']) $parameters->content->title = $params['post_title'];
		  		if($params['message_255_chars']) $parameters->comment = $params['message_255_chars'];
		  		
		  		$parameters->content->{'submitted-url'} = $params['url'];
		  		$parameters->content->{'submitted-image-url'} = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/' .$params['image'];
				
				// share on self status

				$success = $client->CallAPI(
				'http://api.linkedin.com/v1/people/~/shares',
				'POST', $parameters, array('FailOnAccessError'=>true, 'RequestContentType'=>'application/json'), $user);
				echo "<pre>";

				print_r($success);
				// Share on all joined groups

				// Get al lgroups
				$success = $client->CallAPI(
				'http://api.linkedin.com/v1/people/~/group-memberships',
				'GET', null, array('FailOnAccessError'=>true, 'RequestContentType'=>'application/json'), $groups);

				$groups =simplexml_load_string($groups);
				$groups = json_encode($groups);
				$groups = json_decode($groups,true);
				print_r($groups['group-membership']);
				$parameters->title = $parameters->content->title;
				$parameters->summary = $parameters->comment;

				unset($parameters->visibility);
				unset($parameters->comment);

				if(isset($groups['group-membership'])){
					foreach ($groups['group-membership'] as $grp) {
						// print_r($grp);
						$grp_id= $grp['group']['id'];
						echo $grp_id ."<br/>";

						$success = $client->CallAPI(
							'http://api.linkedin.com/v1/groups/'.$grp_id.'/posts',
							'POST', $parameters, array('FailOnAccessError'=>true, 'RequestContentType'=>'application/json'), $groups);
					}
				}
				echo "</pre>";


			}

			if(!$params['url'] and !$params['image']){
				// its network update
				/*	<?xml version='1.0' encoding='UTF-8'?>
					<activity locale="en_US">
				    	<content-type>linkedin-html</content-type>
				    	<body>&amp;lt;a href=&amp;quot;http://www.linkedin.com/profile?viewProfile=&amp;amp;key=3639896&amp;amp;authToken=JdAa&amp;amp;authType=name&amp;amp;trk=api*a119686*s128146*&amp;quot;&amp;gt;Kirsten Jones&amp;lt;/a&amp;gt; is reading about &amp;lt;a href=&amp;quot;http://www.tigers.com&amp;quot;&amp;gt;Tigers&amp;lt;/a&amp;gt;http://www.tigers.com&amp;gt;Tigers&amp;lt;/a&amp;gt;..</body>
					</activity>
				*/
				$parameters = new \stdClass;
				$parameters->{'content-type'} = 'linkedin-html';
		  		// if($params['post_title']) $parameters->content->title = $params['post_title'];
		  		if($params['message_255_chars']) $parameters->body = $params['message_255_chars'];
		  		
				$success = $client->CallAPI(
				'http://api.linkedin.com/v1/people/~/person-activities',
				'POST', $parameters, array('FailOnAccessError'=>true, 'RequestContentType'=>'application/json'), $user);

				// Share on all joined groups

				// Get al lgroups
				$success = $client->CallAPI(
				'http://api.linkedin.com/v1/people/~/group-memberships',
				'GET', null, array('FailOnAccessError'=>true, 'RequestContentType'=>'application/json'), $groups);

				$groups =simplexml_load_string($groups);
				$groups = json_encode($groups);
				$groups = json_decode($groups,true);
				// echo "<pre>";
				// print_r($groups['group-membership']);
				$parameters->title = $parameters->content->title;
				$parameters->summary = $parameters->comment;

				unset($parameters->visibility);
				unset($parameters->comment);

				foreach ($groups['group-membership'] as $grp) {
					// print_r($grp);
					$grp_id= $grp['group']['id'];
					echo $grp_id ."<br/>";

					$success = $client->CallAPI(
						'http://api.linkedin.com/v1/groups/'.$grp_id.'/posts',
						'POST', $parameters, array('FailOnAccessError'=>true, 'RequestContentType'=>'application/json'), $groups);
				}
				// echo "</pre>";

			}

			$success = $client->Finalize($success);

			if(!$success) throw $this->exception('not posted'.$client->error);


	  	}catch(\Exception $e){

	  		echo "<h2>Error: ".$e->getMessage()."</h2>";
	  		// print_r($post_content);
	  	}
	  	
	}

	function get_post_fields_using(){
		return array('title','url','image','255');
	}
}

