$.ajaxSetup({
	cache: false
});
$(document).ready(function() {
	$('body').append('<div id="loading_box">' + lang.process_request + '</div>');
	$('#loading_box').ajaxStart(function(){
		var loadingbox = $(this);
		var left = -(loadingbox.outerWidth() / 2);
		loadingbox.css({'marginRight': left + 'px'});
		loadingbox.delay(3000).fadeIn(400);
	});
	$('#loading_box').ajaxSuccess(function(){
		$(this).stop().stop().fadeOut(400);
	});
});
var loader = '<div class="loader">&nbsp;</div>', result = '<div class="result"></div>', status = '<div class="status"></div>', page = 'undefined', action = 'undefined';
function clearHistory() {
	document.cookie = 'ECS[history]='+escape('');
	$('#history').animate({height:'0',opacity:'0'}, 1000).css({visibility:'hidden'});
	$('#history .tip').tipsy('hide');
}


function showMenuMobile(n) {
    var t = $(n).attr("data-name");
    $("nav").find("li.active").removeClass();
    $(n).parent().addClass("active");
    $("nav").find(".nav-r.active").removeClass("active");
    $("nav").find(".nav-r.nav-" + t).addClass("active")
}

function getRecommend(rec_type, cat_id) {
	$.post(
		'ajax?act=cat_rec',
		{rec_type: rec_type, cid: cat_id},
		function(response){
			var res = $.evalJSON(response), target = '';
			if (res.type == 1) {
				target = $('#show_best');
			} else if (res.type == 2) {
				target = $('#show_new');
			} else {
				target = $('#show_hot');
			}
			if (res.content == '') {
				res.content = '<li class="empty">' + lang.goods_empty + '</li>';
			}
			target.fadeTo(300, 0, function (){
                var target_content = $(this);
                target_content.html(res.content);
                target_content.fadeTo(300, 1);
            });
            target.parent().find('.loader').fadeTo(1000, 0);

		},
		'text'
	);
}
function callmenu(el, target, min, margin){
    var i = $(el).length;
    if(i> 0){
        var a = $('div'+el+' '+target);
        var b = a.find('li');
        if(b.length >= min){
            /* lay chieu rong moi cai */
            var total_with = 0;
            b.each(function() {
                total_with += $(this).outerWidth();
            });
            var w = total_with+(margin*i);
            //var h = i > 1 ? w/i : w;
            a.css('width', w);
        }
    }
}
function orderQuery() {
	var order_sn = order_input.val(), reg = /^[\.0-9]+/;
	if (order_sn.length < 10 || ! reg.test(order_sn)) {
		order_input.focus().tipsy('show');
		return;
	}
	else {
		var order_loader = $('#order_query .loader');
		order_loader.css({visibility:'visible'}).fadeTo(0, 1000);
		$.get(
			'thanh-vien?act=order_query&order_sn=s',
			'order_sn=s' + order_sn,
			function(response){
				var order_result = $('#order_query .result');
				var res = $.evalJSON(response);
				if (res.message != '') {
					order_result.css({display:'block',backgroundColor:'#ff5215'});
					order_result.html(res.message);
				}
				if (res.error == 0) {
					order_result.css({display:'block',backgroundColor:'#97cf4d'});
					order_result.html(res.content);
					//$('form', order_result).attr('name', function(){return this.name+'_new'});
					//$('form a', order_result).attr('href', function(){return this.href.replace(/\'\]\.submit\(\)\;/, '_new\'].submit();')});
					$('form a', order_result).click(function(){
						$(this).parents('form').submit();
						return false;
					});
				}
				order_result.animate({backgroundColor:'#fff'}, 1000);
				order_loader.fadeTo(1000, 0);
			},
			'text'
		);
	}
}

function submitVote()
{
	var type = $('#vote input[name="type"]').val(), vote_id = $('#vote input[name="id"]').val(), option = '';
	$('#vote input[name="option_id"]:checked').each(function() {
		var option_id = $(this).val();
		option += option_id + ',';
	});

	if (option == '') {
		$('#vote form').tipsy('show');
		return;
	} else {
		var vote_result = $('#vote .result');
		$('#vote .loader').css({visibility:'visible'}).fadeTo(0, 1000);
		$.post(
			'vote.php',
			{vote: vote_id, options: option, type: type},
			function(response){
				var res = $.evalJSON(response);
				if (res.message != '') {
					vote_result.css({display:'block',backgroundColor:'#ff5215'});
					vote_result.html(res.message);
				}
				if (res.error == 0) {
					$('#vote_inner').html(res.content);
				}
				vote_result.animate({backgroundColor:'#fff'}, 1000);
				$('#vote .loader').fadeTo(1000, 0);
			},
			'text'
		);
	}
}


function addEmailList() {
	var email = subscription_email.val();
	if (!isValidEmail(email)) {
		subscription_email.focus().tipsy('show');
		return;
	}
	else {
		$('#subscription .loader').css({visibility:'visible'}).fadeTo(0, 1000);
		$.get(
			'thanh-vien?act=email_list&job=add',
			'email=' + email,
			function(response){
                cAlert(response);
				/*$('#subscription .result').css({display:'block',backgroundColor:'#97cf4d'}).html(response).animate({backgroundColor:'#fff'}, 1000);*/
				$('#subscription .loader').fadeTo(1000, 0);
			},
			'text'
		);
	}
}
function cancelEmailList()
{
	var email = subscription_email.val();
	if (!isValidEmail(email)) {
		subscription_email.focus().tipsy('show');
		return;
	}
	else {
		var subscription_result = $('#subscription .result');
		var subscription_loader = $('#subscription .loader');
		subscription_loader.css({visibility:'visible'}).fadeTo(0, 1000);
		$.get(
			'thanh-vien?act=email_list&job=del',
			'email=' + email,
			function(response){
                cAlert(response);
				/*subscription_result.css({display:'block',backgroundColor:'#97cf4d'}).html(response).animate({backgroundColor:'#fff'}, 1000);*/
				subscription_loader.fadeTo(1000, 0);
			},
			'text'
		);
	}
}
function isValidEmail(email) {
	var filter = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
	return filter.test(email);
}

