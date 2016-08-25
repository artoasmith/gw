/*
* /main page
*/
function fancyboxForm(){
	$('.fancybox-form').fancybox({
		openEffect  : 'fade',
		closeEffect : 'fade',
		autoResize:true,
		wrapCSS:'fancybox-form',
		'closeBtn' : true,
		fitToView:true,
		padding:'0'
	})
}

//Форма логинизации на главной
function showFormOnMain(){
    //При нажатии на кнопку "вход"
	$('.forget-pass-form button').click(function(event){
		if ($(this).hasClass('show-form-please') ){
			event.preventDefault();
			$('.form-wrap-for-rows').slideDown(500);
			$(this).removeClass('show-form-please');
		}
	});
    //Не скрывать форму входа при возврате ошибки
	if( !$('.forget-pass-form button').hasClass('show-form-please') ){
		$('.form-wrap-for-rows').slideDown(500);
	}
}

function showWindowAboutOnMain() {
	$('.drop-menu-open').click(function(e){
		e.preventDefault();
		$(this).css('pointer-events', 'none');
		var that = $(this);
		if( !$(this).hasClass('drop-menu-hide') ) {
			$('.convert-about').slideDown(500, function(){
				$('.button-dropdown').css('top', $('.convert-about').height() + 20 );
				that.css('pointer-events', 'auto');
			});
			that.addClass('drop-menu-hide');
		} else {
			$('.convert-about').slideUp(300, function(){
				that.css('pointer-events', 'auto');
			});
			$('.button-dropdown').css('top', 93 );
			$(this).removeClass('drop-menu-hide');
		}
	});
}
// end of /main


/*
* /settings
*/
//Получить данные пользователя
//Если user_login не указан, возвращает данные текущей сессии
function getUserData(user_login){
	$.ajax({
		url:    '/get_user_data',
		type:   'GET',
		data:   {login: user_login},
		success:function(data){
			if(user_login != ''){
				var res = JSON.parse(data);
				if(res['avatar'] != ''){
					$('.user .user-image').append('<img src="/img/user_images/' + res['avatar'] + '" alt="">');
				}
				$('.rating .resurses .gold').text(res['gold']);
				$('.rating .resurses .silver').text(res['silver']);
				$('.rating .resurses .lighting').text(res['energy']);
				$('.preload .preloader, .convert-resurses .preload-resurses').hide();
				$('.preload .user-name, .rating .convert-resurses .resurses').css('opacity', '1');
				window.maxCardQuantity		= res['maxCardQuantity'];
				window.minWarriorQuantity	= res['minWarriorQuantity'];
				window.specailQuantity		= res['specialQuantity'];
				window.leaderQuantity		= res['leaderQuantity'];
				window.leagues				= res['leagues'];
				window.exgange_gold			= res['exchanges']['usd_to_gold'];
				window.gold_to_silver		= res['exchanges']['gold_to_silver'];
				window.gold_to_100_energy	= res['exchanges']['gold_to_100_energy'];
				window.gold_to_200_energy	= res['exchanges']['gold_to_200_energy'];
				window.silver_to_100_energy = res['exchanges']['silver_to_100_energy'];
				window.silver_to_200_energy = res['exchanges']['silver_to_200_energy'];
				window.user_gold			= res['gold'];
			}
		}
	});
}

function settingsInputFile(){
	$('.form-description-settings-inp-wrap input').styler({
		fileBrowse:" ",
		filePlaceholder:"Сменить аватар"
	});
}

