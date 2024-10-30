<?php defined('ABSPATH') or die('hilp.me'); ?>
<form method="post" id="hilp_login" class="hilp_settings_page hilp_settings_box">
    <h1>Login</h1>
    <p>This is the login for your www.hilp.me user</p>
    <input type="text" name="username" placeholder="Username or mail" required> <br>
    <input type="password" name="password" placeholder="Password" required> <br>
    <p class="submit">
        <a href="register" class="hilp_view">Register</a>
        <button type="submit" class="button button-primary text-right">Login</button>
    </p>
</form>