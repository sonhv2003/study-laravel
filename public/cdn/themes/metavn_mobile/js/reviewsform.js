function addReviewReplyclick(cmd_id,parent_id){
        var added = $("#reviews_wrapper #delrevewform");
        /* Xóa form nếu nó tồn tại */
        added.remove();
        /* Them moi */
        var reviews = {type: 0, parent_id: parent_id, id_value: goodsId, cmd_id : cmd_id};
        $.post(
            'ajax?act=reviews_form',
            {data: $.toJSON(reviews)},
            function(response){
                var res = $.evalJSON(response);
                $("#reviews"+parent_id).append(res.content);
            },
            'text'
        );
    return false;
}



function submitReviewReply(frm){
    var cmt = new Object();
    cmt.user_name       = frm.elements['re_reply_user_name'].value;
    cmt.email           = frm.elements['re_reply_user_email'].value;
    cmt.user_tel        = frm.elements['re_reply_user_tel'].value;
    cmt.content         = frm.elements['re_reply_content'].value;
    cmt.type            = frm.elements['re_reply_cmt_type'].value;
    cmt.id              = frm.elements['re_reply_id_value'].value;
    cmt.parent_id       =  frm.elements['re_reply_parent'].value;
    cmt.enabled_captcha = frm.elements['re_reply_enabled_captcha'] ? frm.elements['re_reply_enabled_captcha'].value : '0';
    cmt.captcha         = frm.elements['re_reply_captcha'] ? frm.elements['re_reply_captcha'].value : '';
    cmt.rank            = 0;
    cmt.mod_type  = 1;
    cmt.img = '';

    var validItem = $(frm.elements['re_reply_user_name'], frm.elements['re_reply_user_email'], frm.elements['re_reply_content'], frm.elements['re_reply_enabled_captcha']);
    validItem.tipsy({gravity: 's', fade: true, trigger: 'manual'}).valid8('').focusout(function(){validAndTip($(this));}).keyup(function(){validAndTip($(this));});
    if (cmt.email.length > 0) {
        if (!isValidEmail(cmt.email)) {
            $(frm.elements['re_reply_user_email']).attr('original-title', lang.error_email_format).tipsy('show');
            return false;
        }
    }
    /*else {
        $(frm.elements['re_reply_user_email']).attr('original-title', lang.error_email_required).tipsy('show');
        return false;
    }*/
    if (cmt.user_name.length < 5) {
        $(frm.elements['re_reply_user_name']).attr('original-title', 'Tên có độ dài chưa hợp lệ').tipsy('show');
        return false;
    }
    if (cmt.content.length == 0) {
        $(frm.elements['re_reply_content']).attr('original-title', lang.error_comment_content_required).tipsy('show');
        return false;
    }

    if (cmt.content.length > 500) {
        $(frm.elements['re_reply_content']).attr('original-title', lang.error_comment_maxlenght).tipsy('show');
        return false;
    }

    if ($(frm.elements['re_reply_captcha']).length > 0 && cmt.captcha.length == 0) {
        $(m.elements['re_reply_captcha']).attr('original-title', lang.error_captcha_required).tipsy('show');
        return false;
    }

    $.post(
        'ajax?act=reviews&method=post',
        {cmt: $.toJSON(cmt)},
        function(response){
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