//Изменение пользовательских настроек
function applySettings(){
    $('.form-wrap-input button[name=settingsChange]').click(function(e){
        e.preventDefault();
        var token = $('input[name=_token]').val();

        var formData = new FormData();

        formData.append( 'token', token );
        formData.append( '_method', 'PUT');
        formData.append( 'settings_email', $('.form-wrap-value input[name=settings_email]').val().trim());
        formData.append( 'current_password', $('.form-wrap-value input[name=current_password]').val().trim());
        formData.append( 'settings_pass', $('.form-wrap-value input[name=settings_pass]').val().trim());
        formData.append( 'settings_pass_confirm', $('.form-wrap-value input[name=settings_pass_confirm]').val().trim());
        formData.append( 'image_user', $('.form-description-settings-inp input[name=image_user]').prop('files')[0] );
		formData.append( 'user_name', $('.form-wrap-item input[name=settings_name]').val().trim() );
		formData.append( 'birth_date', $('.form-wrap-item input[name=settings_birth_date]').val().trim() );
		formData.append( 'gender', $('.form-wrap-item select[name=settings_gender]').val().trim() );
        formData.append( 'action', 'user_settings' );

        $.ajax({
            url:        '/settings',
            headers:    {'X-CSRF-TOKEN': token},
            type:       'POST',
            processData:false,
            contentType:false,
            data:       formData,
            success:    function(data){
                if(data == 'success') {
                    location = '/settings';
                }else{
                    $('.form-wrap-for-rows .error-text').text(JSON.parse(data)).show();
                }
            }
        });
    });
}

//обновление изображения пользователя
function settingUpdateImg(){
    $('.form-description-settings-inp input[name=image_user]').change(function(e){
        
        var reader = new FileReader();

        reader.onload = function (e) {
            if( $('.form-description-settings-img .form-description-settings-img-wrap #avatarImg').length > 0 ){
                $('.form-description-settings-img .form-description-settings-img-wrap #avatarImg').attr('src', e.target.result);
            }else{
                $('.form-description-settings-img .form-description-settings-img-wrap').append('<img id="avatarImg" src="" alt="">');
                $('.form-description-settings-img .form-description-settings-img-wrap #avatarImg').attr('src', e.target.result);
            }

        }
        reader.readAsDataURL( $(this).prop('files')[0] );
    });
}

//end of /settings


/*
* /deck
*/
//Построение Отображения карты в колоде
//data - данные карты
//wraper - обертка для карты
function buildCardDeckView(cardData, wraper){
	var result = '' +
		'<div class="content-card-item-main" style="background-image: url(/img/card_images/'+cardData['img_url']+')" data-leader="'+cardData['is_leader']+'" data-type="'+cardData['type']+'" data-weight="'+cardData['weight']+'">' +
			'<div class="content-card-item-main card-load-info card-popup">' +
				'<div class="maxCountInDeck-wrap">' +
					'<span class="current-card-type-count"></span>/<span class="current-max-card-count">'+cardData['max_quant']+'</span>' +
				'</div>' +
				'<div class="label-power-card"><span class="label-power-card-wrap"><span>'+cardData['strength']+'</span></span></div>' +
				'<div class="hovered-items">' +
					'<div class="card-game-status">' +
						'<div class="card-game-status-role"><span class="lghting"></span></div>' +
						'<div class="card-game-status-wrap">' +
							'<span class="knife"></span>' +
							'<span class="knife"></span>' +
							'<span class="ninja"></span>' +
							'<span class="hand"></span>' +
						'</div>' +
					'</div>' +
					'<div class="card-name-property"><p>'+cardData['title']+'</p></div>' +
					'<div class="block-describe">' +
						'<div class="block-image-describe"></div>' +
						'<div class="block-text-describe">' +
							'<div class="block-text-describe-wrap">' +
								'<div class="block-text-describe-main">' +
									'<div class="block-text-describe-main-wrap"><p>'+cardData['descr']+'</p></div>'+
								'</div>' +
							'</div>' +
						'</div>' +
					'</div>' +
				'</div>' +
			'</div>' +
		'</div>';

	if(wraper == 'ul'){
		result = '<li class="content-card-item" data-cardId="'+cardData['id']+'">'+result+'</li>';
	}

	if(wraper == 'div'){
		result = '' +
			'<div class="market-cards-item" data-card="'+cardData['id']+'">'+result+
				'<div class="market-card-item-price">ЦЕНА '+
					'<div class="cfix">';

		if(cardData['gold'] != 0){
			result += '' +
						'<div class="marker-price-gold">'+cardData['gold']+'</div>';
		}
		if(cardData['silver'] != 0){
			result += '' +
						'<div class="marker-price-silver">'+cardData['silver']+'</div>';
		}
		if((cardData['silver'] != 0) || (cardData['gold'] != 0)) {
			result += '' +
					'</div>' +
				'</div>' +
				'<div class="market-card-item-buy"><a href="#" class="button-buy" id="simpleBuy">КУПИТЬ</a></div>';
		}

		if(cardData['only_gold'] != 0){
			result += '' +
				'<div class="market-card-item-price">ТОЛЬКО СЕРЕБРО' +
					'<div class="cfix">' +
						'<div class="marker-price-silver">'+cardData['only_gold']+'</div>' +
					'</div>' +
				'</div>' +
				'<div class="market-card-item-buy"><a href="#" class="button-buy" id="goldOnlyBuy">КУПИТЬ ЗА СЕРЕБРО</a></div>';
		}
		result += '' +
			'</div>';
	}

	return result;
}

