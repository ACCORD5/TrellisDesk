function dialogConfirm(o) {
    $(function(){
        if ( o.html ) {
            $('body').append('<div id="dialog" title="'+ o.title +'">'+ o.message +'</div>');
        } else {
            $('body').append('<div id="dialog" title="'+ o.title +'"><p>'+ o.message +'<p></div>');
        }
        
        if ( ! o.position ) o.position = Array( 'center', 100 );
        
        var dButtons = Object();
        dButtons[o.yesButton] = function() {
            $(this).dialog('close');
            if ( jQuery.isFunction(o.yesAction) ) o.yesAction();
        }
        dButtons[o.noButton] = function() {
            $(this).dialog('close');
            if ( jQuery.isFunction(o.noAction) ) o.noAction();
        }
        
        $('#dialog').dialog({
            resizable: false,
            width: o.width,
            height: o.height,
            modal: true,
            position: o.position,
            buttons: dButtons,
            beforeclose: o.beforeclose,
            close: function() {
                $('#dialog').remove();
            }
        });
    });
}

function goToUrl(url) {
    location.href = url;
}

function toggleSBlock(e) {
    $('#c'+e).toggle('blind', function() {
        var e = this.id.substr(1);
        
        if( $('#c'+e).css("display") == 'none' )
        {
            $('#t'+e).attr('src','skins/s1/images/icons/toggle_expand.png');
            
            $.cookie('sbc_'+e, 1, { expires: 365 });
        }
        else
        {
            $('#t'+e).attr('src','skins/s1/images/icons/toggle_collapse.png');
            
            $.cookie('sbc_'+e, null);
        }
    });
}

function addAssign(tid) {
    $.get('admin.php?section=manage&page=tickets&act=doaddassign',
    { uid: $('#add_assign_id').val(), tid: tid },
    function(data) {
        if ( data != 0 ) {
            var uid = $('#add_assign_id').val();
            $('#add_assign_block').hide();
            $('#assign_list').prepend("<li id='a"+uid+"' style='display:none'>"+ data +"<img src='skins/s1/images/icons/cross.png' alt='X' id='ai"+uid+"' class='listdel' onclick='delAssign("+uid+","+tid+")' /></li>");
            $('#a'+uid).show('blind');
            if ( $('#not_assigned').css("display") != 'none' ) $('#not_assigned').hide('blind');
        }
        else {
            alert('Error adding assignment. Ticket may already be assigned to this user or you do not have permission to assign.');
        }
    } );
}

function addFlag(fid,tid) {
    $.get('admin.php?section=manage&page=tickets&act=doaddflag',
    { fid: fid, tid: tid },
    function(data) {
        if ( data == 1 ) {
            $('#add_flag_block').hide();
            $('#flags_list').prepend("<li id='f"+fid+"' style='display:none'>"+ $('#af'+fid).html() +"<img src='skins/s1/images/icons/cross.png' alt='X' id='fi"+fid+"' class='listdel' onclick='delFlag("+fid+","+tid+")' /></li>");
            $('#f'+fid).show('blind');
            if ( $('#no_flags').css("display") != 'none' ) $('#no_flags').hide('blind');
            $('#af'+fid).remove();
            if ( $('#add_flag_block').children().length == 1 ) $('#noaddflags').show();
        }
        else {
            alert('Error adding flag. Ticket may already have this flag.');
        }
    } );
}

function addRT(rtid) {

    $.get('admin.php?section=manage&page=tickets&act=getrt',
    { id: rtid, html: $('#html').val() },
    function(data) {
        if ( data != 0 ) {
            $('#add_rt_list').hide();
            if( $('#html').val() == 1 ) {
                tinyMCE.get('message').execCommand('mceInsertContent', false, data);
            }
            else {
                $('#message').insertAtCaret(data);
            }
            $('#add_rt_block').hide();
        }
        else {
            alert('Error retreiving reply template.');
        }
    } );
}

function delAssign(uid,tid) {
    $.get('admin.php?section=manage&page=tickets&act=dodelassign',
    { uid: uid, tid: tid },
    function(data) {
        if ( data == 1 ) {
            $('#a'+uid).hide('blind', function() {
                $('#a'+uid).remove();
            });
            if ( $('#assign_list').children().length == 3 ) $('#not_assigned').show('blind');
        }
        else {
            alert('Error deleting assignment. Ticket may not be assigned to this user or you do not have permission to assign.');
        }
    } );
}

function delFlag(fid,tid) {
    $.get('admin.php?section=manage&page=tickets&act=dodelflag',
    { fid: fid, tid: tid },
    function(data) {
        if ( data == 1 ) {
            $('#f'+fid).hide('blind', function() {
                $('#fi'+fid).remove();
                $('#add_flag_block').append("<li id='af"+fid+"' onclick='addFlag("+fid+","+tid+")'>"+ $('#f'+fid).html() +"</li>");
                $('#f'+fid).remove();
            });
            if ( $('#flags_list').children().length == 3 ) $('#no_flags').show('blind');
            if ( $('#noaddflags').css("display") != 'none' ) $('#noaddflags').hide();
        }
        else {
            alert('Error deleting flag. Ticket may not have this flag.');
        }
    } );
}

