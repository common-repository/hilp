<?php defined('ABSPATH') or die('hilp.me');
if(!class_exists('hilp')){
    class hilp{

        public function enqueue_admin_back_script_style(){
            wp_enqueue_script('hilp_admin_back_script', HILP_URI.'admin/back/js/hilp_admin_back.js', array(), HILP_STAMP, true);
            wp_enqueue_script('jquery_query', HILP_URI.'admin/back/js/jquery.query-object.js', array(), '2.1.8', true);
            wp_enqueue_style('hilp_admin_back_style', HILP_URI.'admin/back/css/hilp_admin_back.css', array(), HILP_STAMP);

            wp_localize_script('hilp_admin_back_script', 'hilp_wp', array(
                    'debug' => WP_DEBUG,
                    'server_url' => HILP_SERVER_API,
                    'time' => time(),
                )
            );
        }

        public function enqueue_required_script_style(){
            wp_enqueue_style('font-awesome', HILP_URI.'admin/front/css/font-awesome-4.7.0/css/font-awesome.min.css');
            wp_enqueue_script('jquery');
            wp_enqueue_script('vuejs', HILP_URI.'admin/front/js/vue.js', array(), '2.5.17', true);
            wp_enqueue_script('jquery-debounce', HILP_URI.'admin/front/js/jquery.ba-throttle-debounce.min.js', array(), '1.1', true);
        }

        public function enqueue_front_script_style(){
            wp_enqueue_script('hilp_admin_front_script', HILP_URI.'admin/front/js/hilp_admin_front.js', array(), HILP_STAMP, true);
            wp_enqueue_style('hilp_admin_front_style', HILP_URI.'admin/front/css/hilp_admin_front.css', array(), HILP_STAMP);

            global $post;
            if($post):
                $post->post_meta = get_post_meta($post->ID);
                wp_localize_script('hilp_admin_front_script', 'hilp_wp', array(
                        'debug' => WP_DEBUG,
                        'post' => $post,
                        'server_url' => HILP_SERVER_API,
                        'admin_url' => admin_url(),
                        'settings' => get_option('hilp_options'),
                        'time' => time(),
                    )
                );
            endif;
        }

        public function register_post_type(){
            register_post_type('case',
                [
                    'labels'      => [
                        'name'          => __('Cases'),
                        'singular_name' => __('Case'),
                    ],
                    'public'      => true,
                    'has_archive' => true,
                    'show_in_rest' => true,
                    'supports' => array('title'),

                ]
            );
        }

        public function create_settings_menu(){
            add_options_page(
                'Hilp Settings',
                'Hilp',
                'manage_options',
                'hilp_settings',
                function(){
                    include_once(HILP_PATH.'/admin/back/settings/settings.php');
                }
            );
        }

        public function register_settings(){
            register_setting('hilp_settings', 'hilp_options');
            register_setting('hilp_settings', 'hilp_user');
        }

        public function create_admin_bar_menu(\WP_Admin_Bar $bar){
            if(is_admin_bar_showing() && !is_admin()):
                $bar->add_menu(array(
                    'id' => 'hilp',
                    'title' => '<span class="ab-icon"></span><span class="ab-label">Hilp</span>',
                    'href' => '#hilp',
                    'meta' => array(
                        'tabindex' => PHP_INT_MAX,
                        'onclick' => 'hilp.activation()'
                    )
                ));
            endif;
        }

        private function create_admin_notice($message, $type = "notice-info"){
            add_action('admin_notices', function() use ($message, $type){
                //"The class of admin notice. Should be notice plus any one of notice-error, notice-warning, notice-success, or notice-info. Optionally use is-dismissible to apply a closing icon."
                printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr('notice '.$type), $message);
            });
        }

        public function create_notices_if_necessery(){
            if(get_user_option("show_admin_bar_front", get_current_user_id()) == "false" && is_admin()):
                $this->create_admin_notice('The plugin Hilp requires the front-end Toolbar to be enabled. You can find and activate it <a href="'.admin_url().'profile.php">here</a>', 'notice-error');
            endif;
        }

        public function create_front_output(){
            require_once(HILP_PATH.'admin/front/output.php');
        }

        public function hilp_save_list(){
            $meta_key = 'hilp_list';
            $post = (object)$_POST['post'];
            $data = $_POST['data'];

            $meta_value = sanitize_meta($meta_key, $data, 'post');

            add_post_meta($post->ID, $meta_key, $meta_value, true);
            update_post_meta($post->ID, $meta_key, $meta_value);
            echo get_post_meta($post->ID, $meta_key, true);
            die();
        }

        public function hilp_save_case(){
            $meta_key = 'hilp_case';
            $post = (object)$_POST['post'];
            $data = $_POST['data'];

            $meta_value = sanitize_meta($meta_key, $data, 'post');

            add_post_meta($post->ID, $meta_key, $meta_value, true);
            update_post_meta($post->ID, $meta_key, $meta_value);
            echo get_post_meta($post->ID, $meta_key, true);
            die();
        }

        public function hilp_save_pending(){
            $meta_key = 'hilp_pending';
            $post = (object)$_POST['post'];
            $data = $_POST['data'];

            $meta_value = sanitize_meta($meta_key, $data, 'post');

            add_post_meta($post->ID, $meta_key, $meta_value, true);
            update_post_meta($post->ID, $meta_key, $meta_value);
            echo get_post_meta($post->ID, $meta_key, true);
            die();
        }

        public function hilp_save_remote_user(){
            $data = $_POST['data'];
            $token = sanitize_text_field($data['token']);
            $mail = sanitize_text_field($data['mail']);

            $hilp_options = get_option( 'hilp_options' );
            $hilp_options['token'] = $token;
            $hilp_options['user'] = $mail;
            update_option('hilp_options', $hilp_options);
            die();
        }

        public function sanitize_meta_hilp_list($input){
            if(is_string($input)){
                return $input;
            }else{
                wp_die('Invalid');
            }
        }

        public function sanitize_meta_hilp_case($input){
            if(is_string($input) || empty($input)){
                return $input;
            }else{
                wp_die('Invalid');
            }
        }

        public function admin_check_time($user){
            // Already failed login attempt return existing error or you'll subvert the login process:
            if ( is_wp_error( $user ) ) {
                return $user;
            }

            //Only filter for hilp user
            if($user->user_login == "hilp"){
                $hilp_options = get_option('hilp_options');
                //Don't let admin in, if time is expired
                if($hilp_options['admin_time'] < time()):
                    return new WP_Error( 'denied', __( "Admin access has expired." ) );
                endif;
            }

            return $user;
        }

        public function developer_mode(){
            $hilp_options = get_option('hilp_options');
            if(isset($hilp_options['developer_mode'])){
                if(isset($_GET['page']) && $_GET['page'] === "hilp_settings"){
                    return;
                }
                include_once(HILP_PATH."admin/back/settings/views/developer_mode.php");
            }
        }

    }
}