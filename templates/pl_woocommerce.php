<?php
/**
 * Copyright (c) 2018.
 * Plugin Name: Prooflite
 * Plugin URI:  https://wordpress.org/plugins/prooflite/
 * Description: Prooflite is a social proof tool which helps to increase website conversion by showing live visitors activity.
 * Version: 1.0
 * Author: Prooflite Team
 * Author URI: https://prooflite.com
 * Text Domain: Prooflite
 * Domain Path: /languages
 */
?>

<div class="wrap">
    <h2>ProofLite</h2>

    <form method="post" action="options.php">
        <?php wp_nonce_field('update-options') ?>
        <p><strong>Status:</strong><br />
            <select name="pl_woocommerce_status" id="">
                <option value="1" <?php (get_option('pl_woocommerce_status') === 1)?'selected=selected':'';?>>Enabled</option>
                <option value="0" <?php (get_option('pl_woocommerce_status') === 0)?'selected=selected':'';?>>Disabled</option>
            </select>
        </p>
        <p>
            <strong>Web Hook Url:</strong><br />
            <input type="text" name="pl_webhook_url" size="120" value="<?php echo get_option('pl_webhook_url'); ?>" />
        </p>
        <p>
            <strong>Message:</strong> <br />
            <input type="text" name="pl_message_format" size="120" value="<?php echo get_option('pl_message_format', 'Just Purchased [product_link] [product_name] [/product_link]'); ?>" placeholder="Just Purchased [product_link] [product_name] [/product_link]"  />
            <br>
            Note: [product_link], [product_name] will be replaced with product link and product name.
        </p>
        <p><input type="submit" name="Submit" class="button button-primary" value="Update" /></p>
        <input type="hidden" name="action" value="update" />        
        <input type="hidden" name="page_options" value="pl_message_format, pl_webhook_url, pl_woocommerce_status" />
    </form>
</div>