//Формирование колод пользователя и свободных карт
function getUserDeck(deck, user_login){
	$.ajax({
		url:    '/get_user_deck',
		type:   'GET',
		data:   {deck:deck, login: user_login},
		success:function(data){
			var res = JSON.parse(data);

			$('.content-card-field ul#sortableTwo, .content-card-field ul#sortableOne').empty();

            //Формирование доступных карт
			for(var i=0; i<res['available'].length; i++){
				var available = res['available'][i];
				for(var j = 0; j<available['quantity']; j++){
					$('.content-card-field ul#sortableTwo').append(buildCardDeckView(available, 'ul'));
				}
			}

            //Формирование Карт Колоды
			for(var i=0; i<res['in_deck'].length; i++){
				var in_deck = res['in_deck'][i];
				for(var j = 0; j<in_deck['quantity']; j++){
					$('.content-card-field ul#sortableOne').append(buildCardDeckView(in_deck, 'ul'));
				}
			}

            //Пересчет данных колоды
			recalculateDeck();
		}
	});
}

//скролл
function initScrollpane() {
	$('.scroll-pane, .market-cards, .market-cards-wrap').jScrollPane({
		contentWidth: '0px',
		autoReinitialise: true
	});
}

//Фикс перетягивания колоды
function underDragCardFix() {
	if ($('.content-card-field')) {
		$('.content-card-field').mouseleave(function (event) {
			$(document).mouseup();
		});
	}
}

//пересчет коллоды
function recalculateDeck(){
	var cardsCount = 0;
	var warriorsQuantity = 0;   //Количество воинов
	var specialQuantity = 0;    //Количество спец карт
	var deckWeight = 0;         //Вес колоды
	var league = '';            //Лига колоды (уровень)
	var leaderQuantity = 0;     //Количество карт лидеров
	var cardsDeck = {};
	$('#sortableOne .content-card-item').each(function(){

		if(cardsDeck[$(this).attr('data-cardid')] === undefined){
			cardsDeck[$(this).attr('data-cardid')] = 1;
		}else{
			cardsDeck[$(this).attr('data-cardid')]++;
		}

		cardsCount++;
        //Перечет карт воинов и спец карт
		if($(this).children('.content-card-item-main').attr('data-type') != 'special'){
			warriorsQuantity++;
		}else{
			specialQuantity++;
		}
        //пересчет карт-лидеров
		if($(this).children('.content-card-item-main').attr('data-leader') == '1'){
			leaderQuantity++;
		}
        //Вес колоды
		deckWeight += parseInt($(this).children('.content-card-item-main').attr('data-weight'));
	});

	for(var key in cardsDeck){
		var currentCardCount = $('#sortableOne .content-card-item[data-cardid='+key+'] .card-load-info .maxCountInDeck-wrap .current-card-type-count');
		var maxCardCount = parseInt($('#sortableOne .content-card-item[data-cardid='+key+'] .card-load-info .maxCountInDeck-wrap .current-max-card-count').text());
		currentCardCount.text(cardsDeck[key]);
		if( parseInt(currentCardCount.text()) > maxCardCount ){
			currentCardCount.parent().css({'color':'#e00'});
		}else{
			currentCardCount.parent().css({'color':'#ef0'});
		}
	}

    //Подсчет лиги
	for(var i=0; i<window.leagues.length; i++){
		if(deckWeight > window.leagues[i]['min_lvl']){
			league = window.leagues[i]['title'];
		}
	}

	$('.content-card-center-block .content-card-center-description-block .deck-card-sum').text(cardsCount);
	$('.content-card-center-block .deck-warriors .current-value').text(warriorsQuantity);
	$('.content-card-center-block .deck-special .current-value').text(specialQuantity);
	$('.content-card-center-block .deck-cards-power').text(deckWeight);
	$('.content-card-center-block .deck-league').text(league);
	$('.content-card-center-block .deck-liders .current-value').text(leaderQuantity);

}

