<?php 
// exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1>Medium Publisher Settings</h1>
    <form method="post" action="options.php">
        <?php
        // Output the settings fields.
        settings_fields('medium_publisher_settings');
        do_settings_sections('medium-publisher-settings');
        submit_button();
        ?>
    </form>
</div>