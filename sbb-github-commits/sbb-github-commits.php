<?php
/**
 * Plugin Name: github-commits
 * repository: Affichage de commits dans un widget
 * Version: 0.1
 * Author: Simonbdbc
 */

class Github_Commits extends WP_Widget {
 
        // Configuration globale du widget
        function __construct() {
 
                $widget_args = array(
                        'classname' => 'widget_git_commits',
                        'repository' => 'Affiche les derniers commits du repo selectionné'
                );
 
                parent::__construct(
                        'git_commits',
                        __('Github Commits'),
                        $widget_args
                );
        }
 
        // Affichage en front-end
        function widget($args, $instance) {
                
                extract($args);
                
                $username = strip_tags($instance['username']);
                $repository = strip_tags($instance['repository']);
                $number = strip_tags($instance['number']);
 
                echo $before_widget;

                if ($number > 1) {
                        echo $before_title . $number .' derniers commits du dépôt ' . $repository . $after_title;

                } else {
                        echo $before_title . 'Dernier commit du dépôt ' . $repository . $after_title;
                }

                if (!empty($username) && !empty($repository) && !empty($number)) {

                        if ( false === ( $cache = get_transient( 'repo_commits_cached' ))) {

                                require_once 'vendor/autoload.php';
                                $api = new Milo\Github\Api;

                                $response = $api->get('/repos/'.($username).'/'.($repository).'/commits');
                                $repo_commits = $api->decode($response);
                                
                                set_transient( 'repo_commits_cached', $repo_commits, 60 * 60 * 1);
                        }
                        else {
                            
                                $repo_commits = get_transient('repo_commits_cached');
                                $nb = $number;
                                $i = 0;
                                
                                echo '<ul>';
                                foreach($repo_commits as $commits) {
                                        echo '<li>';
                                        echo ($commits->commit->message);
                                        echo '<br>';
                                        echo '<small>'.($commits->commit->committer->name).'</small>';
                                        echo '</li>';
                                        $i++;
                                        if ($i >= $nb){
                                                break;
                                        }
                                }
                                echo '</ul>';
                        }
                        
                } else {
                        echo 'Aucun commit à afficher';
                }

                echo $after_widget;
        }
 
        // Traitement des données avant sauvegarde
        function update( $new_instance, $old_instance ) {
                $new_instance['username'] = strip_tags($new_instance['username']);
                $new_instance['repository'] = strip_tags($new_instance['repository']);
                $new_instance['number'] = strip_tags($new_instance['number']);
                return $new_instance;
        }
 
        // Affichage du formulaire de réglages du widget en back-end
        function form($instance) {
                $instance = wp_parse_args(
                        $instance,
                        array(
                                'username' => '',
                                'repository' => '',
                                'number' => ''
                        )
                );
 
                $username = strip_tags($instance['username']);
                $repository = strip_tags($instance['repository']);
                $number = strip_tags($instance['number']);
 
                ?>
 
                <p>
                        <label for="<?= $this->get_field_id('username'); ?>">Pseudo Github :</label>
                        <input class="widefat" id="<?= $this->get_field_id('username'); ?>" name="<?= $this->get_field_name('username'); ?>" type="text" value="<?= esc_attr($username); ?>" />
                </p>
 
                <p>
                        <label for="<?= $this->get_field_id('repository'); ?>">Dépôt Github :</label>
                        <input class="widefat" id="<?= $this->get_field_id('repository'); ?>" name="<?= $this->get_field_name('repository'); ?>" type="text" value="<?= esc_attr($repository); ?>" />
                </p>
 
                <p>
                        <label for="<?= $this->get_field_id('number'); ?>">Nombre de commits affichés :</label>
                        <input class="widefat" id="<?= $this->get_field_id('number'); ?>" name="<?= $this->get_field_name('number'); ?>" type="number" value="<?= esc_attr($number); ?>" />
                </p>
  
        <?php }
}
 
function init_commits_widget() {
        register_widget('Github_Commits');
}

add_action('widgets_init', 'init_commits_widget');