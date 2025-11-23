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

        <form id="kisp-generator-form">
            <?php
            $args = [
                'post_type'      => 'post',
                'posts_per_page' => -1,
                'post_status'    => 'publish',
            ];
            $all_posts = get_posts($args);

            if ($all_posts) {
                echo '<table class="wp-list-table widefat fixed striped">';
                echo '<thead><tr><th style="width: 20px;"></th><th>Titel</th></tr></thead>';
                echo '<tbody>';
                foreach ($all_posts as $post) {
                    $content = esc_attr(strip_tags($post->post_content));
                    echo '<tr>';
                    echo '<td><input type="checkbox" name="post_ids[]" value="' . get_the_ID($post) . '" data-content="' . $content . '"></td>';
                    echo '<td>' . get_the_title($post) . '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
                echo '<br><button type="submit" id="generate-posts-btn" class="button button-primary">Posts generieren</button>';
            } else {
                echo '<p>Keine Beiträge gefunden.</p>';
            }
            ?>
        </form>

        <hr>
        <h2>Ergebnisse:</h2>
        <div id="kisp-results-container">
            <p>Hier erscheinen bald die generierten Posts...</p>
        </div>
    </div>

    <style>
        .kisp-result-block { padding: 15px; border: 1px solid #ccc; background: #fff; margin-bottom: 20px; }
        .kisp-result-block h4 { margin-top: 0; }
        .kisp-result-block textarea { width: 100%; height: 120px; margin-bottom: 10px; background: #f9f9f9; }
        .kisp-result-block button { cursor: pointer; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('kisp-generator-form');
            const resultsContainer = document.getElementById('kisp-results-container');
            const generateBtn = document.getElementById('generate-posts-btn');

            form.addEventListener('submit', function(event) {
                event.preventDefault();
                
                const selectedCheckboxes = document.querySelectorAll('input[name="post_ids[]"]:checked');
                
                if (selectedCheckboxes.length === 0) {
                    resultsContainer.innerHTML = '<p style="color: red;">Bitte wähle mindestens einen Beitrag aus.</p>';
                    return;
                }

                resultsContainer.innerHTML = '<p>Generiere Posts... Bitte warten. ⏳</p>';
                generateBtn.disabled = true;

                selectedCheckboxes.forEach(checkbox => {
                    const postContent = checkbox.getAttribute('data-content');
                    const postTitle = checkbox.closest('tr').querySelector('td:last-child').innerText;

                    fetch('http://localhost:3000/generate-posts', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ content: postContent })
                    })
                    .then(response => response.json()) // Wichtig: Wir erwarten jetzt JSON
                    .then(data => {
                        // Erstelle ein schönes HTML-Layout für die Ergebnisse
                        const resultBlock = `
                            <div class="kisp-result-block">
                                <h3>Ergebnis für: ${postTitle}</h3>
                                
                                <h4>LinkedIn</h4>
                                <textarea readonly>${data.linkedin}</textarea>
                                <button class="button" onclick="navigator.clipboard.writeText(this.previousElementSibling.value)">Kopieren</button>
                                
                                <h4>X (Twitter)</h4>
                                <textarea readonly>${data.x}</textarea>
                                <button class="button" onclick="navigator.clipboard.writeText(this.previousElementSibling.value)">Kopieren</button>
                                
                                <h4>Instagram</h4>
                                <textarea readonly>${data.instagram}</textarea>
                                <button class="button" onclick="navigator.clipboard.writeText(this.previousElementSibling.value)">Kopieren</button>
                            </div>
                        `;
                        
                        if (resultsContainer.innerHTML.includes('Generiere Posts')) {
                            resultsContainer.innerHTML = resultBlock;
                        } else {
                            resultsContainer.innerHTML += resultBlock;
                        }
                    })
                    .catch(error => {
                        console.error('Fehler:', error);
                        resultsContainer.innerHTML += `<p style="color: red;">Ein Fehler ist für "${postTitle}" aufgetreten.</p>`;
                    })
                    .finally(() => {
                        generateBtn.disabled = false;
                    });
                });
            });
        });
    </script>
    <?php
}