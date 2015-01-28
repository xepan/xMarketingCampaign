<?php

namespace xMarketingCampaign;

class Controller_DataGrabberExec extends \AbstractController {
	
	public $grabbed_data = array();
	public $total_recursion = 10;
	public $contact_us_texts=array('contact','contacto');

	public $picked_dtgrb=null;
	public $picked_phrase_to_run=null;
	public $hosts_touched=null;

	public $snoopy=null;

	function init(){
		parent::init();
		
		set_time_limit(0);

		$this->owner->add('View_Info')->set('To Stop Auto Calling, Just Close this frame, But current runnig thred will not be terminated');

		$what_dtgrb_instance = $this->add('xMarketingCampaign/Model_DataGrabber');
		$what_dtgrb_instance->addCondition('is_active',true);
		$what_dtgrb_instance->_dsql()->having('is_runnable',1);
		$what_dtgrb_instance->setOrder('last_run_at');
		$what_dtgrb_instance->tryLoadAny();

		$phrase_to_run = null;

		foreach ($what_dtgrb_instance as $junk) {
			$phrase_to_run = $this->add('xMarketingCampaign/Model_DataSearchPhrase');
			$phrase_to_run->addCondition('data_grabber_id',$what_dtgrb_instance->id);
			$phrase_to_run->addCondition('is_grabbed',false);
			// $phrase_to_run->addCondition('page_parameter_start_value','<=' , 'page_parameter_max_value');
			$phrase_to_run->setOrder('last_page_checked_at');
			$phrase_to_run->setLimit(1);

			$phrase_to_run->tryLoadAny();

			if($phrase_to_run->loaded()){
				// Got your phrase to run with
				break;
			}
		}

		if($phrase_to_run==null or ($phrase_to_run and !$phrase_to_run->loaded())){
			$this->owner->add('View_Info')->set('Nothing to run');
			$this->owner->js(true)->reload();
			return;
		}
		
		// $phrase_to_run['is_active'] = false;
		// $phrase_to_run->save();

		// $snoopy = new \Snoopy();
		// $snoopy->read_timeout = 15;
		
		$this->picked_dtgrb = $what_dtgrb_instance;
		$this->picked_phrase_to_run = $phrase_to_run;

		if($phrase_to_run['content_provided']){
			$content=$phrase_to_run['content_provided'];
		}else{
			$url = $what_dtgrb_instance['site_url'] . '?'.$what_dtgrb_instance['query_parameter'] . '=' . rawurlencode($phrase_to_run['name']) . '&' . $what_dtgrb_instance['paginator_parameter'] . '=' . $phrase_to_run['page_parameter_start_value'] ;
			
			if($what_dtgrb_instance['extra_url_parameters'])
				$url .= '&'.$what_dtgrb_instance['extra_url_parameters'];

			$ctx = stream_context_create(array(
			    'http'=>array(
			    	'Accept'=>'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
					'Accept-Encoding'=>'gzip, deflate',
					'Accept-Language'=>	'en-US,en;q=0.5',
					'Cookie'=>'MUID=3DB484C019A4657B37B282751DA466DF; _EDGE_V=1; MUIDB=3DB484C019A4657B37B282751DA466DF; SRCHD=AF=NOFORM; SRCHUID=V=2&GUID=95B7D9A58EF74C81818C9C4A3CEB0D1D; SRCHUSR=AUTOREDIR=0&GEOVAR=&DOB=20140922; s_vnum=1413969702266%26vn%3D1; s_nr=1411377702267; SRCHHPGUSR=CW=1587&CH=423; _SS=SID=94028FF56B4A4FC8B1200D85EFF501A2&bIm=415347; SCRHDN=ASD=0&DURL=#; WLS=TS=63549496140; _HOP=',
					'Host'=>'www.bing.com',
					'Referer'=>'http://www.bing.com/',
					'User-Agent'=>	'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:32.0) Gecko/20100101 Firefox/32.0'
		            )
			    )
			);


			// echo "<iframe id='xyz' src='$url' width='100%' height='800px'></iframe>";
			// return;
			$content = @file_get_contents($url);
			sleep(2);
		}

		$content_parsed ='';
		// echo $content;
		if($what_dtgrb_instance['result_format']=='HTML'){
			if($what_dtgrb_instance['result_selector']){
				include_once (getcwd().'/lib/phpQuery.php');
				$pq = new \phpQuery();
				$doc = $pq->newDocument($content);
				// $content = $pq->pq($what_dtgrb_instance['result_selector'])->html();
				foreach ($pq->pq($what_dtgrb_instance['result_selector']) as $found) {
					if($what_dtgrb_instance['reg_ex_on_href']){
						foreach ($pq->pq($found)->find('a') as $a) {
							preg_match('/'.$what_dtgrb_instance['reg_ex_on_href'].'/', $pq->pq($a)->attr('href'),$arr);
							$pq->pq($a)->attr('href',$arr[1]);
						}
					}
					$content_parsed .= $pq->pq($found)->html();
				}
			}
		}

		if($what_dtgrb_instance['result_format']=='JSON'){
			$content_array = json_decode($content,true);
			$content_parsed ='<div>';
			array_walk_recursive($content_array, function($item,$key)use($what_dtgrb_instance,&$content_parsed){
				if($key == $what_dtgrb_instance['json_url_key']){
					$content_parsed .= '<a href='.$item.'>'.$item.'</a>';
				}
			});
			$content_parsed .='</div>';

			// echo"<pre>";
			// print_r($content_array);
			// echo"</pre>";
		}
		

		// echo $content. "<br/>". $content_parsed;
		// return;
		// Execute the selected phrase here
		// echo "<pre>";
			$result = $this->grab($what_dtgrb_instance['site_url'],$content_parsed, $phrase_to_run['max_page_depth'],$phrase_to_run['max_domain_depth'],$phrase_to_run['max_page_depth'],$phrase_to_run['max_domain_depth'],$what_dtgrb_instance['site_url']);
		// echo "</pre>";

		if($what_dtgrb_instance->ref('xMarketingCampaign/DataSearchPhrase')->addCondition('is_grabbed',false)->count()->getOne() ==0){
			$what_dtgrb_instance['is_active']=false;
			$what_dtgrb_instance->save();
		}


		if($phrase_to_run['content_provided']){
			$phrase_to_run['is_grabbed'] = true;
		}else{
			$phrase_to_run['page_parameter_start_value'] = $phrase_to_run['page_parameter_start_value'] + $what_dtgrb_instance['records_per_page'];
			
		}

		// check if phrase_to_run has completed its max record visit.. if so deactivate it
		$subscription_category_id = $phrase_to_run['subscription_category_id'];
		$phrase_name = $phrase_to_run['name'];
		$phrase_to_run_id = $phrase_to_run->id;
		$phrase_to_run->saveAndUnload();
		$what_dtgrb_instance->saveAndUnload();


		$found_emails = array();
		foreach ($this->grabbed_data as $host => $pages) {
			foreach ($pages as $emails) {
				foreach ($emails as $em) {
					if(!in_array($em, $found_emails))
						$found_emails[] = $em;
				}
			}
		}

		$category=  $this->add('xEnquiryNSubscription/Model_SubscriptionCategories');
		$category->load($subscription_category_id);

		foreach ($found_emails as $email) {
			$subscription_save = $this->add('xEnquiryNSubscription/Model_Subscription');
			$subscription_save->addCondition('email',$email);
			$subscription_save->tryLoadAny();
			
			
			if(!$subscription_save->loaded()){
				$subscription_save['from_app']='DataGrabberPhrase';
				$subscription_save['from_id']=$phrase_to_run_id;
				$subscription_save->save();	
			}
			
			try{
				$category->addSubscriber($subscription_save);
			}catch(\Exception $e){
				
			}

			$subscription_save->destroy();
		}

		echo "<h1> RESULT : </h1>";
		echo "<h3>Found " . count($found_emails). " Emails in this shot </h3>";
		// print_r($this->grabbed_data);
		$this->owner->add('View')->set('Done ' . $phrase_name);
		$this->owner->js(true)->reload();
	}