function getAttrSiy(area) {
	var attrList = new Array();
	area.find('input[name^="spec_"]:checked, select[name^="spec_"]').each(function(i) {
		attrList[i] = $(this).val();
	});
	return attrList;
}

(function($) {
$.fn.ChangePriceSiy = function() {
	var area = $(this);
	loadPrice();
	area.find('input[name^="spec_"], select[name^="spec_"]').change(function() {
		loadPrice();
	});
	area.find('input[name="number"]').keyup(function() {
		var number = area.find('input[name="number"]').val();
		if (number.length > 0) {
			loadPrice();
		}
	});
	function loadPrice() {
		var attr = getAttrSiy(area);
		var number = area.find('input[name="number"]').val();
		if (number < 1) {
			var qty = '1';
		}
		else {
			var qty = number;
		};
		$.get(
			'ajax',
			'act=price&id=' + goodsId + '&attr=' + attr + '&number=' + qty,
			function(response){
				var res = $.evalJSON(response);
				if (res.err_msg.length > 0) {
					$.fn.colorbox({html:'<div class="message_box">' + res.err_msg + '<p class="action"><a href="javascript:void(0);" class="button brighter_button" onclick="$.fn.colorbox.close(); return false;"><span>' + lang.confirm + '</span></a></p></div>'});
				}
				else {
					area.find('[name="number"]').val(res.qty);
					$('.amount').html(res.result);
				}
			},
			'text'
		);
	};
};
})(jQuery);


function buyInstallment(id) {
    var goods = new Object();
    var spec_arr = new Array();
    var form = $('#purchase_form');

    goods.quick    = 1;
    goods.spec     = spec_arr;
    goods.goods_id = id;
    goods.number   = 1;
    goods.parent   =  0;
    goods.installment = 1;

    $.post(
        'gio-hang?step=add_to_cart',
        {goods: $.toJSON(goods)},
        function(response){
            var res = $.evalJSON(response);
            if (res.error > 0) {
                if (res.error == 2) {
                    $.fn.colorbox({html:'<div class="message_box mb_question">' + res.message + '<p class="action"><a href="thanh-vien?act=add_booking&id=' + res.goods_id + '&spec=' + res.product_spec + '" class="button brighter_button"><span>' + lang.booking + '</span></a><a href="javascript:void(0);" class="tool_link" onclick="$.fn.colorbox.close(); return false;">' + lang.continue_browsing_products + '</a></p></div>'});
                }
                else if (res.error == 6) {
                    openSpeSiy(res.message, res.goods_id, number, res.parent);
                }
                else {
                    $.fn.colorbox({html:'<div class="message_box mb_info">' + res.message + '<p class="action"><a href="javascript:void(0);" class="button brighter_button" onclick="$.fn.colorbox.close(); return false;"><span>' + lang.confirm + '</span></a></p></div>'});
                }
            }
            else {
                loadCart();
                location.href = 'tra-gop/'+res.link_installment;
            }
        },
        'text'
    );
}


function buy(id, num, parent) {
	var goods = new Object();
	var spec_arr = new Array();
	var fittings_arr = new Array();
	var number = 1;
	var form = $('#purchase_form');
	var quick = 0;

	if (form.length > 0) {
		spec_arr = getAttrSiy(form);
		var numberInput = form.find('input[name="number"]');
		if (numberInput) {
			number = numberInput.val();
		}
		quick = 1;
	}
	if (num > 0) {
		number = num;
	}

	/* form điền bởi client */
	goods.cform    = new Array();
	if($('#purchase_form .customer_form').length > 0){
		var cform = new Array();
		var msg = '';
		form.find('.customer_form .form_im').each(function(i) {
			var value = $(this).val();
			var name = $(this).data('name');
			if (value) {
				var arr = [];
				arr['id'] = $(this).data('id');
				arr['name']  = name;
				arr['value']  = value;
		        cform.push(name+': '+value);
		    }
		    else{
		    	msg = msg.concat(" - "+name);
		    }
		});
		if (msg.length == 0) {
		    goods.cform  = cform;
		}else{
			$.fn.colorbox({html:'<div class="message_box mb_info">Vui lòng nhập ' + msg + '<p class="action"><a href="javascript:void(0);" class="button brighter_button" onclick="$.fn.colorbox.close(); return false;"><span>' + lang.confirm + '</span></a></p></div>'});
			return false;
		}
		
	}
	/* End form điền bởi client */

	goods.quick    = quick;
	goods.spec     = spec_arr;
	goods.goods_id = id;
	goods.number   = number;
	goods.parent   = (typeof(parent) == 'undefined') ? 0 : parseInt(parent);

	$.post(
		'gio-hang?step=add_to_cart',
		{goods: $.toJSON(goods)},
		function(response){
			var res = $.evalJSON(response);
			
			/* reset form điền bởi client */
			if($('#purchase_form .customer_form').length > 0){
				form.find('.customer_form .form_im').each(function(i) {
					$(this).val('');
				});
			}


			if (res.error > 0) {
				if (res.error == 2) {
					$.fn.colorbox({html:'<div class="message_box mb_question">' + res.message + '<p class="action"><a href="thanh-vien?act=add_booking&id=' + res.goods_id + '&spec=' + res.product_spec + '" class="button brighter_button"><span>' + lang.booking + '</span></a><a href="javascript:void(0);" class="tool_link" onclick="$.fn.colorbox.close(); return false;">' + lang.continue_browsing_products + '</a></p></div>'});
				}
				else if (res.error == 6) {
					openSpeSiy(res.message, res.goods_id, number, res.parent);
				}
				else {
					$.fn.colorbox({html:'<div class="message_box mb_info">' + res.message + '<p class="action"><a href="javascript:void(0);" class="button brighter_button" onclick="$.fn.colorbox.close(); return false;"><span>' + lang.confirm + '</span></a></p></div>'});
				}
			}
			else {
				//$('#cart').html(res.content);
				loadCart();
				if (res.one_step_buy == '1') {
					location.href = 'gio-hang?step=add_to_cart';
				}
				else {
					if ($('#page_flow').length > 0) {
						location.href = 'gio-hang?step=cart';
					} else {
						$.fn.colorbox({html:'<div class="message_box mb_info">' + lang.add_to_cart_success + '<p class="action"><a href="gio-hang?step=cart" class="button brighter_button"><span>' + lang.checkout_now + '</span></a><a href="javascript:void(0);" class="tool_link" onclick="$.fn.colorbox.close(); return false;">' + lang.continue_browsing_products + '</a></p></div>'});
					}
				}
			}
		},
		'text'
	);
}

