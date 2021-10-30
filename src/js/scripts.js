var __widthMobile = 1000;
var __widthMobileTablet = 1024;
var __widthMobileTabletMiddle = 768;
var __widthMobileTabletSmall = 600;
var __widthMobileSmall = 540;
var __isMobile = ($(window).width() <= __widthMobile);
var __isMobileTablet = ($(window).width() <= __widthMobileTablet);
var __isMobileTabletMiddle = ($(window).width() <= __widthMobileTabletMiddle);
var __isMobileTabletSmall = ($(window).width() <= __widthMobileTabletSmall);
var __isMobileSmall = ($(window).width() <= __widthMobileSmall);
var __animationSpeed = 350;	

// ON DOCUMENT READY
$(document).ready(function() {

	$.fn.lightTabs = function() {
		var showTab = function(tab, saveHash) {
			if (!$(tab).hasClass('tab-act')) {
				var tabs = $(tab).closest('.tabs');

				var target_id = $(tab).attr('href');
		        var old_target_id = $(tabs).find('.tab-act').attr('href');
		        $(target_id).show();
		        $(old_target_id).hide();
		        $(tabs).find('.tab-act').removeClass('tab-act');
		        $(tab).addClass('tab-act');

		        if (typeof(saveHash) != 'undefined' && saveHash) history.pushState(null, null, target_id);
			}
		}

		var initTabs = function() {
            var tabs = this;
            
            $(tabs).find('a').each(function(i, tab){
                $(tab).click(function(e) {
                	e.preventDefault();

                	showTab(this, true);
                	fadeoutInit();

                	return false;
                });
                if (i == 0) showTab(tab);                
                else $($(tab).attr('href')).hide();
            });	

            $(tabs).swipe({
				swipeStatus: function(event, phase, direction, distance) {
					var offset = distance;

					if (phase === $.fn.swipe.phases.PHASE_START) {
						var origPos = $(this).scrollLeft();
						$(this).data('origPos', origPos);

					} else if (phase === $.fn.swipe.phases.PHASE_MOVE) {
						var origPos = $(this).data('origPos');

						if (direction == 'left') {
							var scroll_max = $(this).prop('scrollWidth') - $(this).width();
							var scroll_value_new = origPos - 0 + offset;
							$(this).scrollLeft(scroll_value_new);
							if (scroll_value_new >= scroll_max) $(this).addClass('scrolled-full');
							else $(this).removeClass('scrolled-full');

						} else if (direction == 'right') {
							var scroll_value_new = origPos - offset;
							$(this).scrollLeft(scroll_value_new);
							$(this).removeClass('scrolled-full');
						}

					} else if (phase === $.fn.swipe.phases.PHASE_CANCEL) {
						var origPos = $(this).data('origPos');
						$(this).scrollLeft(origPos);

					} else if (phase === $.fn.swipe.phases.PHASE_END) {
						$(this).data('origPos', $(this).scrollLeft());
					}
				},
				threshold: 70
			});	
        };

        return this.each(initTabs);
    };

    function initPage() {
    	initElements();

    	// LINKS
		$('a[href]').click(function(e) {
			if (window.pagePreloader) {
				if (!$(this).parent().hasClass('has-child')
					&& !$(this).hasClass('js-lightbox')
					&& !$(this).hasClass('js-modal-link')
					&& $(this).attr('href').substr(0, 7) != 'mailto:'
					&& $(this).attr('href').substr(0, 4) != 'tel:'
					&& $(this).attr('target') != '_blank') {

					e.preventDefault();

					var href = $(this).attr('href');
					if (href.charAt(0) == '#') {
						_scrollTo(href);
					} else {
						window.pagePreloader.pageClose(function() {
							redirect(href);
							setTimeout(function() {
								window.pagePreloader.pageOpen();
							}, 500);
						});
					}
				}
			}
		});

	    // BURGER
		$('nav').click(function() {
			if (__isMobile && !$('body').hasClass('mobile-opened')) {
				if (!$('header').children('.close').data('inited')) {
					if (!$('header>.close').length) {
						$('header').append('<div class="close"></div>');
					}
					$('header').children('.close').click(function(e) {
						e.stopPropagation();

						$('body').removeClass('mobile-opened');
						$('html').removeClass('html-mobile-long');
						$('#layout').height('auto').removeClass('js-modal-overflow');
					}).data('inited', true);
				}

				$('body').addClass('mobile-opened');

				var innerHeight = $('header').outerHeight();
				if (innerHeight > $(window).height()) {
					$('html').addClass('html-mobile-long');
				} else {
					$('html').removeClass('html-mobile-long');
				}

				$('#layout').addClass('js-modal-overflow').height($('header').outerHeight());
			}
		});

		var isOverheight = function() {
			return !__isMobile ? 
				($('header').outerHeight() > $(window).height()) :
				($('nav').outerHeight() + $('#logo').outerHeight(true) > $(window).height());
		}
		window.isOverheight = isOverheight;

		$('nav ul>li.has-child>a').click(function(e) {
			e.preventDefault();

			if (!$(this).parent().hasClass('opened')) {
				$(this).parent().addClass('opened').children('ul').stop().slideDown(__animationSpeed*2, function() {
					var isOverheight = window.isOverheight();

					if (__isMobile) {
						var h = $('header').outerHeight() - parseInt($('#layout').css('paddingTop'));
						$('html').toggleClass('html-mobile-long', isOverheight);
						$('#layout').addClass('js-modal-overflow').height(h);
					}
				});
			} else {
				$(this).parent().removeClass('opened').children('ul').stop().slideUp(__animationSpeed, function() {
					var isOverheight = window.isOverheight();

					if (__isMobile) {
						var h = $('header').outerHeight() - parseInt($('#layout').css('paddingTop'));
						$('html').toggleClass('html-mobile-long', isOverheight);
						$('#layout').addClass('js-modal-overflow').height(h);
					}
				});
			}
		});

		// HEADER
		$('header').mousewheel(function(e) {
			e.preventDefault();
			e.stopPropagation();

			var boost = 60;
			var newScrollTop = $(this).scrollTop() - (e.deltaY * e.deltaFactor * boost);

			$(this).stop().animate({
				'scrollTop': newScrollTop
			}, 400, 'easeInOutCubic');
		});

		// MODAL LINKS
		$('.js-modal-link').click(function(e) {
			e.preventDefault();
			showModal($(this).attr('href').substring(1));
		});

		// SLICKS
		$('.js-slider').each(function(i, slider) {
			var mobile = $(slider).attr('data-mobile');
			var adaptive = $(slider).attr('data-adaptive');
			var dots = $(slider).attr('data-dots') === 'false' ? false : true;
			var arrows = $(slider).attr('data-arrows') === 'true' ? true : false;
			var autoplay = $(slider).attr('data-autoplay') ? $(slider).attr('data-autoplay') : false;
			var slidesToShow = adaptive ? Math.floor($(slider).outerWidth() / $(slider).children('li, .li').outerWidth()) : 1;
		
			if (mobile) {
				if ((mobile === 'true' && __isMobile) ||
					(mobile === 'middle' && __isMobileTabletMiddle) ||
					(mobile === 'small' && __isMobileTabletSmall) ||
					(mobile === 'mobile' && __isMobileSmall)) {					
		
					$(slider).slick({
						slidesToShow: slidesToShow,
						slidesToScroll: slidesToShow,
						dots: dots,
						arrows: arrows,
						autoplay: autoplay,
						centerMode: true,
	     				centerPadding: '0'
					});
				}
			} else {
				$(slider).slick({
					slidesToShow: slidesToShow,
					slidesToScroll: slidesToShow,
					dots: dots,
					arrows: arrows,
					autoplay: autoplay,
					centerMode: true,
	     			centerPadding: '0'
				});
			}
		});

		// LIGHTBOXES
		var galleries = new Array();
		$('.js-lightbox').each(function(i, a) {
			if (!$(a).is('[data-gallery]')) {
				$(a).magnificPopup({
					type: 'image',
					removalDelay: 300,
					callbacks: {
				        beforeOpen: function() {
				            $(this.contentContainer).removeClass('fadeOut').addClass('animated fadeIn');
				        },
				        beforeClose: function() {
				        	$(this.contentContainer).removeClass('fadeIn').addClass('fadeOut');
				        }
				    },
					midClick: true
				});
			} else {
				if (typeof(galleries[$(a).attr('data-gallery')]) == 'undefined') galleries.push($(a).attr('data-gallery'));
			}
		});
		$.each(galleries, function(i, gallery) {
			$('.js-lightbox[data-gallery="' + gallery + '"]').magnificPopup({
				type: 'image',
				removalDelay: 300,
				callbacks: {
			        beforeOpen: function() {
			             $(this.contentContainer).removeClass('fadeOut').addClass('animated fadeIn');
			        },
			        beforeClose: function() {
			        	$(this.contentContainer).removeClass('fadeIn').addClass('fadeOut');
			        }
			    },
				gallery: {
					enabled: true
				},
				midClick: true
			});
		});

		// VACANCIES LIST
		if ($('#bl-vacancies').length) {
			$('#bl-vacancies>ul>li').each(function(i, li) {
				$(li).children('.toggler').click(function() {
					var self = this;
					var $li = $(this).parent();
					if (!$li.hasClass('opened')) {
						$li.children('.info').stop().animate({
								height: $li.children('.info').get(0).scrollHeight
							}, 
							__animationSpeed, 
							'easeInOutCubic', 
							function() {
								//$li.children('.info').height('auto');
								$(self).text('Скрыть');
						});
						$li.addClass('opened');
					} else {
						$li.children('.info').stop().animate({
							height: 0
						}, {
							duration: __animationSpeed,
						    easing: 'easeInOutCubic',
						    complete: function(){
						    	$li.removeClass('opened');
						    	$(self).text('Подробнее');
						    },
						    queue: false
						});
					}
				});
			});
		}

		// FORMS
		$('form[data-submit]').on('submit', function(e) {
			e.preventDefault();
			e.stopPropagation();

			eval($(this).attr('data-submit') + '(this)');
		});

		// SOCIALS
		$('#social-shares>ul>li>a').click(function(e) {
			e.preventDefault();
			var $shares = $('#social-shares');
			var url = $shares.attr('data-share-url');
			var title = $shares.attr('data-share-title');
			var image = $shares.attr('data-share-image');
			var description = $shares.attr('data-share-description');
			var api_url = $(this).attr('data-api-url');
				
			api_url = api_url.split('%url%').join(url).split('%title%').join(title).split('%image%').join(image).split('%description%').join(description);
			window.open(api_url, title, 'width=640,height=480,status=no,toolbar=no,menubar=no');
		});

		// FEEDBACK
		function sendFeedback(form) {
			msgUnset(form);
			checkResetStatus(form);
			if ($(form.agree).prop('checked')) {
				msgUnset(form.tel);

				if (checkElements(
					[form.tel, form.text], 
					[{1: true}, {1: true}]
				)) {
					form.submit_btn.disabled = true;
					var waitNode = msgSetWait(form);

					$(form).append('<input type="hidden" name="capcha" value="' + navigator.userAgent + '"/>');
						
					$.ajax({
						type: $(form).attr('method'),
						url: $(form).attr('action'),
						data: $(form).serialize(),
						dataType: 'json',
						success: function(response) {
							if(response.status == true) {
								$('#bl-feedback>.holder>h2').text(response.header);
								$('#bl-feedback>.holder>.text').text(response.message);
								form.reset();
								
							} else {
								msgSetError(form, response.message);
							}
							$(waitNode).remove();
							form.submit_btn.disabled = false;
						}
					});
				} else {
					//msgSetError(form.tel, 'Введите, пожалуйста, телефон');
				}
			} else {
				// agreement
			}
		}

		// MEDIA GALLERY VIDEO
		if ($('#gallery .video').length) {
			$('#gallery .video').each(function(i, item) {
				$(item).children('.js-modal-link').click(function() {
					var player = $('#js-youtube-video').data('player');
					var code = $(this).attr('data-video-code');
					new YT.Player('js-youtube-video', {
						height: '100%',
			    		width: '100%',
					    videoId: code,
					    playerVars: { 'autoplay': 1, 'controls': 0 }
					});
					player.loadVideoById(code);
					player.playVideo();
				});
			});

			$('#modal-video .modal-close').click(function() {
				var player = $('#js-youtube-video').data('player');
				player.pauseVideo();
				player.stopVideo();
			});
		}

		// COMPANY VIDEO
		if ($('#js-company-video').length) {
			$('#js-company-video').click(function() {
				var player = $('#js-youtube-video').data('player');
				var code = $(this).attr('data-video-code');
				new YT.Player('js-youtube-video', {
					height: '100%',
			    	width: '100%',
				    videoId: code,
				    playerVars: { 'autoplay': 1, 'controls': 0 }
				});
				player.loadVideoById(code);
				player.playVideo();
			});

			$('#modal-video .modal-close').click(function() {
				var player = $('#js-youtube-video').data('player');
				player.pauseVideo();
				player.stopVideo();
			});
		}
	}

	initPage();

	// BARBA
	var barbaTransEffect = Barba.BaseTransition.extend({
        start: function() {
          	var _this = this;
           	this.newContainerLoading.then(function() {
          		_this.fadeInNewcontent($(_this.newContainer));
            });
        },
        fadeInNewcontent: function(nc) {
	        nc.hide();
	        $(window).trigger('closing');
	        var _this = this;
	        $(this.oldContainer).fadeOut(__animationSpeed).promise().done(function() {
	            nc.css('visibility', 'visible');
	            $('html,body').scrollTop(0);
	            nc.fadeIn(__animationSpeed, function() {
	              	$(window).trigger('opening');
	               	_this.done();	                    	
	               	initPage();
	            });
	        });
        }
    });
    Barba.Pjax.getTransition = function() {
       	return barbaTransEffect;
    }
	Barba.Pjax.start();

	// ANIMATE NUMBERS
	$(window).on('opening', function() {
		$('.js-num-animated').each(function() {
	      	var num = parseInt($(this).text().replace(/[^\d]/g, ''));
	      	var delay = $(this).attr('data-delay') ? $(this).attr('data-delay') - 0 : 0;

	      	$(this).html($(this).text().replace(num, '<span>' + num + '</span>'));

	      	$(this).children('span').animateNumber({
	        	number: num
	      	},
	      	{
	        	easing: 'swing',
	        	duration: __animationSpeed*2 + delay
	      	});
	    });
	});

	// WOW ANIMATION
	$(window).on('opening', function() {
		if (typeof(WOW) != 'undefined') {
			new WOW().init();
		}
	});

    onResize();

    // MAP
    if ($('#map').length) {
    	$(window).on('opening', function() {
    		var coords = $('#map').attr('data-coords').split(',');
    		coords[0] = parseFloat(coords[0]);
    		coords[1] = parseFloat(coords[1]);

    		var placeholderSrc = 'https://smartenergo-rt.ru/assets/images/map_placeholder.png';
	      	var placeholderCoords = coords;
	      	ymaps.ready(function () {
	        	var map = new ymaps.Map('map', {
		          center: placeholderCoords,
		          zoom: 16,
		          controls: ['zoomControl']
		        });
		        map.behaviors.disable('scrollZoom');
		        var mark = new ymaps.Placemark(placeholderCoords, {}, {
			        iconLayout: 'default#image',
			        iconImageHref: placeholderSrc,
		            iconImageSize: __isMobileSmall ? [50, 59] : (__isMobile ? [77, 92] : [95, 113]),
		            iconImageOffset: __isMobileSmall ? [-25, -59] : (__isMobile ? [-38, -92] : [-47, -113])
		        });

	        	map.geoObjects.add(mark);
	      	});
    	});    	
    }
});

// YOUTUBE VIDEO INIT
if ($('#js-youtube-video').length) {
	function onYouTubeIframeAPIReady() {
		var player = new YT.Player('js-youtube-video', {
		    height: '100%',
		    width: '100%'
		});
		$('#js-youtube-video').data('player', player);
	}
}