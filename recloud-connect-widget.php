<?php

/**
 * FooWidget Class
 */
class recloud_connect_widget extends recloud_api {
	/** constructor */
	
	function recloud_connect_widget() {
		$this->api_key = get_option('recloud_api_key', false);
		$this->company = $this->recloud_get_company_by_api_key($this->api_key);
	
		parent::WP_Widget( 'recloud', $name = 'RECloud CRM', array('description' => 'Generates a neat agent bio and lead form that plugs straight into your RECloud CRM!') );
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
				
		if(empty($instance['website_id'])) {
			return;
		}
		
		$website_id = $instance['website_id'];
		$lead_source_id = $instance['lead_source_id'];
		$agent = $this->recloud_agent_for_sidebar($this->api_key, $website_id);

		if(empty($agent)) {
			return;
		}
		
		$agent_name = $agent->first_name . ' ' . $agent->last_name;

		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		
		echo $before_widget;
		
		if ( $title )
			echo $before_title . $title . $after_title; ?>
		
		<div class="recloud-connect-bio">
			<img width="80" class="recloud-connect-bio-img" alt="<?=$agent_name ?>" src="http://condodomain.net/agent_photo/<?=$agent->contact_id ?>/large_thumb.jpg">
			
			<p class="agent-bio">
				<span class="agent-name"><?=$agent_name ?></span>
				<?=substr(strip_tags($agent->description), 0, 135) . '...' ?>&nbsp;<a href="#">Read More</a>
			</p>
		</div>
		<div class="recloud-connect-form">
			<form method="POST" id="recloud_connect_form" action="<?=plugins_url('recloud-process-contact.php', __FILE__) ?>"> 
				<input type="hidden" value="recloud-connect-contact" name="action" />
				<input type="hidden" value="<?=$website_id ?>" id="recloud_office_id" name="office_id" />
				<input type="text" onblur="if (this.value==''){this.value='First Name'}" onfocus="if (this.value=='First Name') this.value='';" class="rside-textfield" value="First Name" id="first_name" name="first_name"> 
				<input type="text" onblur="if (this.value==''){this.value='Last Name'}" onfocus="if (this.value=='Last Name') this.value='';" class="rside-textfield" value="Last Name" id="last_name" name="last_name"> 
				<input type="text" onblur="if (this.value==''){this.value='E-mail'}" onfocus="if (this.value=='E-mail') this.value='';" class="rside-textfield" value="E-mail" id="email_address" name="email_address"> 
				<textarea onblur="if (this.value==''){this.value='Comments'}" onfocus="if (this.value=='Comments') this.value='';" class="rside-textarea" rows="" cols="" id="internal_notes" name="internal_notes">Comments</textarea> 
				<a class="rside-button-green rside-agent-work recloud-connect-submit" href="#">Work with <?=$agent->first_name ?></a>
			</form> 
			
			<div class="recloud_after_form_wrapper">
				<p>Thank you! Your inquiry has been received and a <?=$this->company->company_name ?> representative will get back to you very soon!</p>
			</div>
		</div>
		
		<?php echo $after_widget;
		
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['website_id'] = $new_instance['website_id'];
		$instance['lead_source_id'] = $new_instance['lead_source_id'];
		return $instance;
	}

	/** @see WP_Widget::form */
	function form( $instance ) {

		if ( $instance ) {
			$title = esc_attr( $instance[ 'title' ] );
			$website_id = $instance[ 'website_id' ];
			$lead_source_id = $instance[ 'lead_source_id' ];
		} else {
			$title = __( 'Meet the Team', 'text_domain' );
			$website_id = 0;
			$lead_source_id = 0;
		}
		
		?>
		
		<p>
		<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		
		<?php if(!empty($this->company->websites)) { ?>
			<p>
				<label for="<?php echo $this->get_field_id('website_id'); ?>"><?php _e('Website:'); ?></label>
				<select class="widefat" name="<?php echo $this->get_field_name('website_id'); ?>" id="<?php echo $this->get_field_id('website_id'); ?>">
					<? 
						foreach($this->company->websites as $recloud_website) { 
							$selected = $website_id == $recloud_website->website_id ? ' selected="selected"' : '';
							echo '<option value="'.$recloud_website->website_id.'"'.$selected.'>'.$recloud_website->website_name.'</option>';	
						}
					?>
				</select>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('lead_source_id'); ?>"><?php _e('Lead Source:'); ?></label>
				<select class="widefat" name="<?php echo $this->get_field_name('lead_source_id'); ?>" id="<?php echo $this->get_field_id('lead_source_id'); ?>">
					<? 
						foreach($this->company->lead_sources as $recloud_lead_source_id => $recloud_lead_source) { 
							$selected = $lead_source_id == $recloud_lead_source_id ? ' selected="selected"' : '';
							echo '<option value="'.$recloud_lead_source_id.'"'.$selected.'>'.$recloud_lead_source.'</option>';	
						}
					?>
				</select>
				
				
			</p>
		<?php } ?>
		<?php 
	}

} // class FooWidget

?>