//отправка данных о колодах
//deck   - название колоды
//cardId - id карты
//source - панель колоды(левая правая)
function sendUserDeck(deck, cardId, source){
	var token = $('input[name=_token]').val();
	var formData = new FormData();
	//Наполнение формы
	formData.append( 'token', token );
	formData.append( '_method', 'PUT');
	formData.append( 'deck', deck);
	formData.append( 'card_id', cardId);
	formData.append( 'source', source);

	$.ajax({
		url:        '/change_user_deck',
		headers:    {'X-CSRF-TOKEN': token},
		type:       'POST',
		processData:false,
		contentType:false,
		data:       formData,
		success:    function(){
			//пересчет колоды
			recalculateDeck();
		}
	});
}

//перетягивание
function draggableCards() {
	$.ajax({
		url:	'/check_user_is_plying_status',
		type:	'GET',
		success:function(data) {
			if (data != 0) {
				var res = JSON.parse(data);

				$("#sortableOne, #sortableTwo").sortable({
					cancel:		'.ui-sortable-handle',
					stop: function () {
						$('#buyingCardOrmagic .popup-content-wrap').html('<p>' + res['message'] + '</p>');
						$('#buyingCardOrmagic').show(300).delay(3000).hide(400);
					}
				}).disableSelection();

			} else {

				$("#sortableOne, #sortableTwo").sortable({
					connectWith:	".connected-sortable",
					stop:
						function(e, ui){
							if($(this).attr('id') != ui.item.parent().attr('id')) {
								var error = 1;

								//перетягивание из колоды пользователя
								if ($(this).attr('id') == 'sortableOne') {
									var source = 'user_deck';
									error = 0;
								}

								//перетяггивание из доступных карт
								if ($(this).attr('id') == 'sortableTwo') {
									var source = 'available';
									error = 0;
								}

								//перетягивание происходит не в одной и той же панель
								if (0 == error) {
									var deck = $('.content-card-field-center-wrap .content-card-select select').val();
									var cardId = ui.item.attr('data-cardid');
									//сохранение колоды
									sendUserDeck(deck, cardId, source);
								}
							}
						}
				}).disableSelection();

			}
		}

	});
}

//end of /deck


/*
* /market
*/

function showInsuficientMoney(){
    $('#buyingCardOrmagic .popup-content-wrap').html('' +
        '<p>У вас недостаточно денег</p>' +
        '<p><a class="buy-more-gold" href="#">Купить золота</a>' +
        '<a class="buy-more-silver" href="#">Наменять серебра</a></p>');
    $('#buyingCardOrmagic').show(300);
}

