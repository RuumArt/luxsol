function sendGA(data, event = 'addToCart') {
	dataLayer.push({
		event: event,
		ecommerce: {
			currencyCode: "EUR",
			add: {
				products: [
					{
						...data,
					}
				]
			}
		},
	});
}

function setBasketCountText(data) {
	$('.headerbasket ._count').html(data);
}

function add_cart_catalog(element){
	var id = $(element).data('id');
	var colorcheck = $('.color-'+id).val();
	var quant = $('.quant-item'+id).val();
	var articul = $(element).parents('.product-card').find('.sku-value').text();
	$.ajax({
		type: "POST",
		url: "/ajax/add_cart.php",
		data: ( {"id" : id, "quant": quant, "articul": articul, "color": colorcheck} ),
		dataType: "json",
		success: function(res){
			setBasketCountText(res.count);
			sendGA(res.data);
		}
	});
	return false;
}

function addcart(id) {

	var colorcheck = $('#colorcheck').val();
	var articul = $('#sku').text();

	var quant = $('.quant-item').val();
		$.ajax({
			type: "POST",
			url: "/ajax/add_cart.php",
			data: ( {"id" : id, "quant": quant, "color": colorcheck, "articul": articul} ),
			dataType: "json",
			success: function(res){
				setBasketCountText(res.count)
				sendGA(res.data);
			}
		});
		return false;

}

function addcartcart(id, idcart) {	
	var submit = true; 
	var quant = $('#prop'+id+' .quant-item').val();
    var height = $('#prop'+id+' .heightsetka');
    var width = $('#prop'+id+' .widthsetka');
    var price = $('#prop'+id+' .price-item').val();
	var colorcheck = $('#prop'+id+' .colorcheck').val();
	var sizes = $('#prop'+id+' input[name^=sizes]').serialize();

    if(height.val()==''){
    	height.addClass('has-err'); 
        submit = false; 
    }
    else{
    	height.removeClass('has-err');
    }
    
    if(width.val()==''){
    	width.addClass('has-err'); 
        submit = false; 
    }
    else{
    	width.removeClass('has-err');
    }
    if(submit){
    	$('#errorsetka').hide();
		$.ajax({
			type: "POST",
			url: "/ajax/add_cart.php",
			data: sizes + "&color=" + colorcheck + "&id=" + id + "&quant=" + quant + "&height=" + height.val() + "&width=" + width.val() + "&price=" + price + "&idcart=" + idcart,
			success: function(html){
				window.location.href='/cart/';
			}
		});
    }
    else{
    	$('#errorsetka').show();
    }
	return false;

}

function addcartq(id) {
	var submit = true; 
	var quant = $('.quant-item').val();
    var height = $('.heightsetka');
    var width = $('.widthsetka');
    var price = $('#price-item').val();
	var colorcheck = $('#colorcheck').val();
	var sizes = $('input[name^=sizes]').serialize();

	if($('.sizes__item input[name="sizes[0][height]"]').val() == '' || $('.sizes__item input[name="sizes[0][width]"]').val() == ''){
    	$('.sizes__item:first-child').addClass('sizes__item--error'); 
    	$('#errorsetka').show();
        submit = false; 
    }
    else{
    	$('.sizes__item:first-child').removeClass('sizes__item--error');
    	$('#errorsetka').hide();
    }
    
    if(submit){
    	$('#cart').addClass('active');
    	$('#errorsetka').hide();
		$.ajax({
			type: "POST",
			url: "/ajax/add_cart.php",
			data: sizes + "&color=" + colorcheck + "&id=" + id + "&quant=" + quant + "&height=" + height.val() + "&width=" + width.val() + "&price=" + price,
			dataType: "json",
			success: function(res){
				setBasketCountText(res.count);
				sendGA(res.data);
			}
		});
    }
    else{
    	$('#errorsetka').show();
    }
	return false;
}