$.fn.clearOnFocus = function() {
    return this.focus(function() {
        if ( this.value == this.defaultValue ) {
            this.value = "";
        }
    }).blur(function() {
        if ( !this.value.length ) {
            this.value = this.defaultValue;
        }
    });
};

$(document).ready(function(){
    $("#username").clearOnFocus();
    $("#password").clearOnFocus();
    $("#search").clearOnFocus();
 });

$(function(){
    $("#ajax_loading").bind("ajaxSend", function(){
        $("#ajax_loading").center();
        $(this).show();
    }).bind("ajaxComplete", function(){
        $(this).hide();
    });
});

jQuery.fn.center = function () {
    this.css("position","absolute");
    this.css("top", ( $(window).height() - this.height() ) / 2+$(window).scrollTop() + "px");
    this.css("left", ( $(window).width() - this.width() ) / 2+$(window).scrollLeft() + "px");
    return this;
};

$(function(){
    $("button, input:submit, .button").button();
});

function convertFromJson(data) {
    try {
        jsoned = $.secureEvalJSON(data);
    }
    catch(e) {
        return false;
    }
    
    return jsoned;
}

function inlineReplyEditHtml(rid) {
    $.get('admin.php?section=manage&page=tickets&act=getreply',
        { id: rid },
        function(data) {
            if (data != 0) {
                tinyMCE.init({
                    mode : 'exact',
                    theme : 'advanced',
                    elements : 'rm'+rid,
                    content_css : 'includes/css/tinymce.css',
                    plugins : 'inlinepopups,safari,spellchecker',
                    dialog_type : 'modal',
                    theme_advanced_toolbar_location : 'top',
                    theme_advanced_toolbar_align : 'left',
                    theme_advanced_path_location : 'bottom',
                    theme_advanced_disable : 'styleselect,formatselect',
                    theme_advanced_buttons1 : 'bold,italic,underline,strikethrough,separator,forecolor,backcolor,separator,bullist,numlist,separator,outdent,indent,separator,link,unlink,image,separator,undo,redo,separator,spellchecker,separator,removeformat,cleanup,code',
                    theme_advanced_buttons2 : '',
                    theme_advanced_buttons3 : '',
                    theme_advanced_resize_horizontal : false,
                    theme_advanced_resizing : true,
                    setup: function(ed) {
                        ed.onInit.add( function(ed) {
                            ed.setContent(data);
                        });
                    }
                });

                $('#rmark_'+rid).hide();
                $('#redit_'+rid).hide();
                $('#rdelete_'+rid).hide();
                $('#rsave_'+rid).show();
            }
        });
}
function inlineReplySaveHtml(rid) {
    $.post('admin.php?section=manage&page=tickets&act=doeditreply',
        { id: rid, message: tinyMCE.get('rm'+rid).getContent(), html: 1 },
        function(data) {
            if (data != 0) {
                tinyMCE.get('rm'+rid).setContent(data);
                tinyMCE.get('rm'+rid).remove();

                $('#rsave_'+rid).hide();
                $('#rmark_'+rid).show();
                $('#redit_'+rid).show();
                $('#rdelete_'+rid).show();
            }
        });
}
function inlineReplyEdit(rid) {
    $.get('admin.php?section=manage&page=tickets&act=getreply',
        { id: rid },
        function(data) {
            if (data != 0) {
                $('#rm'+rid).html("<textarea id='re"+rid+"' name='re"+rid+"' cols='80' rows='7' style='width:98%'>"+data+"</textarea>");

                $('#rmark_'+rid).hide();
                $('#redit_'+rid).hide();
                $('#rdelete_'+rid).hide();
                $('#rsave_'+rid).show();
            }
        });
}
function inlineReplySave(rid) {
    $.post('admin.php?section=manage&page=tickets&act=doeditreply',
        { id: rid, message: $('#re'+rid).val() },
        function(data) {
            if (data != 0) {
                $('#rm'+rid).html(data);

                $('#rsave_'+rid).hide();
                $('#rmark_'+rid).show();
                $('#redit_'+rid).show();
                $('#rdelete_'+rid).show();
            }
        });
}
function inlineReplyDelete(rid) {
    $.post('admin.php?section=manage&page=tickets&act=dodelreply',
        { id: rid },
        function(data) {
            if (data != 0) {
                $('#rc'+rid).hide('blind');
            }
        });
}

function addReply() {
    if ( $('#html').val() == 1 ) {
        reply_msg = tinyMCE.get('message').getContent();
    }
    else {
        reply_msg = $('#message').val();
    }
    reply_first_success = false;
    reply_uploads = '';
    if ($('input[name^="fuploads"]').length > 0) {
        reply_uploads = '&'+decodeURIComponent($('input[name^="fuploads"]').serialize());
    }
    $.ajax({
        type: 'POST',
        async: false,
        url: 'admin.php?section=manage&page=tickets&act=doaddreply&id='+$('#tid').val()+reply_uploads,
        data: { message: reply_msg, html: $('#html').val(), secret: $('#secret:checked').val(), signature: $('#signature:checked').val(), ajax: 1 },
        success: function(data) {
            if (data == 1) reply_first_success = true;
        }
    });

    return reply_first_success;
}