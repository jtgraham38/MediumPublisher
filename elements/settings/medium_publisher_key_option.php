<?php 
// exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<?php

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
    if ($decoded != null){
        $data = $decoded['data'];
    }
    else{
        $data = [];
    }
    //var_dump($data);
?>


<div>
    <div>
        <input type="text" id="medium_publisher_key_input" name="medium_publisher_key" style="display: inline;" value="<?php echo esc_attr(get_option('medium_publisher_key')) ?>" />
    </div>
    

    <div class="postbox" style="margin-top: 1rem; padding: 0.25rem;">
        <h5>Medium Account Info.</h5>
        <hr>
        <div style="display: flex; flex-direction: row; justify-content: start;">
            <div style="margin-right: 1.5rem;">
                <img id="medium_profile_pic" style="height: 4rem;" src="<?php echo isset($data['imageUrl']) ? $data['imageUrl'] : "https://cdn-images-1.medium.com/v2/resize:fill:400:400/1*dmbNkD5D-u45r44go_cf0g.png" ?>" alt="profile picture">
            </div>
            <div style="display: flex; flex-direction: column; justify-content: space-evenly;">
                <?php if (count($data) != 0){ ?> 
                    <div>Username: <span id="medium_username"><?php echo isset($data['username']) ? $data['username'] : "No username found!"; ?></span></div>
                    <div>Name: <span id="medium_name"><?php echo isset($data['name']) ? $data['name'] : "No name found!"; ?></span></div>
                    <a id="medium_acct_link" target="_blank" href="<?php echo isset($data['url']) ? $data['url'] : "/#"; ?>">View Account</a>
                <?php } else {?>
                    <div>Your info could not be found!  Please double check that your key is accurate.  If it is, please try refreshing this page to try again.</div>
                <?php } ?>
            </div>
        </div>
        <!--
        'username': '20jgraham', 
        'name': 'jtgraham38_test', 
        'url': 'https://medium.com/@20jgraham', 
        'imageUrl': 'https://cdn-images-1.medium.com/fit/c/400/400/1*dmbNkD5D-u45r44go_cf0g.png'
-->
    </div>
</div>