//Пользователь хочет купить карту
function userByingCard(){

	$('.content-card-wrap-main .market-card-item-buy').on('click', '.button-buy', function(e){

		e.preventDefault();
		var id = $(this).parents('.market-cards-item').attr('data-card');
		var buyType = $(this).attr('id');

		$.ajax({
			url: '/check_user_is_plying_status',
			type: 'GET',
			success: function (data) {
				if (data != 0) {

					var res = JSON.parse(data);
					showErrorMessage(res['message']);

				}else{
					$.ajax({
						url:        '/get_card_data',
						type:       'GET',
						data:       {card_id: id, buy_type:buyType},
						success:    function(data){
							var res = JSON.parse(data);

							if(res['message'] == 'success'){

								var result = confirm('Вы действительно хотите купить карту '+res['title']+'?');
								if(result === true){
									var token = $('#buyingCardOrmagic input[name=_token]').val();

									res['user_gold'] = parseInt(res['user_gold']);
									res['user_silver'] = parseInt(res['user_silver']);
									res['price_gold'] = parseInt(res['price_gold']);
									res['price_silver'] = parseInt(res['price_silver']);

									if( (res['user_gold'] < res['price_gold']) || (res['user_silver'] < res['price_silver']) ){
										showInsuficientMoney();
									}else{
										$.ajax({
											url:    '/card_is_buyed',
											type:   'POST',
											headers:{'X-CSRF-TOKEN': token},
											data:   {card_id: id, buy_type:buyType},
											success:function(data){
												var res = JSON.parse(data);
												if(res['message'] == 'success'){
													refreshRosources(res);
													showErrorMessage('<p>Карта "'+res['title']+'" стала доступной.</p>');
												}
											}
										});
										//end ajax card_is_buyed
									}
								}

							}else{
								alert(res['message']);
							}
						}
					});
					//end ajax get_card_data

				}
			}
		});
		//end ajax check_user_is_plying_status
	});

}

//Украшение селекта рас
function marketSelection(){
	if($('.selection-rase select').length > 0){
		$('.selection-rase select').styler({
			selectSmartPositioning:'-1'
		});
		$('.selection-rase-img').click(function() {
			$('.selection-rase .jq-selectbox__dropdown').show();
			setTimeout(function(){
				$('.selection-rase .jq-selectbox').addClass('opened');
			},200);
		});
	}
}
//end of /market

/*
*	Magic
*/
//Создание отображения таблицы "Волшебства" :3
function buildMagicEffectsView(data){
	return '<tr>' +
		'<td class="no-border"><a href="#" class="button-plus" data-type="' + data['id'] + '"></a></td>' +
		'<td class="effect-img"><img src="img/card_images/' + data['img_url'] + '" alt="" /></td>' +
		'<td class="effect-title">' + data['title'] + '</td>' +
		'<td class="effect-descript">' + data['descr'] + '</td>' +
		'<td class="energy-effect">' + data['energy'] + '</td>' +
		'<td class="gold-tableCell">' + data['gold'] + '</td>' +
		'<td class="silver-tableCell">' + data['silver'] + '</td>' +
		'<td class="market-status-wrap done"><div class="market-status ' + data['status'] + '"><span></span></div></td>' +
		'<td class="effect-date">' + data['used_times'] + '</td>' +
		'</tr>';
}

//пользователь покупает волшебство
function userByingMagic(){
	$('.main-table tr td .button-plus').click(function(e){
		e.preventDefault();
		var id = $(this).attr('data-type');

		$.ajax({
			url:	'/check_user_is_plying_status',
			type:	'GET',
			success:function(data) {
				if (data != 0) {

					var res = JSON.parse(data);
					showErrorMessage(res['message']);

				} else {
					$.ajax({
						url:	'/get_magic_effect_data',
						type:	'GET',
						data:	{magic_id:id},
						success:function(data){
							var res = JSON.parse(data);

							var result = confirm('Вы действительно хотите купить карту '+res['title']+'?');
							if(result === true){

								var token = $('#buyingCardOrmagic input[name=_token]').val();
								res['user_gold'] = parseInt(res['user_gold']);
								res['user_silver'] = parseInt(res['user_silver']);
								res['price_gold'] = parseInt(res['price_gold']);
								res['price_silver'] = parseInt(res['price_silver']);
								if( (res['user_gold'] < res['price_gold']) || (res['user_silver'] < res['price_silver']) ){
									showInsuficientMoney();
								}else{
									$.ajax({
										url:	'/magic_is_buyed',
										type:   'POST',
										headers:{'X-CSRF-TOKEN': token},
										data:   {magic_id: id},
										success:function(data){
											var res = JSON.parse(data);
											if(res['message'] == 'success'){
												$('.main-table tr a[data-type="'+id+'"]').parent().parent().children('.market-status-wrap').children('.market-status').removeClass('disabled');
												$('.main-table tr a[data-type="'+id+'"]').parent().parent().children('.effect-date').html(res['date']);
												refreshRosources(res);
												showErrorMessage('<p>Волшебство '+res['title']+' стала доступным.</p>');
											}
										}
									});
									//end ajax magic_is_buyed
								}
							}
						}
					});
					//end ajax get_magic_effect_data
				}
			}
		});
		//end ajax check_user_is_plying_status

	});
}

