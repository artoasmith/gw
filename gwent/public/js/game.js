$(window).load(function(){
    $.get('/get_socket_settings', function(data){

        var socketResult = JSON.parse(data);

        var ident = {
            battleId:   socketResult['battle'],
            userId:     socketResult['user'],
            hash:       socketResult['hash']
        };

        var conn = new WebSocket('ws://'+socketResult['dom']+':8080');
        var stat = false;

        var timeleft = 0; //количество секунд до окончания хода, передавать в таймер (обновляется с battleTimer)
        var timedeley = socketResult['timeOut']*1000;
        var timer = false;

        function battleTimer(time) { //обновление значения таймера в ходе боя
            if(typeof timer != "boolean")
                clearTimeout(timer);
            timeleft = time;

            time = time*1000;
            timer = setTimeout(timeOutCheck, time);
        }

        function timeOutCheck(){ //запускается когда заканчивается время хода, для проверки даннных на сервере
            conn.send(
                JSON.stringify({
                    action: 'checkBattle',
                    ident: ident
                })
            );
        }

        conn.onopen = function (data) {
            console.log('connected');
            stat = true;
            conn.send(
                JSON.stringify({
                    action: 'join',
                    ident: ident
                })
            );
        };

        conn.onclose = function (event) {
            stat = false;

            if (event.code == 1000)
                reason = "Normal closure, meaning that the purpose for which the connection was established has been fulfilled.";
            else if(event.code == 1001)
                reason = "An endpoint is \"going away\", such as a server going down or a browser having navigated away from a page.";
            else if(event.code == 1002)
                reason = "An endpoint is terminating the connection due to a protocol error";
            else if(event.code == 1003)
                reason = "An endpoint is terminating the connection because it has received a type of data it cannot accept (e.g., an endpoint that understands only text data MAY send this if it receives a binary message).";
            else if(event.code == 1004)
                reason = "Reserved. The specific meaning might be defined in the future.";
            else if(event.code == 1005)
                reason = "No status code was actually present.";
            else if(event.code == 1006)
                reason = "The connection was closed abnormally, e.g., without sending or receiving a Close control frame";
            else if(event.code == 1007)
                reason = "An endpoint is terminating the connection because it has received data within a message that was not consistent with the type of the message (e.g., non-UTF-8 [http://tools.ietf.org/html/rfc3629] data within a text message).";
            else if(event.code == 1008)
                reason = "An endpoint is terminating the connection because it has received a message that \"violates its policy\". This reason is given either if there is no other sutible reason, or if there is a need to hide specific details about the policy.";
            else if(event.code == 1009)
                reason = "An endpoint is terminating the connection because it has received a message that is too big for it to process.";
            else if(event.code == 1010) // Note that this status code is not used by the server, because it can fail the WebSocket handshake instead.
                reason = "An endpoint (client) is terminating the connection because it has expected the server to negotiate one or more extension, but the server didn't return them in the response message of the WebSocket handshake. <br /> Specifically, the extensions that are needed are: " + event.reason;
            else if(event.code == 1011)
                reason = "A server is terminating the connection because it encountered an unexpected condition that prevented it from fulfilling the request.";
            else if(event.code == 1015)
                reason = "The connection was closed due to a failure to perform a TLS handshake (e.g., the server certificate can't be verified).";
            else
                reason = "Unknown reason";

            showPopup(reason);
        };

        conn.onerror = function (e) {
            showPopup('Socket error');
        };


        /*--On response from server--*/
        conn.onmessage = function (e) {
            var resp = JSON.parse(e.data);
            if(typeof resp.ERROR != 'undefined')
                return showPopup(resp.ERROR);

            if(typeof resp.MESSAGE != 'undefined')
                showPopup(resp.MESSAGE);

            console.log(resp);
            var action = 'none';
            if(typeof resp.action != 'undefined')
                action = resp.action;

            //battle info logic
            if(typeof resp.battleInfo != 'undefined'){
                console.log('battle info logic with fight status '+resp.battleInfo.fightStatus);

                var fightStatus = parseInt(resp.battleInfo.fightStatus);
                switch (fightStatus){
                    case 0: //логика ожидание других играков

                        break;
                    case 1: //логика подготовки к бою
                        var error = 0;

                        for(var i=0; i<resp.battleInfo.members.length; i++){
                            if(resp.battleInfo.members[i]['online'] !== true){
                                error = 1;
                                break;
                            }
                        }
                        if(error == 0){
                            console.log('game_start ajax');
                            var token = $('.market-buy-popup input[name=_token]').val().trim();
                            $.ajax({
                                url:    '/game_start',
                                type:   'PUT',
                                headers:{'X-CSRF-TOKEN':token},
                                data:   {battle_id:resp.battleInfo['id']},
                                success:function(data){
                                    data = JSON.parse(data);
                                    if(data['message'] == 'success'){
                                        window.usersData = data['userData'];
                                        //Формирование данных пользователей и окна выбора карт
                                        buildPlayRoomView(window.usersData);
                                    }
                                }
                            });
                        }

                        console.log(resp);
                        break;
                    case 2: //логика хода боя
                        if(typeof resp.battleInfo.endTime == 'number' && resp.battleInfo.endTime>0) //обновления таймеров хода
                            battleTimer(resp.battleInfo.endTime);

                        break;
                    case 3: //логика окончаного боя

                        break
                }
            }
        };


        //Формирование данных пользователя
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


        function handReformDeck(user){
            var shift = 0;
            $('.user-card-stash #sortableUserCards li').each(function(){
                $(this).width($(this).width()+45);
                $(this).css({'left':shift+'%'});
                shift += 8;
            });

            $('#sortableUserCards').on('mouseover', 'li', function(){
                $(this).css({'top': '-80px', 'z-index': '300'});
                var _this = $(this);
                $(this).mouseout(function(){
                    _this.css({'top': '0px', 'z-index': '6'});
                });
            });

            $('#sortableUserCards').on('click', 'li', function(){
                var cardData = [];
                for(var i=0; i<window.usersData[user]['hand'].length; i++){
                    if( $(this).attr('data-cartid') == window.usersData[user]['hand'][i]['id']){
                        cardData = window.usersData[user]['hand'][i];
                    }
                }
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
        }

        changeDeckCardsWidth('.user-card-stash', '#sortableUserCards');
        handReformDeck($('.convert-battle-front .user').attr('data-user'));


        function showPopup(ms){
            $('#buyingCardOrmagic .popup-content-wrap').html('<p>' + ms + '</p>');
            $('#buyingCardOrmagic').show(300).delay(3000).hide(400);
        }

    });
});