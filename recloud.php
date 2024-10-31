<?php
	
	require_once('core/recloud_api.php');

	class recloud extends recloud_api {
	
		private $short_name = 'RECloud';
		private $long_name = 'Real Estate Cloud';
		private $plugin_name = 'RECloud Connect';
		private $settings_page = 'recloud';
		private $plugin_slug = 'recloud';
		private $plugin_setup = false;
	
		public function __construct() {
			$api_key = get_option('recloud_api_key', false);
			
			if(!empty($api_key)) {
				$this->company = $this->recloud_get_company_by_api_key($api_key);
			}
			
			if(!$this->company) {
				if($_SERVER['REQUEST_METHOD']!='POST') {
					add_action('admin_notices', array($this, 'recloud_nosetup_notice'));
				}
			} else {
				$this->plugin_setup = true;
			}
		
			add_action('admin_head', array($this, 'recloud_admin_head'));
			add_action('admin_menu',  array($this, 'recloud_add_options_page'));
			
			add_action('wp_ajax_recloud-connect-contact', array($this, 'recloud_contact_process'));
			add_action('wp_ajax_nopriv_recloud-connect-contact', array($this, 'recloud_contact_process'));
			
			add_action('wp_ajax_recloud-connect-submit-dsi', array($this, 'recloud_contact_process_dsi'));
			add_action('wp_ajax_nopriv_recloud-connect-submit-dsi', array($this, 'recloud_contact_process_dsi'));

			if($this->plugin_setup) {
				require_once('recloud-connect-widget.php');
				
				wp_enqueue_style('recloud-connect', plugins_url('css/recloud-connect.css', __FILE__));
				
				wp_enqueue_script('recloud-connect', plugins_url('js/recloud-connect.js', __FILE__), array('jquery'));
				wp_localize_script( 'recloud-connect', 'recloud_connect', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
				
				$recloud_hijack_dsi = get_option('recloud_hijack_dsi', 0);
				
				if($recloud_hijack_dsi) {
					wp_enqueue_script('recloud-dsi', plugins_url('js/recloud-dsi.js', __FILE__), array('jquery'));
					wp_localize_script( 'recloud-dsi', 'recloud_dsi', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
				}
				
				add_action( 'widgets_init', create_function( '', 'return register_widget("recloud_connect_widget");' ) );
			}
		}
			
		public function recloud_contact_process() {
			unset($_POST['action']);
			
			$data = $_POST;
			$data['api_key'] = get_option('recloud_api_key');
						
			$this->recloud_submit_lead($data);
		}
		
		public function recloud_contact_process_dsi() {
			unset($_POST['action']);
			
			$field_mappings = array(
				'firstName' => 'first_name',
				'lastName' => 'last_name',
				'phoneNumber' => 'primary_phone',
				'emailAddress' => 'email_address'
			);
			
		    $data = array();
		    
		    if(!empty($_POST['office_id'])) {
		    	$data['office_id'] = $_POST['office_id'];
		    } else {
		    	$data['office_id'] = 5;
		    }
		    
			$data['api_key'] = get_option('recloud_api_key');
			$data['internal_notes'] = '';		    
	    
		    foreach($field_mappings as $field => $new_field) {
		    	if(!empty($_POST[$field])) {
		    		$data[$new_field] = $_POST[$field];
		    	}
		    }
		    
		    if(!empty($_POST['scheduleYesNo'])) {
			    if($_POST['scheduleYesNo']=='on') {
			    	$data['internal_notes'] .= 'Schedule showing on ' . $_POST['scheduleDateMonth'] . '/' . $_POST['scheduleDateDay'] . '/' . date('Y') . "\n";
			    }
		    }
		    
		    if(!empty($_POST['propertyID'])) {
		    	$data['internal_notes'] .= 'MLS # ' . $_POST['propertyID'];
		    }
		    
		    if(!empty($_POST['comments'])) {
		    	$data['internal_notes'] = $_POST['comments'] . "\n\n" . $data['internal_notes'];
		    }
		    
			$this->recloud_submit_lead($data);
		}
		
		public function recloud_nosetup_notice() {
			echo "<div id='recloud-notice' class='error fade'><p>".$this->plugin_name . 
					" is installed but not setup properly. Please visit the <a href=\"options-general.php?page=" . 
					$this->settings_page."\">settings page</a> to set it up.</p></div>";
		}
		
		public function recloud_admin_head() {
        	echo '<link rel="stylesheet" type="text/css" href="' .plugins_url('css/wp-admin.css', __FILE__). '">';
		}

		public function recloud_add_options_page() {
			add_options_page($this->org_name . ' Connect', 'RECloud Connect', 9, 'recloud', array($this, 'recloud_options'));
		}
	
		public function recloud_options() {
			if ($_POST['action'] == 'update') {
				$api_key = $_POST['recloud_api_key'];
				$this->company = $this->recloud_get_company_by_api_key($api_key);
				
				if($this->company) {
					update_option('recloud_api_key', $api_key);
					update_option('recloud_hijack_dsi', $_POST['recloud_hijack_dsi']);
					
					echo "<div id='recloud-notice' class='updated fade'><p>RECloud settings successfully updated.</p></div>";
				} else {	
					echo "<div id='recloud-notice' class='error fade'><p>Error: API Key \"" . $api_key . "\" is invalid.</p></div>";
				}
			}
			
			$recloud_api_key = get_option('recloud_api_key');
			$recloud_hijack_dsi = get_option('recloud_hijack_dsi', 0);
			
			?>
				<div class="wrap">
				<h2><?=$this->long_name ?> CRM Settings</h2>
				<form action="?page=recloud" method="POST">
					<input type="hidden" name="action" value="update" />
					<?php wp_nonce_field('update-options'); ?>
					<table class="form-table">
						<tr valign="top">
							<th scope="row">API Key</th>
							<td>
								<fieldset>
									<input type="text" class="text_option long" name="recloud_api_key" id="recloud_api_key" value="<?=$recloud_api_key ?>" />								
								</fieldset>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">Hijack dsIDXpress Forms</th>
							<td>
								<fieldset>
									<input type="hidden" name="recloud_hijack_dsi" value="0" />
									<input type="checkbox" name="recloud_hijack_dsi" id="recloud_hijack_dsi" value="1"<?=$recloud_hijack_dsi==1 ? ' checked="checked"' : '' ?> />								
								</fieldset>
							</td>
						</tr>
					</table>
					<p>
						<input name="Submit" value="<?php echo __('Save Changes'); ?>" type="submit">
					</p>
				</form>
			<?php
		}
	
	}
	
?>