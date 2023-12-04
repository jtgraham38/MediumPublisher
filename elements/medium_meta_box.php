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
        <label for="publish_to_medium_next_time">Publish to Medium?</label>
        <input
            type="checkbox" 
            id="publish_to_medium_next_time_input" 
            name="publish_to_medium_next_time" 
            <?php checked('1', $publish_next_time, true) ?> 
            value="<?php echo esc_attr($publish_next_time=='1' ? '1':'0') ?>"
            title='If checked, this post will be posted to Medium on the next press of either the "Publish" or "Update" button.'
        >
        <input type="hidden" name="publish_to_medium_next_time_nonce" value="<?php echo esc_attr( wp_create_nonce('publish_to_medium_next_time_nonce') ); ?>" >
        
    </div>
    <br>
    <?php
        $db_tags = get_post_meta(get_the_ID(), 'medium_tags', true);
        if (isset($db_tags)){
            $tags = explode(",", $db_tags);
            $tag1_val = isset($tags[0]) ? $tags[0] : "";
            $tag2_val = isset($tags[1]) ? $tags[1] : "";
            $tag3_val = isset($tags[2]) ? $tags[2] : "";
        }
        //var_dump($db_tags);
        
    ?>
    <label for="medium_tags" style="margin-bottom: 0.25rem;">Tags:</label>
    <div id="medium_tags" style="display: flex; flex-direction: column; justify-content: space-between; align-items: left;">
        <input type="text" id="medium_tag_1_input" name="medium_tag_1" class="medium_tag_input" placeholder="Tag #1" value="<?php echo esc_attr( $tag1_val )?>" style="margin-bottom: 0.125rem;" >
        <input type="text" id="medium_tag_2_input" name="medium_tag_2" class="medium_tag_input" placeholder="Tag #2" value="<?php echo esc_attr( $tag2_val )?>" style="margin-bottom: 0.125rem;">
        <input type="text" id="medium_tag_3_input" name="medium_tag_3" class="medium_tag_input" placeholder="Tag #3" value="<?php echo esc_attr( $tag3_val )?>" style="margin-bottom: 0.125rem;">
    </div>

    <script>
        
    </script>

    <small>Don't forget to save after a change!</small>
<?php } else { ?>
    <div>View on <a target="_blank" href="<?php echo esc_attr($medium_url) ?>">Medium</a>.</div>
<?php } ?>


<script>
    document.addEventListener('DOMContentLoaded', (event)=>{
        console.log('loaded')

        //update publish checkbox state
        //get publish checkbox
        const medium_publish_checkbox = document.getElementById('publish_to_medium_next_time_input')

        //add event listener to update input value on check change
        medium_publish_checkbox.addEventListener('change', (event)=>{
            medium_publish_checkbox.value = medium_publish_checkbox.value=='1' ? '0' : '1'
        })

        //validate tag inputs
        //get tag elements
        const tag_inputs = Array.from(document.querySelectorAll(".medium_tag_input"))
        //add event listeners to set validation
        tag_inputs.map((input)=>{
            input.addEventListener('change', (event)=>{
                const regex = /^[a-zA-Z0-9]{1,25}$/
                const passed = regex.test(input.value)
                //console.log(passed)
                //test format
                if (!passed && input.value != ""){
                    input.setCustomValidity("Tags must consist of 25 or fewer alphanumeric characters.")
                    input.reportValidity()
                }
                else input.setCustomValidity("")
            })
        })
    })
</script>