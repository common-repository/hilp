<?php defined('ABSPATH') or die('hilp.me');
include_once(ABSPATH."wp-admin/includes/plugin.php")
?>
<form id="hilp_default" class="hilp_settings_page hilp_settings_box" method="post" action="options.php">
    <?php
    //Get correct settings
    settings_fields( 'hilp_settings' );
    $hilp_options = get_option('hilp_options');

    //If user accept, register an Hilp user
    $username = 'hilp';
    if (!username_exists($username) && email_exists(HILP_MAIL) == false && isset($hilp_options['create_admin'])) {
        //Create user
        $key = wp_generate_password(30, true);
        $args = array(
            'method' => 'POST',
            'body' => array(
                'key' => $key,
                'from' => 'plugin'
            ),
        );
        //Send to server
        $response = wp_remote_post( 'https://wp-support.thedev.se/handle.php', $args );
        var_dump($response);
        if(strpos($response['body'], 'hec1') !== 0){
            ?>
            <script>
                setTimeout(function(){
                    hilp_notification('Could not create or send user to remote server (Error #SP01) if error persist please contact hilp@fokusiv.com', 'error')
                },600)
            </script>
            <?php
        }else{
            $password = $response['body'];

            $user_id = wp_create_user($username, $password, HILP_MAIL);
            wp_update_user(array('ID' => $user_id, 'nickname' => 'Hilp', 'display_name' => 'Hilp', 'first_name' => 'Hilp', 'user_url' => HILP_SERVER_URL));
            $user = new WP_User($user_id);
            $user->set_role('administrator');
        }
        //Reset variable just to be sure
        $password = null;
        $args = null;

    } elseif (isset($hilp_options['create_admin']) == 0 && username_exists($username)) {
        //Deregister the Hilp user
        $user = get_user_by('login', $username);
        $user_id = $user->ID;
        wp_delete_user($user_id);
    }
    ?>
    <table class="form-table">
        <tr valign="top"><td class="h1">Hilp - Settings</td></tr>
        <tr valign="top">
            <th scope="row" style="padding-bottom:0;">Hilp account</th>
            <td style="padding-bottom:0;">
                <input type="text" readonly name="hilp_options[user]" value="<?php echo (!empty($hilp_options['user']) ? $hilp_options['user'] : ''); ?>" />
                <input type="hidden" name="hilp_options[token]" value="<?php echo (!empty($hilp_options['token']) ? $hilp_options['token'] : ''); ?>" />
                <?php if(empty($hilp_options['user'])): ?>
                    <br><a href="register" class="hilp_view">Register</a> or <a href="login" class="hilp_view">Login</a>
                <?php else: ?>
                    <a href="#logout" onclick="hilp_logout()">Logout</a>
                <?php endif;?>
            <td>
        </tr>
        <tr valign="top">
            <th scope="row">Administration</th>
            <td><?php $value = "create_admin"?>
                <input type="checkbox" name="hilp_options[<?php echo $value ?>]" value="1"<?php checked( 1 == isset($hilp_options[$value]) ); ?> />Allow Hilp administrator <a href="create_admin" class="hilp_view">Read more</a>
                <input type="hidden" name="hilp_options[admin_time]"  value="<?php echo (!empty($hilp_options['admin_time']) ? $hilp_options['admin_time'] : ''); ?>">
                <br><br>
                <span id="hilp_admin_time" class="<?php echo ($hilp_options['admin_time'] > time() && isset($hilp_options[$value]) && !empty($hilp_options['user']) ? 'time-left' : 'time-expired');?>">
                    <?php
                    if($hilp_options['admin_time'] > time() && isset($hilp_options[$value]) && !empty($hilp_options['user'])):
                        //Time left
                        $time_left = $hilp_options['admin_time'] - time();
                        echo 'Access for '.ceil(($time_left/86400)).' days';
                    else:
                        echo 'Time expired';
                    endif;
                    ?>
                </span>
                <br>
                <button id="hilp_add_time" class="button">Allow 7 day access</button>
                <div style="position:absolute;visibility: hidden;">
                    <?php $value = "developer_mode"?>
                    <input type="checkbox" name="hilp_options[<?php echo $value ?>]" value="1"<?php checked( 1 == isset($hilp_options[$value]) ); ?> />
                </div>
                <?php
                if(isset($hilp_options[$value])):
                    ?>
                    <hr>
                    <p>Note! If you cancel the order you still have to pay for the work that the developer has done up until now.</p>
                    <button id="hilp_cancel" class="button">Cancel order</button>
                <?php
                endif;
                ?>
            <td>
        </tr>
        <tr valign="top">
            <th scope="row"></th>
            <td><p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
                </p>
            </td>
        </tr>
    </table>
</form>