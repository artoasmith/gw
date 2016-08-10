$(window).load(function(){
    $.get('/get_socket_settings', function(data){
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

            conn.onclose = function(event){}

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
                                    //Формирование данных пользователей и окна выбора карт
                                    buildRoomPreview(data['userData']);
                                }
                            }
                        });
                        if(result.userTurn == $('.user-describer').attr('id')){                                                        
                            userMakeAction(1);
                        }else{
                            userMakeAction(0);
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
                }
            }
            
            
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
                        if (userData[key]['hand'].length > 0) {
                            //Вывод карт руки и колоды
                            //createUserCardSelect(userData[key]['hand'], userData[key]['can_change_cards']);
                            $('#selecthandCardsPopup .cards-select-message-wrap span').text(userData[key]['can_change_cards']);
                            for(var i=0; i<userData[key]['hand'].length; i++){
                                $('#selecthandCardsPopup #handCards').append(createFieldCardView(userData[key]['hand'][i], true));
                            }
                            //Изменение ширины карт при выборе Карт "Руки"
                            $('#selecthandCardsPopup').show(300, function () {
                                var cardsSelectBlockWidth = $('#selecthandCardsPopup #handCards').width();
                                var singleCardBlockLength = Math.floor(cardsSelectBlockWidth/$('#selecthandCardsPopup #handCards li').length)-1;
                                $('#selecthandCardsPopup #handCards li').css({'width': singleCardBlockLength, 'padding-left': '1px'});
                                $('#selecthandCardsPopup #handCards li img').css({'width': singleCardBlockLength});
                            });

                            //Пользователь поменял карты
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

                            $('.user-card-stash #sortableUserCards').empty();
                            for(var i=0; i< data[$('.user-describer').attr('id')]['hand'].length; i++){
                                $('.user-card-stash #sortableUserCards').append(createFieldCardView(data[$('.user-describer').attr('id')]['hand'][i], true));
                            }

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
            function userMakeAction(allowActions){
                $('ul li[data-cardid]').click(function(){
                    if($(this).hasClass('active')){
                        $(this).removeClass('active');
                        clearRowSelection();
                        $('#notSortableOne').empty().css({'opacity': 0});
                    }else{
                        $(this).parents('ul').children('li').removeClass('active');
                        $(this).addClass('active');
                    }
                    if($(this).hasClass('active')){
                        $.ajax({
                            url:     '/game_get_card_data',
                            type:    'GET',
                            data:    {card:$(this).attr('data-cardid')},
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
                                if(allowActions !== 0){
                                    if(data['type'] == 'special'){
                                        for(var i=0; i<data['actions'].length; i++){
                                            var action = ''+data['actions'][i]['action'];
                                            if((action == '21')||(action == '26'))illuminateAside();
                                            if(action == '13') illuminateOpponent();
                                            if((action == '24')||(action == '25')||(action == '27')||(action == '28')||(action == '29')) illuminateSelf();
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
                            }
                        });
                    }
                });                    
            }           
            
            createCardLayers($('#sortableUserCards li'));
    
        });
    });
    
    function clearRowSelection(){
        $('.mezhdyblock .bor-beutifull-box #sortable-cards-field-more').removeClass('active');
        $('.convert-stuff .field-for-cards').each(function(){
            $(this).removeClass('active')
        });
    }
    function illuminateAside(){$('.mezhdyblock .bor-beutifull-box #sortable-cards-field-more').addClass('active');}
    function illuminateOpponent(){$('.oponent .convert-stuff .field-for-cards').addClass('active');}
    function illuminateSelf(){$('.user .convert-stuff .field-for-cards').addClass('active');}
    function illuminateCustom(parent, row){
        
        for(var i=0 ;i<row.length; i++){
            
            switch(row[i].toString()){
                case '0': var field = '#meele'; break
                case '1': var field = '#range'; break
                case '2': var field = '#superRange'; break
            }
            console.log(field)
            $('.convert-battle-front '+parent+' .convert-one-field '+field).addClass('active')
        }
        
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
    function createFieldCardView(cardData, titleView){
        return '' +
        '<li data-cardid="'+cardData['id']+'" data-relative="'+cardData['type']+'">'+
            createCardDescriptionView(cardData, titleView)+
        '</li>';
    }

    //Создание отображения карты
    function createCardDescriptionView(cardData, titleView){
        var result =''+
            '<div class="card-wrap">'+
                '<img src="/img/card_images/'+cardData['img_url']+'" alt="">'+
                '<div class="label-power-card">'+cardData['strength']+'</div>';
        if(titleView === true){
            result += ''+
            '<div class="hovered-items">'+
                '<div class="card-name-property"><p>'+cardData['title']+'</div>'+
            '</div>';
        }
        result += '</div>';
        return result;
    }
    
    function createCardLayers(handler){
        var shift = 0;
        var zIndex = 6;
        handler.each(function() {
            $(this).css({'left': shift + '%', 'z-index': zIndex});
            shift += 19 - handler.length;
            zIndex++;
        });
    }
    
    function showPopup(ms){
        $('.market-buy-popup .popup-content-wrap').html('<p>' + ms + '</p>');
        $('.market-buy-popup').show(300);
    }
});