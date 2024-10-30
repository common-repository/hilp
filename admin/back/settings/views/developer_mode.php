<?php defined('ABSPATH') or die('hilp.me'); ?>
<div id="hilp_developer_mode">
    <div class="hilp_settings_box">
        <h1>Hilp has put your site in developer mode</h1>
        <p>Our developers are working hard on your case. We have put your site into developer mode, which means you cannot edit the site before our work is done. This is done to avoid the database version to split.</p>
        <p>We appreciate your patience while we work on your site. If you have any questions, feel free to mail us at hilp@fokusiv.com</p>
        <p>If you want to cancel your order, you can do it <a href="<?php echo admin_url()?>options-general.php?page=hilp_settings">here</a>. This will also disable the developer mode.</p>
        <p class="submit">
            <a href="<?php echo HILP_SERVER_URL?>how-it-works/#developer-mode" target="_blank" class="button button-primary text-right">Read about developer mode</a>
        </p>
    </div>
</div>