<?php

namespace xMarketingCampaign;

class Model_SocialConfig extends \Model_Table{
	public $table='xMarketingCampaign_SocialConfig';

	function init(){
		parent::init();

		$this->addField('social_app')->mandatory(true)->system(true); // Must Be Set In Extended class

		$this->addField('name');
		$this->addField('appId')->type('text');
		$this->addField('secret')->type('text');
		$this->addField('post_in_groups')->type('boolean')->defaultValue(true);
		$this->addField('filter_repeated_posts')->type("boolean")->defaultValue(true);

		$this->hasMany('xMarketingCampaign/SocialUsers','config_id');

		$this->addHook('beforeDelete',$this);

		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeDelete(){
		$this->ref('xMarketingCampaign/SocialUsers')->deleteAll();
	}

}

class Model_SocialUsers extends \Model_Table{
	public $table='xMarketingCampaign_SocialUsers';

	function init(){
		parent::init();
		$this->hasOne('xMarketingCampaign/SocialConfig','config_id');
		
		$this->addField('name');
		$this->addField('userid'); // Used for profile in case different then api returned userid like facebook
		$this->addField('userid_returned');
		$this->addField('access_token')->system(false)->type('text');
		$this->addField('access_token_secret')->system(false)->type('text');
		$this->addField('access_token_expiry')->system(false)->type('datetime');
		$this->addField('is_access_token_valid')->type('boolean')->defaultValue(false)->system(true);
		$this->addField('is_active')->type('boolean')->defaultValue(true);

		$this->add('dynamic_model/Controller_AutoCreator');
	}
}

// Model Post

class Model_SocialPosting extends \Model_Table{
	public $table="xMarketingCampaign_SocialPostings";

	function init(){
		parent::init();

		$this->addExpression('social_app')->set(function($m,$q){
			$config = $m->add('xMarketingCampaign/Model_SocialConfig',array('table_alais'=>'tmp'));
			$user_j = $config->join('xMarketingCampaign_SocialUsers.config_id');
			$user_j->addField('user_j_id','id');

			$config->addCondition('user_j_id',$q->getField('user_id'));

			return $config->fieldQuery('social_app');

		})->caption('At')->sortable(true);


		$this->hasOne('xMarketingCampaign/Model_SocialUsers','user_id');
		$this->hasOne('xMarketingCampaign/SocialPost','post_id');
		
		$this->hasOne('xMarketingCampaign/Campaign','campaign_id')->sortable(true);

		$this->addField('post_type')->mandatory(true)->sortable(true); // Status Update / Share a link / Group Post etc.

		$this->addField('postid_returned'); // Rturned by social site 
		$this->addField('posted_on')->type('datetime')->defaultValue(date('Y-m-d H:i:s'))->sortable(true);
		$this->addField('group_id')->sortable(true);
		$this->addField('group_name')->sortable(true);

		$this->addField('likes')->sortable(true)->defaultValue(0); // Change Caption in subsequent extended social controller, if nesecorry
		$this->addField('share')->sortable(true)->defaultValue(0); // Change Caption in subsequent extended social controller, if nesecorry
		$this->addExpression('total_comments')->set(function($m,$q){
			return $m->refSQL('xMarketingCampaign/Activity')->count();
		})->sortable(true);

		$this->addExpression('unread_comments')->set(function($m,$q){
			return $m->refSQL('xMarketingCampaign/Activity')->addCondition('is_read',false)->count();
		})->sortable(true);

		$this->addField('is_monitoring')->type('boolean')->defaultValue(true)->sortable(true);
		$this->addField('force_monitor')->type('boolean')->defaultValue(false)->caption('Keep Monitoring')->sortable(true);

		$this->hasMany('xMarketingCampaign/Activity','posting_id');

		$this->add('dynamic_model/Controller_AutoCreator');
	}

	function keep_monitoring(){
		if($this['force_monitor']){
			$this['force_monitor']=false;
			$this['is_monitoring']=false;
		}else{
			$this['force_monitor']=true;
			$this['is_monitoring']=true;
		}
		$this->save();
		return $this;
	}

	function create($user_id, $social_post_id, $postid_returned, $post_type,$group_id=0,$group_name="", $campaign_id=0){
		if($this->loaded()) $this->unload();

		$this['post_type'] = $post_type;
		$this['user_id'] = $user_id;
		$this['post_id'] = $social_post_id;
		$this['postid_returned'] = $postid_returned;
		$this['campaign_id'] = $campaign_id;
		$this['group_id'] = $group_id;
		$this['group_name'] = $group_name;
		$this->save();

		return $this;

	}

	function updateLikesCount($count){
		$this['likes']=$count;
		$this->save();
	}

	function updateShareCount($count){
		$this['share']=$count;
		$this->save();
	}

}


// Model Post Activity/Comments
class Model_Activity extends \SQL_Model{
	public $table = "xMarketingCampaign_SocialPostings_Activities";

	function init(){
		parent::init();
		$this->hasOne('xMarketingCampaign/Model_SocialPosting','posting_id');

		$this->addField('activityid_returned');
		$this->addField('activity_type');
		$this->addField('activity_on')->type('datetime'); // NOT DEFAuLT .. MUst get WHEN actual activity happened from social sites

		$this->addField('is_read')->type('boolean')->defaultValue(false);// is read
		$this->addField('activity_by');// Get the user from social site who did it.. might be an id of the user on that social site
		$this->addField('name')->caption('Activity')->allowHTML(true);
		$this->addField('action_allowed')->defaultValue(''); // Can remove/ can edit etc if done by user itself

		$this->add('dynamic_model/Controller_AutoCreator');		
	}

}

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

	function postSingle($user_model,$params,$post_in_groups=true, &$groups_posted=array(),$under_campaign_id=0){
		throw $this->exception('Define in extnding class');
	}

	function postAll($params){
		throw $this->exception('Define in extnding class');
		
	}

	function icon($only_css_class=false){
		throw $this->exception('Define in extnding class');
	}

	function profileURL($user_id){
		throw $this->exception('Define in extnding class');
	}

	function postURL($post_id){
		throw $this->exception('Define in extnding class');
	}

	function groupURL($group_id){
		throw $this->exception('Define in extnding class');
	}

	function updateActivities($posting_model){
		throw $this->exception('Define in extnding class');
	}

	function comment($posting_model){
		throw $this->exception('Define in extnding class');
	}

}