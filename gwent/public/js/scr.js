var language = 'ru';

jQuery.browser = {};
jQuery.browser.mozilla = /mozilla/.test(navigator.userAgent.toLowerCase()) && !/webkit/.test(navigator.userAgent.toLowerCase());
jQuery.browser.webkit = /webkit/.test(navigator.userAgent.toLowerCase());
jQuery.browser.opera = /opera/.test(navigator.userAgent.toLowerCase());
jQuery.browser.msie = /msie/.test(navigator.userAgent.toLowerCase());

var scroller=jQuery.browser.webkit ? "body": "html";

/* modernize */
function modernize() {
	// placeholder
	if(!Modernizr.input.placeholder){
		$('[placeholder]').each(function() {
			$(this).watermark($(this).attr('placeholder'));
		});
	}
}


/* input only Number  */
function inputNumber(block, options) {

    var callbacks = {
        keyupFunc:null,
        keydownFunc:null,
        blurFunc:null
    }

    $.extend(callbacks, options);



    $(block).each(function() {

        var item = $(this);

    	item.find('input').keypress(function(e) {

            var charCode = (e.which) ? e.which : e.keyCode;

    		if (($(this).val().length != 0 && (charCode >= 47 && charCode <= 57)) || ($(this).val().length == 0 && (charCode >= 49 && charCode <= 57))){
                if(callbacks.keydownFunc != null){
                    callbacks.keydownFunc($(this));
                }
            }
    		else return false;
    	});

    	item.find('input').keyup(function() {

    		$inputNum = $(this);

    		if(item.data('max') != 'undefined' && $inputNum.val() > parseInt(item.data('max'))){
				$inputNum.val(item.data('max'));
            }

            if(callbacks.keyupFunc != null){
                callbacks.keyupFunc($(this));
            }

    	});

        item.find('input').blur(function(){


            var text = $(this).val();

            if(!/^\d+$/.test(text)){

                if(item.data('min') != 'undefined'){
                    $(this).val(item.data('min'));
                }else{
                    $(this).val('');
                }

            }else{
                if((item.data('min') == 'undefined') && (item.find('input').val == '' || item.find('input').val() == 0)){
                    item.val('');
                }else if((item.data('min') != 'undefined') && (item.find('input').val == '' || item.find('input').val() < parseInt(item.data('min')))){
                    item.find('input').val(item.data('min'));
                }
            }

            if(callbacks.blurFunc != null){
                callbacks.blurFunc($(this));
            }

        });

    });

}

/* u_tabs */
function u_tabs(link, block) {
	$(link).click(function(e) {
		var $currentTab = $(this);
		var tabId = $currentTab.data('utab');

		$(link).removeClass('active');
		$currentTab.addClass('active');

		$(block).hide().removeClass('active');
		$(block+'[data-utab="' + tabId + '"]').show().addClass('active');
		if($(link).is('a')){
			e.preventDefault();
		}
	});
	$(link).eq(0).click();
}

/* scrollUp */
function scrollUp(block,targetBlock) {

	$(block).click(function(e){
		var target = $(targetBlock).offset().top;

		$(scroller).animate({scrollTop:target},800);
		return false;

		e.preventDefault();
	});
}

function oneHeightItems(){

	function oneHeight(block){
		var height=0;
		block.removeAttr('style');
		block.each(function(){
			if($(this).height()>height){
				height=$(this).height();
			}
		});
		block.css('height', height);
	}

	oneHeight($('.oneHeight'));
}

function selectStyled(){
	$('.style-select').styler();
	$('.date-select').styler({
		selectPlaceholder:'Дата рождения'
	});
	/*
	$('.male-select').styler({
		selectPlaceholder:'Пол'
	});
	*/
	if (language == 'ru') {
        $('.male-select').styler({
            selectPlaceholder: 'Выроб расы'
        });
    };
    if (language == 'en') {
        $('.male-select').styler({
            selectPlaceholder: 'Select rase'
        });
    };
    if (language == 'ua') {
        $('.male-select').styler({
            selectPlaceholder: 'Выроб расы'
        });
    };
}