//Пользователь меняет статус активности волшебства
function userChangesMagicEffectStatus(){
	$('.main-table .market-status-wrap .market-status').click(function(){

		if( !$(this).hasClass('disabled') ) {

			var status_id = $(this).parents('tr').children('.no-border').children('.button-plus').attr('data-type');
			var token = $('#buyingCardOrmagic input[name=_token]').val();
			var is_active = $(this).hasClass('active');

			$.ajax({
				url: '/check_user_is_plying_status',
				type: 'GET',
				success: function (data) {
					if (data != 0) {

						var res = JSON.parse(data);
						showErrorMessage(res['message']);

					} else {
						$.ajax({
							url: '/magic_change_status',
							type: 'PUT',
							headers: {'X-CSRF-TOKEN': token},
							data: {status_id: status_id, is_active: is_active},
							success: function (data) {
								console.log(data);
								var res = JSON.parse(data);
								if (res[0] == 'success') {
									if (res[1] == 0) {
										$('.main-table tr .no-border a[data-type="' + status_id + '"]').parent().parent().children('.market-status-wrap').children('.market-status').removeClass('active');
									} else {
										$('.main-table tr .no-border a[data-type="' + status_id + '"]').parent().parent().children('.market-status-wrap').children('.market-status').addClass('active');
									}
								}
								if (res[0] == 'too_much') {
									showErrorMessage('<p>Разрешается использовать только ТРИ активных волшебства.</p>');
								}
							}
						});
						//end ajax magic_change_status
					}
				}
			});
			//end ajax check_user_is_plying_status
		}
	});
}

//end of /magic

/*
* Общие методы
*/

//Возвращает карты/волшебство в зависимости от расы
function getCardsByRace(race){

	switch($('.market-page').attr('id')){
		case 'market':  var url = '/get_cards_by_race'; break;
		case 'magic':   var url = '/get_magic_by_race'; break;
	}

	$.ajax({
		url:	url,
		type:	'GET',
		data:	{race:race},
		success:function(data){
			var res = JSON.parse(data);

			switch($('.market-page').attr('id')){
				case 'market':
					$('.market-selection .select-rase-img, .content-card-field-wrap .market-cards-items-wrap').empty();
					for(var i=0; i<res['cards'].length; i++){
						$('.content-card-field-wrap .market-cards-items-wrap').append(buildCardDeckView(res['cards'][i], 'div'));
					}
					userByingCard();
					break;
				case 'magic':
					$('.content-card-field-wrap .main-table>tbody>tr').remove();
					for(var i=0; i<res['effects'].length; i++){
						$('.content-card-field-wrap .main-table>tbody').append(buildMagicEffectsView(res['effects'][i]));
					}
					userByingMagic();
					userChangesMagicEffectStatus();
					break;
			}
			if(res['race_img_url'] != ''){
				$('.market-selection .select-rase-img').append('<img src="img/card_images/' + res['race_img_url'] + '" alt="">');
			}
		}
	})
}

