	//
	// mobile menu
	//
 
function navToggle(){
    $('.nav-toggle').click(function(e){
        e.preventDefault();
        $(this).toggleClass('active');
        $('.header-nav').slideToggle();
    }); 		
};
	

	//
	// mobile menu ...end;
	//


 
 
function openModal(){
	$('[data-target]').click(function(e){
		e.preventDefault();
		
		var target = $(this).attr('data-target');
		$('#' + target).toggleClass('active');
//		$('body').css('overflow-y','hidden');
	});
	
	$('.modal-close, .overlay-close').click(function(e){
		e.preventDefault();
		$('.modal-overlay').removeClass('active');
//		$('body').css('overflow-y','auto');
	});		
}
 
 
function slider(){
    $('.main-slider').owlCarousel({
        items:1,
        loop:true,
		nav: true,
		dots: true,
        margin:0,
		autoplay: true,
		autoplayTimeout: 5000,
		autoplaySpeed: 1000,
//		smartSpeed: 1000,
//		animateOut: 'fadeOut',
//		animateIn: 'fadeIn',
//		mouseDrag: false
    });		
	
    $('.review-slider').owlCarousel({
        items:1,
        loop:false,
		nav: true,
        margin:10,
    });		    
	$('.tearm-slider').owlCarousel({
        items:1,
        loop:false,
		nav: true,
        margin:10,
		responsive:{
			0:{
				items:1 
			},
			480:{
				items:2
			},			
			768:{
				items:3
			}
		}		
    });			
}


function openCategory(){
	$('.category-btn').click(function(e){
		e.preventDefault();
		
		$('.submenu').toggleClass('active');
	});
	
	$(document).click(function(e){
		var drop = $('.submenu');
		if  ($(e.target).closest('.header-nav__category').length===0) {
				drop.removeClass('active');
		}
	});		
}

function sidebarItem(){
	$('.sidebar-title').click(function(e){
		// клик по значку подсказки не должен сворачивать блок фильтра
		if ($(e.target).closest('.sidebar-item__tooltip').length) {
			return;
		}

		e.preventDefault();
		$(this).closest('.sidebar-item').toggleClass('open');
	});
}

 
function openFilter(){
	$('.btn-filter').click(function(e){
		e.preventDefault();
		
		$('.sidebar').toggleClass('open');
	});
}
function tabs(){
	$('[data-tab]').click(function(e){
		e.preventDefault();
		var tab = $(this).attr('data-tab');
		
		$('.tab').removeClass('active');
		$('[data-tab]').removeClass('active');
		$('.'+ tab).addClass('active');
		$(this).addClass('active');
	});
} 

$(document).ready(function(){  
    navToggle();
	openModal();
	slider();
	openCategory();
	sidebarItem();
	openFilter();
	tabs();
	
	$('.js-select').styler();
 
 	if($(window).width() <= 992){
		toggleM();
	}
	
    $('.js-selectize').selectize({
        create: true,
        sortField: {
            field: 'text',
            direction: 'asc'
        }
    });   
    	
	
	
	$('.card-slider').lightSlider({
      gallery:true,
      item:1,
      vertical:true,
      verticalHeight:560,
      vThumbWidth:65,
      thumbItem:8,
      thumbMargin:10,
      slideMargin:0,
       responsive : [
            {
                breakpoint:992,
                settings: {
                    verticalHeight:400,
					thumbItem:5,
                  }
            },
            {
                breakpoint:500,
                settings: {
                    verticalHeight:350,
					thumbItem:4,
                  }
            }		   
        ]		
    });  
});    
 

function toggleM(){
	$('.dropdown > a').click(function(e){
		e.preventDefault();
		$(this).toggleClass('active');
		$(this).closest('.dropdown').find('.dropdown-menu').stop().slideToggle();
	});
}

$(window).resize(function(){  
 	if($(window).width() <= 992){
		toggleM();
	} 	
});    

 $(function() {

  $(".product__count").prepend('<div class="dec button">-</div>');
  $(".product__count").append('  <div class="inc button">+</div>');	

  $(".button").on("click", function() {

    var $button = $(this);
    var oldValue = $button.parent().find("input").val();

    if ($button.hasClass('inc')) {
  	  var newVal = parseFloat(oldValue) + 1;
  	} else {
	   // Don't allow decrementing below zero
      if (oldValue > 1) {
        var newVal = parseFloat(oldValue) - 1;
	    } else {
        newVal = 1;
      }
	}

    $button.parent().find("input").val(newVal);

  });

});