// loading info about cards by class 'card-load-info'

function contentCardLoad(){

	$('.card-load-info').each(function(){

		var background = $(this).data('mainimg');
		var value = $(this).data('value');
		var subTypeMas = $(this).data('subtypemas').split(',');
		var titleText = $(this).data('title');
		var icon = $(this).data('icon');
		var contentText = $(this).data('contenttext');
		var cardType = $(this).data('cardtype');
		var cardRole = $(this).data('cardrole');

		if(cardType != undefined){
			$(this).addClass(cardType).removeAttr('data-cardtype');
		}

		$(this).addClass(cardType).removeClass('card-load-info').removeAttr('data-mainimg data-value data-subtypemas data-title data-icon data-contenttext data-cardrole');
		// remove all unusless attr and classes

		$(this).css({'background-image':'url('+background+')'});
		$(this).find('.label-power-card-wrap span').text(value);
		$(this).find('.card-game-status-role').append('<span class="'+cardRole+'"></span>');
		for(var i=0;i<subTypeMas.length;i++){
			$(this).find('.card-game-status-wrap').append('<span class="'+subTypeMas[i]+'"></span>');
		}
		$(this).find('.card-name-property p').text(titleText);
		$(this).find('.block-image-describe').prepend('<img src="'+icon+'" alt="" />');
		$(this).find('.block-text-describe-main-wrap').html(contentText);

	});

}

function cardOpenFancybox(){

	var timerFancy = null;

	$(document).on('click','.fog', function(){
    	$('.cart-open').removeClass('open');
    	setTimeout(function(){
    		$.fancybox.close();
    	},750);
	})

    $(document).on('click', '.card-popup', function(event) {

    	var itemHtml = $(this).parent().html();

        $.fancybox([{
            href : '#cart-open',
            wrapCSS:'fancybox-card-open',
            'closeBtn' : false,
            fitToView:true,
            padding:'0',
            helpers:{
            	overlay:{
            		closeClick:false
            	}
            },
            beforeLoad:function(){

            	$('.cart-open').html(itemHtml).find('.card-popup').removeClass('card-popup'); //reaquired to remove

                var cardRace = $('.cart-open .content-card-item-main').attr('data-race');

                $('.cart-open').addClass(cardRace);

            	$('.cart-open .block-text-describe-wrap').jScrollPane({
			        contentWidth: '0px',
			        autoReinitialise:true,
			        showArrows:true,
			        verticalDragMaxHeight:20
			    });

            },
            afterLoad:function(){
            	$('.fancybox-overlay').addClass('fog'); //adding fog
            },
            afterShow:function(){
            	$('.cart-open').addClass('open');
            }
        }]);

    });

}

// /loading info about cards by class 'card-load-info'

// artem help (card moving to prevent field)

