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
        
        //save should post post meta
        add_action('save_post', array($this, 'save_medium_meta_box'), 10);   //lower priority value (10) means it will happen before higher priority value (20)

        //publish the post to medium if conditions are met
        add_action('save_post', array($this, 'post_to_medium'), 20);            //^

        // set up plugin settings
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'settings_init'));
    }

    //post the post to medium, if conditions are met
    function post_to_medium($post_id){
        //semantic checks
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return $post_id;
        }
        $post_type = get_post_type($post_id);
        if ($post_type !== 'post') {
            return $post_id;
        }

        //ensure this post was not saved as a draft
        $post_status = get_post_status($post_id);
        if ($post_status == 'draft') {
            return $post_id;
        }

        //ensure this post is not already on medium
        if (get_post_meta($post_id, 'medium_url', true) != ''){
            return $post_id;
        }

        //ensure the user wants to post this post to medium
        if (!get_post_meta($post_id, 'publish_to_medium_next_time', true))
        {
            return $post_id;
        }

        //cross post to medium
        //api url
        $author_id = $this->get_medium_acct_info()['id'];
        

        //ensure an author id was found
        if ($author_id == ''){
            return $post_id;
        }
        

        $url = "https://api.medium.com/v1/users/" . $author_id . "/posts";

        //ensure publisher key is defined
        $publisher_key = get_option('medium_publisher_key', '');
        if ($publisher_key == ''){
            return $post_id;
        }

        //ensure title is not empty
        $post_title = get_the_title($post_id);
        if ($post_title == ''){
            return $post_id;
        }

        //ensure content is not empty
        $post_content = get_the_content(null, false, $post_id);
        if ($post_content == ''){
            return $post_id;
        }
        

        //define request headers
        $headers = array(
            "Authorization: Bearer " . $publisher_key,
            "Content-Type: application/json",
            "Accept: application/json",
            "Accept-Charset: utf-8",
        );

        //add footer link to medium post content
        $author_id = get_post_field('post_author', get_the_ID());
        $author_name = get_the_author_meta('display_name', $author_id);
        $post_content .= '<p>Originally posted to <a href="' . get_permalink( $post_id ) . '">' . get_bloginfo('name') . '</a> by ' . (isset($author_name) ? $author_name : "Anonymous") . '.</p>';

        //define data to be posted
        $payload = array(
            'title' => $post_title,
            'contentFormat' => 'html',
            'content' => $post_content,
            'canonicalUrl' => get_permalink($post_id),//
            'tags' => explode(",", get_post_meta($post_id, 'medium_tags', true)),
            'publishStatus' => 'public',//
            'license' => 'all-rights-reserved',
            'notifyFollowers' => 'true',
        );
        //echo get_the_content(null, false, $post_id);
        //initialize a cURL session
        $ch = curl_init($url);

        //set cURL options for the POST request
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        //send request and get the response
        $response = curl_exec($ch);



        //check if errors ocurred
        if (curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
        }

        //close cUrl session
        curl_close($ch);

        //parse the json response as an object
        $decoded = json_decode($response, true);
        //echo "------";
        //echo(curl_getinfo($ch, CURLINFO_HTTP_CODE));

        if ($decoded != null  && isset($decoded['data'])){
            $data = $decoded['data'];
        }
        else{
            //if here, something went wrong, so return
            return $post_id;
        }
        
        

        //$data format: 
        /*
        {
            "id": "e62d84c13e14",
            "title": "Test publishing to Medium",
            "authorId": "12b73749ab449fefc2c2919bb3d9d29ddc32d4b208ff60e0b919310f949eed5e0",
            "url": "https:\/\/medium.com\/@20jgraham\/test-publishing-to-medium-e62d84c13e14",
            "canonicalUrl": "https:\/\/wpdev-site.local\/test-publishing-to-medium\/",
            "publishStatus": "public",
            "publishedAt": 1701559764514,
            "license": "all-rights-reserved",
            "licenseUrl": "https:\/\/policy.medium.com\/medium-terms-of-service-9db0094a1e0f",
            "tags": []
        }
        */

        //update post meta
        update_post_meta($post_id, 'medium_id', $data['url']);
        update_post_meta($post_id, 'medium_title', $data['title']);
        update_post_meta($post_id, 'medium_author_id', $data['authorId']);
        update_post_meta($post_id, 'medium_url', $data['url']);
        update_post_meta($post_id, 'medium_canonical_url', $data['canonicalUrl']);
        update_post_meta($post_id, 'medium_publish_status', $data['publishStatus']);
        update_post_meta($post_id, 'medium_published_at', $data['publishedAt']);
        update_post_meta($post_id, 'medium_license', $data['license']);
        update_post_meta($post_id, 'medium_license_url', $data['licenseUrl']);
        update_post_meta($post_id, 'medium_tags', implode(',', $data['tags']));


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

    function save_medium_meta_box($post_id){
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

        //update post tags
        $tag1_val = substr($_POST['medium_tag_1'], 0, 25);  //tags for the medium api cannot be longer than 25 characters
        $tag2_val = substr($_POST['medium_tag_2'], 0, 25);  //^
        $tag3_val = substr($_POST['medium_tag_3'], 0, 25);  //^

        //update post meta
        update_post_meta($post_id, 'medium_tags', implode( ",", [$tag1_val, $tag2_val, $tag3_val] ));
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

    function get_medium_acct_info(){

        //api url
        $url = "https://api.medium.com/v1/me";

        //define request headers
        $headers = [
            "Authorization: Bearer " . esc_attr(get_option('medium_publisher_key')),
            "Content-Type: application/json",
            "Accept: application/json",
            "Accept-Charset: utf-8",
        ];

        //initialize a cURL session
        $ch = curl_init($url);

        //set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        //send request and get the response
        $response = curl_exec($ch);
        
        //check if errors ocurred
        if (curl_errno($ch)) {
        echo 'Curl error: ' . curl_error($ch);
        }

        //close cUrl session
        curl_close($ch);

        //parse the json response as an object
        $decoded = json_decode($response, true);
        if ($decoded != null && isset($decoded['data'])){
        $data = $decoded['data'];
        }
        else{
        $data = [];
        }
        //var_dump($data);
        return $data;
    }
}

$plugin = new Medium_Publisher();

?>