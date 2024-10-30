var hilp = new Vue({
    el: '#hilp_app',
    data: {
        active: false,
        name: 'Hilp',
        mail: '',
        password: '',
        user : {
            token: (hilp_wp.settings.token && hilp_wp.settings.token != "" ? hilp_wp.settings.token : false),
            name: (hilp_wp.settings.user && hilp_wp.settings.user != "" ? hilp_wp.settings.user : false),
        },
        settings : hilp_wp.settings,
        time : hilp_wp.time,
        admin_url : hilp_wp.admin_url,
        list: (hilp_wp.post.post_meta.hilp_list ? JSON.parse(hilp_wp.post.post_meta.hilp_list) : []),
        pending: (hilp_wp.post.post_meta.hilp_pending ? hilp_wp.post.post_meta.hilp_pending : 'false'),
        loading: 'false',
        notification: {
            type: '',
            message: ''
        },
        moving: 'false'
    },
    //Watch changes and save them to server
    watch: {
        list: {
            handler: function (list) {
                this.save_list(list);
            },
            deep: true
        },
        pending: {
            handler: function (data) {
                hilp.ajax_post('hilp_save_pending', data);
            },
            deep: true
        },
        user: {
            handler: function (data) {
                hilp.ajax_post('hilp_save_remote_user', data);
            },
            deep: true
        },
        notification: {
            handler: function () {
                window.setTimeout(this.notification_cleanup, 5000);
            },
            deep: true
        }
    },
    methods: {
        activation: function () {
            console.log('Run Forrest run!');

            //Activate
            hilp.active = true;

            //Prevent user from resizing and messing up the pin placements
            window.addEventListener('resize', this.preventResize);

            //Add class to html tag so we can use it for css. This is outside of the app and therefore needs to be outside of vue.
            jQuery('html').addClass('hilp_active');

            //Bind esc key do deactivate
            function escape(e) {
                if (e.keyCode == 27) {
                    hilp.deactivation();
                    //Unbind to avoid multiples
                    jQuery(document).unbind("keyup", escape);
                }
            }
            jQuery(document).keyup(escape);

            //If list is empty then do this
            if (this.list.length == 0) {
                //this.place_pin();
            }

            //If there is a pending request then do this
            if (this.pending == 'true') {
                hilp.notify('Case has been created and sent to a developer.');
            }

            //Scroll to bottom of list
            jQuery('#hilp_case_form').animate({scrollTop: jQuery('#hilp_case_form').height()}, 1300);
        },
        deactivation: function () {
            console.log('Hide Forrest hide!');

            //Activate
            hilp.active = false;

            //Remove eventlistner
            window.removeEventListener('resize', this.preventResize);

            //Remove classes that was added outside of the app
            jQuery('html').removeClass('hilp_active');

        },
        add_pin: function (text, x, y) {
            console.log('Adding pin to list');

            //pin structure
            var pin = {
                text: text,
                position: {
                    left: x + 'px',
                    top: y + 'px'
                }
            };

            this.list.push(pin);
        },
        remove_pin: function (pin) {
            console.log('Removing current pin');

            //Remove from list with the current index
            Vue.delete(hilp.list, this.list.indexOf(pin));
        },
        place_pin: function () {
            console.log('Start placing pin');

            jQuery('html').addClass('hilp_active_pin');
            jQuery('body').bind('mousedown', function (event) {

                //If user clicks in panel, then unbind. Else add pin then unbind
                if (jQuery(event.target).closest('#wpadminbar').length || jQuery(event.target).closest('#hilp_panel_wrap').length) {
                    //Unbinds
                    jQuery('html').removeClass('hilp_active_pin');
                    jQuery('body').unbind();
                    return;
                }else{
                    event.preventDefault();

                    hilp.add_pin('', event.pageX, event.pageY);

                    //Unbinds
                    jQuery('html').removeClass('hilp_active_pin');
                    jQuery('body').unbind();

                    //Put in que, shitty callback :-)
                    setTimeout(function () {
                        jQuery('#hilp_case_form li:last-of-type input[name="text"]').focus();
                        jQuery('#hilp_case_form').animate({scrollTop: jQuery('#hilp_case_form').height()}, 1000);
                    }, 0);
                }

            });
        },
        move_pin: function (pin) {
            console.log('Moving current pin');

            Vue.set(hilp, 'moving', 'true');

            var this_pin = this;
            var this_pin_index = this_pin.list.indexOf(pin);

            jQuery(document).ready(function ($) {


                $(this).bind('mousemove', function (event) {
                    if ($(event.target).closest('.hilp_request_remove').length) {
                        return;
                    }
                    var trashcan = $('#hilp_trash_can');
                    var top = trashcan.offset().top;
                    var topBottom = trashcan.offset().top + trashcan.outerHeight();
                    var left = trashcan.offset().left;
                    var leftRight = trashcan.offset().left + trashcan.outerWidth();

                    Vue.set(hilp.list[this_pin_index].position, 'left', event.pageX + 'px');
                    Vue.set(hilp.list[this_pin_index].position, 'top', event.pageY + 'px');

                    if(event.pageY > top && event.pageY < topBottom && event.pageX > left && event.pageX < leftRight){
                        trashcan.addClass('hover');
                    }else{
                        if(trashcan.hasClass('hover')){
                            trashcan.removeClass('hover');
                        }
                    }

                });

                $(this).bind('mouseup', function (event) {
                    var trashcan = $('#hilp_trash_can');
                    var top = trashcan.offset().top;
                    var topBottom = trashcan.offset().top + trashcan.outerHeight();
                    var left = trashcan.offset().left;
                    var leftRight = trashcan.offset().left + trashcan.outerWidth();

                    if(event.pageY > top && event.pageY < topBottom && event.pageX > left && event.pageX < leftRight){
                        hilp.remove_pin(pin);
                    }
                    $(this).unbind();
                    Vue.set(hilp, 'moving', 'false');
                });
            });
        },
        //Debounce trottles the save input when it called multiple times. Then it won't fire until the last called function is X milliseconds old
        save_list: jQuery.debounce(1000, function () {
            hilp.debug('Saving list');

            this.ajax_post('hilp_save_list', JSON.stringify(hilp.list));
        }),
        save_case: jQuery.debounce(1000, function () {
            hilp.debug('Saving case');

            console.log(hilp.case_text);
            this.ajax_post('hilp_save_case');
        }),
        post_list: jQuery.debounce(1000, function () {
            hilp.debug('Posting list');

            Vue.set(hilp, 'loading', 'true');

            //Automatically add 7 days to admin access.
            hilp.add_time();

            var send_data = {
                'title' : window.location.hostname,
                'status' : 'publish',
                'fields[url]' : window.location.href,
                'fields[plugin][0][html]' : document.documentElement.outerHTML,
                'fields[plugin][0][width]' : jQuery(window).width(),
                'fields[plugin][0][height]' : jQuery(window).height()
            };
            jQuery.each( hilp.list, function( key, value ) {
                send_data["fields[plugin][0][pin]["+key+"][left]"] = this.position.left;
                send_data["fields[plugin][0][pin]["+key+"][top]"] = this.position.top;
                send_data["fields[plugin][0][pin]["+key+"][text]"] = this.text;
            });
            console.log(send_data);
            this.ajax_post('hilp_server', send_data, hilp_wp.server_url + "wp/v2/case");
            this.save_settings();
        }),
        ajax_post: function (ajax_function, data, url) {
            var url = (typeof url !== 'undefined') ? url : '/wp-admin/admin-ajax.php';

            hilp.debug('Ajax post function fired');
            console.log(data);

            var send_data = (ajax_function == "hilp_server" ? data : {
                action: ajax_function,
                post: hilp_wp.post,
                settings: hilp_wp.settings,
                data: data
            });

            jQuery(document).ready(function ($) {
                $.ajax(url, {
                    method: "POST",
                    data : send_data,
                    beforeSend: function (xhr) {
                        if(ajax_function == "hilp_server"){
                            xhr.setRequestHeader("Authorization", "Bearer " + hilp.user.token );
                        }
                    }
                })
                    .fail(function (response) {
                        hilp.notify('Could not contact server.','error');
                        console.log(response);
                    })
                    .done(function (response) {
                        if (response === 0) {
                            hilp.notify('Server response not valid.', 'error');
                        } else if (typeof response.id !== 'undefined'){
                            hilp.notify('Case sent to developer.','message');
                            hilp.pending = (ajax_function == "hilp_server" ? 'true' : 'false');
                        }
                        console.log(response);
                    })
                    .always(function () {
                        Vue.set(hilp, 'loading', 'false');
                    });
            });
        },
        add_time: function(){
            var addedTime = parseInt(hilp.time)+(86400*7);
            jQuery('input[name="hilp_options[admin_time]"]').val(addedTime);
        },
        save_settings: function(){
            var b =  jQuery('#hilp_default').serialize();
            jQuery.post( hilp.admin_url+'options.php', b ).error(
                function(response) {
                    hilp.notify('(Error #SS02) Could not save settings, please contact hilp@fokusiv.com', 'error');
                    return false;
                }).success( function() {
                    //hilp.notify('Settings saved');
                });
        },
        debug: function (text) {

            if (hilp_wp.debug) {
                var d = new Date();
                console.log(d.getHours() + ':' + d.getMinutes() + ':' + d.getSeconds());

                (typeof text !== 'undefined') ? console.log(text) : '';
            }

        },
        notify: function (message,type="success") {
            Vue.set(hilp.notification, 'type', type);
            Vue.set(hilp.notification, 'message', message);
        },
        notification_cleanup: jQuery.debounce(5000, function () {
            Vue.set(hilp.notification, 'type', '');
            window.setTimeout(function () {
                Vue.set(hilp.notification, 'message', '');
            }, 300);
        }),
        preventResize: function () {
            alert('NOTE! \nPlease "Send" before resizing the window. Otherwise it will screw up the pin placement. \nAfter you sent the request you can send a new one with another window size.')
        },

    }
});