function cardMoving(){

	//select card
	function selectCard(){
		$('.user-card-stash>#sortable-user-cards>li').click(function(){
	        resetFields();
	        $('#sortable-user-cards>li').removeClass('chossen-card').addClass('grayscl');
	        $('#sortable-user-cards>li').css('margin-top', '0px');
	        $(this).addClass('chossen-card');
	        $('#sortable-user-cards>li.chossen-card').removeClass('grayscl');
	        $('#notSortableOne').html($(this).clone().removeAttr('style'));
	        $(this).css('margin-top', '-40px');

	        $('#notSortableOne').addClass('show');

	        fieldsShow($(this));

	    });
	}

	// fieltering field by cliking card
	function fieldsShow(item){

    	var type = item.find('.card-game-status-role span').attr('class');

    	$('.user .field-for-cards').addClass('unselectable-row grayscl');

    	if(type == 'bow'){
    		$('.user .field-for-cards.field-range').removeClass('unselectable-row grayscl').addClass('swapable');
    	}
    	else if(type == 'axe'){
    		$('.user .field-for-cards.field-meele').removeClass('unselectable-row grayscl').addClass('swapable');
    	}
    	else if(type == "lighting"){
    		$('.user .field-for-cards.field-super-renge').removeClass('unselectable-row grayscl').addClass('swapable');
    	}
    	else if(type == 'bow-axe'){
    		$('.user .field-for-cards.field-meele, .user .field-for-cards.field-range').removeClass('unselectable-row grayscl').addClass('swapable');
    	}
    	else if(type == 'lighting-bow'){
    		$('.user .field-for-cards.field-range, .user .field-for-cards.field-super-renge').removeClass('unselectable-row grayscl').addClass('swapable');
    	}
    	else if(type == 'lighting-axe'){
    		$('.user .field-for-cards.field-meele, .user .field-for-cards.field-super-renge').removeClass('unselectable-row grayscl').addClass('swapable');
    	}
    	else{
    		$('.user .field-for-cards').removeClass('unselectable-row grayscl').addClass('swapable');
    	}

	}

	//function card position

	function cardPositon(cardList){

		var cardsCount = cardList.length;
		var cardsPosValue = 100/(cardsCount+1);
		var currentValue = cardsPosValue;

		cardList.each(function(){
			$(this).css({'left': 'calc('+currentValue+'% - 45px)'});
			currentValue+=cardsPosValue;
		});

	}

	// card anim after chosing field
	function cardAnim(){

		$(document).on('click', '.field-for-cards.swapable', function(){

			// anim params
			var field = $(this);
			var bigCardPosLeftWrap = $('#sortable-user-cards').offset().left;
			var fieldsLeftPos = $('.fields-for-cards-wrap').offset().left;
			var fieldsLeftPosCenter = fieldsLeftPos + ($('.fields-for-cards-wrap').width()/2);
			var typeField = field.data('fieldtype');
			var topPos = field.offset().top;
			var bigCardPosTop = $('#sortable-user-cards .chossen-card').offset().top;
			var topPosDiferens = topPos - bigCardPosTop;
			var cardMovePos = null;

			var bigCardPosLeft = $('#sortable-user-cards .chossen-card').offset().left;

			if(field.find('li').length == 0){
				cardMovePos = (bigCardPosLeft - bigCardPosLeftWrap - 60) + (fieldsLeftPosCenter - bigCardPosLeft);
			}
			else{
				cardMovePos = (bigCardPosLeft - bigCardPosLeftWrap - 60) + (field.find('li:last-child').offset().left - bigCardPosLeft);
			}
			$('#sortable-user-cards .chossen-card').addClass('moving-card').css({'left':cardMovePos+'px', 'top':topPosDiferens + 'px', 'opacity':'0'});

			setTimeout(function(){

				var item = $('#sortable-user-cards .chossen-card').remove();

				field.find('ul').append(item).find('.moving-card').removeAttr('style').removeClass('moving-card chossen-card');
				cardPositon(field.find('li'));
				cardPositon($('#sortable-user-cards li'));
				$('#notSortableOne li').remove();

			}, 400);

			$('#sortable-user-cards li').removeClass('grayscl');
			$('.field-for-cards').removeClass('unselectable-row grayscl');
			$('#notSortableOne').removeClass('show');


		});

	}

	// --- //

	selectCard();
	cardAnim();

	// sort all cards

	$('.sort').each(function(){
		cardPositon($(this).find('li'));
		$(this).removeClass('sort');
	});

};

// /artem help (card moving to prevent field)


/* DOCUMENT READY  */
$(document).ready(function() {

	//modernize();
	$('.footer_placeholder').height($('.footer').outerHeight());

	selectStyled();

	oneHeightItems();

	contentCardLoad();

	cardOpenFancybox();

	cardMoving();

    buyMorePopup();

    inputNumber('.num-only');

});

$(window).load(function(){

});

$(window).resize(function() {

    $('.footer_placeholder').height($('.footer').outerHeight());
});