$(document).on("pageshow", "div", function(event){
	$("div.ui-header a").removeClass("ui-btn-active");
	var url = $('.ui-page-active').data('url');
	$("div.ui-header").find("a[href='" + url + "']").addClass("ui-btn-active");
	
	$("li.expandable").nextUntil('li.ui-li-divider').hide();
	$("li.collapsible").nextUntil('li.ui-li-divider').hide();
});

// $(document).on('pageinit','[data-role=page]', function(){
    // $('[data-position=fixed]').fixedtoolbar({ tapToggle:false});
// });

// $(document).on('taphold', '[data-role=page]', function(){
    // $('[data-position=fixed]').fixedtoolbar('toggle');
// });

$(document).on("tap", ".scroll-top", function(event){
	$('html, body').animate({scrollTop: '0px'}, 500, function(){ $('body').clearQueue(); });
});

$(document).on("tap", ".postcontent img, .messagecontent img", function(event){
	window.location = $(this).attr("src");
});

$(document).on("tap", "li.expandable, li.collapsible", function(event){
	if ($(this).next().is(":visible")) {
		$(this).nextUntil('li.ui-li-divider').hide();		
		$(this).find(".ui-icon-minus").addClass("ui-icon-plus").removeClass("ui-icon-minus");
	} else {
		$(this).nextUntil('li.ui-li-divider').show();
		$(this).find(".ui-icon-plus").addClass("ui-icon-minus").removeClass("ui-icon-plus");
	}
});

$(window).scroll(function() {
	if($(window).scrollTop() + $(window).height() == $(document).height()) {
		var url = $('.ui-page-active').data('url');

		if (url == "/forum.php") {
			$('#load-priv-threads:enabled').trigger('click');
		} else if (url == "/pubforum.php") {
			$('#load-pub-threads:enabled').trigger('click');
		} else if (url == "/public/pubforum-public.php") {
			$('#load-pub-threads-public:enabled').trigger('click');
		} else if (url == "/messages.php") {
			$('#load-messages:enabled').trigger('click');
		} else if (url == "/messages-sent.php") {
			$('#load-messages-sent:enabled').trigger('click');
		}
	}
});

$(document).on("tap", "a.refresh", function(event){
	$.mobile.showPageLoadingMsg();
	window.location.reload();
});

$(document).on("click", "#load-priv-threads", function(event){
	$.mobile.showPageLoadingMsg();
	$('#load-priv-threads').button('disable');
	var start = parseInt($("#priv-start").val());
	var count = parseInt($("#priv-count").val());
	var total = parseInt($("#priv-total").val());
	var url = $(this).attr("data-href") + "?start=" + start + "&count=" + count;
	
	$.ajax({
		url: url,
		success: function(data) {				
			$("#priv-start").val(start + count);
			$('#priv-threads').append(data);
			$('#priv-threads').listview('refresh');
			$.mobile.hidePageLoadingMsg();
			if ((start + count) < total) {
				$('#load-priv-threads').button('enable');;
			}			
		}
	});

	return false;
});

$(document).on("click", "#load-pub-threads", function(event){
	$.mobile.showPageLoadingMsg();
	$('#load-pub-threads').button('disable');
	var start = parseInt($("#pub-start").val());
	var count = parseInt($("#pub-count").val());
	var total = parseInt($("#pub-total").val());
	var url = $(this).attr("data-href") + "?start=" + start + "&count=" + count;
	
	$.ajax({
		url: url,
		success: function(data) {				
			$("#pub-start").val(start + count);
			$('#pub-threads').append(data);
			$('#pub-threads').listview('refresh');
			$.mobile.hidePageLoadingMsg();
			if ((start + count) < total) {
				$('#load-pub-threads').button('enable');
			}		
		}
	});

	return false;
});

$(document).on("click", "#load-pub-threads-public", function(event){
	$.mobile.showPageLoadingMsg();
	$('#load-pub-threads-public').button('disable');
	var start = parseInt($("#pub-public-start").val());
	var count = parseInt($("#pub-public-count").val());
	var total = parseInt($("#pub-public-total").val());
	var url = $(this).attr("data-href") + "?start=" + start + "&count=" + count;
	
	$.ajax({
		url: url,
		success: function(data) {				
			$("#pub-public-start").val(start + count);
			$('#pub-public-threads').append(data);
			$('#pub-public-threads').listview('refresh');
			$.mobile.hidePageLoadingMsg();
			if ((start + count) < total) {
				$('#load-pub-threads-public').button('enable');
			}		
		}
	});

	return false;
});

