<?php defined('ABSPATH') or die('hilp.me'); ?>
<div id="hilp_app">
    <div id="hilp_panel_wrap">
        <div id="hilp_toolbar">
            <span v-if="user.token !== false" id="hilp_toolbar_user"><img alt="" src="http://2.gravatar.com/avatar/b8ccb1f89ff2c028edba733c2adddfa7?s=26&amp;d=mm&amp;r=g" srcset="http://2.gravatar.com/avatar/b8ccb1f89ff2c028edba733c2adddfa7?s=52&amp;d=mm&amp;r=g 2x" class="avatar avatar-26 photo" height="26" width="26"> {{ user.name }}</span>
            <span id="hilp_toolbar_settings"><a href="<?php echo admin_url('options-general.php?page=hilp_settings')?>">Settings</a></span>
            <span id="hilp_toolbar_deactivation" class="hilp_pointer dashicons dashicons-no-alt" v-on:click.left="deactivation()"></span>
        </div>
        <div id="hilp_loading" v-if="loading === 'true'" v-bind:class="loading">
            <div id="hilp_loading_text">
                <span class="dashicons dashicons-update"></span>
                <span>Loading</span>
            </div>
        </div>


        <div id="hilp_panel">
            <div>
                <div id="hilp_case">
                    <form id="hilp_case_form">
                        <li v-for="pin in list">
                            <input type="text" name="text" autocomplete="off" placeholder='Explain what needs to be done' v-model="pin.text">
                        </li>
                        <div id="hilp_add_pointer_wrap">
                            <span id="hilp_add_pointer" class="hilp_pointer" @click="place_pin()" title="Add pin"><span class="dashicons dashicons-plus"></span></span>
                        </div>
                    </form>
                </div>
            </div>
            <div>
                <div id="hilp_portal">

                    <form id="hilp_login" v-if="user.token === false">
                        <br>
                        <p>You need a hilp account to send cases to our developers.</p><br><br>
                        <a class="hilp_pointer button" href="<?php echo admin_url('options-general.php?page=hilp_settings&view=login')?>">Login</a><br>
                        <a class="hilp_pointer" href="<?php echo admin_url('options-general.php?page=hilp_settings&view=register')?>">or register</a>
                    </form>

                    <div id="hilp_loggedin" v-if="user.token !== false">

                        <div class="hilp_choose_plan">
                            <div>
                                <label>
                                    <input type="radio" name="hilp_plan" value="normal" checked>
                                    <span></span>
                                    Standard<br>
                                    <span class="small">(€70/h)</span>
                                </label>

                            </div><!--
                            --><div>
                                <label>
                                    <input type="radio" name="hilp_plan" value="urgent">
                                    <span></span>
                                    Urgent<br>
                                    <span class="small">(€120/h)</span>
                                </label>
                            </div>
                        </div>
                        <button class="hilp_pointer" @click="post_list()">Send</button>
                        <br>
                        <span class="small">Remember, you wont be able to edit the site while our developers work on it. You will get notified by mail when we start working. <a class="hilp_pointer" href="<?php echo HILP_SERVER_URL?>/how-it-works/" target="_blank">Read more here</a></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <transition name="fade">
        <ul id="hilp_list" v-if="active">
            <li v-for="pin in list" class="hilp_request" v-bind:style="pin.position">
                <div class="hilp_the_pointer" v-on:mousedown.left="move_pin(pin)">
                    <div class="inner-icon"></div>
                </div>
            </li>
        </ul>
    </transition>
    <div id="hilp_notification" @click="notification.type = ''" v-html="notification.message" v-bind:class="notification.type"></div>

    <div id="hilp_trash_can" v-if="moving === 'true'"><span class="dashicons dashicons-trash"></span></div>

    <div style="visibility:hidden;position:absolute;">
        <?php include_once(HILP_PATH.'admin/back/settings/views/default.php')?>
    </div>
</div>
