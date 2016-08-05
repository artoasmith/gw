$(window).load(function(){
    $.get('/get_socket_settings', function(data) {

        window.allowActions = 0;

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
                            window.allowActions = 1;
                            userMakeAction();
                        }

                        break;

                    case 'allUsersAreReady':
                        if(result.login == $('.user-describer').attr('id')){
                            showPopup('Ваша очередь ходить');
                            window.allowActions = 1;
                            userMakeAction();
                        }else{
                            window.allowActions = 0;
                            showPopup('Ход игрока '+result.login);
                        }
                        console.log('allowActions = '+window.allowActions);
                        break;

                    case 'UserMadeMove':

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
                            createUserCardSelect(userData[key]['hand'], userData[key]['deck'], userData[key]['can_change_cards']);

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
                $('#handCards li').click(function(){
                    if($(this).children('img').hasClass('disactive')){
                        $(this).children('img').removeClass('disactive');
                    }else{
                        if($('#handCards li .disactive').length < can_change_cards){
                            $(this).children('img').addClass('disactive');
                        }
                    }
                });

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
                            console.log(data);
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
                            console.log('block has sended Ready');
                        }
                    });
                });
            }


            function createUserDescriber(userLogin, user_img, userRace){
                if(user_img != ''){
                    $('.convert-right-info #'+userLogin+' .stash-about .image-oponent-ork').css({'background':'url(/img/user_images/'+user_img+') 50% 50% no-repeat'});
                }
                $('.convert-right-info #'+userLogin+' .stash-about .naming-oponent .name').text(userLogin);
                $('.convert-right-info #'+userLogin+' .stash-about .naming-oponent .rasa').text(userRace);
            }


            function createUserMagicFieldCards(userLogin, magicData){
                for(var i=0; i<magicData.length; i++){
                    $('.convert-right-info #' + userLogin + ' .useless-card').children().children('.magic-effects-wrap').append(createMagicEffectView(magicData[i]));
                }
            }


            function createMagicEffectView(magicData){
                return  '' +
                    '<li data-cardid="' + magicData['id'] + '">' +
                    '<img src="/img/card_images/' + magicData['img_url']+'" alt="' + magicData['slug'] +'" title="' + magicData['title'] +'">'+
                    '</li>';
            }

            function createUserCardToSelectView(card){
                return  '' +
                    '<li data-cardid="' + card['id'] + '">' +
                    '<img src="/img/card_images/' + card['img_url']+'" alt="' + card['slug'] +'" title="' + card['title'] +'">' +
                    '<div class="card-strength-wrap">' + card['strength'] + '</div>' +
                    '</li>';
            }


            function createUserCardSelect(handDeck, userDeck, cardsToSelectQuantity){
                $('#selecthandCardsPopup .cards-select-message-wrap span').text(cardsToSelectQuantity);
                for(var i=0; i<handDeck.length; i++){
                    $('#selecthandCardsPopup #handCards').append(createUserCardToSelectView(handDeck[i]));
                }
            }


            function changeDeckCardsWidth(parent, handler){
                var cardsSelectBlockWidth = $(parent+' '+handler).width();
                var cardsCount = $(parent+' '+handler+' li').length;
                var singleCardBlockLength = Math.floor(cardsSelectBlockWidth/cardsCount);
                var cardsSelectBlockMargin = Math.floor((cardsSelectBlockWidth - singleCardBlockLength*cardsCount)/2 - 0.5);
                $(parent+' '+handler+' li').width(singleCardBlockLength);
                $(parent+' '+handler).css({'padding-left': cardsSelectBlockMargin+'px', 'padding-right': cardsSelectBlockMargin+'px'});

            }


            function createUserHandView(cardData){
                return  '' +
                    '<li data-cartid="'+cardData['id']+'" data-relative="'+cardData['type']+'">'+
                        '<img title="'+cardData['title']+'" alt="'+cardData['slug']+'" src="/img/card_images/'+cardData['img_url']+'">'+
                        '<div class="card-strength-wrap">'+cardData['strength']+'</div>' +
                        '<div class="card-name-property"><p>'+cardData['title']+'</p></div>'+
                    '</li>';
            }

            function handReformCardLayers(handler){
                var shift = 0;
                var zIndex = 6;
                console.log(handler);
                handler.each(function() {
                    $(this).css({'left': shift + '%', 'z-index': zIndex});
                    shift += 19 - handler.length;
                    zIndex++;
                });
            }


            function handReformDeck(user){

                $('.user-card-stash #sortableUserCards li').each(function(){
                    $(this).width($(this).width()+45);
                    handReformCardLayers($('.user-card-stash #sortableUserCards li'));
                });

                $('#sortableUserCards').on('click', 'li', function(){
                    var cardData = getCardData($(this).attr('data-cartid'), $('.user-describer').attr('id'));

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

                $('#sortableUserCards').on('mouseover', 'li', function(){
                    var zIndex = $(this).css('z-index');
                    $(this).css({'top': '-80px', 'z-index': '300'});
                    var _this = $(this);
                    $(this).mouseout(function(){
                        _this.css({'top': '0px', 'z-index': zIndex});
                    });
                });
            }


            function userMakeAction(){

                $('.buttons-block-play button[name=userPassed]').click(function(){
                    if(window.allowActions == 1){
                        var result = confirm('Вы действительно хотите спасовать?');
                        if(result === true){
                            conn.send(
                                JSON.stringify({
                                    action: 'userPassedTurn',
                                    ident: ident
                                })
                            )
                            window.allowActions = 0;
                        }
                    }
                });

                if(window.allowActions == 1) {

                    $('.user-card-stash #sortableUserCards').sortable({
                        connectWith: '.user .convert-card-box .can-i-use-useless',
                        stop: function (e, ui) {
                            $(this).children('li[data-cartid='+ui.item.attr('data-cartid')+']').remove();
                            handReformCardLayers($('.user-card-stash #sortableUserCards li'));
                        }
                    });

                    $('.user .convert-card-box .can-i-use-useless').droppable({
                        accept: '.ui-sortable-handle',
                        drop: function(e, ui){
                            var field = $(this).attr('id');
                            var currentCard = getCardData(ui.draggable[0].attributes['data-cartid'].nodeValue, $('.user-describer').attr('id'));

                            if(currentCard.action_row.length == 1){
                                switch(currentCard.action_row[0]){
                                    case 0:
                                        var targetField = '#sortable-user-cards-field-meele';
                                        break;
                                    case 1:
                                        var targetField = '#sortable-user-cards-field-range';
                                        break;
                                    case 2:
                                        var targetField = '#sortable-user-cards-field-super-renge';
                                        break;
                                }
                                if(currentCard['type'] == 'special'){
                                    $('.user .convert-stuff '+targetField).parents('.convert-stuff').childrens('.image-inside-line').empty().append('<ul>'+createFieldCardView(currentCard)+'</ul>');
                                }else{
                                    $('.user .convert-stuff '+targetField).append(createFieldCardView(currentCard));
                                    handReformCardLayers($('.user .convert-stuff '+targetField+' li'));
                                }
                            }else{ // Если у карты неcколько рядов действия

                            }
                        }
                    });
                }
            }

            function createFieldCardView(cardData){
                return '' +
                '<li class="content-card-item" data-cartid="'+cardData['id']+'" data-relative="'+cardData['type']+'">'+
                    '<div class="content-card-item-main" style="background-image: url(/img/card_images/'+cardData['img_url']+')">'+
                        '<div class="label-power-card">'+cardData['strength']+'</div>'+
                        '<div class="hovered-items">'+
                            '<div class="card-name-property"><p>'+cardData['title']+'</div>'+
                        '</div>'+
                    '</div>'+
                '</li>';
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


            function showPopup(ms){
                $('.market-buy-popup .popup-content-wrap').html('<p>' + ms + '</p>');
                $('.market-buy-popup').show(300);
            }
        });

    });


});