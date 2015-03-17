(function(window) {
    var re = {
        not_string: /[^s]/,
        number: /[dief]/,
        text: /^[^\x25]+/,
        modulo: /^\x25{2}/,
        placeholder: /^\x25(?:([1-9]\d*)\$|\(([^\)]+)\))?(\+)?(0|'[^$])?(-)?(\d+)?(?:\.(\d+))?([b-fiosuxX])/,
        key: /^([a-z_][a-z_\d]*)/i,
        key_access: /^\.([a-z_][a-z_\d]*)/i,
        index_access: /^\[(\d+)\]/,
        sign: /^[\+\-]/
    }

    function sprintf() {
        var key = arguments[0], cache = sprintf.cache
        if (!(cache[key] && cache.hasOwnProperty(key))) {
            cache[key] = sprintf.parse(key)
        }
        return sprintf.format.call(null, cache[key], arguments)
    }

    sprintf.format = function(parse_tree, argv) {
        var cursor = 1, tree_length = parse_tree.length, node_type = "", arg, output = [], i, k, match, pad, pad_character, pad_length, is_positive = true, sign = ""
        for (i = 0; i < tree_length; i++) {
            node_type = get_type(parse_tree[i])
            if (node_type === "string") {
                output[output.length] = parse_tree[i]
            }
            else if (node_type === "array") {
                match = parse_tree[i] // convenience purposes only
                if (match[2]) { // keyword argument
                    arg = argv[cursor]
                    for (k = 0; k < match[2].length; k++) {
                        if (!arg.hasOwnProperty(match[2][k])) {
                            throw new Error(sprintf("[sprintf] property '%s' does not exist", match[2][k]))
                        }
                        arg = arg[match[2][k]]
                    }
                }
                else if (match[1]) { // positional argument (explicit)
                    arg = argv[match[1]]
                }
                else { // positional argument (implicit)
                    arg = argv[cursor++]
                }

                if (get_type(arg) == "function") {
                    arg = arg()
                }

                if (re.not_string.test(match[8]) && (get_type(arg) != "number" && isNaN(arg))) {
                    throw new TypeError(sprintf("[sprintf] expecting number but found %s", get_type(arg)))
                }

                if (re.number.test(match[8])) {
                    is_positive = arg >= 0
                }

                switch (match[8]) {
                    case "b":
                        arg = arg.toString(2)
                    break
                    case "c":
                        arg = String.fromCharCode(arg)
                    break
                    case "d":
                    case "i":
                        arg = parseInt(arg, 10)
                    break
                    case "e":
                        arg = match[7] ? arg.toExponential(match[7]) : arg.toExponential()
                    break
                    case "f":
                        arg = match[7] ? parseFloat(arg).toFixed(match[7]) : parseFloat(arg)
                    break
                    case "o":
                        arg = arg.toString(8)
                    break
                    case "s":
                        arg = ((arg = String(arg)) && match[7] ? arg.substring(0, match[7]) : arg)
                    break
                    case "u":
                        arg = arg >>> 0
                    break
                    case "x":
                        arg = arg.toString(16)
                    break
                    case "X":
                        arg = arg.toString(16).toUpperCase()
                    break
                }
                if (re.number.test(match[8]) && (!is_positive || match[3])) {
                    sign = is_positive ? "+" : "-"
                    arg = arg.toString().replace(re.sign, "")
                }
                else {
                    sign = ""
                }
                pad_character = match[4] ? match[4] === "0" ? "0" : match[4].charAt(1) : " "
                pad_length = match[6] - (sign + arg).length
                pad = match[6] ? (pad_length > 0 ? str_repeat(pad_character, pad_length) : "") : ""
                output[output.length] = match[5] ? sign + arg + pad : (pad_character === "0" ? sign + pad + arg : pad + sign + arg)
            }
        }
        return output.join("")
    }

    sprintf.cache = {}

    sprintf.parse = function(fmt) {
        var _fmt = fmt, match = [], parse_tree = [], arg_names = 0
        while (_fmt) {
            if ((match = re.text.exec(_fmt)) !== null) {
                parse_tree[parse_tree.length] = match[0]
            }
            else if ((match = re.modulo.exec(_fmt)) !== null) {
                parse_tree[parse_tree.length] = "%"
            }
            else if ((match = re.placeholder.exec(_fmt)) !== null) {
                if (match[2]) {
                    arg_names |= 1
                    var field_list = [], replacement_field = match[2], field_match = []
                    if ((field_match = re.key.exec(replacement_field)) !== null) {
                        field_list[field_list.length] = field_match[1]
                        while ((replacement_field = replacement_field.substring(field_match[0].length)) !== "") {
                            if ((field_match = re.key_access.exec(replacement_field)) !== null) {
                                field_list[field_list.length] = field_match[1]
                            }
                            else if ((field_match = re.index_access.exec(replacement_field)) !== null) {
                                field_list[field_list.length] = field_match[1]
                            }
                            else {
                                throw new SyntaxError("[sprintf] failed to parse named argument key")
                            }
                        }
                    }
                    else {
                        throw new SyntaxError("[sprintf] failed to parse named argument key")
                    }
                    match[2] = field_list
                }
                else {
                    arg_names |= 2
                }
                if (arg_names === 3) {
                    throw new Error("[sprintf] mixing positional and named placeholders is not (yet) supported")
                }
                parse_tree[parse_tree.length] = match
            }
            else {
                throw new SyntaxError("[sprintf] unexpected placeholder")
            }
            _fmt = _fmt.substring(match[0].length)
        }
        return parse_tree
    }

    var vsprintf = function(fmt, argv, _argv) {
        _argv = (argv || []).slice(0)
        _argv.splice(0, 0, fmt)
        return sprintf.apply(null, _argv)
    }

    /**
     * helpers
     */
    function get_type(variable) {
        return Object.prototype.toString.call(variable).slice(8, -1).toLowerCase()
    }

    function str_repeat(input, multiplier) {
        return Array(multiplier + 1).join(input)
    }

    /**
     * export to either browser or node.js
     */
    if (typeof exports !== "undefined") {
        exports.sprintf = sprintf
        exports.vsprintf = vsprintf
    }
    else {
        window.sprintf = sprintf
        window.vsprintf = vsprintf

        if (typeof define === "function" && define.amd) {
            define(function() {
                return {
                    sprintf: sprintf,
                    vsprintf: vsprintf
                }
            })
        }
    }
})(typeof window === "undefined" ? this : window);

