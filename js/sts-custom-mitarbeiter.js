function tryParseJSON(jsonString) {
    try {
        var o = jQuery.parseJSON(jsonString);

        // Handle non-exception-throwing cases:
        // Neither JSON.parse(false) or JSON.parse(1234) throw errors, hence the type-checking,
        // but... JSON.parse(null) returns null, and typeof null === "object",
        // so we must check for that, too. Thankfully, null is falsey, so this suffices:
        if (o && typeof o === "object") {
            return o;
        }
    } catch (e) {
    }

    return false;
};


ajaxgetuserinfo = function () {
    $.ajax({
        type: "POST",
        url: 'index.php',
        data: {
            ajax: true,
            action: "ajaxGetUserInfo",
            page: "mitarbeiter",
            userid: $(".deputy_selector").val()
        }
    })
        .done(function (msg) {
            if (msg != 'false') {
                values = jQuery.parseJSON(msg);
                $('.useremail').val(values.email);
                $('.sts_username').html('<strong> Benutzername </strong> : ' + values.username);
                $('.notifications_radio').each(function (key, element) {
                    if (element.value == values.notifications)
                        $(element).prop('checked', true);
                });
                $('.priv_checkbox').prop('checked', false);

                $.each(values.privileges, function (key, value) {
                    if (value != 'null')
                        $("." + key).prop('checked', true);
                });


            }
        });

};