function openSpeSiy(message, goods_id, num, parent)
{
	var html = '<div class="message_box" id="properties_box"><div class="properties_wrapper">';
	for (var spec = 0; spec < message.length; spec++) {
		var tips = '';
		if (message[spec]['attr_type'] == 2) {
			var tips = 'title="' + lang.multi_choice + '"';
		};
		html += '<dl class="properties clearfix" ' + tips + '><dt>' +  message[spec]['name'] + '：</dt>';
		if (message[spec]['attr_type'] == 1) {
			html += '<dd class="radio">';
			for (var val_arr = 0; val_arr < message[spec]['values'].length; val_arr++) {
				var check = '';
				var title = '';
				if (val_arr == 0) {
					var check = 'checked="checked"';
				}
				if (message[spec]["values"][val_arr]["price"] != '') {
					var title = 'title="' + lang.add + message[spec]["values"][val_arr]["format_price"] + '"';
				}
				html += '<label for="spec_value_'+ message[spec]["values"][val_arr]["id"] +'" ' + title + '><input type="radio" name="spec_' + message[spec]["attr_id"] + '" value="' + message[spec]["values"][val_arr]["id"] + '" id="spec_value_' + message[spec]["values"][val_arr]["id"] + '" ' + check + ' />' + message[spec]["values"][val_arr]["label"] + '</label>';
			}
			html += '<input type="hidden" name="spec_list" value="' + val_arr + '" /></dd>';
		}
		else {
			html += '<dd class="checkbox">';
			for (var val_arr = 0; val_arr < message[spec]["values"].length; val_arr++) {
				var title = '';
				if (message[spec]["values"][val_arr]["price"] != '') {
					var title = 'title="' + lang.add + message[spec]["values"][val_arr]["format_price"] + '"';
				}
				html += '<label for="spec_value_' + message[spec]["values"][val_arr]["id"] + '" ' + title + '><input type="checkbox" name="spec_' + message[spec]["attr_id"] + '" value="' + message[spec]["values"][val_arr]["id"] + '" id="spec_value_' + message[spec]["values"][val_arr]["id"] + '" />' + message[spec]["values"][val_arr]["label"] + '</label>';
			}
			html += '<input type="hidden" name="spec_list" value="' + val_arr + '" /></dd>';
		}
		html += "</dl>";
	}
	html += '</div><p class="action"><a href="javascript:submitSpeSiy(' + goods_id + ',' + num + ',' + parent + ')" class="buy button brighter_button"><span>' + lang.buy + '</span></a><a href="javascript:void(0);" class="tool_link" onclick="$.fn.colorbox.close(); return false;">' + lang.cancel + '</a></p></div>';

	$.fn.colorbox({scrolling:false,html: html,title: lang.select_spe});
	$('.properties').Formiy();
	$('.properties dl').tipsy({gravity: 'e',fade: true,html:true});
	$('.properties label').tipsy({gravity: 's',fade: true,html:true});
}