jQuery(document).ready(function($) {

	// If pre-member is logged in
	if (php_vars.show_top_alert === '1') {
		$("#top-alerts").append(successAlert('<div class="alert-content">' + php_vars.i18n.TopAlert + '</div>', true));
	}

	var p = PUBNUB.init(php_vars.pubnub);
	var requestor_uuid = p.uuid();
	var code = false;

	// This event Fires when a new User has Joined.
	p.events.bind( 'presence-user-join', function(uuid) {
		$('#'+uuid +' .member-offline').hide();
		$('#'+uuid +' .member-online').show();
		$('#'+uuid + ' .action').show();
	} );
	// This event Fires when a new User has Left.
	p.events.bind( 'presence-user-leave', function(uuid) {
		$('#'+uuid).find('.member-offline').show();
		$('#'+uuid).find('.member-online').hide();

		// User leaved just a moment ago
		if ($('#'+uuid).find('.text-success').text()) {
			nickname = $('#' + uuid + ' .item-title').text();
			
			message = php_vars.i18n.UserLeaveAlphass.replace('%s', nickname);

			$('#alerts').append(dangerAlert(message , true));
		}
		$('#'+uuid).find('.action').hide();
	} );

	p.events.bind( 'presence-user-timeout', function(uuid) {
		$('#'+uuid).find('.member-offline').show();
		$('#'+uuid).find('.member-online').hide();

		// User leaved just a moment ago
		if ($('#'+uuid).find('.text-success').text()) {
			nickname = $('#' + uuid + ' .item-title').text();
		
			message = php_vars.i18n.UserLeaveAlphass.replace('%s', nickname);

			$('#alerts').append(dangerAlert(message , true));
		}
		$('#'+uuid).find('.action').hide();
	} );

	p.subscribe({
		channel: 'onlineUsers',
		callback: function(m) {
			console.log(m);
		},
		presence: function(details){
			var uuid = 'uuid' in details && (''+details.uuid).toLowerCase();

			if ('action' in details && uuid) p.events.fire(
				'presence-user-' + details.action, uuid
			);
		},
		error: function(error) {
			pubNubErrorAlert();
		}
	});

	$('.channel-logout').click(function(){
		p.unsubscribe({
			channel: 'onlineUsers' 
		});
	});

	$('#wp-admin-bar-logout a').click(function(){
		p.unsubscribe({
			p: 'onlineUsers' 
		});
	});

	p.subscribe({
		channel: requestor_uuid + '_invitation_codes',
		callback: function(m) {
			console.log(m);
			if (! code) {

				nickname = $('#' + m.uuid + ' .item-title').text();
				code     = m.invitation_code;

				$('#' + m.uuid + ' .text-success').text('Invitation Code: ' + m.invitation_code);

				// Pass invintation code to cookies
				$.cookie("invintation_code", m.invitation_code, { path: '/activate'});

				message = sprintf(php_vars.i18n.InvitationCodeGetAlert, nickname, m.invitation_code);
				$('#alerts').append(successAlert(message , true));
			}
		},
		error: function(error) {
			pubNubErrorAlert();
		}
	});

	$('.request-invitation').click(function(){
		// Detect username
		var nickname = $(this).parent().parent().parent().find('.item .item-title a').text();
		var uuid     = $(this).parent().parent().parent().attr('id');
		var el       = $(this);
		
		$('#alerts').append(successAlert(php_vars.i18n.RequestSent, true));

		$(this).hide();
		$(this).parent().append('<b class="text-success">' + php_vars.i18n.RequestSentShort + '</b>');

		uuid = $(this).parent().parent().parent().attr('id');

		p.publish({
			channel: uuid + '_invitation_request',
			message: {
				'requestor_uuid': requestor_uuid,
				'requestor_nickname': php_vars.nickname
			},
			callback: function(m) {
				console.log(m);
			},
			error: function(error) {
				pubNubErrorAlert();
			}
		});

		return false;
	});
});