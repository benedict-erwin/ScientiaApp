(function ($) {
 "use strict";
		/*--------------------------
		 auto-size Active Class
		---------------------------- */
		$(".auto-size")[0] && autosize($(".auto-size"));
		/*--------------------------
		 Collapse Accordion Active Class
		---------------------------- */
		$(".collapse")[0] && ($(".collapse").on("show.bs.collapse", function(e) {
            $(this).closest(".panel").find(".panel-heading").addClass("active")
        }), $(".collapse").on("hide.bs.collapse", function(e) {
            $(this).closest(".panel").find(".panel-heading").removeClass("active")
        }), $(".collapse.in").each(function() {
            $(this).closest(".panel").find(".panel-heading").addClass("active")
        }));
		/*----------------------------
		 jQuery tooltip
		------------------------------ */
		$('[data-toggle="tooltip"]').tooltip();
		/*--------------------------
		 popover
		---------------------------- */
		$('[data-toggle="popover"]')[0] && $('[data-toggle="popover"]').popover();
		/*--------------------------
		 File Download
		---------------------------- */
		$('.btn.dw-al-ft').on('click', function(e) {
			e.preventDefault();
		});
		/*--------------------------
		 Sidebar Left
		---------------------------- */
		$('#sidebarCollapse').on('click', function () {
			$('#sidebar').toggleClass('active');

		 });
		$('#sidebarCollapse').on('click', function () {
			$("body").toggleClass("mini-navbar");
			SmoothlyMenu();
		});
		$('.menu-switcher-pro').on('click', function () {
			let button = $(this).find('i.nk-indicator');
			button.toggleClass('notika-menu-befores').toggleClass('notika-menu-after');

		});
		$('.menu-switcher-pro.fullscreenbtn').on('click', function () {
			let button = $(this).find('i.nk-indicator');
			button.toggleClass('notika-back').toggleClass('notika-next-pro');
		});
		/*--------------------------
		 Button BTN Left
		---------------------------- */

		$(".nk-int-st")[0] && ($("body").on("focus", ".nk-int-st .form-control", function() {
            $(this).closest(".nk-int-st").addClass("nk-toggled")
        }), $("body").on("blur", ".form-control", function() {
            let p = $(this).closest(".form-group, .input-group"),
                i = p.find(".form-control").val();
            p.hasClass("fg-float") ? 0 == i.length && $(this).closest(".nk-int-st").removeClass("nk-toggled") : $(this).closest(".nk-int-st").removeClass("nk-toggled")
        })), $(".fg-float")[0] && $(".fg-float .form-control").each(function() {
            let i = $(this).val();
            0 == !i.length && $(this).closest(".nk-int-st").addClass("nk-toggled")
        });
		/*--------------------------
		 mCustomScrollbar
		---------------------------- */
		$(window).on("load",function(){
			$(".widgets-chat-scrollbar").mCustomScrollbar({
				setHeight:460,
				autoHideScrollbar: true,
				scrollbarPosition: "outside",
				theme:"light-1"
			});
			$(".notika-todo-scrollbar").mCustomScrollbar({
				setHeight:445,
				autoHideScrollbar: true,
				scrollbarPosition: "outside",
				theme:"light-1"
			});
			$(".comment-scrollbar").mCustomScrollbar({
				autoHideScrollbar: true,
				scrollbarPosition: "outside",
				theme:"light-1"
			});
		});
	/*----------------------------
	 jQuery MeanMenu
	------------------------------ */
	jQuery('nav#dropdown').meanmenu();

	/*----------------------------
	 wow js active
	------------------------------ */
	 new WOW().init();

	/*----------------------------
	 owl active
	------------------------------ */
	$("#owl-demo").owlCarousel({
      autoPlay: false,
	  slideSpeed:2000,
	  pagination:false,
	  navigation:true,
      items : 4,
	  /* transitionStyle : "fade", */    /* [This code for animation ] */
	  navigationText:["<i class='fa fa-angle-left'></i>","<i class='fa fa-angle-right'></i>"],
      itemsDesktop : [1199,4],
	  itemsDesktopSmall : [980,3],
	  itemsTablet: [768,2],
	  itemsMobile : [479,1],
	});

	/*--------------------------
	 scrollUp
	---------------------------- */
	$.scrollUp({
        scrollText: '<i class="fa fa-angle-up"></i>',
        easingType: 'linear',
        scrollSpeed: 900,
        animation: 'fade'
    });

	/*-------------------------
	 Tanggal + Jam WIB
	------------------------- */
	setInterval(() => {
		$("#tikClock").attr({"data-original-title": moment().format('dddd, Do MMMM YYYY') + " _ Pukul: "+ moment().format('HH:mm') + " WIB"});
	}, 1000);

})(jQuery);