function submitSpeSiy(goods_id, num, parent)
{
	var goods = new Object();
	var spec_arr = new Array();
	var fittings_arr = new Array();
	var number = 1;
	var area = $('#properties_box');
	var quick = 1;

	if (num > 0) {
		number = num;
	}

	var spec_arr = getAttrSiy(area);

	goods.quick    = quick;
	goods.spec     = spec_arr;
	goods.goods_id = goods_id;
	goods.number   = number;
	goods.parent   = (typeof(parent) == "undefined") ? 0 : parseInt(parent);

	$.post(
		'gio-hang?step=add_to_cart',
		{goods: $.toJSON(goods)},
		function(response){
			var res = $.evalJSON(response);
			if (res.error > 0) {
				if (res.error == 2) {
					$.fn.colorbox({html:'<div class="message_box mb_question">' + res.message + '<p class="action"><a href="thanh-vien?act=add_booking&id=' + res.goods_id + '&spec=' + res.product_spec + '" class="button brighter_button"><span>' + lang.booking + '</span></a><a href="javascript:void(0);" class="tool_link" onclick="$.fn.colorbox.close(); return false;">' + lang.continue_browsing_products + '</a></p></div>'});
				}
				else if (res.error == 6) {
					openSpeSiy(res.message, res.goods_id, goods.number, res.parent);
				}
				else {
					$.fn.colorbox({html:'<div class="message_box mb_info">' + res.message + '<p class="action"><a href="javascript:void(0);" class="button brighter_button" onclick="$.fn.colorbox.close(); return false;"><span>' + lang.confirm + '</span></a></p></div>'});
				}
			}
			else {
				//$('#cart').html(res.content);
				loadCart();
				if (res.one_step_buy == '1') {
					location.href = 'gio-hang?step=add_to_cart';
				}
				else {
					if ($('#page_flow').length > 0) {
						location.href = 'gio-hang?step=cart';
						//window.location.reload();
					} else {
						$.fn.colorbox({html:'<div class="message_box mb_info">' + lang.add_to_cart_success + '<p class="action"><a href="gio-hang?step=cart" class="button brighter_button"><span>' + lang.checkout_now + '</span></a><a href="javascript:void(0);" class="tool_link" onclick="$.fn.colorbox.close(); return false;">' + lang.continue_browsing_products + '</a></p></div>'});
					};
				}
			}
		},
		'text'
	);
}

function collect(id)
{
	$.get(
		'thanh-vien?act=collect',
		'id=' + id,
		function(response){
			var res = $.evalJSON(response);
			$.fn.colorbox({html:'<div class="message_box mb_info">' + res.message + '<p class="action"><a href="javascript:void(0);" class="button brighter_button" onclick="$.fn.colorbox.close(); return false;"><span>' + lang.confirm + '</span></a></p></div>'});
		},
		'text'
	);
}


function addPackageToCart(id) {
	var package_info = new Object();
	var number       = 1;

	package_info.package_id = id
	package_info.number     = number;

	$.post(
		'gio-hang?step=add_package_to_cart',
		{package_info: $.toJSON(package_info)},
		function(response){
			var res = $.evalJSON(response);
			if (res.error > 0) {
				if (res.error == 2) {
					$.fn.colorbox({html:'<div class="message_box mb_question">' + res.message + '<p class="action"><a href="thanh-vien?act=add_booking&id=' + res.goods_id + '" class="button brighter_button"><span>' + lang.booking + '</span></a><a href="javascript:void(0);" class="tool_link" onclick="$.fn.colorbox.close(); return false;">' + lang.cancel + '</a></p></div>'});
				}
				else {
					$.fn.colorbox({html:'<div class="message_box mb_info">' + res.message + '<p class="action"><a href="javascript:void(0);" class="button brighter_button" onclick="$.fn.colorbox.close(); return false;"><span>' + lang.confirm + '</span></a></p></div>'});
				}
			}
			else {
				//$('#cart').html(res.content);
				loadCart();
				if (res.one_step_buy == '1') {
					location.href = 'gio-hang?step=add_to_cart';
				}
				else {
					$.fn.colorbox({html:'<div class="message_box mb_info">' + lang.add_to_cart_success + '<p class="action"><a href="gio-hang?step=cart" class="button brighter_button"><span>' + lang.checkout_now + '</span></a><a href="javascript:void(0);" class="tool_link" onclick="$.fn.colorbox.close(); return false;">' + lang.continue_browsing_products + '</a></p></div>'});
				}
			}
		},
		'text'
	);
}

function fittings_to_flow(goodsId,parentId)
{
	var goods        = new Object();
	var spec_arr     = new Array();
	var number       = 1;
	goods.spec     = spec_arr;
	goods.goods_id = goodsId;
	goods.number   = number;
	goods.parent   = parentId;

	$.post(
		'gio-hang?step=add_to_cart',
		{goods: $.toJSON(goods)},
		function(response){
			var res = $.evalJSON(response);
			if (res.error > 0) {
				if (res.error == 2) {
					$.fn.colorbox({html:'<div class="message_box mb_question">' + res.message + '<p class="action"><a href="thanh-vien?act=add_booking&id=' + res.goods_id + '&spec=' + res.product_spec + '" class="button brighter_button"><span>' + lang.booking + '</span></a><a href="javascript:void(0);" class="tool_link" onclick="$.fn.colorbox.close(); return false;">' + lang.continue_browsing_products + '</a></p></div>'});
				}
				else if (res.error == 6) {
					openSpeSiy(res.message, res.goods_id, goods.number, res.parent);
				}
				else {
					$.fn.colorbox({html:'<div class="message_box mb_info">' + res.message + '<p class="action"><a href="javascript:void(0);" class="button brighter_button" onclick="$.fn.colorbox.close(); return false;"><span>' + lang.confirm + '</span></a></p></div>'});
				}
			} else {
				location.href = 'gio-hang?step=cart';
			}
		},
		'text'
	);
}


function validAndTip(obj){if(obj.isValid()){obj.tipsy('hide');}else{obj.tipsy('show');}return false;}
function validAndTipNext(obj){if(obj.isValid()){obj.next().tipsy('hide');}else{obj.next().tipsy('show');}return false;}

