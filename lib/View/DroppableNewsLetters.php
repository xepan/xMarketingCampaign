<?php

namespace xMarketingCampaign;


class View_DroppableNewsLetters extends \Grid{

	public $preview_vp; // created in the page itself and passed here

	function init(){
		// $this->rename('x');
		parent::init();
		$this->template->tryDel('Pannel');
		
	}


	function recursiveRender(){
		$this->addFormatter('name','preview');
		$this->addFormatter('name','dropable');
		parent::recursiveRender();
	}

	function format_dropable($f){
		$this->current_row_html[$f] = '<div class="draggable-newsletter" data-event=\'{"title":"'.$this->model['name'].'", "_nid": '.$this->model->id.', "_eventtype": "NewsLetter", "color":"#922" }\' style="cursor: move">'.$this->current_row_html[$f].'</div>';
	}

	function format_preview($f){
		$this->current_row_html[$f]='<a href="javascript:void(0)" onclick="'. $this->js()->univ()->frameURL($this->model['email_subject'],$this->api->url($this->preview_vp->getURL(),array('newsletter_id'=>$this->model->id))) .'">'.$this->current_row[$f].'</a>';
	}

	function render(){
		$this->js(true)->_selector('.draggable-newsletter')->draggable(array( 'helper'=> 'clone'));
		parent::render();
	}
}