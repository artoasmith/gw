$(window).load(function(){
    $.get('/get_socket_settings', function(data){
        var socketResult = JSON.parse(data); //Получение данных настроек соккета
        //Формирование начального пакета идентификации битвы
        var ident = {
            battleId: socketResult['battle'],
            userId: socketResult['user'],
            hash: socketResult['hash']
        };

        var allowActions = 0;   //Пользователю разрешены действия
        var userMadeAction = 0; //Пользователь произвел действие
        var cardSource = 'hand';//Активная колода для выбора карт (рука пользователя; Также доступны: deck- колода, discard- отбой).
        var cardToPlay = [];
        var allowCardChoise = 1;//Маркер разрешения выбора карт из колоды или отбоя

        $(document).ready(function(){
            var conn = new WebSocket('ws://' + socketResult['dom'] + ':8080');//Создание сокет-соединения
            //Создание сокет-соединения
            conn.onopen = function(data){
                console.log('Соединение установлено');
                conn.send(
                    JSON.stringify({
                        action: 'userJoinedToRoom',//Отправка сообщения о подключения пользователя к столу
                        ident: ident
                    })
                );
            }

            conn.onclose = function(event){}

            conn.onerror = function (e) {
                showPopup('Socket error');
            };

            conn.onmessage = function (e) {
                var result = JSON.parse(e.data);
                console.log(result);

                switch(result.message){
                    //Пользователи присоединились к игре
                    case 'usersAreJoined':
                        var token = $('.market-buy-popup input[name=_token]').val().trim();
                        //Запрос на формирование изначальной колоды и руки пользователя
                        $.ajax({
                            url:    '/game_start',
                            type:   'PUT',
                            headers:{'X-CSRF-TOKEN':token},
                            data:   {battle_id: result.battleInfo},
                            success:function(data){
                                data = JSON.parse(data);
                                if(data['message'] == 'success'){
                                    //Формирование данных пользователей и окна выбора карт
                                    buildRoomPreview(data['userData']);
                                    console.log('room builded');
                                }
                            }
                        });
                    break;
                    //Все пользователи готовы к игре
                    case 'allUsersAreReady':
                        changeTurnIndicator(result.login);
                    break;
                    //Сыграная карта пользователя предусматривает отыгрыш карты из отбоя или колоды
                    case 'ownCardsData':
                        for(var field in result.battleData){
                            for(var row in result.battleData[field]){
                                var currentRow = intRowToField(row);
                                for(var i in result.battleData[field][row]){
                                    $('.convert-battle-front #'+field+' .convert-stuff '+currentRow+' .cards-row-wrap li[data-cardid=\''+result.battleData[field][row][i]+'\']').addClass('glow');
                                }
                            }
                        }
                    break;
                    //Раунд закончен
                    case 'roundEnds':
                        showPopup(result.roundResult+'<p>Подождите, идет подготовка нового раунда</p>');
                        allowActions = 0;
                        userMadeAction = 1;
                        cardSource = 'hand';
                        allowCardChoise = 0;
                        changeTurnIndicator(null);
                    break;
                    //Игра закончена
                    case 'gameEnds':
                        showPopup('<p>Игра окончена.</p>'+result.gameResult+'<p>Вы можете <a href="/">вернуться в меню</a> или остаться и наслаждаться победой/проиграшем (нужное подчеркнуть)</p>');
                        allowActions = 0;
                        userMadeAction = 1;
                        cardSource = 'hand';
                        allowCardChoise = 0;
                        changeTurnIndicator(null);
                    break;
                    
                    //Пользователь произвел действие
                    case 'userMadeAction':
                        //смена индикатора хода
                        changeTurnIndicator(result.login);
                        //Отображение поля битвы
                        buildBattleField(result.field_data);
                        //Данные о колоде и отбое пользователей
                        if(result.counts !== undefined){
                            //колода противника
                            if(parseInt(result.counts['opon_deck']) > 0){
                                $('#card-give-more-oponent li[data-field=deck]').empty().append(createDeckCardPreview(result.counts['opon_deck'], false));
                            }else{
                                $('#card-give-more-oponent li[data-field=deck').empty().append('<div class="nothinh-for-swap"></div>');
                            }
                            //отбой противника
                            if(parseInt(result.counts['opon_discard']) > 0){
                                $('#card-give-more-oponent li[data-field=discard]').empty().append(createDeckCardPreview(result.counts['opon_discard'], false));
                            }else{
                                $('#card-give-more-oponent li[data-field=discard]').empty().append('<div class="nothinh-for-swap"></div>');
                            }
                            //колода игрока
                            if(parseInt(result.counts['user_deck']) > 0){
                                if(result.user_deck != undefined){
                                    $('#card-give-more-user li[data-field=deck]').empty().append(createDeckCardPreview(result.counts['user_deck'], true, result.user_deck));
                                }
                            }else{
                                $('#card-give-more-user li[data-field=deck]').empty().append('<div class="nothinh-for-swap"></div>');
                            }
                            //отбой игрока
                            if(parseInt(result.counts['user_discard']) > 0){
                                if(result.user_discard != undefined){
                                    $('#card-give-more-user li[data-field=discard]').empty().append(createDeckCardPreview(result.counts['user_discard'], true, result.user_discard));
                                }
                            }else{
                                $('#card-give-more-user li[data-field=discard]').empty().append('<div class="nothinh-for-swap"></div>');
                            }
                            //рука игрока
                            if(result.user_hand !== undefined){
                                $('.user-card-stash #sortableUserCards').empty();
                                for(var i in result.user_hand){
                                    $('.user-card-stash #sortableUserCards').append(createFieldCardView(result.user_hand[i], result.user_hand[i]['strength'], true));
                                }
                                //Убираем сыграную карту из руки
                                if( (result.login != $('.user-describer').attr('id')) || ((result.cardSource != 'hand') && (result.login = $('.user-describer').attr('id')) ) ){
                                    $('#sortableUserCards .active').remove();
                                    $('#sortableUserCards li').removeClass('active');
                                }
                            }
                        }
                        createDeckLayers();//Переформирование отображения отбоя и колоды
                        createCardLayers($('#sortableUserCards li'));//Переформирование отображения руки
                        //Обработка Маг. Эффектов
                        if(result.magicUsage !== undefined){
                            for(var activated_in_round in result.magicUsage){
                                $('.user-describer .magic-effects-wrap li[data-cardid="'+result.magicUsage[activated_in_round]+'"]').removeClass('active').addClass('disactive');
                            }
                        }
                    break;
                }
                //Просчет перехода хода и дальнейших действий
                if((result.message == 'usersAreJoined') || (result.message == 'allUsersAreReady') || (result.message == 'userMadeAction')){
                    if(result.login == $('.user-describer').attr('id')){
                        allowActions = 1;
                        userMadeAction = 0;
                    }else{
                        allowActions = 0;
                    }
                    
                }
                //Отображение отбоя или колоды при отыграше спец.карты
                if((result.message == 'allUsersAreReady') || (result.message == 'userMadeAction')){
                    if(result.cardSource != undefined){
                        cardSource = result.cardSource;//Переопределение источника карт для проведения хода
                        cardToPlay = result.cardToPlay;
                        if(cardSource != 'hand'){
                            //"Подсветка" разрешеных для хода карт
                            var cardList = $('.convert-left-info #card-give-more-user li[data-field='+cardSource+']');
                            cardList.children('.card-my-init').children('ul.deck-cards-list').addClass('active');
                            
                            if(cardToPlay.length == 0){
                                cardList.children('.card-my-init').children('ul.deck-cards-list').children('li:not([data-relative=special])').addClass('glow');
                            }else{
                                for(var i=0; i<cardToPlay.length; i++){
                                    cardList.children('.card-my-init').children('ul.deck-cards-list').children('li:eq('+cardToPlay[i]+')').addClass('glow');
                                }
                            }
                        }
                        if((result.login == $('.user-describer').attr('id')) && (result.message == 'allUsersAreReady')){
                            allowCardChoise = 1;
                        }
                    }
                    //Отображение карт отбоя или колоды
                    if((allowCardChoise == 1) || (cardSource != 'hand')) showDecks(cardSource, cardToPlay);
                }

                if(result.message != 'ownCardsData'){
                    userMakeAction(allowActions, conn, cardSource)
                }
            }

            function userChangeDeck(can_change_cards){
                //Смена карт при старте игры
                $('#handCards li').click(function(){
                    if($(this).children().children('img').hasClass('disactive')){
                        $(this).children().children('img').removeClass('disactive');
                    }else{
                        if($('#handCards li .disactive').length < can_change_cards){
                            $(this).children().children('img').addClass('disactive');
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
                        cardsToChange.push($('#handCards li .disactive:eq('+i+')').parents('li').attr('data-cardid'));
                    }

                    cardsToChange = JSON.stringify(cardsToChange);

                    $.ajax({
                        url:    '/game_user_change_cards',
                        type:   'PUT',
                        headers:{'X-CSRF-TOKEN':token},
                        data:   {cards:cardsToChange},
                        success:function(data){
                            data = JSON.parse(data);
                            console.log(data[$('.user-describer').attr('id')]['deck']);
                            
                            $('#card-give-more-user li[data-field=deck]').empty().append(createDeckCardPreview(data[$('.user-describer').attr('id')]['deck'].length, true, data[$('.user-describer').attr('id')]['deck']));
                            
                            $('.user-card-stash #sortableUserCards').empty();
                            for(var i=0; i< data[$('.user-describer').attr('id')]['hand'].length; i++){
                                $('.user-card-stash #sortableUserCards').append(createFieldCardView(data[$('.user-describer').attr('id')]['hand'][i], data[$('.user-describer').attr('id')]['hand'][i]['strength'], true));
                            }
                            createDeckLayers();
                            createCardLayers($('#sortableUserCards li'));
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

            //Пользователь должен сделать действие
            function userMakeAction(allowActions, conn, cardSource){
                if((allowActions !== 0) && (userMadeAction === 0) && (allowActions !== undefined)){
                    //Пользователь совершил действия из "Руки"
                    $('.convert-battle-front .convert-stuff, .mezhdyblock .bor-beutifull-box').on('click', 'div.active, ul.active',function(){
                        var card = $('#sortableUserCards .active').attr('data-cardid');
                        switch(cardSource){
                            case 'deck':    var card = $('.convert-left-info #card-give-more-user li[data-field=deck] ul.deck-cards-list li.glow.active').attr('data-cardid'); break;
                            case 'discard': var card = $('.convert-left-info #card-give-more-user li[data-field=discard] ul.deck-cards-list li.glow.active').attr('data-cardid'); break;
                        }
                        var field = $(this).attr('id');
                        if(card != undefined){
                            if((allowActions !== 0) && (allowActions !== undefined)){
                                conn.send(
                                    JSON.stringify({
                                        action: 'userMadeCardAction',
                                        ident: ident,
                                        card: card,
                                        field: field,
                                        source: cardSource
                                    })
                                );
                            }
                            allowActions = 0;
                            userMadeAction = 1;
                        }else{                            
                            var magic = $('.user-describer ul.magic-effects-wrap .active').attr('data-cardid');
                            if(magic != undefined){
                                if((allowActions !== 0) && (allowActions !== undefined)){
                                    conn.send(
                                        JSON.stringify({
                                            action: 'userMadeCardAction',
                                            ident: ident,
                                            magic: magic,
                                        })
                                    );
                                }
                                allowActions = 0;
                                userMadeAction = 1;
                            }
                        }
                    });
                    //Пользователь совершил действие из отбоя или колоды
                    $('.convert-battle-front .convert-cards .convert-stuff .cards-row-wrap').on('click', 'li.glow', function(){
                        var card = $('#sortableUserCards .active').attr('data-cardid');
                        var field = $(this).parents('.field-for-cards').attr('id');
                        var player = $(this).parents('.convert-cards').attr('id');
                        var retrieve = $(this).attr('data-cardid'); 
                        if((allowActions !== 0) && (allowActions !== undefined)){
                            conn.send(
                                JSON.stringify({
                                    action: 'userMadeCardAction',
                                    ident: ident,
                                    card: card,
                                    field: field,
                                    source: 'hand',
                                    retrieve: retrieve,
                                    player: player
                                })
                            );
                        }
                        allowActions = 0;
                        userMadeAction = 1;
                    });
                    //Пользователь нажал "Пас"
                    $('.user-card-stash button[name=userPassed]').click(function(){
                        if((allowActions !== 0) && (allowActions !== undefined)){
                            conn.send(
                                JSON.stringify({
                                    action: 'userPassed',
                                    ident: ident
                                })
                            );
                        }
                        allowActions = 0;
                        userMadeAction = 1;
                    });
                }
            }
            //Сортировка карт руки (На всякий случай)
            createCardLayers($('#sortableUserCards li'));
            //Отображение карт руки, отбоя, колоды
            function showDecks(cardSource, cardToPlay){
                if(cardSource == 'hand'){
                    $('.convert-battle-front ul.cards-row-wrap, .user-card-stash #sortableUserCards').on('click', 'li', function(){
                        if($(this).hasClass('active')){
                            $(this).removeClass('active');
                            clearRowSelection();
                            $('#notSortableOne').empty().css({'opacity': 0});
                        }else{
                            $(this).parents('ul').children('li').removeClass('active');
                            $(this).addClass('active');
                        }

                        if($(this).hasClass('active')){
                            showCardActiveRow($(this).attr('data-cardid'), conn, ident);
                        }
                    });
                    
                    $('.user-describer ul.magic-effects-wrap li').click(function(){
                        if(!$(this).hasClass('disactive')){
                            $('.user-describer ul.magic-effects-wrap li').removeClass('active');
                            $(this).addClass('active');
                            $('#sortableUserCards li').removeClass('active');
                            showMagic($(this).attr('data-cardid'));
                            illuminateOpponent();
                            illuminateSelf();
                        }
                    });
                }else{
                    /*if(cardToPlay == 0){*/
                        $('.convert-left-info #card-give-more-user li[data-field='+cardSource+'] .card-my-init').css({'pointer-events':'none'});
                        $('.convert-left-info #card-give-more-user li[data-field='+cardSource+'] ul.deck-cards-list').css({'pointer-events':'auto'});
                        $('.convert-left-info #card-give-more-user li[data-field='+cardSource+'] ul.deck-cards-list li.glow').css({'pointer-events':'auto'});
                        $('.convert-left-info #card-give-more-user li[data-field='+cardSource+'] ul.deck-cards-list').on('click', 'li.glow', function(){
                            if($(this).hasClass('active')){
                                $(this).removeClass('active');
                                clearRowSelection();
                            }else{
                                $(this).parents('ul').children('li').removeClass('active');
                                $(this).addClass('active');
                            }

                            if($(this).hasClass('active')){
                                showCardActiveRow($(this).attr('data-cardid'), conn, ident);
                            }
                        });
                    /*}else{
                        console.log(cardToPlay);
                    }*/
                    
                }
                allowCardChoise = 0;
            }

            //Формирование стола по пользовательским данным
            function buildRoomPreview(userData){
                //очищение списков поп-апа выбора карт
                $('#selecthandCardsPopup #handCards').empty();

                //Отображаем данные пользователей
                for(var key in userData){
                    if( $('.convert-right-info #'+key).length <1){
                        //Установить никнейм оппонета в правом сайдбаре
                        $('.convert-right-info .oponent-describer').attr('id',key);
                        //Установить никнейм оппонента в отображение колоды
                        $('.field-battle .cards-bet #card-give-more-oponent').attr('data-user', key);
                        //Установить логин оппонента в его поле битвы
                        $('.convert-battle-front .oponent').attr('data-user', key);
                    }
                    //Создать описание пользователей
                    createUserDescriber(key, userData[key]['img_url'], userData[key]['deck_title']);
                    //Количество карт в колоде
                    $('.convert-left-info .cards-bet ul[data-user='+key+'] .deck .counter').text(userData[key]['deck_count']);
                    //Если у пользователя есть магические эффекты
                    if(userData[key]['magic'].length > 0){
                        //Вывод текущей магии пользователей
                        $('.convert-right-info #' + key + ' .useless-card').children().children('.magic-effects-wrap').empty();
                        createUserMagicFieldCards(key, userData[key]['magic']);
                    }
                    //Если пользователь не готов (не выбраны карты для игры)
                    if( 0 == parseInt(userData[key]['ready'])){
                        console.log('is _not_ready')
                        if (userData[key]['hand'].length > 0) {
                            //Вывод карт руки и колоды
                            $('#selecthandCardsPopup .cards-select-message-wrap span').text(userData[key]['can_change_cards']);
                            for(var i=0; i<userData[key]['hand'].length; i++){
                                $('#selecthandCardsPopup #handCards').append(createFieldCardView(userData[key]['hand'][i], userData[key]['hand'][i]['strength'], true));
                            }
                            //Изменение ширины карт при выборе Карт "Руки"
                            $('#selecthandCardsPopup').show(300, function(){
                                var cardsSelectBlockWidth = $('#selecthandCardsPopup #handCards').width();
                                var singleCardBlockLength = Math.floor(cardsSelectBlockWidth/$('#selecthandCardsPopup #handCards li').length)-1;
                                $('#selecthandCardsPopup #handCards li').css({'width': singleCardBlockLength, 'padding-left': '1px'});
                                $('#selecthandCardsPopup #handCards li img').css({'width': singleCardBlockLength});
                            });

                            //Пользователь поменял карты
                            userChangeDeck(userData[key]['can_change_cards']);
                        }
                    }
                }
            }
        });
    });

    //Отображение активных полей действия карты
    function showCardActiveRow(card, conn, ident){
        $.ajax({
            url:     '/game_get_card_data',
            type:    'GET',
            data:    {card:card},
            success: function(data){
                data = JSON.parse(data);
                $('#notSortableOne').animate({'opacity':'1'}, 240);

                $('#notSortableOne').empty().append('' +
                '<li class="content-card-item chossen-card" id="'+data['id']+'" data-type="'+data['type']+'" data-row="'+data['action_row']+'" >' +
                    '<div class="content-card-item-main" style="background-image: url(/img/card_images/'+data['img_url']+')">' +
                        '<div class="label-power-card">' +
                            '<span class="label-power-card-wrap"><span>'+data['strength']+'</span></span>' +
                        '</div>' +
                        '<div class="hovered-items">' +
                            '<div class="card-name-property"><p>'+data['title']+'</p></div>' +
                            '<div class="block-describe">' +
                                '<div class="block-text-describe">' +
                                    '<div class="block-text-describe-wrap">' +
                                        '<div class="block-text-describe-main">' +
                                            '<div class="block-text-describe-main-wrap"><p>'+data['descript']+'</p></div>' +
                                        '</div>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</li>');

                clearRowSelection();
                if(data['type'] == 'special'){

                    for(var i=0; i<data['actions'].length; i++){
                        var action = ''+data['actions'][i]['action'];
                        if(action == '21')illuminateAside();
                        if((action == '13')||(action == '26')) illuminateOpponent();
                        if((action == '25')||(action == '27')||(action == '28')||(action == '29')) illuminateSelf();
                        if(action == '24') illuminateCards(conn, ident);
                    }
                }else{
                    illuminateCustom('.user', data['action_row']);
                    for(var i=0; i<data['actions'].length; i++){
                        var action = ''+data['actions'][i]['action'];

                        if(action == '12'){ 
                            clearRowSelection();
                            illuminateCustom('.oponent', data['action_row']);
                        }
                    }
                }
            }
        });
    }

    //Отмена подсветки ряда действий карты
    function clearRowSelection(){
        $('.mezhdyblock .bor-beutifull-box #sortable-cards-field-more').removeClass('active');
        $('.convert-stuff .field-for-cards').each(function(){
            $(this).removeClass('active')
            $(this).children('.fields-for-cards-wrap').children('.cards-row-wrap').children('li').removeClass('glow');
        });
    }

    //Подсветка рядов действия карты
    function illuminateAside(){$('.mezhdyblock .bor-beutifull-box #sortable-cards-field-more').addClass('active');}
    function illuminateOpponent(){$('.oponent .convert-stuff .field-for-cards').addClass('active');}
    function illuminateSelf(){$('.user .convert-stuff .field-for-cards').addClass('active');}
    function illuminateCustom(parent, row){
        for(var i=0 ;i<row.length; i++){
            var field = intRowToField(row[i]);
            $('.convert-battle-front '+parent+' .convert-one-field '+field).addClass('active');
        }
    }
    function illuminateCards(conn, ident){
        conn.send(
            JSON.stringify({
                action: 'getOwnBattleFieldData',
                ident: ident
            })
        );
    }
    //Перевод значения названия поля в id ряда
    function intRowToField(row){
        switch(row.toString()){
            case '0': var field = '#meele'; break;
            case '1': var field = '#range'; break;
            case '2': var field = '#superRange'; break;
        }
        return field;
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

    //Создание отображения карты в списке
    function createFieldCardView(cardData, strength, titleView){
        return '' +
        '<li data-cardid="'+cardData['id']+'" data-relative="'+cardData['type']+'">'+
            createCardDescriptionView(cardData, strength, titleView)+
        '</li>';
    }

    //Создание отображения карты
    function createCardDescriptionView(cardData, strength, titleView){
        var result =''+
            '<div class="card-wrap">'+
                '<img src="/img/card_images/'+cardData['img_url']+'" alt="">'+
                '<div class="label-power-card">'+strength+'</div>';
        if(titleView === true){
            result += ''+
            '<div class="hovered-items">'+
                '<div class="card-name-property"><p>'+cardData['title']+'</div>'+
            '</div>';
        }
        result += '</div>';
        return result;
    }

    //Отображение карт "Гармошкой"
    function createCardLayers(handler){
        var shift = 0;
        var zIndex = 6;
        handler.each(function() {
            $(this).css({'left': shift + '%', 'z-index': zIndex});
            shift += 19 - handler.length;
            zIndex++;
        });
    }

    //Пересчет Силы рядов
    function recalculateBattleField(){
        var players = {"oponent-meele":0,"oponent-range":0,"oponent-superRange":0,"user-meele":0,"user-range":0,"user-superRange":0};
        var total = {"oponent":0, "user":0}

        $('.convert-battle-front .convert-stuff .field-for-cards').each(function(){
            var _this = $(this);
            var prefix = 'oponent';
            if($(this).parents('.convert-cards').hasClass('user')){
                prefix = 'user';
            }
            $(this).children('.fields-for-cards-wrap').children('.cards-row-wrap').children('li').each(function(){
                players[prefix+'-'+_this.attr('id')] += parseInt($(this).children('.card-wrap').children('.label-power-card').text());
                total[prefix] += parseInt($(this).children('.card-wrap').children('.label-power-card').text());
            });
        });

        for(var key in players){
            var temp = key.split('-');
            $('.convert-battle-front .'+temp[0]+' .convert-stuff #'+temp[1]).parents('.convert-stuff').children('.field-for-sum').text(players[key]);
        }
        for(var key in total){
            $('.convert-right-info .'+key+'-describer .power-element .power-text').text(total[key]);
        }
    }

    //Вызов popup-окна с сообщением
    function showPopup(ms){
        $('.market-buy-popup .popup-content-wrap').html('<p>' + ms + '</p>');
        $('.market-buy-popup').show(300);
    }

    //Смена идентификатора хода пользователя
    function changeTurnIndicator(login){
        if(login == $('.user-describer').attr('id')){
            $('.user-turn-wrap .turn-indicator').addClass('active');
        }else{
            $('.user-turn-wrap .turn-indicator').removeClass('active');
        }
    }

    //Создание отображения колоды
    function createDeckCardPreview(count, is_user, deck){
        var divClass = (is_user) ? 'card-my-init cards-take-more' : 'card-init';
        var cardList = '';
        if(deck != undefined){
            for(var i=0; i<deck.length; i++){
                cardList += createFieldCardView(deck[i], deck[i]['strength'], true);
            }
            cardList ='<ul class="deck-cards-list">'+cardList+'</ul>';
        }
        return ''+
        '<div class="'+divClass+'">'+
            cardList+
            '<div class="card-otboy-counter deck">'+
                '<div class="counter">'+count+'</div>'+
            '</div>'+
        '</div>';
    }

    //Создание "гармошки" для колоды/отбоя
    function createDeckLayers(){
        $('#card-give-more-user li').each(function(){
            var shift = 0;
            var zIndex = 100;
            $(this).children('.card-my-init').children('ul.deck-cards-list').children('li').each(function(){
                $(this).css({'top': -shift+'px', 'z-index':zIndex});
                shift += 40;
                zIndex--;
            });
        });
    }

    //Отображение магии пользователя
    function showMagic(id) {
        $.ajax({
            url:     '/game_get_magic_data',
            type:    'GET',
            data:    {id:id},
            success: function(data){
                data = JSON.parse(data);
                $('#notSortableOne').animate({'opacity':'1'}, 240);
                $('#notSortableOne').empty().append('' +
                '<li class="content-card-item chossen-card" id="'+data['id']+'" >' +
                '<div class="content-card-item-main" style="background-image: url(/img/card_images/'+data['img_url']+')">' +
                '<div class="label-power-card">' +
                '<span class="label-power-card-wrap"><span>'+data['energy_cost']+'</span></span>' +
                '</div>' +
                '<div class="hovered-items">' +
                '<div class="card-name-property"><p>'+data['title']+'</p></div>' +
                '<div class="block-describe">' +
                '<div class="block-text-describe">' +
                '<div class="block-text-describe-wrap">' +
                '<div class="block-text-describe-main">' +
                '<div class="block-text-describe-main-wrap"><p>'+data['descript']+'</p></div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</li>');
            }
        });
    }
    
    function buildBattleField(fieldData){
        //Очищение полей
        $('.mezhdyblock #sortable-cards-field-more, .convert-battle-front #p1 .cards-row-wrap, .convert-battle-front #p1 .image-inside-line, .convert-battle-front #p2 .cards-row-wrap, .convert-battle-front #p2 .image-inside-line').empty();
        for(var fieldType in fieldData){
            if(fieldType == 'mid'){
                for(var i=0; i<fieldData['mid'].length; i++){
                    $('.mezhdyblock #sortable-cards-field-more').append(createFieldCardView(fieldData['mid'][i]['card'], 0, false));
                }
            }else{
                for(var i=0; i<fieldData[fieldType].length; i++){
                    var row = intRowToField(i);
                    for(var j=0; j<fieldData[fieldType][i]['warrior'].length; j++){
                        $('.convert-battle-front #'+fieldType+' .convert-stuff '+row+' .cards-row-wrap').append(createFieldCardView(fieldData[fieldType][i]['warrior'][j]['card'], fieldData[fieldType][i]['warrior'][j]['strength'], false));
                    }
                    if(fieldData[fieldType][i]['special'] != ''){
                        $('.convert-battle-front #'+fieldType+' .convert-stuff '+row+' .image-inside-line').append(createCardDescriptionView(fieldData[fieldType][i]['special']['card'], 0, false));
                    }
                }
            }
        }
        //Переформирование отображения поля битвы противника
        $('.oponent .convert-stuff .field-for-cards').each(function(){
            var handler = $('.oponent #'+$(this).attr('id')+' .cards-row-wrap li');
            createCardLayers(handler);
        });
        //Переформирование отображения поля битвы пользователя
        $('.user .convert-stuff .field-for-cards').each(function(){
            var handler = $('.user #'+$(this).attr('id')+' .cards-row-wrap li');
            createCardLayers(handler);
        });
        //Переформирование отображения поля боковых спец. карт
        createCardLayers($('.mezhdyblock #sortable-cards-field-more li'));
        recalculateBattleField();//Пересчет значений силы
        console.log('battleField id builted');
    }
    //Действия по умолчанию
    //Пересчет сил пользователей
    recalculateBattleField();
    //Отображение карт поля битвы
    $('.oponent .convert-stuff .field-for-cards').each(function(){
        var handler = $('.oponent #'+$(this).attr('id')+' .cards-row-wrap li');
        if(handler.length){
            createCardLayers(handler);
        }
    });
    $('.user .convert-stuff .field-for-cards').each(function(){
        var handler = $('.user #'+$(this).attr('id')+' .cards-row-wrap li');
        if(handler.length){
            createCardLayers(handler);
        }
    });
    createCardLayers($('.mezhdyblock #sortable-cards-field-more li'));
    //Отображение Колоды или Отбоя
    $('.convert-left-info .cards-bet #card-give-more-user').on('click', '.card-my-init', function(){
        if($(this).css('pointer-events') != 'none'){
            $(this).children('ul.deck-cards-list').toggleClass('active');
        }
    });
    createDeckLayers();
});