function validLogin() {
	$('#username_login, #password_login').valid8('').tipsy({gravity: 'w', fade: true, trigger: 'manual'}).focusout(function(){validAndTip($(this));}).keyup(function(){validAndTip($(this));});
	$('#username_login').focus().attr('original-title', lang.error_username_required);
	$('#password_login').attr('original-title', lang.error_password_required);
	if ($('#captcha_login').length > 0) {
		$('#captcha_login').valid8('').next().tipsy({gravity: 'w', fade: true, trigger: 'manual'}).attr('original-title', lang.error_captcha_required);
		$('#captcha_login').focusout(function(){validAndTipNext($(this));}).keyup(function(){validAndTipNext($(this));});
	}
	$('#user_login').submit(function(){
		var unc = 0,pwc = 0,ccc = 0;
		validAndTip($('#username_login'));
		validAndTip($('#password_login'));
		if ($('#username_login').val() != '') {unc = 1};
		if ($('#password_login').val() != '') {pwc = 1};
		if ($('#captcha_login').length > 0) {
			validAndTipNext($('#captcha_login'));
			if ($('#captcha_login').val() != '') {ccc = 1};
			if(unc+pwc+ccc == 3){
				return true;
			} else {
				return false;
			}
		} else {
			if(unc+pwc+ccc == 2){
				return true;
			} else {
				return false;
			}
		};
	});
}

function openQuickLogin() {
	$.fn.colorbox({scrolling:false,href:'thanh-vien?act=login&ajax=1',onComplete:function(){
			$('.tipsy').remove();
			validQuickLogin();
		},onCleanup:function(){
			$('.tipsy').remove();
		}
	});
}

function validAndTip2(obj){if(obj.val() != ''){obj.tipsy('hide');}else{obj.tipsy('show');}return false;}
function validAndTipNext2(obj){if(obj.val() != ''){obj.next().tipsy('hide');}else{obj.next().tipsy('show');}return false;}

function validQuickLogin() {
	$('#user_login .tip').tipsy({gravity: 's',fade: true,html: true});
	$('#username_login, #password_login').tipsy({gravity: 'w', fade: true, trigger: 'manual'}).focusout(function(){validAndTip2($(this));}).keyup(function(){validAndTip2($(this));});//
	$('#username_login').focus().attr('original-title', lang.error_username_required);
	$('#password_login').attr('original-title', lang.error_password_required);
	if ($('#captcha_login').length > 0) {
		$('#captcha_login').next().tipsy({gravity: 'w', fade: true, trigger: 'manual'}).attr('original-title', lang.error_captcha_required);
		$('#captcha_login').focusout(function(){validAndTipNext2($(this));}).keyup(function(){validAndTipNext2($(this));});//
	}
	$('#user_login').submit(function(){
		var unc = 0,pwc = 0,ccc = 0;
		validAndTip2($('#username_login'));
		validAndTip2($('#password_login'));
		if ($('#username_login').val() != '') {unc = 1};
		if ($('#password_login').val() != '') {pwc = 1};
		if ($('#captcha_login').length > 0) {
			validAndTipNext2($('#captcha_login'));
			if ($('#captcha_login').val() != '') {ccc = 1};
			if(unc+pwc+ccc == 3){
				quickLogin();
				return false;
			} else {
				return false;
			}
		} else {
			if(unc+pwc+ccc == 2){
				quickLogin();
				return false;
			} else {
				return false;
			}
		};
	});
}


function quickLogin() {
	var userArea = $('#user_area');
	var username = $('#username_login').val();
	var password = $('#password_login').val();
	var remember = '';
	var captcha = '';
	if ($('#captcha_login').length > 0) {
		var captcha = $('#captcha_login').val();
	}
	if ($('#remember_login:checked').length > 0) {
		$.post(
			'thanh-vien?act=signin',
			{username: username, password: encodeURIComponent(password), captcha: captcha, remember: 1},
			function(response){
				var res = $.evalJSON(response);
				if (res.error > 0) {
					$.fn.colorbox({html:'<div class="message_box mb_info">' + res.content + '<p class="action"><a href="thanh-vien?act=login" class="button brighter_button quick_login"><span>' + lang.login_again + '</span></a><a href="javascript:void(0);" class="tool_link" onclick="$.fn.colorbox.close(); return false;">' + lang.login_cancel + '</a></p></div>'});
					if(res.html) {
						userArea.html(res.html);
					}
				} else {
					$.fn.colorbox({html:'<div class="message_box mb_info">' + lang.login_success + '<p class="action"><a href="javascript:void(0);" class="button brighter_button" onclick="$.fn.colorbox.close(); return false;"><span>' + lang.confirm + '</span></a><a href="thanh-vien" class="tool_link">' + lang.go_to_user_center + '</a></p></div>'});
					userArea.html(res.content);
				}
			},
			'text'
		);
	} else {
		$.post(
			'thanh-vien?act=signin',
			{username: username, password: encodeURIComponent(password), captcha: captcha},
			function(response){
				var res = $.evalJSON(response);
				if (res.error > 0) {
					$.fn.colorbox({html:'<div class="message_box mb_info">' + res.content + '<p class="action"><a href="thanh-vien?act=login" class="button brighter_button quick_login"><span>' + lang.login_again + '</span></a><a href="javascript:void(0);" class="tool_link" onclick="$.fn.colorbox.close(); return false;">' + lang.login_cancel + '</a></p></div>'});
					if(res.html) {
						userArea.html(res.html);
					}
				} else {
					$.fn.colorbox({html:'<div class="message_box mb_info">' + lang.login_success + '<p class="action"><a href="javascript:void(0);" class="button brighter_button" onclick="$.fn.colorbox.close(); return false;"><span>' + lang.confirm + '</span></a><a href="thanh-vien" class="tool_link">' + lang.go_to_user_center + '</a></p></div>'});
					userArea.html(res.content);
				}
			},
			'text'
		);
	}
}

