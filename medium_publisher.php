<?php
/*
Plugin Name: Medium Publisher
Plugin URI: https://jacob-t-graham.com/projects
Description: This plugin allows you to cross-post all of your posts to Medium, an online social writing platform with millions of users.
Version: 1.0.0
Author: Jacob Graham
Author URI: https://jacob-t-graham.com/
Text Domain: medium-publisher
*/

// exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// define plugin controller
class Medium_Publisher
{
    // Constructor.
    public function __construct()
    {
        //add meta box on post editor
        add_action('add_meta_boxes', array($this, 'add_medium_meta_box'));
        
        //save custom post meta
        add_action('save_post', array($this, 'save_medium_metadata'));

        //add a pre-publish check to see if we should post to medium
        add_action('pre-publish', array($this, 'check_if_should_post'));

        // set up plugin settings
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'settings_init'));
    }

    //add pre-publish check for posting to medium
    function check_if_should_post($post_id){

    }

    //add a meta box to the edit post menu
    function add_medium_meta_box(){
        add_meta_box( 
            'medium_crosspost_meta_box',        //id for the meta box
            "Medium Publisher",                   //title of the meta box
            function(){                         //callback that creates meta box contents
                require_once plugin_dir_path(__FILE__) . 'elements/medium_meta_box.php';
            }, 
            'post',                             //post type to show box on 
            'side',                             //how to place the box in the editor, options: 'normal', 'side', or 'advanced'
            'default'                           //priority: 'high', 'core', 'default' or 'low'
        );
    }

    function save_medium_metadata($post_id){
        //semantic checks
        if (!isset($_POST['publish_to_medium_next_time_nonce']) || !wp_verify_nonce($_POST['publish_to_medium_next_time_nonce'], 'publish_to_medium_next_time_nonce')) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        $post_type = get_post_type($post_id);
        if ($post_type !== 'post') {
            return;
        }
        //update whether to publish to medium next time
        $checkbox_value = isset($_POST['publish_to_medium_next_time']) && $_POST['publish_to_medium_next_time'] === '1' ? '1' : '0';
        $publish_to_medium_next_time = sanitize_text_field($checkbox_value);
        update_post_meta($post_id, 'publish_to_medium_next_time', $publish_to_medium_next_time);
    }


    //add plugin settings page
    public function add_settings_page()
    {
        // add the settings page
        add_submenu_page(
            'edit.php',             //slug of the menu page to add the submenu under
            'Medium Publisher',     //title of page
            'Medium Publisher',     //title of entry in submenu
            'manage_options',       //permissions required to access the page
            'medium-publisher-settings',     //slug for submenu page
            function(){             //callback to get page contents
                require_once plugin_dir_path(__FILE__) . 'elements/settings/medium_publisher_settings.php';
            }
        );
    }

    //initialize plugin settings
    public function settings_init(){
        // create section for settings
        add_settings_section(
            'medium_publisher_settings',     //settings section identifier
            '',                              //formatted title of the section
            function(){                      //callback that echoes out content between the title and the fields
                require_once plugin_dir_path(__FILE__) . 'elements/settings/setup_instructions.php';
            },
            'medium-publisher-settings',    //page on which to show the settings
        );

        // create the settings fields
        add_settings_field(
            'medium_publisher_key',    //field identifier
            'Medium Token:',               //title/field label
            function(){             //get field html
                require_once plugin_dir_path(__FILE__) . 'elements/settings/medium_publisher_key_option.php';
            },
            'medium-publisher-settings', //page on which to show the setting
            'medium_publisher_settings'  //settings page section id on which to show the box
        );

        // create the settings themselves
        register_setting(
            'medium_publisher_settings',    //setting group
            'medium_publisher_key',         //setting name
            array(                          //extras
                'default' => ''             //default value
            )
        );
    }
}

$plugin = new Medium_Publisher();

?>