//Функция обновления значений цены usd в золото
function refreshGoldPrices(){
	$('.market-buy-popup input[name=goldToBuy]').change(function(){
		var goldValue = parseInt($(this).val());
		if( Number.isInteger(goldValue) ){
			var usd = goldValue * window.exgange_gold;
			$('#buySomeGold #goldToUsd').text(usd);
			$('#buySomeGold input[name=LMI_PAYMENT_AMOUNT]').val(usd);
		}else{
			alert('Ошибка. Введите числовое значение.');
		}
	});
}


//Функция обновления значений ресурсов пользователя
function refreshRosources(resources){
	if(resources['gold'] != 'undefined') $('.rating .resurses .gold').text(resources['gold']);
	if(resources['silver'] != 'undefined') $('.rating .resurses .silver').text(resources['silver']);
	if(resources['energy'] != 'undefined') $('.rating .resurses .lighting').text(resources['energy']);
}


//Функция обновления значений цены золото в серебро
function refreshSilverPrices(){
	$('.market-buy-popup input[name=goldToSell]').change(function(){
		var goldValue = parseInt($(this).val());

		if( Number.isInteger(goldValue) ){
			var silverToBuy = parseInt(goldValue * window.gold_to_silver);
			$('#buySomeSilver #silverToBuy').text(silverToBuy);
		}else{
			alert('Ошибка. Введите числовое значение.');
		}
	});
}


//Покупка энергии
function showEnergyBuyingPopup(){
	$(document).on('click', '.buy-more-energy', function(e) {
		e.preventDefault();
		$.ajax({
			url:	'/check_user_is_plying_status',
			type:	'GET',
			success:function (data) {
				if (data != 0) {

					var res = JSON.parse(data);
					showErrorMessage(res['message']);

				} else {
					$('#buySomeEnergy').show(300);

					$('#buySomeEnergy input[type=button]').click(function(){
						var payType = $(this).attr('name');
						$.ajax({
							url:	'/user_buying_energy',
							type:	'PUT',
							headers:{'X-CSRF-TOKEN': $('.market-buy-popup input[name=_token]').val()},
							data:	{pay_type:payType},
							success:function(data){
								var res = JSON.parse(data);
								if(res['message'] == 'success'){
									refreshRosources(res);
								}else{
									alert(res['message']);
								}
							}
						})
					});
				}
			}
		});
	});
}


//Покупка золота
function showGoldBuyingPopup(){
	$(document).on('click', '.buy-more-gold', function(e){
		e.preventDefault();
		$.ajax({
			url:	'/check_user_is_plying_status',
			type:	'GET',
			success:function (data) {
				if (data != 0) {

					var res = JSON.parse(data);
					showErrorMessage(res['message']);

				} else {
					$('#buySomeGold').show(300);

					$('#buySomeGold #pay input[type=submit]').click(function(e){
						if($('#buySomeGold input[name=LMI_PAYMENT_AMOUNT]').val() < 1){
							return false;
						}
					});
					refreshGoldPrices();
				}
			}
		});
	});
}


//Покупка Серебра
function showSilverBuyingPopup(){
	$(document).on('click', '.buy-more-silver', function(e){
		e.preventDefault();
		$.ajax({
			url: '/check_user_is_plying_status',
			type: 'GET',
			success: function (data) {
				if (data != 0) {

					var res = JSON.parse(data);
					showErrorMessage(res['message']);

				} else {
					$('#buySomeSilver').show(300);

					$('#buySomeSilver input[name=buyingSilver]').click(function(){
						var goldToSell = parseInt($('#buySomeSilver input[name=goldToSell]').val());
						$.ajax({
							url:	'/user_buying_silver',
							type:	'PUT',
							headers:{'X-CSRF-TOKEN': $('.market-buy-popup input[name=_token]').val()},
							data:	{gold:goldToSell},
							success:function(data){
								var res = JSON.parse(data);
								if(res['message'] == 'success'){
									refreshRosources(res);
								}else{
									alert(res['message']);
								}
							}
						})
					});
					refreshSilverPrices();
				}
			}
		});
	});
}


function showErrorMessage(message){
	$('#buyingCardOrmagic .popup-content-wrap').html('<p>' + message + '</p>');
	$('#buyingCardOrmagic').show(300).delay(3000).hide(400);
}