function getImgRating() {
    if ($("#photo_review li").length > 0) {
        var n = "";
        $("#photo_review li").each(function() {
            var t = $(this).attr("data-name");
            t != null && t != "" && (n += t + ",")
        });
        $("#reviews_photos").val(n);
    }
}



function submitReviews(frm) {
    var cmt = new Object();
    cmt.user_name       = frm.elements['user_name'].value;
    cmt.email           = frm.elements['user_email'].value;
    cmt.user_tel        = frm.elements['user_tel'].value;
    cmt.content         = frm.elements['content'].value;
    cmt.type            = frm.elements['cmt_type'].value;
    cmt.id              = frm.elements['id'].value;
    cmt.enabled_captcha = frm.elements['enabled_captcha'] ? frm.elements['enabled_captcha'].value : '0';
    cmt.captcha         = frm.elements['captcha'] ? frm.elements['captcha'].value : '';
    cmt.rank            = 0;
    for (i = 0; i < frm.elements['comment_rank'].length; i++) {
        if (frm.elements['comment_rank'][i].checked) {
            cmt.rank = frm.elements['comment_rank'][i].value;
        }
    }
    cmt.mod_type  = 1;

    var validItem = $(frm.elements['user_name'], frm.elements['user_email'], frm.elements['content'], frm.elements['enabled_captcha']);
    validItem.tipsy({gravity: 's', fade: true, trigger: 'manual'}).valid8('').focusout(function(){validAndTip($(this));}).keyup(function(){validAndTip($(this));});
    if (cmt.email.length > 0) {
        if (!isValidEmail(cmt.email)) {
            $(frm.elements['email']).attr('original-title', lang.error_email_format).tipsy('show');
            return false;
        }
    }
   /* else {
        $(frm.elements['email']).attr('original-title', lang.error_email_required).tipsy('show');
        return false;
    }*/
    if (cmt.user_name.length < 5) {
        $(frm.elements['user_name']).attr('original-title', 'Tên chưa hợp lệ').tipsy('show');
        return false;
    }
    if (cmt.content.length == 0) {
        $(frm.elements['content']).attr('original-title', lang.error_comment_content_required).tipsy('show');
        return false;
    }

    if (cmt.content.length > 500) {
        $(frm.elements['content']).attr('original-title', lang.error_comment_maxlenght).tipsy('show');
        return false;
    }

    if ($(frm.elements['captcha']).length > 0 && cmt.captcha.length == 0) {
        $(m.elements['captcha']).attr('original-title', lang.error_captcha_required).tipsy('show');
        return false;
    }

    getImgRating();
    cmt.img = $('#reviews_photos').val();


    $.post(
        'ajax?act=reviews&method=post',
        {cmt: $.toJSON(cmt)},
        function(response){
            //console.log(response);
            var res = $.evalJSON(response);
            if (res.message) {
                $.fn.colorbox({html:'<div class="message_box mb_info">' + res.message + '<p class="action"><a href="javascript:void(0);" class="button brighter_button" onclick="$.fn.colorbox.close(); return false;"><span>' + lang.confirm + '</span></a></p></div>'});
            }
            if (res.error == 0) {
                $('#reviews_wrapper').html(res.content);
                $('.rank_star').rating({
                    focus: function(value, link){
                        var tip = $('#star_tip');
                        tip[0].data = tip[0].data || tip.html();
                        tip.html(link.title || 'value: '+value);
                    },
                    blur: function(value, link){
                        var tip = $('#star_tip');
                        $('#star_tip').html(tip[0].data || '');
                    }
                });
                $('.tip').tipsy({gravity: 's',fade: true,html: true});
            }
        },
        'text'
    );
    return false;
}



function getImgComment() {
    if ($("#images_review li").length > 0) {
        var n = "";
        $("#images_review li").each(function() {
            var t = $(this).attr("data-name");
            t != null && t != "" && (n += t + ",")
        });
        $("#comment_photos").val(n);
    }
}


