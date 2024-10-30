<?php defined('ABSPATH') or die('hilp.me'); ?>
<div id="hilp_settings" class="wrap">
    <form id="hilp_response"></form>
    <?php
    #Print all views, let js decide what should show
    require_once('views/default.php');

    require_once('views/register.php');
    require_once('views/login.php');
    require_once('views/create_admin.php');
    require_once('views/finished.php');
    ?>
</div>