function addcartqcolor(id) {
	var submit = true; 
	var quant = $('.quant-item').val();
    var height = $('.heightsetka');
    var width = $('.widthsetka');
	var price = $('#price-item').val();
	var colorcheck = $('#colorcheck').val();
	var sku = $('#sku').text();
	var sizes = $('input[name^=sizes]').serialize();

	if(!colorcheck){
		$('body,html').stop().animate({
			scrollTop: $('#rescheckcol').offset().top - 100
		}, 500);
	}else if($('.sizes__item').length == 1 && ($('.sizes__item input[name="sizes[0][height]"]').val() == '' || $('.sizes__item input[name="sizes[0][width]"]').val() == '')){
		$('body,html').stop().animate({
			scrollTop: $('.sizes').offset().top - 100
		}, 500);
	}

    if(colorcheck ==''){
    	$('#rescheckcol').show();
		submit = false;
    }
    else{
    	$('#rescheckcol').hide();
    }

    if($('.sizes__item input[name="sizes[0][height]"]').val() == '' || $('.sizes__item input[name="sizes[0][width]"]').val() == ''){
    	$('.sizes__item:first-child').addClass('sizes__item--error'); 
    	$('#errorsetka').show();
        submit = false; 
    }
    else{
    	$('.sizes__item:first-child').removeClass('sizes__item--error');
    	$('#errorsetka').hide();
    }
    
    if(submit){
    	$('#cart').addClass('active');
    	$('#errorsetka').hide();
		$.ajax({
			type: "POST",
			url: "/ajax/add_cart.php",
			data: sizes + "&color=" + colorcheck + "&id=" + id + "&quant=" + quant + "&height=" + height.val() + "&width=" + width.val() + "&price=" + price + "&articul=" + sku,
			success: function(res){
				setBasketCountText(res.count);
				sendGA(res.data);
			}
		});
    }
    else{
    	
    }
	return false;

}


function getFromCart(id){
	$.ajax({
		type: "POST",
		url: "/ajax/get_product_cart.php",
		data: ( {"id" : id} ),
		success: function(html){
			if(html){
				$('.sizes').html(html);
			}else{
				$('.sizes').html(roomSizesItem(0, true));
			}
		}
	});
}


/**
 * Разметка строки размеров берётся из заготовок в шаблоне
 * catalog.element/element/template.php, чтобы тексты не дублировались в JS
 */
function roomSizesItem(index, isFirst) {
	var tpl = $(isFirst ? '#sizes-item-first-template' : '#sizes-item-template').html();

	if (!tpl) {
		return '';
	}

	return tpl.replace(/\{\{INDEX\}\}/g, index);
}

function del(id){
	var quant = $('#quant_'+id).val();
	$.ajax({
		type: "POST",
		url: "/ajax/cart.php",
		data: ( {"delete" : "Y", "id" : id} ),
		success: function(html){
			$('#result_cart').html(html);
		}
	});
}

function slideoffer(id){
	$('.card-left').hide();
	$('.slide'+id).show();
	if($('.slide'+id).find('.p-single-slider')[0].swiper) $('.slide'+id).find('.p-single-slider')[0].swiper.update();
	if($('.slide'+id+' .p-single-slider-thumb').length > 0){
		if($('.slide'+id).find('.p-single-slider-thumb')[0].swiper) $('.slide'+id).find('.p-single-slider-thumb')[0].swiper.update();
	}
}

function price(id){
	$.ajax({
		type: "POST",
		url: "/ajax/priceoffer.php",
		data: { id: id },
		dataType: "json",
		success: function(res){

			$('.price_offer_dph').html(res.html).find('.price_offer_dph_no').remove();
			$('.price_offer_dph_no').html(res.html).find('.price_offer_dph').remove();

			sendGA(res.data, 'showDetail');
		}
	});
}
function cart2(id){
	$('.btncartnull').hide();
	$('.btncart'+id).show();
}



function add_to_fav(id){
	$.ajax({
		type: "POST",
		url: "/ajax/add_favorites.php",
		data: ( {"id" : id} ),
		success: function(html){

			$('.icon-heart').html(html);
			//$('.opener-favorites').addClass('bg-none');
		}
	});
}
function add_to_fav_delete(id){
	$.ajax({
		type: "POST",
		url: "/ajax/add_favorites.php",
		data: ( {"id" : id, "del": "Y"} ),
		success: function(html){
			$('.icon-heart').html(html);
		}
	});
}
function add_to_comp(id){
	$.ajax({
		type: "POST",
		url: "/ajax/comparison.php",
		data: ( {"id" : id} ),
		success: function(html){

			$('.icon-rating').html(html);
			//$('.opener-favorites').addClass('bg-none');
		}
	});
}

function delete_to_comp(id){
	$.ajax({
		type: "POST",
		url: "/ajax/comparison.php",
		data: ( {"id" : id, "del": "Y"} ),
		success: function(html){

			$('.icon-rating').html(html);
			//$('.opener-favorites').addClass('bg-none');
		}
	});
}