function submitComment(frm) {
    var cmt = new Object();
    cmt.user_name       = frm.elements['user_name'].value;
    cmt.email           = frm.elements['user_email'].value;
    cmt.user_tel        = frm.elements['user_tel'].value;
    cmt.content         = frm.elements['content'].value;
    cmt.type            = frm.elements['cmt_type'].value;
    cmt.id              = frm.elements['id'].value;
    cmt.enabled_captcha = frm.elements['enabled_captcha'] ? frm.elements['enabled_captcha'].value : '0';
    cmt.captcha         = frm.elements['captcha'] ? frm.elements['captcha'].value : '';
    cmt.rank            = 0;
    for (i = 0; i < frm.elements['comment_rank'].length; i++) {
        if (frm.elements['comment_rank'][i].checked) {
            cmt.rank = frm.elements['comment_rank'][i].value;
        }
    }
    cmt.mod_type  = 0;
    cmt.token = frm.elements['cm_csrf_token'].value;
    cmt.parent_id  =  frm.elements['parent_id'].value;

    var validItem = $(frm.elements['user_name'], frm.elements['user_email'], frm.elements['content'], frm.elements['enabled_captcha']);
    validItem.tipsy({gravity: 's', fade: true, trigger: 'manual'}).valid8('').focusout(function(){validAndTip($(this));}).keyup(function(){validAndTip($(this));});
    if (cmt.email.length > 0) {
        if (!isValidEmail(cmt.email)) {
            $(frm.elements['email']).attr('original-title', lang.error_email_format).tipsy('show');
            return false;
        }
    }
    // else {
    //     $(frm.elements['email']).attr('original-title', lang.error_email_required).tipsy('show');
    //     return false;
    // }
    if (cmt.user_name.length < 6) {
        $(frm.elements['user_name']).attr('original-title', 'Tên chưa hợp lệ').tipsy('show');
        return false;
    }
    if (cmt.content.length == 0) {
        $(frm.elements['content']).attr('original-title', lang.error_comment_content_required).tipsy('show');
        return false;
    }

    if (cmt.content.length > 500) {
        $(frm.elements['content']).attr('original-title', lang.error_comment_maxlenght).tipsy('show');
        return false;
    }

    /*if ($(frm.elements['captcha']).length > 0 && cmt.captcha.length == 0) {
        $(m.elements['captcha']).attr('original-title', lang.error_captcha_required).tipsy('show');
        return false;
    }*/

    if($('#comment_photos').length > 0){
        getImgComment();
        cmt.img = $('#comment_photos').val();
    }

    $.post(
        'ajax?act=comment&method=post',
        {cmt: $.toJSON(cmt)},
        function(response){
            var res = $.evalJSON(response);
            if (res.message) {
                $.fn.colorbox({html:'<div class="message_box mb_info">' + res.message + '<p class="action"><a href="javascript:void(0);" class="button brighter_button" onclick="$.fn.colorbox.close(); return false;"><span>' + lang.confirm + '</span></a></p></div>'});
            }
            if (res.error == 0) {
                $('#comment_wrapper').html(res.content);
                // $('.rank_star').rating({
                //  focus: function(value, link){
                //      var tip = $('#star_tip');
                //      tip[0].data = tip[0].data || tip.html();
                //      tip.html(link.title || 'value: '+value);
                //  },
                //  blur: function(value, link){
                //      var tip = $('#star_tip');
                //      $('#star_tip').html(tip[0].data || '');
                //  }
                // });
                $('.tip').tipsy({gravity: 's',fade: true,html: true});
            }
        },
        'text'
    );
    return false;
}



/* 评论的翻页 */
function gotoReviewPage(page, id, type) {
    $.get(
        'ajax?act=reviews&method=get',
        'page=' + page + '&id=' + id + '&type=' + type,
        function(response){
            var res = $.evalJSON(response);
            $('#reviews_wrapper').html(res.content);
            $('.rank_star').rating({
                focus: function(value, link){
                    var tip = $('#star_tip');
                    tip[0].data = tip[0].data || tip.html();
                    tip.html(link.title || 'value: '+value);
                },
                blur: function(value, link){
                    var tip = $('#star_tip');
                    $('#star_tip').html(tip[0].data || '');
                }
            });
        },
        'text'
    );
}


/* 评论的翻页 */
function gotoPage(page, id, type) {
    $.get(
        'ajax?act=comment&method=get',
        'page=' + page + '&id=' + id + '&type=' + type,
        function(response){
            var res = $.evalJSON(response);
            $('#comment_wrapper').html(res.content);
            // $('.rank_star').rating({
            //  focus: function(value, link){
            //      var tip = $('#star_tip');
            //      tip[0].data = tip[0].data || tip.html();
            //      tip.html(link.title || 'value: '+value);
            //  },
            //  blur: function(value, link){
            //      var tip = $('#star_tip');
            //      $('#star_tip').html(tip[0].data || '');
            //  }
            // });
        },
        'text'
    );
}


/* 购买记录的翻页 */
function gotoBuyPage(page, id) {
	$.get(
		'ajax?act=gotopage',
		'page=' + page + '&id=' + id,
		function(response){
			var res = $.evalJSON(response);
			$('#bought_wrap').html(res.result);
		},
		'text'
	);
}

/* =user */
function sendHashMail() {
	$.get(
		'thanh-vien?act=send_hash_mail',
		'',
		function(response){
			var res = $.evalJSON(response);
			$.fn.colorbox({html:'<div class="message_box mb_info">' + res.message + '<p class="action"><a href="javascript:void(0);" class="button brighter_button" onclick="$.fn.colorbox.close(); return false;"><span>' + lang.confirm + '</span></a></p></div>'});
			$('#comment_wrapper').html(res.content);
		},
		'text'
	);
}


/* =snatch */
function bid()
{
	var form = $('#snatch_form');
	var id = form.find('input[name="snatch_id"]').val();
	var priceInput = form.find('input[name="price"]');
	var price = priceInput.val();
	priceInput.tipsy({gravity: 'w', fade: true, trigger: 'manual'}).focusout(function() {
		$(this).tipsy('hide');
	}).keypress(function() {
		$(this).tipsy('hide');
	});;
	if (price == '') {
		priceInput.attr('original-title', lang.error_price_required).tipsy('show');
		return;
	} else {
		var reg = /^[\.0-9]+/;
		if ( ! reg.test(price)) {
			priceInput.attr('original-title', lang.error_price).tipsy('show');
			return;
		} else {
			$.post(
				'snatch.php?act=bid',
				{id: id, price: price},
				function(response){
					var res = $.evalJSON(response);
					if (res.error == 0) {
						$('#snatch_wrapper').html(res.content);
					} else {
						$.fn.colorbox({html:'<div class="message_box mb_info">' + res.content + '<p class="action"><a href="javascript:void(0);" class="button brighter_button" onclick="$.fn.colorbox.close(); return false;"><span>' + lang.confirm + '</span></a></p></div>'});
					}
				},
				'text'
			);
		}
	}
}

