<?php 
// exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>
<div>
    <input type="text" name="format" value="<?php echo esc_attr(get_option('medium_publisher_key')) ?>" />
    <small>
    <ol>
        <li>Register for/Login to a <a href="https://medium.com/">Medium Account</a>.</li>
        <li>Navigate to your Profile</li>
        <li>Click "Edit Profile"</li>
        <li>Click "Security and Apps"</li>
        <li>Click "Integration Tokens"</li>
        <li>Create an integration token, and name it something descriptive, like "Wordpress Token".</li>
        <li>Copy the token, and paste it into this filed</li>
    </ol>
    </small>
</div>