	// Url and its fetched_content
	function grab($url, $content, $max_page_depth, $max_domain_depth, $total_max_page_depth, $initial_domain_depth, $path){
		
		try{
		
			$parsed_url = parse_url($url);

			$start=microtime(true);
			// get Emails and Mobile Number and ... 
			$pattern = '/[a-z0-9_\-\+\.]+(@|(.)?\[(.)?at(.)?\](.)?)[a-z0-9\-]+(\.|(.)?\[(.)?dot(.)?\](.)?)([a-z]{2,3})(?:(\.|(.)?\[(.)?dot(.)?\](.)?)[a-z]{2})?/i';
			$pattern = '/[a-z0-9_\-\+\.]{1,80}+@[a-z0-9\-]{1,80}+\.([a-z]{2,3})(?:\.[a-z]{2})?/i';
			// preg_match_all returns an associative array
			preg_match_all($pattern, $content, $email_found);
			// echo '<br/>'.$path . " [<b> $url </b>] @ <b>$max_page_depth</b> level". "<br/>";
			$end=microtime(true);
			// echo print_r($email_found[0],true) . ' in '.($end-$start).' seconds from <b>'.$url.'</b><br/>';
			// ob_flush();
			// flush();

			$this->grabbed_data[$parsed_url['host']][$parsed_url['path'] . $parsed_url['query']] = $email_found[0];

			include_once (getcwd().'/lib/phpQuery.php');
			$pq = new \phpQuery();
			$doc = @$pq->newDocumentHTML($content);
			
			// if($max_domain_depth== $initial_domain_depth)
				$get_a = $doc['a'];
			// else
				// $get_a = $doc['a:contains("contact")'];

			// echo "Found Links: ";
			
			$unique_filtered_links = array();

			foreach ($get_a as $a) {
				// echo '<br/>--------  &nbsp; &nbsp; &nbsp; '.$pq->pq($a)->attr('href'). ' <br/>';
				preg_match('/(\.pdf|\.exe|\.msi|\.zip|\.rar|\.gz|\.tar|\.flv|\.mov|\.mpg|\.mpeg)/i', $pq->pq($a)->attr('href'),$arr);
				if(count($arr)) {
					// echo "Found pdf etc so not taking to check in ". $pq->pq($a)->attr('href') .'<br/>';
					continue;
				}


				$new_website = parse_url($pq->pq($a)->attr('href'));
				if(!$new_website['scheme']) $new_website['scheme'] = $parsed_url['scheme'];
				if(!$new_website['host']) $new_website['host'] = $parsed_url['host'];
				$new_url = $new_website['scheme'].'://'.$new_website['host'] . '/'.$new_website['path'].$new_website['query'];

				// if(in_array($new_website['path'].$new_website['query'], array_keys($this->grabbed_data[$parsed_url['host']]))){
				// 	echo "Already Visited <br/>";
				// 	continue;
				// }

				if(!in_array($new_url, $unique_filtered_links)){
					$unique_filtered_links[] = $new_url;
				}
			}
			// echo "Unique Links to check <br/>";
			// print_r($unique_filtered_links);

			$start = microtime(true);
			$results = $this->multi_request($unique_filtered_links);
			echo "Fetched ". count($unique_filtered_links).  " websites in ". (microtime(true) - $start) . ' seconds <br/>';

			$contact_us_pages =array();
			foreach ($unique_filtered_links as $id => $site_url) {
				// somehow if no result was found just carry on
				if(!$results[$id]) {
					// echo "No Result for " . $site_url. '<br/>';
					continue;
				}

				$parsed_url = parse_url($site_url);

				preg_match_all($pattern, $results[$id], $email_found);
				$this->grabbed_data[$parsed_url['host']][$parsed_url['path'] . $parsed_url['query']] = $email_found[0];

				
				$doc = @$pq->newDocumentHTML($results[$id]);
				$get_a = $doc['a:contains("contact")'];

				foreach ($get_a as $a) {
					// echo '<br/>--------  &nbsp; &nbsp; &nbsp; '.$pq->pq($a)->attr('href'). ' <br/>';
					preg_match('/(\.pdf|\.exe|\.msi|\.zip|\.rar|\.gz|\.tar|\.flv|\.mov|\.mpg|\.mpeg)/i', $pq->pq($a)->attr('href'),$arr);
					if(count($arr)) {
						// echo "Found pdf etc so not taking to check in ". $pq->pq($a)->attr('href') .'<br/>';
						continue;
					}


					$new_website = parse_url($pq->pq($a)->attr('href'));
					if(!$new_website['scheme']) $new_website['scheme'] = $parsed_url['scheme'];
					if(!$new_website['host']) $new_website['host'] = $parsed_url['host'];
					$new_url = $new_website['scheme'].'://'.$new_website['host'] . '/'.$new_website['path'].$new_website['query'];

					// if(in_array($new_website['path'].$new_website['query'], array_keys(is_array($this->grabbed_data[$parsed_url['host']])?:array()))){
					// 	echo "Already Visited <br/>";
					// 	continue;
					// }

					if(!in_array($new_url, $contact_us_pages)){
						$contact_us_pages[] = $new_url;
					}
				}
			}
			
			// echo "Unique Contact Links to check <br/>";
			// print_r($contact_us_pages);

			$start = microtime(true);
			$results = $this->multi_request($contact_us_pages);
			echo "Fetched ". count($contact_us_pages).  " contact-pages in ". (microtime(true) - $start) . ' seconds <br/>';

			foreach ($results as $id => $contact_page_content) {
				if(!$results[$id]){
					// echo "Contact Page no result ". $contact_us_pages[$id] .'<br/>';
					continue;
				}

				$parsed_url = parse_url($contact_us_pages[$id]);

				preg_match_all($pattern, $contact_page_content, $email_found);
				$this->grabbed_data[$parsed_url['host']][$parsed_url['path'] . $parsed_url['query']] = $email_found[0];
			}





				// // if from same host and !already visited
				// if($new_website['host'] == $parsed_url['host'] and !in_array($new_website['path'].$new_website['query'], array_keys($this->grabbed_data[$parsed_url['host']]))){
				// 	$this->grab($new_url,$results[$id],$max_page_depth-1,$max_domain_depth,$total_max_page_depth, $initial_domain_depth, $path . '|-'. $new_url);
				// }
				// // else if from another host and ! already visted
				// if($new_website['host'] != $parsed_url['host'] and !in_array($new_website['path'].$new_website['query'], array_keys($this->grabbed_data[$parsed_url['host']]))){
				// 	$this->grab($new_url,$results[$id],$total_max_page_depth,$max_domain_depth-1,$total_max_page_depth,  $initial_domain_depth, $path .'==>'. $new_url );
				// }



		}catch(Exception $e){
			return;
		}
	}

	function multi_request($urls)
	{
		$curly = array();
		$result = array();
		$mh = curl_multi_init();

		foreach ($urls as $id => $url) {
			$curly[$id] = curl_init();
			curl_setopt($curly[$id], CURLOPT_URL, $url);
			curl_setopt($curly[$id], CURLOPT_HEADER, 0);
			curl_setopt($curly[$id], CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curly[$id], CURLOPT_TIMEOUT, 30);
			curl_setopt($curly[$id], CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($curly[$id], CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curly[$id], CURLOPT_SSL_VERIFYHOST, 0);
			curl_multi_add_handle($mh, $curly[$id]);
		}

		$running = null;
		do {
			curl_multi_exec($mh, $running);
		} while($running > 0);

		foreach($curly as $id => $c) {
			$result[$id] = curl_multi_getcontent($c);
			curl_multi_remove_handle($mh, $c);
		}
		curl_multi_close($mh);
		return $result;
	}

}