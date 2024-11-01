<?php

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (! class_exists('Wpvc_Shortcode_Controller')) {
    /**
     * Shortcode Rest Controller.
     */
    class Wpvc_Shortcode_Controller
    {
        /**
         * Controller Contructor.
         */
        public function __construct()
        {
            // Script Exclude for Without Shortcode.
            add_filter('the_posts', array( $this, 'wpvc_conditionally_add_scripts_and_styles' ));

            // Script Include for Without Shortcode.
            $settings = get_option(WPVC_VOTES_SETTINGS);
            if (isset($settings['plugin']['loadscript']) && $settings['plugin']['loadscript'] == 'on') {
                add_action('wp_enqueue_scripts', array( $this, 'wpvc_add_styles_to_front_end' ), 99);
            }

            // Shortcode functions.
            add_shortcode('showcontestants', array( $this, 'wpvc_votes_show_contestants' ));

            add_shortcode('addcontestants', array( $this, 'wpvc_voting_add_contestants' ));

            add_shortcode('upcomingcontestants', array( $this, 'wpvc_voting_start_contestants' ));
            add_shortcode('endcontestants', array( $this, 'wpvc_voting_end_contestants' ));

            add_filter('single_template', array( $this, 'wpvc_vote_contestant_single_contestant' ), 11);

            add_action('init', array( $this, 'wpvc_set_url_cookie'));

        }

        /**
         * Set gallary page url for single page.
         *
         */
        public function wpvc_set_url_cookie()
        {
            if (!headers_sent()) {

                global $wp;
                $current_url = home_url(add_query_arg(array(), $wp->request));

                // Delete and create cookie for single page
                setcookie('wpvc_contestant_URL', '', time() - 3600);
                setcookie('wpvc_contestant_URL', $current_url, time() + (86400 * 30), '/');

            }
        }

        /**
         * Show Contestants.
         *
         * @param mixed $show_cont_args Shortcode atrributes.
         */
        public function wpvc_votes_show_contestants($show_cont_args)
        {

            require_once WPVC_FR_VIEW_PATH . 'wpvc_shortcode_view.php';
            $show_cont_args = Wpvc_Shortcode_Model::wpvc_get_category_options_and_values($show_cont_args);
            ob_start();
            wpvc_common_div_to_get('showcontestants');
            wpvc_showcontestants_view($show_cont_args);
            $content = ob_get_clean();
            $content = do_shortcode($content);
            return $content;
        }
        /**
         * Add Contestants.
         *
         * @param mixed $show_cont_args Shortcode atrributes.
         */
        public function wpvc_voting_add_contestants($show_cont_args)
        {
            global $wp;
            require_once WPVC_FR_VIEW_PATH . 'wpvc_shortcode_view.php';
            $show_cont_args = Wpvc_Shortcode_Model::wpvc_get_category_options_and_values_addcontestants($show_cont_args);
            ob_start();
            wpvc_common_div_to_get('addcontestants');
            wpvc_addcontestants_view($show_cont_args);
            $content = ob_get_clean();
            $content = do_shortcode($content);
            return $content;
        }
        /**
         * Show Contestants.
         *
         * @param mixed $show_cont_args Shortcode atrributes.
         */
        public function wpvc_voting_start_contestants($show_cont_args)
        {
            global $wp;
            require_once WPVC_FR_VIEW_PATH . 'wpvc_shortcode_view.php';
            if (empty($show_cont_args)) {
                $show_cont_args = array();
            }
            $show_cont_args['showform']    = 1;
            $show_cont_args['showtimer']   = 1;
            $show_cont_args['showtop']     = 0;
            $show_cont_args['showprofile'] = 0;

            $show_cont_args = Wpvc_Shortcode_Model::wpvc_get_category_options_and_values($show_cont_args, null, 'upcoming');
            ob_start();
            wpvc_common_div_to_get('upcomingcontestants');
            wpvc_upcoming_showcontestants_view($show_cont_args);
            $content = ob_get_clean();
            $content = do_shortcode($content);
            return $content;
        }
        /**
         * Show Contestants.
         *
         * @param mixed $show_cont_args Shortcode atrributes.
         */
        public function wpvc_voting_end_contestants($show_cont_args)
        {
            global $wp;
            require_once WPVC_FR_VIEW_PATH . 'wpvc_shortcode_view.php';
            if (empty($show_cont_args)) {
                $show_cont_args = array();
            }
            $show_cont_args['showform']    = 0;
            $show_cont_args['showtimer']   = 1;
            $show_cont_args['showtop']     = 0;
            $show_cont_args['showprofile'] = 0;

            $show_cont_args = Wpvc_Shortcode_Model::wpvc_get_category_options_and_values($show_cont_args, null, 'endcontest');
            ob_start();
            wpvc_common_div_to_get('endcontestants');
            wpvc_endcontest_showcontestants_view($show_cont_args);
            $content = ob_get_clean();
            $content = do_shortcode($content);
            return $content;
        }
        /**
         * Single Contestants.
         *
         * @param mixed $single Shortcode atrributes.
         */
        public function wpvc_vote_contestant_single_contestant($single)
        {
            global $wp_query;
            require_once WPVC_FR_VIEW_PATH . 'wpvc_shortcode_view.php';
            if (isset($wp_query->query_vars['contestants']) || ($wp_query->query_vars['preview'] == 'true'
                && $wp_query->query_vars['post_type'] == 'contestants')) {
                if ($wp_query->query_vars['contestants'] != '' || (isset($_GET['post_type']) && sanitize_text_field(wp_unslash($_GET['post_type'])) == 'contestants')) {

                    $post_id    = get_the_ID();
                    $votes_view = get_post_meta($post_id, WPVC_VOTES_VIEWERS, true);
                    update_post_meta($post_id, WPVC_VOTES_VIEWERS, intval($votes_view) + 1);
                    Wpvc_Single_Contestant_Model::wpvc_check_voting_single_template();

                    $template = WPVC_FR_VIEW_PATH . 'wpvc_contestants_single_view.php';

                    if (file_exists(WPVC_PARENT_TEMPLATE_PATH . 'wpvc_contestants_single_view.php')) {

                        $template = WPVC_PARENT_TEMPLATE_PATH . 'wpvc_contestants_single_view.php';
                    } elseif (file_exists(WPVC_CHILD_TEMPLATE_PATH . 'wpvc_contestants_single_view.php')) {

                        $template = WPVC_CHILD_TEMPLATE_PATH . 'wpvc_contestants_single_view.php';
                    } else {
                        wpvc_common_div_to_get('singlecontestants');
                        $template = WPVC_FR_VIEW_PATH . 'wpvc_contestants_single_view.php';
                    }
                    return $template;
                }
            }
            return $single;
        }
        /**
         * Scripts.
         *
         * @param mixed $posts Posts Object.
         */
        public function wpvc_conditionally_add_scripts_and_styles($posts)
        {
            global $wp_query;
            if (empty($posts)) {
                return $posts;
            }

            $shortcode  = 'showcontestants';
            $shortcode2 = 'addcontestants';
            $shortcode4 = 'upcomingcontestants';
            $shortcode5 = 'endcontestants';
            $shortcode9 = 'contestauthor';

            $shortcode_found = false; // use this flag to see if styles and scripts need to be enqueued.

            // Visual Builder Fix
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
            if (is_plugin_active('js_composer/js_composer.php') && defined('WPB_VC_VERSION')) {
                $shortcode_found = true;
            }

            foreach ($posts as $post) {
                if (isset($wp_query->query_vars['contestants']) || stripos($post->post_content, '[' . $shortcode) !== false || stripos($post->post_content, '[' . $shortcode2) !== false || stripos($post->post_content, '[' . $shortcode5) !== false) {
                    $shortcode_found = true;
                    break;
                }
            }

            if ($shortcode_found) {
                add_action('wp_enqueue_scripts', array( $this, 'wpvc_add_styles_to_front_end' ), 99);
            }

            return $posts;
        }
        /**
         * Front Styles.
         *
         * @param mixed $vote_opt Settings Object.
         */
        public static function wpvc_add_styles_to_front_end($vote_opt)
        {
            if (! is_admin()) {
                wp_enqueue_style('ow-wpvc-front-css', plugins_url('assets/css/ow-wpvc-front-css.css', dirname(__FILE__)), '', WPVC_VOTE_VERSION);
                wp_enqueue_style('ow-wpvc-gallery', plugins_url('assets/css/ow-wpvc-gallery.css', dirname(__FILE__)), '', WPVC_VOTE_VERSION);
                // Add Builded React JS.
                wp_enqueue_script(
                    'wpvc-owfront-runtime',
                    plugins_url('/wpvc_views/build/runtime.js', dirname(__FILE__)),
                    array( 'wp-element', 'wp-i18n' ),
                    WPVC_VOTE_VERSION, // Change this to null for production.
                    true
                );
                wp_enqueue_script(
                    'wpvc-owfront-vendor',
                    plugins_url('/wpvc_views/build/vendors.js', dirname(__FILE__)),
                    array( 'wp-element', 'wp-i18n' ),
                    WPVC_VOTE_VERSION, // Change this to null for production.
                    true
                );
                wp_enqueue_script(
                    'wpvc-owfront-react',
                    plugins_url('/wpvc_views/build/front.js', dirname(__FILE__)),
                    array( 'wp-element', 'wp-i18n' ),
                    WPVC_VOTE_VERSION, // Change this to null for production.
                    true
                );

                $query_args  = array(
                    'family' => 'Open+Sans:400,500,700|Oswald:700|Roboto:300,400,500,700',
                    'subset' => 'latin,latin-ext',
                );
                $query_icons = array(
                    'family' => 'Material+Icons',
                );
                wp_register_style('google_fonts', add_query_arg($query_args, '//fonts.googleapis.com/css'), array(), null);
                wp_enqueue_style('google_fonts');
                wp_register_style('material_icons', add_query_arg($query_icons, '//fonts.googleapis.com/icon'), array(), null);
                wp_enqueue_style('material_icons');

                // Add Guttenburg Scripts for the WordPress Hooks.
                wp_enqueue_script('postbox', admin_url('js/postbox.min.js'), array( 'jquery-ui-sortable' ), false, 1);
                wp_tinymce_inline_scripts();
                wp_enqueue_script('heartbeat');
            }
        }


    }
} else {
    die('<h2>' . esc_html_e('Failed to load the Voting Shortcode Controller', 'voting-contest') . '</h2>');
}

return new Wpvc_Shortcode_Controller();
