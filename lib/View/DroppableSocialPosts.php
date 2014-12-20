<?php

namespace xMarketingCampaign;

class View_DroppableSocialPosts extends \Grid{
	public $preview_vp; // created in the page itself and passed here

	function setModel($model,$fields=array()){
		parent::setModel($model,$fields);
	}

	function recursiveRender(){
		$this->addFormatter('name','preview');
		$this->addFormatter('name','dropable');
		parent::recursiveRender();
	}

	function format_dropable($f){
		$this->current_row_html[$f] = '<div class="draggable-socialpost" data-event=\'{"title":"'.$this->model['name'].'", "_nid": '.$this->model->id.', "_eventtype": "SocialPost", "color":"#7a7"}\'  style="cursor: move">'.$this->current_row_html[$f].'</div>';
	}

	function format_preview($f){
		$this->current_row_html[$f]='<a href="javascript:void(0)" onclick="'. $this->js()->univ()->frameURL($this->model['name'],$this->api->url($this->preview_vp->getURL(),array('socialpost_id'=>$this->model->id))) .'">'.$this->current_row[$f].'</a>';
	}

	function render(){
		$this->js(true)->_selector('.draggable-socialpost')->draggable(array( 'helper'=> 'clone'));
		parent::render();
	}
}