//Вывод Колод для игры
function showUserDecks(){
	$('.conteiner-rase .afterloader').css({'opacity':'0', 'z-index':'-1'});

	$('.conteiner-rase ul li .button-buy-next').click(function(e){
		e.preventDefault();
		var race = $(this).attr('name');
		$('.conteiner-rase #gameForm input[name=currentRace]').val(race);

		$.ajax({
			url:	'/validate_deck',
			type:	'GET',
			beforeSend: function(){
				$('.conteiner-rase .afterloader').css({'opacity':'1', 'z-index':'100'});
			},
			data:	{race:race},
			success:function(data){
				$('.conteiner-rase .afterloader').css({'opacity':'0', 'z-index':'-1'});
				var res = JSON.parse(data);
				if(res['message'] == 'success'){
					$('.conteiner-rase #gameForm').submit();
				}else{
					$('.fancybox-overlay').hide();
					showErrorMessage(res['message']);
				}
			}
		});
	});
}


//Присоединение к игре
function userConnectToGame(){
	$('.tables-list').on('click', 'a.play-game', function(e){
		e.preventDefault();
		var id = $(this).attr('id');

		$.ajax({
			url:	'/user_connect_to_battle',
			type:	'PUT',
			headers:{'X-CSRF-TOKEN': $('.market-buy-popup input[name=_token]').val()},
			data:	{id:id},
			success:function(data){
				var res = JSON.parse(data);
				if(res['message'] == 'success'){
					location = '/play/'+id;
				}else{
					showErrorMessage(res['message']);
				}
			}
		});
	});
}

function array_unique( inputArr ) {
	var result = [];
	$.each(inputArr, function(i, el){
		if($.inArray(el, result) === -1) result.push(el);
	});

	return result;
}



$(document).ready(function(){
	if( (!$('.login-page').length>0) && (!$('.registration-main-page').length > 0) ) getUserData();  //Получить данные пользователя (по идее должна не работать только после логинизации)
	showFormOnMain();                       //Украшение формы логина на главной
	showWindowAboutOnMain();                //Кнопка "ОБ ИГРЕ" на главной
	fancyboxForm();
	settingsInputFile();                    //Страница "Настройки". Украшение файл приемника
	initScrollpane();                       //Инициализация скролла на страницах "Мои карты", "Магазин", ("Волшебство не проверялось")
	draggableCards();                       //Инициализация перетягивания карт
	underDragCardFix();                     //Фикс перетягивания

	showGoldBuyingPopup();
	showSilverBuyingPopup();
	showEnergyBuyingPopup();

	showUserDecks();
	userConnectToGame();

	$('.male-select').styler({
		selectPlaceholder: 'Выроб расы'
	});

    //вычисление количества активных пользователей на сайте
	setInterval(function(){
	   $.get('/get_user_quantity', function(data){
		   $('.people-box .preload-peoples img').hide();
		   $('.people-box .people').css('opacity', '1').text(data);
	   });

	},15000);

    //Украшение селекторов
    if($('.content-card-top .market-selection select').length > 0){
        marketSelection();
        getCardsByRace($('.content-card-top .market-selection select').val());
        $('.content-card-top .market-selection select').change(function(){
            getCardsByRace($(this).val());
        });
    }

    //Изменение настроек пользоателя
    applySettings();
    //Изменение картинки
    settingUpdateImg();


    //Выбор расы колоды/волшебства
	$('.content-card-center-title select').change(function(){
		getUserDeck($(this).val());
	});
    //Начальная загрузка расы колоды/волшебства
	if($('.content-card-center-title select').length > 0){
		getUserDeck($('.content-card-center-title select').val());
	}

    //Закрытие popup-окна
	$(document).on('click', '.close-popup', function(){
	   $(this).parent().hide();
	});

	//пользователь создает стол
	$(document).on('click', 'input[name=createTable]', function(){
		$('#createTable').show(300);
	});
});