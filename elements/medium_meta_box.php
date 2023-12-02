<?php 
// exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$medium_url = get_post_meta(get_the_ID(), 'medium_url', true);
$publish_next_time = get_post_meta(get_the_ID(), 'publish_to_medium_next_time', true);

?>

<?php if ($medium_url == ""){ ?>
    <div style="display: flex; flex-direction: row; justify-content: space-between; align-items: center;">
        <label for="publish_to_medium_next_time">Ready to publish to Medium?</label>
        <input
            type="checkbox" 
            id="publish_to_medium_next_time_input" 
            name="publish_to_medium_next_time" 
            <?php checked('1', $publish_next_time, true) ?> 
            value="<?php echo $publish_next_time=='1' ? '1':'0' ?>"
        >
        <input type="hidden" name="publish_to_medium_next_time_nonce" value="<?php echo esc_attr( wp_create_nonce('publish_to_medium_next_time_nonce') ); ?>" >
        
        <script>

            //get element
            const medium_publish_checkbox = document.getElementById('publish_to_medium_next_time_input')

            //add event listener to update input value on check change
            medium_publish_checkbox.addEventListener('change', (event)=>{
                medium_publish_checkbox.value = medium_publish_checkbox.value=='1' ? '0' : '1'
            })
        </script>
        
    </div>
    <small>Don't forget to save after a change!</small>
<?php } else { ?>
    <div>View on <a target="_blank" href="<?php echo esc_attr($medium_url) ?>">Medium</a>.</div>
<?php } ?>