$(function () {

    $.widget("custom.combobox", {
        _create: function () {
            this.wrapper = $("<span>")
                .addClass("custom-combobox")
                .insertAfter(this.element);

            this.element.hide();
            this._createAutocomplete();
            this._createShowAllButton();
        },

        _createAutocomplete: function () {
            var selected = this.element.children(":selected"),
                value = selected.val() ? selected.text() : "";

            this.input = $("<input>")
                .appendTo(this.wrapper)
                .val(value)
                .attr("title", "")
                .addClass("custom-combobox-input ui-widget ui-widget-content ui-state-default ui-corner-left")
                .autocomplete({
                    delay: 0,
                    minLength: 0,
                    source: $.proxy(this, "_source")
                })
//	          .tooltip({
//	            tooltipClass: "ui-state-highlight"
//	          })
            ;

            this._on(this.input, {
                autocompleteselect: function (event, ui) {
                    ui.item.option.selected = true;
                    this._trigger("select", event, {
                        item: ui.item.option
                    });
                },

                autocompletechange: "_removeIfInvalid"
            });
        },

        _createShowAllButton: function () {
            var input = this.input,
                wasOpen = false;

            $("<a>")
                .attr("tabIndex", -1)
                .attr("title", "Show All Items")
                //	          .tooltip()
                .appendTo(this.wrapper)
                .button({
                    icons: {
                        primary: "ui-icon-triangle-1-s"
                    },
                    text: false
                })
                .removeClass("ui-corner-all")
                .addClass("custom-combobox-toggle ui-corner-right")
                .mousedown(function () {
                    wasOpen = input.autocomplete("widget").is(":visible");
                })
                .click(function () {
                    input.focus();

                    // Close if already visible
                    if (wasOpen) {
                        return;
                    }

                    // Pass empty string as value to search for, displaying all results
                    input.autocomplete("search", "");
                });
        },

        _source: function (request, response) {
            var matcher = new RegExp($.ui.autocomplete.escapeRegex(request.term), "i");
            response(this.element.children("option").map(function () {
                var text = $(this).text();
                if (this.value && (!request.term || matcher.test(text)))
                    return {
                        label: text,
                        value: text,
                        option: this
                    };
            }));
        },

        _removeIfInvalid: function (event, ui) {

            // Selected an item, nothing to do
            if (ui.item) {
                return;
            }

            // Search for a match (case-insensitive)
            var value = this.input.val(),
                valueLowerCase = value.toLowerCase(),
                valid = false;
            this.element.children("option").each(function () {
                if ($(this).text().toLowerCase() === valueLowerCase) {
                    this.selected = valid = true;
                    return false;
                }
            });

            // Found a match, nothing to do
            if (valid) {
                return;
            }

            // Remove invalid value
            this.input
                .val("")
                .attr("title", value + " didn't match any item");
            this.element.val("");
            this._delay(function () {
//	          this.input.tooltip( "close" ).attr( "title", "" );
            }, 2500);
            this.input.autocomplete("instance").term = "";
        },

        _destroy: function () {
            this.wrapper.remove();
            this.element.show();
        }
    });


    $("#fps_dep_add_edit_form").steps({
        headerTag: "h3",
        bodyTag: "fieldset",
        stepsOrientation: "vertical",
        enableAllSteps: false,
        transitionEffect: "fade",
        enableCancelButton: true,
        labels: {
            previous: 'Zurück',
            next: 'Weiter',
            finish: 'Beenden',
            cancel: 'Abbrechen'
        },
        onCanceled: function () {
            window.history.back();
        },
        onStepChanging: function (event, currentIndex, newIndex) {
            // Always allow going backward even if the current step contains invalid fields!
            if (currentIndex > newIndex) {
                return true;
            }

            var form = $(this);

            // Clean up if user went backward before
            if (currentIndex < newIndex) {
                // To remove error styles
                $(".body:eq(" + newIndex + ") label.error", form).remove();
                $(".body:eq(" + newIndex + ") .error", form).removeClass("error");
            }

            if ($('.wizard_user_username').val() == '') {
                string = $('.wizard_user_email').val().split('@');
                username = string[0];
                $.ajax({
                    type: "POST",
                    url: 'index.php',
                    data: {
                        ajax: true,
                        action: "ajaxSuggestUserName",
                        page: 'mitarbeiter',
                        depusername: username
                    }
                })
                    .done(function (msg) {
                        $('.wizard_user_username').val(msg)
                    });


            }
            // Disable validation on fields that are disabled or hidden.
            form.validate().settings.ignore = ":disabled,:hidden";

            // Start validation; Prevent going forward if false
            return form.valid();
        },
        onStepChanged: function (event, currentIndex, priorIndex) {

            if (currentIndex > priorIndex && $('#fps_dep_add_edit_form' + '-p-' + currentIndex).data('panelaction') == 'saveform') {
//                     					$('#fps_dep_add_edit_form'+'-p-'+currentIndex).append('<br><br>Benutzer Konto wird erst beim Klicken auf <strong>Benutzer Konto erstellen</strong> erstellt<br>');
                $('.wizard_button_finish').html('Benutzer Konto erstellen');
                $('.wizard_button_finish').addClass('submit_saveneu');
            }

        },
        onFinishing: function (event, currentIndex) {
            var form = $(this);

            // Disable validation on fields that are disabled.
            // At this point it's recommended to do an overall check (mean ignoring only disabled fields)
            form.validate().settings.ignore = ":disabled";

            // Start validation; Prevent form submission if false
            return form.valid();
        },
        onFinished: function (event, currentIndex) {
            var form = $(this);


            // Submit form input
//                            form.submit();
            if ($('#fps_dep_add_edit_form' + '-p-' + currentIndex).data('panelaction') != 'saveform') {
                window.location.href = 'index.php'
            }

        }
    }).validate({
//                        errorPlacement: function (error, element)
//                        {
//                            element.before(error);
//                        },
        rules: {
            email: {
                email: true
            },
            endoption: {
                required: true
            },
            depusername: {
                required: true,
                regex_user: /^[A-Za-z0-9\s._*]*$/,
                remote: {
                    url: "index.php",
                    type: "post",
                    data: {
                        page: 'mitarbeiter',
                        action: 'ajaxCheckUserName'
                    }
                }
            }
        },
        messages:
            {
                email: 'Bitte korrigieren Sie die Email Adresse',
                depusername:
                    {
                        required: 'Benutzername erforderlich',
                        remote: 'Benutzername bereits vorhanden',
                        regex_user: 'Benutzername enthält ungültige Zeichen'
                    },
                endoption: 'Bitte wählen Sie ein Option',
            }

    });

    $.validator.addMethod("regex_user", function (value, element, param) {
        return param.test(value);
    });

    $("#fps_dep_edit_form").steps({
        headerTag: "h3",
        bodyTag: "fieldset",
        stepsOrientation: "vertical",
        enableAllSteps: false,
        transitionEffect: "fade",
        enableCancelButton: true,
        labels: {
            previous: 'Zurück',
            next: 'Weiter',
            finish: 'Beenden',
            cancel: 'Abbrechen'
        },
        onCanceled: function () {
            window.history.back();
        },
        onStepChanging: function (event, currentIndex, newIndex) {
            // Always allow going backward even if the current step contains invalid fields!
            if (currentIndex > newIndex) {
                return true;
            }

            var form = $(this);

            // Clean up if user went backward before
            if (currentIndex < newIndex) {
                // To remove error styles
                $(".body:eq(" + newIndex + ") label.error", form).remove();
                $(".body:eq(" + newIndex + ") .error", form).removeClass("error");
            }

            // Disable validation on fields that are disabled or hidden.
            form.validate().settings.ignore = ":disabled,:hidden:not(.deputy_selector)";
            // Start validation; Prevent going forward if false
            return form.valid();
        },
        onStepChanged: function (event, currentIndex, priorIndex) {

            if ($(this).attr('id') == 'fps_dep_edit_form') {
                if ($('#fps_dep_edit_form' + '-p-' + currentIndex).data('panelaction') == 'saveform') {
//                    					$('#fps_dep_edit_form'+'-p-'+currentIndex).append('<br><br>Änderungen werden erst beim Klicken auf <strong>Änderungen speichern</strong> übernommen<br>');
                    $('.wizard_button_finish').html('Änderungen speichern');
                    $('.wizard_button_finish').addClass('submit_saveexist');
                }

            }

        },
        onFinishing: function (event, currentIndex) {
            var form = $(this);

            // Disable validation on fields that are disabled.
            // At this point it's recommended to do an overall check (mean ignoring only disabled fields)
            form.validate().settings.ignore = ":disabled";


            return form.valid();
        },
        onFinished: function (event, currentIndex) {
            var form = $(this);

            if ($('#fps_dep_edit_form' + '-p-' + currentIndex).data('panelaction') != 'saveform')
                window.location.href = 'index.php';
        }
    }).validate({
        errorPlacement: function (error, element) {
            element.before(error);
        },
        rules: {
            deputy: {
                required: true
            },
            passwdone: {
                required: true,
                passwdstrength: ''
//                          		regex: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@.,!%*?& ])[A-Za-z\d$@.,!%*?& ]{10,}/
            },
            passwdtwo: {
                equalTo: "#passwdone"
            },
            accountaction: {
                required: true
            },
            confirmsts: {
                required: true
            },
        },
        messages:
            {
                deputy: 'Bitte wählen Sie ein Benutzer Konto',
                accountaction: 'Bitte wählen Sie ein Option',
                confirmsts: 'Bitte bestätigen',
                passwdone: {
                    required: 'Bitte geben Sie das neue Passwort ein',
                    passwdstrength: 'Passwortstärke sehr schwach'
                },
                passwdtwo:
                    {
                        equalTo: "Die Passwörter stimmen nicht überein"
                    }
            }

    });
    $.validator.addMethod(
        "passwdstrength",
        function (value, element, param) {
            var strength = {
                0: "sehr schwach",
                1: "sehr schwach",
                2: "schwach",
                3: "gut",
                4: "sehr stark"
            }

            var password = document.getElementById('passwdone');
            var val = password.value;
            var username = $('#passwdone').data('username');
            split_usernames = username.split('.');
            blacklist = ["test", "street", "scooter", "streetscooter"];
            if (split_usernames.length)
                blacklist = blacklist.concat(split_usernames);
            blacklist = blacklist.concat([username]);
            var result = zxcvbn(val, blacklist);

            // Update the password strength meter
            $('#password-strength-meter').val(result.score);

            // Update the text indicator
            if (val !== "") {
                $('#password-strength-text').html("Passwortstärke : " + strength[result.score]);
            } else {
                $('#password-strength-text').html();
            }
            if (result.score > 2) return true;
            else return false;
        }
    );

    $.validator.addMethod(
        "regex",
        function (value, element, param) {
            if (this.optional(element)) {
                return true;
            }
            if (typeof regexp === "string") {
                param = new RegExp("^(?:" + param + ")$");
            }
            return param.test(value);
        },
        function (value, element, regexp) {
            var re = new RegExp("[A-Z]{1}");
            if (!re.test(element.value))
                return "Bitte verwenden Sie mindestens einen Großbuchstaben";
            re = new RegExp("[a-z]{1}");
            if (!re.test(element.value))
                return "Bitte verwenden Sie mindestens einen Kleinbuchstaben"
            re = new RegExp("[0-9]{1}");
            if (!re.test(element.value))
                return "Bitte verwenden Sie mindestens eine Zahl"
            re = new RegExp("[$@.,!%*?& ]{1}");
            if (!re.test(element.value))
                return "Bitte verwenden Sie mindestens ein Sonderzeichen $@.,!%*?& oder Leerzeichen"
            re = new RegExp("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@.,!%*?& ])[A-Za-z\d$@.,!%*?& ]{10,}/")
            if (!re.test(element.value))
                return "Bitte verwenden Sie mindestens 10 Zeichen."
        }
    );

    $('.accountaction').click(function () {
        thisfs = $(this).val();
        thisfs = thisfs.split('_');
        $('.' + thisfs[1] + '_fs').find('input').prop('disabled', false);
        $('.' + thisfs[1] + '_fs').show();
        $('.' + thisfs[1] + '_fs').siblings('fieldset').each(function (value) {
            $(this).find('input').prop('disabled', true);
        });
        $('.' + thisfs[1] + '_fs').siblings('fieldset').hide();

        if (thisfs[1] == 'deleteuser')
            $('.delete_user_confirm').val(1);
        else
            $('.delete_user_confirm').val(0);

        if (thisfs[1] == 'konto')
            $('#resetpass').val($('#temppasswd').val());
        else
            $('#resetpass').val('');


    });


    $('body').on('click', '.submit_saveneu', function (e) {

        $('.wizard_button_previous').remove();
        $('.wizard_button_cancel').remove();

        $('.submit_saveneu').hide();

        $('.submit_saveneu').after('<span class="saving">Wird gespeichert..</span>');

        $.ajax({
            type: "POST",
            url: 'index.php',
            data: $("#fps_dep_add_edit_form").serialize(), // serializes the form's elements.
            success: function (msg) {

                values = tryParseJSON(msg);
                if (values && typeof values === "object") {

                    errorstr = '';
                    if (!values.user) {
                        $("#fps_dep_add_edit_form").steps("add", {
                            title: "Fehler",
                            content: '<span class="error_msg" >Benuzter konnte nicht erstellet werden!</span>'
                        });

                    } else if (!values.email) {
                        password = $('#password_field').val();
                        passwordstr = '<br>Keine Emails wurden versandt. <br>Bitte teilen Sie dieses Passwort auf sicherem Weg z.B. in eine verschlüsselte E-Mail dem/-der Benutzer/-in mit.<br><br>' + $('.wizard_user_username').val() + '<h2>' + password + '</h2>';
                        $("#fps_dep_add_edit_form").steps("add", {
                            title: "Passwort",
                            content: passwordstr
                        });
                    } else {
                        $("#fps_dep_add_edit_form").steps("add", {
                            title: "Status",
                            content: 'Email erfolgreich verschickt.'
                        });
                    }

                } else {

                    password = $('#password_field').val();
                    passwordstr = '<br>Keine Emails wurden versandt. <br>Bitte teilen Sie dieses Passwort auf sicherem Weg z.B. in eine verschlüsselte E-Mail dem/-der Benutzer/-in mit.<br><br>' + $('.wizard_user_username').val() + '<h2>' + password + '</h2>';
                    $("#fps_dep_add_edit_form").steps("add", {
                        title: "Passwort",
                        content: passwordstr
                    });
                }
                $('.submit_saveneu').show(); // show response from the php script.
                $('.saving').remove();
                $("#fps_dep_add_edit_form").steps("next");
                $("#fps_dep_add_edit_form > .steps").find('li').removeClass('done');
                $("#fps_dep_add_edit_form > .steps").find('li').removeClass('first');
                $("#fps_dep_add_edit_form > .steps").find('li').addClass('disabled');
                $('.wizard_button_finish').html('Beenden');
                if ($('.wizard_button_finish').hasClass('submit_saveneu'))
                    $('.wizard_button_finish').removeClass('submit_saveneu');
            }
        });

        e.preventDefault();

    });

    $('body').on('click', '.submit_saveexist', function (e) {

        if (!$("#fps_dep_edit_form").valid())
            return false;

        $('.wizard_button_previous').remove();
        $('.wizard_button_cancel').remove();

        $('.submit_saveexist').hide();

        $('.submit_saveexist').after('<span class="saving">Wird gespeichert..</span>');

        $.ajax({
            type: "POST",
            url: 'index.php',
            data: $("#fps_dep_edit_form").serialize(), // serializes the form's elements.
            success: function (msg) {
                values = tryParseJSON(msg);
                if (values && typeof values === "object") {

                    $('.submit_saveexist').show(); // show response from the php script.
                    $('.saving').remove();
                    $("#fps_dep_edit_form").steps("next");
                    $("#fps_dep_edit_form > .steps").find('li').removeClass('done');
                    $("#fps_dep_edit_form > .steps").find('li').removeClass('first');
                    $("#fps_dep_edit_form > .steps").find('li').addClass('disabled');
                    $('.wizard_button_finish').html('Beenden');
                    if ($('.wizard_button_finish').hasClass('submit_saveexist'))
                        $('.wizard_button_finish').removeClass('submit_saveexist');
                } else {
                    //continue here
                }
                if (values.titlestr)
                    $("#fps_dep_edit_form").steps("add", {
                        title: values.titlestr,
                        content: values.contentstr
                    });

                $("#fps_dep_edit_form").steps('next');
            }
        });

        e.preventDefault();

    });

    $(".deputy_selector").combobox({select: ajaxgetuserinfo});
    $('.showtooltip').next('label').hover(function () {
        $(this).children('.tooltiptext').show();
    }, function () {
        $(this).children('.tooltiptext').hide();
    });


});