/* Build Groupmenu */
function buildGroupmenu(data) {
	let html = ''; /* Wrap with div if true */
    for (item in data) {
        html += '<li>';
        html += '<a data-toggle="tab" href="#' + data[item].GM + '"><i class="' + data[item].ICON_GROUPMENU + '"></i> ' + ucwords(strtolower(data[item].NM_GROUPMENU)) + '</a>';
        html += '</li>';
    }
    return html;
}

/* Build Submenu */
function buildList(data, isSub) {
    let html = ''; /* Wrap with div if true */
    for (item in data) {
        if (typeof (data[item].MENU_LIST) == 'object') { /* An array will return 'object' */
            if (isSub) {
                /* console.log('isSub'); */
				html += '<li><a href=".' + data[item].URL + '" data-id="' + data[item].GM + '">' + data[item].NM_MENU + '</a></li>';
            } else {
				//console.log(data[item].MENU_LIST);
				html += '<div id="' + data[item].GM + '" class="tab-pane notika-tab-menu-bg animated flipInX">';
				html += '<ul class="notika-main-menu-dropdown">';
				html += buildList(data[item].MENU_LIST, true); /* Submenu found. Calling recursively same method (and wrapping it in a div) */
				html += '</ul>';
				html += '</div>';
			}
        } else {
            /* console.log('NoSubmenu'); */
            html += '<li><a href=".' + data[item].URL + '" data-id="' + data[item].GM + '">' + data[item].NM_MENU + '</a></li>'; // No submenu
        }
    }
    return html;
}

/* Build Submenu */
function buildListMobile(data, isSub) {
    let html = ''; /* Wrap with div if true */
    for (item in data) {
        if (typeof (data[item].MENU_LIST) == 'object') { /* An array will return 'object' */
            if (isSub) {
                /* console.log('isSub'); */
				html += '<li><a href=".' + data[item].URL + '">' + data[item].NM_MENU + '</a></li>';
            } else {
				//console.log(data[item].MENU_LIST);
				html += '<li><a data-toggle="collapse" data-target="#' + data[item].GM + 'Mob" href="#" onclick="return false;">' + ucwords(strtolower(data[item].NM_GROUPMENU)) + '</a>';
				html += '<ul id="' + data[item].GM + 'Mob" class="collapse dropdown-header-top">';
				html += buildListMobile(data[item].MENU_LIST, true); /* Submenu found. Calling recursively same method (and wrapping it in a div) */
				html += '</ul>';
				html += '</li>';
			}
        } else {
            /* console.log('NoSubmenu'); */
            html += '<li><a href=".' + data[item].URL + '">' + data[item].NM_MENU + '</a></li>';
        }
    }
    return html;
}

/* Template menu */
function loadMenu() {
	if ($("div.mean-bar").length > 0){
		$("div.mean-bar > a.meanmenu-reveal").addClass("animated infinite flip");
	}
    $.ajax({
        "type": 'POST',
        "url": SiteRoot + 'cmenu',
        "dataType": 'json',
        "headers": { Authorization: "Bearer " + get_token(API_TOKEN) },
        success: function (data, textStatus, jqXHR) {
            if (data.success == true) {
                set_token(API_TOKEN, jqXHR.getResponseHeader('JWT'));
                let arr = $.map(data.message, function (el) { return el; });
				arr = sortJson(arr, 'ORDER', true);
				$('#grpMenu').html(buildGroupmenu(arr));
				$('#subMenu').html(buildList(arr, false));
				$('div.breadcomb-icon > i').removeClass("animated infinite flip");
				/*----------------------------------*/
                /*	SIDEBAR NAVIGATION              */
                /*----------------------------------*/
                if (getCurrentPath() == '/') {
					let smx = $('a[href="./home"]');
					smx.closest('div').addClass('active');
					$("#grpMenu a[href='#"+smx.attr('data-id')+"']").click();
                    smx.addClass('menu-active');
                } else {
                    /* Set Active class */
					let smx = $('a[href=".' + getCurrentPath() + '"]');
					$("#grpMenu a[href='#"+smx.attr('data-id')+"']").click();
                    smx.addClass('menu-active');
				}

				/* Generate Mobile Menu */
				$('#mobMenu').html(buildListMobile(arr, false));
				if ($("div.mean-bar").length > 0){
					$("div.mean-bar > a.meanmenu-reveal").removeClass("animated infinite flip");
				}

				/* Set sp_uname */
				$('h2#sp_uname').text('Selamat datang, ' + ucfirst(data.sp_uname));
            } else {
                notification((data.message.error) ? data.message.error : data.message, 'warn');
			}
			setNprogressLoader("done");
        },
        error: function (jqXHR, textStatus, errorThrown) {
            notification(errorThrown, 'error');
            if (errorThrown == 'Token Expired') {
                set_token(API_TOKEN, '');
                window.location.reload();
            }
            console.log(jqXHR);
            console.log(textStatus);
            console.log(errorThrown);
        }
	});
}