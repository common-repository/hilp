function is_hilp_settings(){
    var url_string = window.location.href;
    var url = new URL(url_string);
    var page = url.searchParams.get("page");
    if(page === "hilp_settings"){
        return true
    }else{
        return false
    }
}
if(is_hilp_settings()){

    function hilp_notification(text, type="success"){
        jQuery('#hilp_response').removeClass();
        jQuery('#hilp_response').html(text).addClass(type)
    }
    function hilp_register(formId) {
        var data = jQuery(formId).serializeArray();
        data.push({name: 'username', value: jQuery(formId+' input[name="email"]').val()});
        jQuery.ajax({
            type: "POST",
            url: hilp_wp.server_url + 'wp/v2/users',
            data: data,
            success: function (data) {
                if (typeof data.id != "undefined") {
                    //Success, now login
                    hilp_notification('User created, now logging in...', 'success');
                    hilp_login(jQuery(formId + ' input[name="email"]').val(), jQuery(formId + ' input[name="password"]').val())
                } else {
                    //Could not create user
                    hilp_notification('(Error #R01) User could not be created, please contact hilp@fokusiv.com', 'error');
                    console.log(data);
                }
            },
            error: function (response) {
                // Error in response
                hilp_notification(response.responseJSON.message+ ' (Error #R02) If error persist, please contact hilp@fokusiv.com', 'error');
                console.log(response);
            }
        });
    }
    function hilp_login(username, password) {
        jQuery.ajax(hilp_wp.server_url + "jwt-auth/v1/token", {
            method: "POST",
            data: {
                username: username,
                password: password
            }
        })
            .done(function (response) {
                if (response === 0) {
                    //Server not responding correctly
                    hilp_notification('(Error #L02) Could not login, please contact hilp@fokusiv.com', 'error');
                    console.log(response);
                } else if (response.token.length) {
                    jQuery('input[name="hilp_options[user]"]').val(username);
                    jQuery('input[name="hilp_options[token]"]').val(response.token);

                    //If action activate go to next
                    const params = new URLSearchParams(location.search);
                    var action = params.get('action');
                    if(action === "activated"){
                        hilp_view('create_admin');
                        hilp_save_settings('Logged in as '+ username);
                    }else{
                        hilp_save_settings('Logged in as '+ username, 'default');
                    }


                } else {
                    //Could not create token
                    hilp_notification('(Error #L03) Could not login, please contact hilp@fokusiv.com', 'error');
                    console.log(response);
                }
            })
            .fail(function (response) {
                hilp_notification(response.responseJSON.message+ ' (Error #L01) If error persist, please contact hilp@fokusiv.com', 'error');
                console.log(response);
            })
    }

    function hilp_logout() {
        jQuery('input[name="hilp_options[user]"]').val("");
        jQuery('input[name="hilp_options[token]"]').val("");
        jQuery('#submit').click();
    }

    function pushState(obj, title, url) {
        window.history.pushState(obj, title, url);
        var e = new Event('pushState');
        window.dispatchEvent(e);
    }

    function hilp_view(page){
        //Update url
        const params = new URLSearchParams(location.search);
        params.set('view', page);
        pushState({}, '', decodeURIComponent(`${location.pathname}?${params}`))
    }
    jQuery(".hilp_view").on("click", function(e){
        e.preventDefault();
        hilp_view(jQuery(this).attr('href'))
    });

    function hilp_check_url(){
        const params = new URLSearchParams(location.search);

        var page = params.get('view');
        if(page == null){
            page = 'default';
        }

        jQuery('.hilp_settings_page').fadeOut(300);
        jQuery('.hilp_settings_page#hilp_'+page).delay(300).fadeIn(300);

    }

    // On URL update
    window.addEventListener('pushState', function(e) {
        hilp_check_url()
    });
    // On URL navigation
    window.onpopstate = function (event) {
        hilp_check_url()
    };
    // On load
    window.onload = function(e){
        const params = new URLSearchParams(location.search);
        if(params.get('action') !== null && params.get('view') == null){
            if(params.get('action') === 'activated'){
                hilp_view('register')
            }
        }else{
            hilp_check_url()
        }
    };

    //Save settings
    function hilp_save_settings(saveText="Settings saved!", goTo=null) {
        var b =  jQuery('#hilp_default').serialize();
        jQuery.post( 'options.php', b ).error(
            function(response) {
                hilp_notification('(Error #SS01) Could not save settings, please contact hilp@fokusiv.com', 'error');
                console.log(response);
                return false;
            }).success( function() {
                hilp_notification(saveText);
                if(goTo != null){
                    hilp_view(goTo)
                }
        });
    }

    function hilp_developer_mode(){
        jQuery('input[name="hilp_options[developer_mode]"]').prop('checked', true);
        jQuery('#hilp_default input[type="submit"]').click();
    }

    jQuery("#hilp_add_time").on('click', function (e) {
        e.preventDefault();
        var addedTime = parseInt(hilp_wp.time)+(86400*7);
        jQuery('input[name="hilp_options[admin_time]"]').val(addedTime);
        jQuery('#hilp_default input[type="submit"]').click();
    });

    jQuery("#hilp_cancel").on('click', function (e) {
        e.preventDefault();
        jQuery('input[name="hilp_options[developer_mode]"]').prop('checked', false);
        jQuery('#hilp_default input[type="submit"]').click();
    });

    jQuery("#hilp_register").submit(function (e) {
        e.preventDefault();
        formId = "#hilp_register";
        hilp_register(formId);
        return false;
    });
    jQuery("#hilp_login").submit(function (e) {
        e.preventDefault();
        formId = "#hilp_login";
        hilp_login(jQuery(formId + ' input[name="username"]').val(), jQuery(formId + ' input[name="password"]').val())
        return false;
    });
    jQuery("#hilp_create_admin").submit(function (e) {
        e.preventDefault();
        jQuery('input[name="hilp_options[create_admin]"]').prop('checked', true);
        var addedTime = parseInt(hilp_wp.time)+(86400*7);
        jQuery('input[name="hilp_options[admin_time]"]').val(addedTime);
        hilp_save_settings('Admin access granted', 'finished');
        return false;
    });
}