$(document).on("click", "#load-messages", function(event){
	$.mobile.showPageLoadingMsg();
	$('#load-messages').button('disable');
	var start = parseInt($("#messages-start").val());
	var count = parseInt($("#messages-count").val());
	var total = parseInt($("#messages-total").val());
	var url = $(this).attr("data-href") + "?start=" + start + "&count=" + count;
	
	$.ajax({
		url: url,
		success: function(data) {				
			$("#messages-start").val(start + count);
			$('#messages').append(data);
			$('#messages').listview('refresh');
			$.mobile.hidePageLoadingMsg();
			if ((start + count) < total) {
				$('#load-messages').button('enable');;
			}			
		}
	});

	return false;
});

$(document).on("click", "#load-messages-sent", function(event){
	$.mobile.showPageLoadingMsg();
	$('#load-messages-sent').button('disable');
	var start = parseInt($("#messages-sent-start").val());
	var count = parseInt($("#messages-sent-count").val());
	var total = parseInt($("#messages-sent-total").val());
	var url = $(this).attr("data-href") + "?start=" + start + "&count=" + count;
	
	$.ajax({
		url: url,
		success: function(data) {				
			$("#messages-sent-start").val(start + count);
			$('#messages-sent').append(data);
			$('#messages-sent').listview('refresh');
			$.mobile.hidePageLoadingMsg();
			if ((start + count) < total) {
				$('#load-messages-sent').button('enable');;
			}			
		}
	});

	return false;
});

$(document).on("click", ".vote-yes", function(event){
	var pollid = $(this).parents("li").attr("id");
	var vote = $("#vote-" + pollid).val();
	var increase = parseInt($("#increase-" + pollid).val());
	var yespercent = parseInt($("#yespercent-" + pollid).val());
	var nopercent = parseInt($("#nopercent-" + pollid).val());
	var yeswidth = yespercent + increase;
	
	$(this).button('disable');
	//$(this).parent().addClass("ui-btn-up-b").removeClass("ui-btn-up-a");	
	
	//$.mobile.showPageLoadingMsg();
	
	$.ajax({
		url: $(this).attr("data-href"),
		success: function(data) {			
			$("#" + pollid + " .yes .ui-slider-handle").animate({left: yeswidth + "%"}, 500);
			$("#" + pollid + " .yes .ui-slider-bg").animate({width: yeswidth + "%"}, 500);
			$("#yes-" + pollid).val(yeswidth);
			$("#yespercent-" + pollid).val(yeswidth);

			var reverse = nopercent - increase;
			
			if (vote != "") {
				$("#" + pollid + " .no .ui-slider-handle").animate({left: reverse + "%"}, 500);
				$("#" + pollid + " .no .ui-slider-bg").animate({width: reverse + "%"}, 500);
				$("#no-" + pollid).val(reverse);
				$("#nopercent-" + pollid).val(reverse);
				//$("#" + pollid + " .vote-no").parent().addClass("ui-btn-up-a").removeClass("ui-btn-up-b");
				$("#" + pollid + " .vote-no").button('enable');
			}
			
			$("#vote-" + pollid).val("1");
			
			//$.mobile.hidePageLoadingMsg();
		}
	});
	
	return false;
});

$(document).on("click", ".vote-no", function(event){
	var pollid = $(this).parents("li").attr("id");
	var vote = $("#vote-" + pollid).val();
	var increase = parseInt($("#increase-" + pollid).val());
	var yespercent = parseInt($("#yespercent-" + pollid).val());
	var nopercent = parseInt($("#nopercent-" + pollid).val());
	var nowidth = nopercent + increase;
	
	$(this).button('disable');
	//$(this).parent().removeClass("ui-btn-up-a").addClass("ui-btn-up-b");
	
	//$.mobile.showPageLoadingMsg();
	
	$.ajax({
		url: $(this).attr("data-href"),
		success: function(data) {				
			$("#" + pollid + " .no .ui-slider-handle").animate({left: nowidth + "%"}, 500);
			$("#" + pollid + " .no .ui-slider-bg").animate({width: nowidth + "%"}, 500);
			$("#no-" + pollid).val(nowidth);
			$("#nopercent-" + pollid).val(nowidth);
			
			var reverse = yespercent - increase;
			
			if (vote != "") {				
				$("#" + pollid + " .yes .ui-slider-handle").animate({left: reverse + "%"}, 500);
				$("#" + pollid + " .yes .ui-slider-bg").animate({width: reverse + "%"}, 500);
				$("#yes-" + pollid).val(reverse);
				$("#yespercent-" + pollid).val(reverse);
				//$("#" + pollid + " .vote-yes").parent().addClass("ui-btn-up-a").removeClass("ui-btn-up-b");
				$("#" + pollid + " .vote-yes").button('enable');
			}
			
			$("#vote-" + pollid).val("0");
			
			//$.mobile.hidePageLoadingMsg();
		}
	});
	
	return false;
});