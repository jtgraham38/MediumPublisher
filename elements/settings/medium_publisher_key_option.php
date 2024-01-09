<?php 
// exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<?php 
$data = $this->get_medium_acct_info();    //get the medium account info for the user 

// 'username': 'the_the_username', 
// 'name': 'name', 
// 'url': 'https://medium.com/@<username>', 
// 'imageUrl': 'https://xyz.com'

//var_dump($data);
?>

TODOS:
<ol>
    <li>
        uploading images does work, but has to be in the exact format specified <a href="https://blog.medium.com/accepted-markup-for-medium-s-publishing-api-a4367010924e">here</a>.
        I may thus have to go in and strip out tag extras.
    </li>
    <li>handle failed api requests gracefully, either by showing message, trying again, or both</li>
    <li>show error messages when returning early from a function (not logic related but user input related)</li>
    <li>security checks on incoming and echoed data.</li>
    <li>add link to wordpress site to the end of the MEdium body to redirect users back to the blog + get backlinks</li>
    <li>features to show site visitors to the author's medium profile?</li>
</ol>

<div>

    <input type="text" id="medium_publisher_key_input" name="medium_publisher_key" style="display: block;" value="<?php echo esc_attr(get_option('medium_publisher_key')) ?>" />


    <div class="postbox" style="margin-top: 1rem; padding: 0.25rem; display: inline-block">
        <h5>Medium Account Info.</h5>
        <hr>
        <div style="display: flex; flex-direction: row; justify-content: start;">
            <div style="margin-right: 1.5rem;">
                <img id="medium_profile_pic" style="height: 4rem;" src="<?php echo esc_url(isset($data['imageUrl']) ? $data['imageUrl'] : "https://cdn-images-1.medium.com/v2/resize:fill:400:400/1*dmbNkD5D-u45r44go_cf0g.png") ?>" alt="profile picture">
            </div>
            <div style="display: flex; flex-direction: column; justify-content: space-evenly;">
                <?php if (count($data) != 0){ ?> 
                    <div>Username: <span id="medium_username"><?php echo esc_attr(isset($data['username']) ? $data['username'] : "No username found!"); ?></span></div>
                    <div>Name: <span id="medium_name"><?php echo esc_attr(isset($data['name']) ? $data['name'] : "No name found!"); ?></span></div>
                    <a id="medium_acct_link" target="_blank" href="<?php echo esc_url(isset($data['url']) ? $data['url'] : "/#"); ?>">View Account</a>
                <?php } else {?>
                    <div>Your info could not be found!  Please double check that your key is accurate.  If it is, please try refreshing this page to try again.</div>
                <?php } ?>
            </div>
        </div>

    </div>
</div>

