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
   
    $('.tip').tipsy({gravity:'s',fade:true,html:true});
});


$('#search-keyword').click(function() {
    var search = $('#search-site');
    search.find('.btn-reset').show();
    search.find('.btn-submit').show();
    search.find('.iconsearch').hide();
});
$('#search-site .btn-reset').click(function() {
    $('#suggestion').hide();
});



$('#_menu, .navhome a.moremenu').click(function(){
    $('#_subnav, div.over').toggleClass('show');
    $('#_menu').toggleClass('actmenu');
    $('body').toggleClass('fixbody');
});
$('#hide_nav, div.over').click(function(){
    $('#_subnav, div.over').removeClass('show');
    $('#_menu').removeClass('actmenu');
    $('body').removeClass('fixbody');
});
$('#_infoother').click(function(){
    $('#_subother').toggleClass('show');
});
$('#navigation li>span .toggle_arrow').click(function(){
    $(this).parents('li').find('ul.sub_cat').toggleClass('hide');
});