$( document ).ready(function() {
	$(".close-bb").click(function(e){
		e.preventDefault();
		$(".modal-overlay").removeClass("active");
	});
	
	$(document).on('click', '.product-card__like', function(e){
		e.preventDefault();
		var $self = $(this);
		var dataId = $self.data('id');
		
		if(!$(this).hasClass('_active')){
			$.ajax({
				type: "POST",
				url: "/ajax/add_favorites.php",
				data: ( {"id" : dataId} ),
				success: function(html){
					$self.addClass('_active');
					$('.icon-heart').html(html);
					console.log(html);
				}
			});
		}else{
			$.ajax({
				type: "POST",
				url: "/ajax/add_favorites.php",
				data: ( {"id" : dataId, "del": "Y"} ),
				success: function(html){
					$self.removeClass('_active');
					$('.icon-heart').html(html);
					console.log(html);
				}
			});
		}

	});

	$(document).on('click', '.product-card__compare', function(e){
		e.preventDefault();
		var $self = $(this);
		var dataId = $self.data('id');
		if(!$(this).hasClass('_active')){
			$.ajax({
				type: "POST",
				url: "/ajax/comparison.php",
				data: ( {"id" : dataId} ),
				success: function(html){
					$self.addClass('_active');
					$('.icon-rating').html(html);
					//$('.opener-favorites').addClass('bg-none');
				}
			});
		}else{
			$.ajax({
				type: "POST",
				url: "/ajax/comparison.php",
				data: ( {"id" : dataId, "del": "Y"} ),
				success: function(html){
					$self.removeClass('_active');
					$('.icon-rating').html(html);
					//$('.opener-favorites').addClass('bg-none');
				}
			});
		}
	});

	$(document).on('click', '.number-input__button', function(e){
		e.preventDefault();
		var value = parseInt($(this).parent().find('.number-input__el').val());
		
		if($(this).hasClass('number-input__button--plus') && value <= 199){
			value++;
		}
		
		if($(this).hasClass('number-input__button--minus') && value > 1){
			value--;
		}

		$(this).parent().find('.number-input__el').val(value);
		$(this).parent().find('.number-input__el').trigger('change');
	});

	$(document).on('click', '.js-cookie-yes, .cookie-pane__close', function(e){
		e.preventDefault();
		
		$(this).parents('.cookie-pane').fadeOut(300, function() {
			$(this).remove();
			BX.setCookie('cookie', 'Y', { expires: 604800, path: '/' });
		});
	
	});

	$(document).on('click', '.js-cookie-no', function(e){
		e.preventDefault();
		$(this).parents('.cookie-pane').fadeOut(300);
	});

	if($('.card-color').length > 0) $('.card-color li:first-child a').trigger('click');


	/* upsell slider */

	if($('.u-list').length > 0){
		var upSlider = [];

		$('.u-list').each(function(index, item){

			upSlider[index] = new Swiper($(this)[0], {
				speed: 1000,
				spaceBetween: 30,
				slidesPerView: 6,
				watchOverflow: true,
				pagination: {
					el: $(this).find('.slider-pagination')[0],
					type: 'bullets',
					bulletClass: 'slider-pagination__dot',
					bulletActiveClass: 'slider-pagination__dot--active',
					clickable: true,
					hiddenClass: 'slider-pagination--hidden',
					lockClass: 'slider-pagination--lock',
				},
				breakpoints: {
					576: {
						slidesPerView: 2,
						spaceBetween: 10
					},
					768: {
						slidesPerView: 3,
						spaceBetween: 10
					},
					992: {
						slidesPerView: 4,
						spaceBetween: 40
					}
				},
			});

		});
	}

	if($('.sizes__item').length > 0){
		$('.sizes__item-input input').each(function(){
			$(this).inputmask("decimal", {
				radixPoint: ',',
				inputtype: "text",
				showMaskOnHover: false,
				showMaskOnFocus: false,
				rightAlign: false
			});
		})
	}

	$(document).on('click', '.sizes__item-plus, .sizes__item-plus-label', function(e){
		e.preventDefault();

		if($('.sizes__item').length < 10){

			var index = $(this).parents('.sizes').find('.sizes__item').length;
			var output = roomSizesItem(index, false);

			if (!output) {
				return;
			}

			$(this).parents('.sizes').append(output);

			$('.sizes__item-input input').each(function(){
				if(!$(this).inputmask("hasMaskedValue")){
					$(this).inputmask("decimal", {
						radixPoint: ',',
						inputtype: "text",
						showMaskOnHover: false,
						showMaskOnFocus: false,
						rightAlign: false
					});
				}
			});
		}
	});

	$(document).on('click', '.sizes__item-minus', function(e){
		e.preventDefault();
		$(this).parents('.sizes__item').remove();
	});

	$(document).on('click', '.tooltip__icon', function(e){
		e.stopPropagation();
		
		if(!$(this).next().is(":visible")){
			$('.tooltip__text').fadeOut();
			$(this).next().fadeIn();
		}else{
			$(this).next().fadeOut();
		}
	});

	$(document).on('click touchstart', function(e){
		if ($(e.target).closest(".tooltip").length == 0) {
			$('.tooltip__text').fadeOut();
		}
	});

	if($('.p-single-slider').length > 0 && !window.matchMedia("(max-width: 767px)").matches){
		$(".p-single-slider .p-single-slider__item img").each(function(){
			$(this).magnify({
				speed: 200,
			});
		});
	}

	if($('.p-single-slider').length > 0){
		console.log(2);
		var prodSlider = [];
		var options = {};

		options.speed = 1000;
		options.spaceBetween = 0;
		options.effect = 'fade';
		options.fadeEffect = {
			crossFade: true,
		};

		$('.p-single-slider').each(function(index, item){
			
			if($(this).next().length > 0){
				options.thumbs = {
					swiper: {
						el: $(this).next()[0],
						slidesPerView: 4,
						spaceBetween: 15,
					}
				};
			}

			prodSlider[index] = new Swiper($(this)[0], options);
		});
		
	}

	$(document).on('click', '.js-get-pane', function(e){
		e.preventDefault();
		var href = $(this).attr('href');
		if($('.mobile-pane._open').length > 0){
			$('.mobile-pane._open').removeClass("_open");
		}
		$(this).addClass("active"); 
		$(".mobile-pane"+href).addClass('_open');
		$("body").addClass('_menu-open');
	});

	$(document).on('click', '.mobile-pane__close', function(e){
		e.preventDefault();
		$(this).parents('.mobile-pane').removeClass("_open");
		$('.nav-toggle').removeClass("active"); 
		$("body").removeClass('_menu-open');

		setTimeout(function() {
			$('.catalog-pane .dropdown-menu').removeClass('_hidden').removeClass('_active').removeClass('_open');
			$('.catalog-pane__back').removeClass('_show');
			$(this).parent().children('span').text('VŠETKY KATEGÓRIE');
		}, 300);
		
	});

	$(document).on('click', '.catalog-pane__nav li.dropdown > a', function(e){
		e.preventDefault();
		var catLinkText = $(this).text();
		$(this).parent().children('.dropdown-menu').addClass('_open').addClass('_active');
		$(this).parents('.mobile-pane').find(".mobile-pane__header span").text(catLinkText);
		
		$('.catalog-pane__nav .dropdown-menu').animate({
			scrollTop: 0,
		}, 0);

		if(!$('.catalog-pane__back').hasClass('_show')) $('.catalog-pane__back').addClass('_show');

		if($(this).parents('.dropdown-menu').length > 0){
			$(this).parents('.dropdown-menu').addClass('_hidden').removeClass('_active');
		}
	});

	$(document).on('click', '.catalog-pane__back', function(e){
		e.preventDefault();
		var parent = $('.catalog-pane__nav .dropdown-menu._active').parents('.dropdown-menu');
		var headeText = parent.prev().text();
		if(parent.length > 0){
			$(this).parent().children('span').text(headeText);
		}else{
			$(this).parent().children('span').text('VŠETKY KATEGÓRIE');
		}
		$('.catalog-pane__nav .dropdown-menu._open._active').removeClass('_active').removeClass('_open');
		if(parent.length > 0){
			parent.removeClass('_hidden').addClass('_active');
		}else{
			$('.catalog-pane__back').removeClass('_show');
		}
	});
	
	$(".js-open-filter").click(function(e) {
		e.preventDefault();
		$('.sidebar').addClass("open");
		$('body').addClass("_lock");
	});

	$(".sidebar-close").click(function(e) {
		e.preventDefault();
		$('.sidebar').removeClass("open");
		$('body').removeClass("_lock");
	});

	
	
});
