$(window).load(function(){
    $.get('/get_socket_settings', function(data) {

        var socketResult = JSON.parse(data);

        var ident = {
            battleId: socketResult['battle'],
            userId: socketResult['user'],
            hash: socketResult['hash']
        };

        $(document).ready(function(){
            var conn = new WebSocket('ws://' + socketResult['dom'] + ':8080');

            conn.onopen = function(data){
                console.log('Соединение установлено');
                conn.send(JSON.stringify(
                    {
                        action: 'userJoinedToRoom',
                        ident: ident
                    }
                ));
            }


            conn.onclose = function(event){

            }


            conn.onerror = function (e) {
                showPopup('Socket error');
            };

            conn.onmessage = function (e) {
                var result = JSON.parse(e.data);
                console.log(result);

                switch(result.message){
                    case 'usersAreJoined':

                        var token = $('.market-buy-popup input[name=_token]').val().trim();
                        $.ajax({
                            url:    '/game_start',
                            type:   'PUT',
                            headers:{'X-CSRF-TOKEN':token},
                            data:   {battle_id: result.battleInfo},
                            success:function(data){
                                data = JSON.parse(data);
                                if(data['message'] == 'success'){
                                    window.usersData = data['userData'];
                                    //Формирование данных пользователей и окна выбора карт
                                    buildPlayRoomView(window.usersData);
                                }
                            }
                        });

                        if(result.userTurn == $('.user-describer').attr('id')){
                            showPopup('Ваша очередь ходить');                            
                            userMakeAction(1);
                        }

                        break;

                    case 'allUsersAreReady':
                        if(result.login == $('.user-describer').attr('id')){
                            showPopup('Ваша очередь ходить');
                            userMakeAction(1);
                        }else{
                            userMakeAction(0);
                            showPopup('Ход игрока '+result.login);
                        }
                        break;

                    case 'userMadeAction':
                        if(result.login != $('.user-describer').attr('id')){
                            showPopup('Ваша очередь ходить');
                            userMakeAction(1);
                        }else{
                            userMakeAction(0);
                            showPopup('Ход игрока '+result.login);
                        }
                        //console.log($('.convert-cards ul[id$=super-renge]').attr('id'));
                        $('.convert-cards .convert-stuff .can-i-use-useless').empty();
                        for(var key in result.battle_field){
                            for(var row in result.battle_field[key]){
                                row = ''+row;
                                switch(row){
                                    case '2': var rowField = 'super-renge'; break;
                                    case '1': var rowField = 'field-range'; break;
                                    case '0': var rowField = 'field-meele'; break;
                                }
                                for(var warrior_card in result.battle_field[key][row]['warrior']){
                                    $('.convert-cards[data-user='+key+'] .convert-card-box .convert-stuff .field-for-cards ul[id$='+rowField+']').append(createFieldCardView(result.battle_field[key][row]['warrior'][warrior_card]));                                    
                                }
                                if(result.battle_field[key][row]['special'].length > 0){
                                    console.log(result.battle_field[key][row]);
                                    $('.convert-cards[data-user='+key+'] .convert-card-box .convert-stuff .field-for-cards ul[id$='+rowField+']').parents('.field-for-cards').children('.image-inside-line').html(createCardDescriptionView(result.battle_field[key][row]['special'][0]));
                                }                                
                            }
                        }
                        handReformFieldsLayers();
                        break;
                        
                    case 'selfTurnEnds':
                        userMakeAction(0);
                        showPopup('Ход игрока '+result.login);                       
                        break;
                        
                }
            }


            function buildPlayRoomView(userData){

                //очищение списков поп-апа выбора карт
                $('#selecthandCardsPopup #userSelectCardsToHand').empty();
                $('#selecthandCardsPopup .cards-select-wrap').empty();

                //Читаем данніе пользователя

                for(var key in userData){
                    if( $('.convert-right-info #'+key).length <1){
                        //Установить никнейм оппонета
                        $('.convert-right-info .oponent-describer').attr('id',key);
                        //Установить логин оппонента
                        $('.field-battle .cards-bet #card-give-more-oponent').attr('data-user', key);
                        //Установить логин оппонента в его поле битвы
                        $('.convert-battle-front .oponent').attr('data-user', key);
                    }

                    //Создать описание пользователей
                    createUserDescriber(key, userData[key]['img_url'], userData[key]['deck_title']);

                    //Количество карт в колоде
                    $('.convert-left-info .cards-bet ul[data-user='+key+'] .deck .counter').text(userData[key]['deck_count']);

                    //Если у пользователя есть магические способности
                    if(userData[key]['magic'].length > 0){
                        //Вывод текущей магии пользователей
                        $('.convert-right-info #' + key + ' .useless-card').children().children('.magic-effects-wrap').empty();
                        createUserMagicFieldCards(key, userData[key]['magic']);
                    }

                    if( 0 == parseInt(userData[key]['ready'])){
                        if (userData[key]['hand'].length > 0) {
                            //Вывод карт руки и колоды
                            createUserCardSelect(userData[key]['hand'], userData[key]['can_change_cards']);

                            //Появление поп-апа выбора карт руки

                            $('#selecthandCardsPopup').show(300, function () {
                                changeDeckCardsWidth('#selecthandCardsPopup', '#handCards', 0);
                            });

                            userChangeDeck(userData[key]['can_change_cards']);
                        }
                    }else{
                        conn.send(
                            JSON.stringify({
                                action: 'userReady',
                                ident: ident
                            })
                        );
                    }
                }
            }


            function userChangeDeck(can_change_cards){
                //Смена карт при старте игры
                $('#handCards li').click(function(){
                    if($(this).children('img').hasClass('disactive')){
                        $(this).children('img').removeClass('disactive');
                    }else{
                        if($('#handCards li .disactive').length < can_change_cards){
                            $(this).children('img').addClass('disactive');
                        }
                    }
                });

                //Пользователь Выбрал карты для сноса и нажал "ОК"
                $('#selecthandCardsPopup input[name=accpetHandDeck]').click(function(){
                    var token = $('.market-buy-popup input[name=_token]').val().trim();
                    var n = $('#handCards li .disactive').length;
                    var cardsToChange = [];

                    if(n > can_change_cards) n = can_change_cards;

                    for(var i = 0; i < n; i++){
                        cardsToChange.push($('#handCards li .disactive:eq('+i+')').parent().attr('data-cardid'));
                    }

                    cardsToChange = JSON.stringify(cardsToChange);

                    $.ajax({
                        url:    '/game_user_change_cards',
                        type:   'PUT',
                        headers:{'X-CSRF-TOKEN':token},
                        data:   {cards:cardsToChange},
                        success:function(data){
                            data = JSON.parse(data);
                            for(var key in data){
                                window.usersData[key]['deck'] = data[key]['deck'];
                                window.usersData[key]['hand'] = data[key]['hand'];
                                window.usersData[key]['deck_count'] = data[key]['deck_count'];
                            }

                            $('.user-card-stash #sortableUserCards').empty();
                            for(var i=0; i< window.usersData[key]['hand'].length; i++){
                                $('.user-card-stash #sortableUserCards').append(createUserHandView(window.usersData[key]['hand'][i]));
                            }
                            changeDeckCardsWidth('.user-card-stash', '#sortableUserCards');

                            handReformDeck(key);

                            $('#selecthandCardsPopup').hide(300);
                            $('#selecthandCardsPopup #handCards').empty();

                            conn.send(
                                JSON.stringify({
                                    action: 'userReady',
                                    ident: ident
                                })
                            );
                            console.log('user send Ready');
                        }
                    });
                });
            }

            
            //Создание описаний пользователей в правом сайдбаре
            function createUserDescriber(userLogin, user_img, userRace){
                if(user_img != ''){
                    $('.convert-right-info #'+userLogin+' .stash-about .image-oponent-ork').css({'background':'url(/img/user_images/'+user_img+') 50% 50% no-repeat'});
                }
                $('.convert-right-info #'+userLogin+' .stash-about .naming-oponent .name').text(userLogin);
                $('.convert-right-info #'+userLogin+' .stash-about .naming-oponent .rasa').text(userRace);
            }


            //Создание изображений магических еффектов в правом сайдбаре
            function createUserMagicFieldCards(userLogin, magicData){
                for(var i=0; i<magicData.length; i++){
                    $('.convert-right-info #' + userLogin + ' .useless-card').children().children('.magic-effects-wrap').append(createMagicEffectView(magicData[i]));
                }
            }


            //Созднаие Отображения маг. еффекта
            function createMagicEffectView(magicData){
                return  '' +
                    '<li data-cardid="' + magicData['id'] + '">' +
                    '<img src="/img/card_images/' + magicData['img_url']+'" alt="' + magicData['slug'] +'" title="' + magicData['title'] +'">'+
                    '</li>';
            }


            //Создание списка карт "Руки"
            function createUserCardSelect(handDeck, cardsToSelectQuantity){
                $('#selecthandCardsPopup .cards-select-message-wrap span').text(cardsToSelectQuantity);
                for(var i=0; i<handDeck.length; i++){
                    $('#selecthandCardsPopup #handCards').append(createUserHandView(handDeck[i]));
                }
            }


            //Изменение ширины карт при выборе Карт "Руки"
            function changeDeckCardsWidth(parent, handler){
                var cardsSelectBlockWidth = $(parent+' '+handler).width();
                var cardsCount = $(parent+' '+handler+' li').length;
                var singleCardBlockLength = Math.floor(cardsSelectBlockWidth/cardsCount);
                var cardsSelectBlockMargin = Math.floor((cardsSelectBlockWidth - singleCardBlockLength*cardsCount)/2 - 0.5);
                $(parent+' '+handler+' li').width(singleCardBlockLength);
                $(parent+' '+handler).css({'padding-left': cardsSelectBlockMargin+'px', 'padding-right': cardsSelectBlockMargin+'px'});
            }


            //Отображение карт в "Руке"
            function createUserHandView(cardData){
                return  '' +
                    '<li data-cardid="'+cardData['id']+'" data-relative="'+cardData['type']+'">'+
                        '<img title="'+cardData['title']+'" alt="'+cardData['slug']+'" src="/img/card_images/'+cardData['img_url']+'">'+
                        '<div class="card-strength-wrap">'+cardData['strength']+'</div>' +
                        '<div class="card-name-property"><p>'+cardData['title']+'</p></div>'+
                    '</li>';
            }

            
            //Смешение карт "гармошкой"
            function handReformCardLayers(handler){
                var shift = 0;
                var zIndex = 6;
                handler.each(function() {
                    $(this).css({'left': shift + '%', 'z-index': zIndex});
                    shift += 19 - handler.length;
                    zIndex++;
                });
            }
            
            
            function handReformFieldsLayers(){
                var rowSummary = {
                    "sortable-oponent-cards-field-super-renge":0,
                    "sortable-oponent-cards-field-range":0,
                    "sortable-oponent-cards-field-meele":0,
                    "sortable-user-cards-field-meele":0,
                    "sortable-user-cards-field-range":0,
                    "sortable-user-cards-field-super-renge":0
                };
                
                $('.convert-battle-front .can-i-use-useless').each(function(){
                    handReformCardLayers($(this).children('li'));
                });
                
                for(var key in rowSummary){                    
                    $('.convert-cards .convert-card-box .convert-stuff .field-for-cards #'+key+' li').each(function(){
                        rowSummary[key] += parseInt($(this).children('.content-card-item-main').children('.label-power-card').text());
                    });
                    $('.convert-cards .convert-card-box .convert-stuff .field-for-cards #'+key).parents('.convert-stuff').children('.field-for-sum').text(rowSummary[key]);
                }
                var summ = rowSummary["sortable-oponent-cards-field-super-renge"] + rowSummary["sortable-oponent-cards-field-range"]+rowSummary["sortable-oponent-cards-field-meele"];
                $('.oponent-describer .power-text-oponent').text(summ);
                var summ = rowSummary["sortable-user-cards-field-super-renge"] + rowSummary["sortable-user-cards-field-range"]+rowSummary["sortable-user-cards-field-meele"];
                $('.user-describer .power-text-user').text(summ);
            }


            //Формирование "Руки"
            function handReformDeck(user){
                //Изменение ширины карт "Руки"
                $('.user-card-stash #sortableUserCards li').each(function(){
                    $(this).width($(this).width()+45);
                    handReformCardLayers($('.user-card-stash #sortableUserCards li'));
                });
                //Нажатие на карту
                $('#sortableUserCards').on('click', 'li', function(){
                    var cardData = getCardData($(this).attr('data-cardid'), $('.user-describer').attr('id'));

                    $('#notSortableOne').animate({'opacity':'1'}, 500);

                    $('#notSortableOne').empty().append('' +
                    '<li class="content-card-item chossen-card">' +
                        '<div class="content-card-item-main" style="background-image: url(/img/card_images/'+cardData['img_url']+')">' +
                            '<div class="label-power-card">' +
                                '<span class="label-power-card-wrap"><span>'+cardData['strength']+'</span></span>' +
                            '</div>' +
                            '<div class="hovered-items">' +
                                '<div class="card-name-property"><p>'+cardData['title']+'</p></div>' +
                                '<div class="block-describe">' +
                                    '<div class="block-text-describe">' +
                                        '<div class="block-text-describe-wrap">' +
                                            '<div class="block-text-describe-main">' +
                                                '<div class="block-text-describe-main-wrap"><p>'+cardData['descript']+'</p></div>' +
                                            '</div>' +
                                        '</div>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                    '</li>');
                });
                //Выдвижение карты при наведении
                $('#sortableUserCards').on('mouseover', 'li', function(){
                    var zIndex = $(this).css('z-index');
                    $(this).css({'top': '-80px', 'z-index': '300'});
                    var _this = $(this);
                    $(this).mouseout(function(){
                        _this.css({'top': '0px', 'z-index': zIndex});
                    });
                });
            }


            //Пользователь производит действие
            function userMakeAction(allowActions){
                if(allowActions !== 0) {
                    
                    //Перетягивание карты на поле боя
                    $('.user-card-stash #sortableUserCards').sortable({
                        connectWith: '.convert-cards .convert-card-box .can-i-use-useless',
                        stop: function (e, ui) {                            
                            handReformCardLayers($('.user-card-stash #sortableUserCards li'));
                        }
                    });

                    //Пользователь перетянул карту
                    $('.convert-cards .convert-card-box .can-i-use-useless').droppable({
                        accept: '.ui-sortable-handle',
                        drop: function(e, ui){
                            if((allowActions !== 0) && (allowActions !== undefined)){                               

                                var destignationField = 'user';

                                var currentCard = getCardData(ui.draggable[0].attributes['data-cardid'].nodeValue, $('.user-describer').attr('id'));

                                for(var i=0; i<currentCard['actions'].length; i++){
                                    if(currentCard['actions'][i]['CAspy_get_cards_num'] !== undefined){
                                        destignationField = 'oponent';
                                    }
                                }

                                if(currentCard['type'] == 'special'){
                                    if($(this).context['id'].indexOf('user') > 0){
                                        destignationField = 'user';
                                    }
                                    if($(this).context['id'].indexOf('oponent') > 0){
                                        destignationField = 'oponent';                            
                                    }
                                }

                                var fieldArray = [
                                    '#sortable-'+destignationField+'-cards-field-meele',
                                    '#sortable-'+destignationField+'-cards-field-range',
                                    '#sortable-'+destignationField+'-cards-field-super-renge'
                                ];

                                $('#sortableUserCards li[data-cardid='+ui.draggable[0].attributes['data-cardid'].nodeValue+']').remove();
                                handReformCardLayers($('.user-card-stash #sortableUserCards li'));

                                if(currentCard.action_row.length == 1){
                                    targetField = fieldArray[currentCard.action_row[0]];
                                    checkCardTypeThenPut(currentCard, targetField);
                                }else{
                                    // Если у карты неcколько рядов действия
                                    var field = $(this).context['id'];

                                    for(var i = 0; i<currentCard.action_row.length; i++){
                                        if('#'+field == fieldArray[i]){
                                            targetField = '#'+field;
                                            checkCardTypeThenPut(currentCard, targetField);
                                        }
                                    }
                                }

                                conn.send(
                                    JSON.stringify({
                                        action: 'userMadeCardAction',
                                        cardData: currentCard['id'],
                                        field: targetField,
                                        ident: ident
                                    })
                                )
                                allowActions = 0;
                            }
                        }
                    });
                }
            }
            
            /*function recalculateBattleFields(){
                $('.convert-battle-front .convert-cards .convert-one-field')
            }*/
            
            
            function checkCardTypeThenPut(currentCard, targetField){
                if(currentCard['type'] == 'special'){
                    $('.convert-battle-front .convert-stuff '+targetField).parents('.convert-stuff').children('.convert-one-field').children('.field-for-cards').children('.image-inside-line').html(createCardDescriptionView(currentCard));
                }else{
                    $('.convert-battle-front .convert-stuff '+targetField).append(createFieldCardView(currentCard));
                    handReformCardLayers($('.user .convert-stuff '+targetField+' li'));
                }
            }
            

            function createFieldCardView(cardData){
                return '' +
                '<li class="content-card-item" data-cardid="'+cardData['id']+'" data-relative="'+cardData['type']+'">'+
                    createCardDescriptionView(cardData)+
                '</li>';
            }
            
            
            function createCardDescriptionView(cardData){
                return ''+
                '<div class="content-card-item-main" style="background-image: url(/img/card_images/'+cardData['img_url']+')">'+
                    '<div class="label-power-card">'+cardData['strength']+'</div>'+
                    '<div class="hovered-items">'+
                        '<div class="card-name-property"><p>'+cardData['title']+'</div>'+
                    '</div>'+
                '</div>';
            }


            function getCardData(id, user){
                var cardData = [];
                for(var i=0; i<window.usersData[user]['hand'].length; i++){

                    if( id == window.usersData[user]['hand'][i]['id']){
                        cardData = window.usersData[$('.user-describer').attr('id')]['hand'][i];
                    }
                }
                return cardData;
            }

            changeDeckCardsWidth('.user-card-stash', '#sortableUserCards');
            handReformDeck($('.convert-battle-front .user').attr('data-user'));
            
            handReformFieldsLayers();

            function showPopup(ms){
                $('.market-buy-popup .popup-content-wrap').html('<p>' + ms + '</p>');
                $('.market-buy-popup').show(300);
            }
        });

    });

});