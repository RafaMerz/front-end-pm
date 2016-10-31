<?php
/*
Plugin Name: Front End PM
Plugin URI: https://www.shamimsplugins.com/wordpress/contact-us/
Description: Front End PM is a Private Messaging system and a secure contact form to your WordPress site.This is full functioning messaging system fromfront end. The messaging is done entirely through the front-end of your site rather than the Dashboard. This is very helpful if you want to keep your users out of the Dashboard area.
Version: 4.6-beta
Author: Shamim
Author URI: https://www.shamimsplugins.com/wordpress/contact-us/
Text Domain: front-end-pm
License: GPLv2 or later
*/
//DEFINE

if ( !defined ('FEP_PLUGIN_VERSION' ) )
define('FEP_PLUGIN_VERSION', '4.6' );

class Front_End_Pm {

	private static $instance;
	
	private function __construct() {

		$this->constants();
		$this->includes();
		$this->actions();
		//$this->filters();

	}
	
	public static function init()
        {
            if(!self::$instance instanceof self) {
                self::$instance = new self;
            }
            return self::$instance;
        }
	
	function constants()
    	{
			global $wpdb;
			
			//define('FEP_PLUGIN_VERSION', '4.5' );
			define('FEP_PLUGIN_FILE',  __FILE__ );
			define('FEP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			define('FEP_PLUGIN_URL', plugins_url( '/', __FILE__ ) );
			
			if ( !defined ('FEP_MESSAGES_TABLE' ) )
			define('FEP_MESSAGES_TABLE',$wpdb->prefix.'fep_messages');
			
			if ( !defined ('FEP_META_TABLE' ) )
			define('FEP_META_TABLE',$wpdb->prefix.'fep_meta');
    	}
	
	function includes()
    	{
			require_once( FEP_PLUGIN_DIR. 'functions.php');

			if( file_exists( FEP_PLUGIN_DIR. 'pro/pro-features.php' ) ) {
				require_once( FEP_PLUGIN_DIR. 'pro/pro-features.php');
			}
    	}
	
	function actions()
    	{
			register_activation_hook(__FILE__ , array($this, 'fep_plugin_activate' ) );
			register_deactivation_hook(__FILE__ , array($this, 'fep_plugin_deactivate' ) );
    	}
	
	function fep_plugin_activate(){

		global $wpdb;
		
			$roles = array_keys( get_editable_roles() );
			$id = $wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE post_content LIKE '%[front-end-pm]%' AND post_status = 'publish' AND post_type = 'page' LIMIT 1");
			
			$options = array();
			
			$options['userrole_access'] = $roles;
			$options['userrole_new_message'] = $roles;
			$options['userrole_reply'] = $roles;
			$options['plugin_version'] = FEP_PLUGIN_VERSION;
			$options['page_id'] = $id;
			
			update_option( 'FEP_admin_options', wp_parse_args( get_option('FEP_admin_options'), $options) );
			
			fep_add_caps_to_roles();
	
	}
	
	function fep_plugin_deactivate(){
	}
	
} //END Class

Front_End_Pm::init();

if ( !function_exists('fep_get_plugin_caps') ) :

function fep_get_plugin_caps( $edit_published = false, $for = 'both' ){
	$message_caps = array(
		'delete_published_fep_messages' => 1,
		'delete_private_fep_messages' => 1,
		'delete_others_fep_messages' => 1,
		'delete_fep_messages' => 1,
		'publish_fep_messages' => 1,
		'read_private_fep_messages' => 1,
		'edit_private_fep_messages' => 1,
		'edit_others_fep_messages' => 1,
		'edit_fep_messages' => 1,
		);
	
	$announcement_caps = array(
		'delete_published_fep_announcements' => 1,
		'delete_private_fep_announcements' => 1,
		'delete_others_fep_announcements' => 1,
		'delete_fep_announcements' => 1,
		'publish_fep_announcements' => 1,
		'read_private_fep_announcements' => 1,
		'edit_private_fep_announcements' => 1,
		'edit_others_fep_announcements' => 1,
		'edit_fep_announcements' => 1,
		'create_fep_announcements' => 1,
		);
	
	if( 'fep_message' == $for ) {
		$caps = $message_caps;
		if( $edit_published ) {
			$caps['edit_published_fep_messages'] = 1;
		}
	} elseif( 'fep_announcement' == $for ){
		$caps = $announcement_caps;
		if( $edit_published ) {
			$caps['edit_published_fep_announcements'] = 1;
		}
	} else {
		$caps = array_merge( $message_caps, $announcement_caps );
		if( $edit_published ) {
			$caps['edit_published_fep_messages'] = 1;
			$caps['edit_published_fep_announcements'] = 1;
		}
	}
	return $caps;
}

endif;

if ( !function_exists('fep_add_caps_to_roles') ) :

function fep_add_caps_to_roles( $roles = array( 'administrator', 'editor' ) ) {

	if( ! is_array( $roles ) )
		$roles = array();
	
	$caps = fep_get_plugin_caps();
	
	foreach( $roles as $role ) {
		$role_obj = get_role( $role );
		if( !$role_obj )
			continue;
			
		foreach( $caps as $cap => $val ) {
			if( $val )
				$role_obj->add_cap( $cap );
		}
	}
}

endif;

	
