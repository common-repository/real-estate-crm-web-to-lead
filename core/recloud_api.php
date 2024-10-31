<?php

	// NEED TO ADD OFFICE_ID

	class recloud_api extends WP_Widget {
	
		public static $company;
		public $api_server = 'http://recloud.me/api/';
		private $default_request_fields = array(
				'return_type' => 'json',
				'auth_type' => 1
		);
		
		private function recloud_request($method, $data = array()) {


			if(empty($data['api_key'])) {
				error_log(print_r(array("NO API KEY", $method, $data), true));
				die();
			}

			$post_url = $this->api_server . $method . '/';
			$auto_fields = $this->default_request_fields;
			
			$final_post = $auto_fields;
			
			foreach($data as $field => $value) {
				if(!empty($value)) {
					$final_post[$field] = $value;
				}
			}
			
			$querystring = http_build_query($final_post);
		
			$ch = curl_init($post_url);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $querystring);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		 	return curl_exec($ch);
			curl_close($ch);
		 
			exit;	
		}	
	
		public function recloud_get_company_by_api_key($api_key) {
			$company_data = $this->recloud_request('get_company_by_api_key', array('api_key' => $api_key));
			$company = json_decode($company_data);
			
			if(empty($company->company_id)) {
				return false; 
			} else {
				$company->websites = $this->recloud_get_websites($api_key);
				$company->lead_sources = array();
				
				$company->lead_sources[6] = 'CondoLoft Blog';
				$company->lead_sources[17] = 'RealEstate Weekly Blog';
				$company->lead_sources[18] = 'Condos Weekly Blog';
				$company->lead_sources[19] = 'Apartments Blog';
				
				return $company;
			}
		}
		
		public function recloud_get_websites($api_key) {
			$website_data = $this->recloud_request('get_websites_by_company', array('api_key' => $api_key));
			$websites = json_decode($website_data);
			
			return $websites;
		}
		
		public function recloud_agent_for_sidebar($api_key, $website_id) {
			$agent_data = $this->recloud_request('get_agent_for_sidebar', array('api_key' => $api_key, 'website_id' => $website_id));
			$agent = json_decode($agent_data);
			
			return $agent;
		}
		
		public function recloud_submit_lead($data) {	
			$data['ip_address'] = $_SERVER['REMOTE_ADDR'];
			$data['submit_page'] = $_SERVER['HTTP_REFERER'];
			$data['landing_page'] = 'http://' . $_SERVER['HTTP_HOST'] . '/';
			$data['lead_source_id'] = 2;
					
			if(empty($data['email_address']))
				header('Location: '.$_SERVER['HTTP_REFERER']);
		
			$lead_data = $this->recloud_request('lead_gateway', $data);
			$lead = json_decode($lead_data);
			
			print_r($lead);
			die();
			
		}
	
	}


?>
