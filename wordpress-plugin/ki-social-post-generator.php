<?php
/**
 * Plugin Name:       KI Social Post Generator
 * Description:       Generiert Social-Media-Posts aus Blog-Inhalten mithilfe der OpenAI API.
 * Version:           1.0
 * Author:            Albert Kupper
 */


if (!defined('ABSPATH')) {
    exit;
}


function kisp_add_admin_menu() {
    add_menu_page(
        'KI Social Post Generator', 
        'KI Generator',             
        'manage_options',           
        'ki-social-post-generator', 
        'kisp_display_plugin_page', 
        'dashicons-format-status',  
        6                           
    );
}

add_action('admin_menu', 'kisp_add_admin_menu');


function kisp_display_plugin_page() {
    ?>
    <div class="wrap">
        <h1>KI Social Post Generator</h1>
        <p>Wähle die Beiträge aus, für die du Social-Media-Posts generieren möchtest.</p>

        <form method="post" action="">
            <?php
            
            $args = array(
                'post_type'      => 'post',
                'posts_per_page' => -1, 
                'post_status'    => 'publish',
            );

            
            $all_posts = get_posts($args);

            if ($all_posts) {
                echo '<table class="wp-list-table widefat fixed striped">';
                echo '<thead><tr><th style="width: 20px;"></th><th>Titel</th></tr></thead>';
                echo '<tbody>';

                foreach ($all_posts as $post) {
                    echo '<tr>';
                    echo '<td><input type="checkbox" name="post_ids[]" value="' . get_the_ID($post) . '"></td>';
                    echo '<td><a href="' . get_edit_post_link($post) . '">' . get_the_title($post) . '</a></td>';
                    echo '</tr>';
                }
                
                wp_reset_postdata(); 

                echo '</tbody></table>';
                echo '<br><input type="submit" name="generate_posts" class="button button-primary" value="Posts generieren">';

            } else {
                echo '<p>Keine Beiträge gefunden. Erstelle zuerst einen neuen Beitrag.</p>';
            }
            ?>
        </form>
    </div>
    <?php
}