function newPrice(id) {
	$.get(
		'snatch.php?act=new_price_list',
		'id=' + id,
		function(response){
			$('#price_list').html(response);
			$('#price_list').find('.bd').css({backgroundColor:'#ffc'}).animate({backgroundColor:'#fff'}, 1000);
		},
		'text'
	);
}

function regionChanged(obj, type, selName) {
	var parent = obj.options[obj.selectedIndex].value;
	loadRegions(parent, type, selName);
}
function loadRegions(parent, type, target) {
	var target = $('#'+target+'');
	target.after(loader).next('.loader').css({visibility:'visible'}).fadeTo(0, 1000);
	target.nextAll('select').css('display','none');
	$.get(
		'ajax?act=region',
		'type=' + type + '&target=' + target + "&parent=" + parent,
		function(response){
			var res = $.evalJSON(response);
			target.next('.loader').fadeTo(500, 0, function(){
				$(this).remove();
			});
			target.find('option[value!="0"]').remove();
			if (res.regions.length == 0) {
				target.css('display','none');
				target.nextAll('select').css('display','none');
			} else {
				target.css('display','');
				for (i = 0; i < res.regions.length; i ++ ) {
					target.append('<option value="' + res.regions[i].region_id + '">' + res.regions[i].region_name + '</option>');
				}
			};
		},
		'text'
	);
}

function loadCart(show) {
	$.post(
		'gio-hang?step=cart&ajax=1',
		'',
		function(response){
            /* $("header form.search").addClass('search-cart'); */
            console.log(response);
			$("#cart").html(response);
		},
		'text'
	);
}

function cartDrop(id) {
	$('#cart .loader').css({visibility:'visible'}).fadeTo(0, 1000);
	$.get(
		'gio-hang?step=drop_goods',
		'id=' + id,
		function(response){
			if ($('#page_flow').length > 0) {
				if (action == 'checkout') {
					location.href = 'gio-hang?step=checkout';
				} else {
					location.href = 'gio-hang?step=cart';
				}
			} else {
				loadCart(1);
			}
		},
		'text'
	);
}

function closeCart() {
	$('#cart .list_wrapper').hide();
}

function cAlert(content) {
	$.fn.colorbox({transition:'none',html:'<div class="message_box mb_info">' + content + '<p class="action"><a href="javascript:void(0);" class="button brighter_button" onclick="$.fn.colorbox.close(); return false;"><span>' + lang.confirm + '</span></a></p></div>'});
}

function submitTag() {
	var tag = $('#tag_form input[name="tag"]').val();
	var goods_id = $('#tag_form input[name="goods_id"]').val();
	if (tag.length > 0 && parseInt(goods_id) > 0) {
		$.post(
			'thanh-vien?act=add_tag',
			{id: goods_id, tag: tag},
			function(response){
				var res = $.evalJSON(response);
				if (res.error > 0) {
					cAlert(res.message);
				} else {
					var tags = res.content;
					var html = '';
					for (i = 0; i < tags.length; i++) {
						html += '<a href="tim-kiem?keywords='+tags[i].word+'" class="item">' +tags[i].word + '<em>' + tags[i].count + '</em></a>';
					}
					$('#tags').html(html);
				}
			},
			'text'
		);
	}
}

function quickView(id) {
	$.get(
		'goods_quick_view.php',
		'id=' + id,
		function(response){
			$.fn.colorbox({scrolling:false,html:response,onClosed:function(){
				if (goodsIdCurrent != 'undefined') {
					goodsId = goodsIdCurrent;
				}
			}});
			goodsId = id;
			if ($('#quick_view .properties').length) {
				$('#quick_view .properties').Formiy();
				$('#quick_view .properties dl').tipsy({gravity: 'e',fade: true,html:true});
				$('#quick_view .properties label').tipsy({gravity: 's',fade: true,html:true});
			}
			$('#purchase_form_qv').ChangePriceSiy();
			$('#quick_view .cloud_zoom, #quick_view .cloud_zoom_gallery').CloudZoom();
			var current = $('#goods_list [data-id='+id+']');
			var prev_id = parseInt(current.prevAll('[data-id]').first().data('id'));
			var next_id = parseInt(current.nextAll('[data-id]').first().data('id'));
			if (prev_id > 0) {
				$('#cboxPrevious').show().unbind('click').click(function(){
					quickView(prev_id);
				});
			} else {
				$('#cboxPrevious').hide().unbind('click').click(function(){
					return false;
				});
			}
			if (next_id > 0) {
				$('#cboxNext').show().unbind('click').click(function(){
					quickView(next_id);
				});
			} else {
				$('#cboxNext').hide().unbind('click').click(function(){
					return false;
				});
			}
		},
		'text'
	);
}


/**
 * Gửi đăng ký
 */
function sendcall(el){
    var cname = $(el+' .cname').val();
    var ctel = $(el+' .ctel').val();
    var cemail = '';
    if($(el+' .cemail').length > 0){
        cemail = $(el+' .cemail').val();
    }
    var sendcall = {cname: cname, ctel: ctel, url: window.location.href, cemail: cemail};
    $.post(
        'ajax?act=sendcall',
        {data: $.toJSON(sendcall)},
        function(response){
            var res = $.evalJSON(response);
            alert(res.content);
            if(res.error == 0){
                $(el+' .cname').val('');
                $(el+' .ctel').val('');
                if($(el+' .cemail').length > 0){
                    $(el+' .cemail').val('');
                }
            }
        },
        'text'
    );
}