$(document).ready(function(){
    function array_unique( inputArr ) {
        var result = [];
        $.each(inputArr, function(i, el){
            if($.inArray(el, result) === -1) result.push(el);
        });

        return result;
    }

    //Выбор Действия магии
    function loadMagicActionsList(action){
        var token = $('input[name=_token]').val();
        $.ajax({
            url:        '/admin/magic/get_params_by_action/',
            type:       'GET',
            data:       {token:token, action:action},
            success:    function(data){
                data = JSON.parse(data);
                var actionTable = '';
                for(var i =0; i<data['html_data'].length; i++){
                    actionTable += '<tr><td><label>' +data['html_data'][i][0] + ':</label></td><td>' + data['html_data'][i][1] + '</td></tr>';
                }
                //Название действия
                $('#tableMagicActionList tbody').empty().append(actionTable);
                //HTML действия
                $('#tableMagicActionList thead td:eq(2)').empty().append(data['descr']);
                GetAllCardsSelector();
            }
        });
    }
    loadMagicActionsList($('select[name=magic_actions_select]').val());
    $('select[name=magic_actions_select]').change(function(){
        loadMagicActionsList($(this).val());
    });

    //Список доступных карт
    function GetAllCardsSelector(){
        $.ajax({
            url:    '/admin/get_all_cards_selector',
            type:   'GET',
            success:function(data){
                $('#tableMagicActionList tr td #MAplayCardsFromDeck').empty().append(data);
                $('#tableMagicActionList tr td #MAplayCardsFromDeck select').prepend('<option value="0">Случайно</option>');

                //Определение состояния radio Действия "Находит в колоде карту (можно и не одну) и немедленно играет её"
                if($('#tableMagicActionList tr td input[name=MAplayCardsFromDeck_cardType]').val() == 0){
                    $('#tableMagicActionList tr td #MAplayCardsFromDeck').parents('tr').show();
                    $('#tableMagicActionList tr td input[name=MAplayCardsFromDeck_ActionRow]').parents('tr').hide();
                }else{
                    $('#tableMagicActionList tr td #MAplayCardsFromDeck').parents('tr').hide();
                    $('#tableMagicActionList tr td input[name=MAplayCardsFromDeck_ActionRow]').parents('tr').show();
                }

                $('#tableMagicActionList tr td input[name=MAplayCardsFromDeck_cardType]').change(function(){
                    if($(this).val() == 0){
                        $('#tableMagicActionList tr td #MAplayCardsFromDeck').parents('tr').show();
                        $('#tableMagicActionList tr td input[name=MAplayCardsFromDeck_ActionRow]').parents('tr').hide();
                        GetAllCardsSelector();
                    }else{
                        $('#tableMagicActionList tr td #MAplayCardsFromDeck').parents('tr').hide();
                        $('#tableMagicActionList tr td input[name=MAplayCardsFromDeck_ActionRow]').parents('tr').show();
                    }
                });
            }
        })
    }
    GetAllCardsSelector();

    //Функция создания целевых рас/рядов действия
    function setCheckboxesToJson(object){
        var realActionRow = '[';
        var displayActionRow = '';

        var checked = 0; //если ничего не выбрано
        object.each(function(){
            realActionRow += '"' + $(this).val() + '", ';
            displayActionRow += $(this).next().text() + ', ';
            checked = 1;
        });

        if(1 == checked) {
            realActionRow = fixString(realActionRow);
            displayActionRow = fixString(displayActionRow);
        }
        realActionRow += ']';
        displayActionRow+= ';<br>';

        return [realActionRow, displayActionRow];
    }

    function checkGroupTable(object){

        //создание json массива
        var realActionRow = '[';
        //создание строки описания
        var displayActionRow = '';

        var checked = 0; //если ничего не выбрано
        //выборка из таблицы добавленых групп (1е поле - удалить, 2е - название группы, 3я - hidden-> id групп)
        object.parents('.container-wrap').children('table.edition').children('tbody').children('tr').each(function(){
            realActionRow += '"' + $(this).children('td:eq(2)').text() + '", ';
            displayActionRow += $(this).children('td:eq(1)').text() + ', ';
            checked = 1;
        });

        if(1 == checked){
            realActionRow = fixString(realActionRow);
            displayActionRow = fixString(displayActionRow);
        }
        realActionRow += ']';
        realActionRow = array_unique(JSON.parse(realActionRow));
        realActionRow = '[' + realActionRow + ']';
        displayActionRow += ';<br>';

        return [realActionRow, displayActionRow];
    }

    function fixString(row){
        row = row.substr(0, row.length -2);
        return row;
    }

    //Добавление Карты в Действие
    $(document).on('click', 'input[name=MAaddCardToPlayFromDeck]', function(){
        var cardId = $(this).prev('#MAplayCardsFromDeck').children('select').val();
        var cardTitle = $(this).prev('#MAplayCardsFromDeck').children('select').find('option:selected').text();
        $(this).next('.edition').append('<tr><td><a class="drop" href="#"></a></td><td>' + cardTitle + '</td><td style="display: none;">' + cardId + '</td></tr>').show();
        $('.edition tr td').on('click', 'a.drop', function(e){
            e.preventDefault();
            $(this).parent().parent().remove();
        });
    });

    //Действие "Убийца" -> Нужное для совершения убийства количество силы карт воинов в ряду
    $(document).on('change', 'input[name=MAkiller_recomendedTeamateForceAmount_OnOff]', function(){
        console.log($(this).parent().next().hasClass('disactive'));
        $(this).parent().next('.container-wrap').toggleClass('disactive');
        $(this).parent().next().next('.container-wrap').toggleClass('disactive');
    });

    //Добавление действия в Волшебство
    function actionsPreviewTable(object, display, actions){
        object.parent().parent().children('#magicCurrentActions').append('<tr><td><a class="drop" href="#"></a></td><td>' + display + '</td><td style="display: none;">' + actions + '</td></tr>');
        $('#magicCurrentActions tr td').on('click', 'a.drop', function(e){
            e.preventDefault();
            $(this).parent().parent().remove();
        });
    }

    //Добавление действия
    $(document).on('click', 'input[name=addMoreMagicActions]', function(){
        //Узнаем мип действия карты
        var actionType = $('select[name=magic_actions_select] option:selected').attr('data-title');
        var _this = $(this);

        //отображение описания действия для пользователя
        displayActionRow= '<ins>' + $('select[name=magic_actions_select] option:selected').text() + '</ins>: <br>';

        //описания действия для заноса в БД
        realActionRow   = '{"action": "' + $('select[name=magic_actions_select]').val() + '"';

        switch(actionType) {
            case 'blokirovka_osobyh_sposobnostej':
                realActionRow += '}';
                break;

            case 'voodushevlenie':
                var temp = setCheckboxesToJson($('.container-wrap input[name=MAinspiration_ActionRow]:checked'));
                realActionRow += ', "MAinspiration_ActionRow": ' + temp[0];
                displayActionRow += ' - Дальность: ' + temp[1];

                //Выбор модификатора силы: 0-умножение/1- добавление силы
                realActionRow += ', "MAinspiration_modificator": "' + $('input[name=MAinspiration_modificator]:checked').val() + '"';
                displayActionRow += ' - Модификатор силы: ' + $('input[name=MAinspiration_modificator]:checked').next('label').text() + ';<br>';

                //Значение силы
                realActionRow += ', "MAinspiration_multValue": "' + $('input[name=MAinspiration_multValue]').val() + '"}';
                displayActionRow += ' - Значение: ' + $('input[name=MAinspiration_multValue]').val() + ';<br>';
                break;

            case 'dobavit_sily':
                realActionRow += ', "MAaddStrengthValue": "' + $('input[name=MAaddStrengthValue]').val() + '"';
                displayActionRow += ' - Значение повышения силы на: ' + $('input[name=MAaddStrengthValue]').val() + ' единиц;<br>';

                var temp = setCheckboxesToJson($('.container-wrap input[name=MAaddStrength_ActionRow]:checked'));
                realActionRow += ', "MAaddStrength_ActionRow": ' + temp[0];
                displayActionRow += ' - Ряд действия: ' + temp[1];

                realActionRow += '}';
                break;

            case 'zabrat_karty_v_ruku':
                realActionRow += ', "MAgetCardsFromBtlField": "' + $('input[name=MAgetCardsFromBtlField]').val() + '"';
                displayActionRow += ' - Количество карт: ' + $('input[name=MAgetCardsFromBtlField]').val() + '<br>';

                var temp = setCheckboxesToJson($('.container-wrap input[name=MAgetCardsSource]:checked'));
                realActionRow += ', "MAgetCardsSource": ' + temp[0];
                displayActionRow += ' - Карты берутся из: ' + temp[1];

                realActionRow += ', "MAgetCardsSourceOwn": "' + $('input[name=MAgetCardsSourceOwn]:checked').val() + '"';
                if($('input[name=MAgetCardsSourceOwn]:checked').val() == 0){
                    displayActionRow += ' - Используются свои карты<br>';
                }else{
                    displayActionRow += ' - Карты берутся у противника<br>';
                }

                realActionRow += ', "MAgetCardsPlayIt": "' + $('input[name=MAgetCardsPlayIt]').prop('checked') + '"';
                if($('input[name=MAgetCardsPlayIt]').prop('checked') === true){
                    displayActionRow += ' - Карты играются немедленно.<br>';
                }else{
                    displayActionRow += ' - Карты остаются в руке.<br>';
                }

                realActionRow += ', "MAgetCardsMethod": "' + $('input[name=MAgetCardsMethod]:checked').val() + '"}';
                if($('input[name=MAgetCardsMethod]:checked').val() == 0){
                    displayActionRow += ' - Карты выбираются вручную.<br>';
                }else{
                    displayActionRow += ' - Карты выбираются случайно.<br>';
                }

                break;

            case 'otmena_negativnyh_effektov':
                realActionRow += '}';
                break;

            case 'otmenit_voodushevlenie':
                var temp = setCheckboxesToJson($('.container-wrap input[name=MAcancelInspir_ActionRow]:checked'));
                realActionRow += ', "MAcancelInspir_ActionRow": ' + temp[0];
                displayActionRow += ' - Дальность: ' + temp[1];
                realActionRow += '}';
                break;

            case 'podsmotret_karty':
                realActionRow += ', "MAlookToEnemyHand": "' + $('input[name=MAlookToEnemyHand]').val() + '"}';
                displayActionRow += ' - Количество карт: ' + $('input[name=MAlookToEnemyHand]').val() + ' единиц;<br>';
                break;

            case 'poluchit_iz_kolody_karty_i_sygrat_ih':
                realActionRow += ', "MAplayCardsFromDeck_cardType": "' + $('input[name=MAplayCardsFromDeck_cardType]:checked').val() + '"';

                if($('input[name=MAplayCardsFromDeck_cardType]:checked').val() == 0){
                    var temp = checkGroupTable($('select[name=currentCard]'));
                    realActionRow += ', "currentCard": ' + temp[0];
                    displayActionRow += ' - Получить из колоды карты: ' + temp[1];
                }else{
                    var temp = setCheckboxesToJson($('.container-wrap input[name=MAplayCardsFromDeck_ActionRow]:checked'));
                    realActionRow += ', "MAplayCardsFromDeck_ActionRow": ' + temp[0];
                    displayActionRow += ' - Получить случайную карту из рядов: ' + temp[1];
                }

                realActionRow += '}';
                break;

            case 'ponizit_silu_u_protivnika':
                var temp = setCheckboxesToJson($('.container-wrap input[name=MAdecrStrength_ActionRow]:checked'));
                realActionRow += ', "MAdecrStrength_ActionRow": '+ temp[0];
                displayActionRow += ' - Ряд действия: ' + temp[1];

                realActionRow += ', "MAdecrStrengthValue": "' + $('input[name=MAdecrStrengthValue]').val() + '"}';
                displayActionRow += ' - Значение силы: ' + $('input[name=MAdecrStrengthValue]').val() + '<br>';
                break;

            case 'sbros_i_podnyatie_kart_iz_kolody':
                realActionRow += ', "MAthrowCardToRetreat": "' + $('input[name=MAthrowCardToRetreat]').val() + '"';
                displayActionRow += ' - Сколько карт сбросить: ' + $('input[name=MAthrowCardToRetreat]').val()+ '<br>';

                realActionRow += ', "MAthrowCardToGetFormDeck": "' + $('input[name=MAthrowCardToGetFormDeck]').val() + '"';
                displayActionRow += ' - Сколько взять с колоды: ' + $('input[name=MAthrowCardToGetFormDeck]').val()+ '<br>';

                realActionRow += ', "MAthrowCardAllowCase": "' + $('input[name=MAthrowCardAllowCase]:checked').val() + '"}';
                if($('input[name=MAthrowCardAllowCase]:checked').val() == 0){
                    displayActionRow += ' - Карты из колоды беруться случайно<br>';
                }else{
                    displayActionRow += ' - Карты из колоды выбираются игроком<br>';
                }
                break;

            case 'sbros_kart_protivnika_v_otboj':
                realActionRow += ', "MAkickOutEnemyCards": "' + $('input[name=MAkickOutEnemyCards]').val() + '"}';
                displayActionRow += ' - Количество карт: ' + $('input[name=MAkickOutEnemyCards]').val()+ '<br>';
                break;

            case 'ubijstvo':
                var temp = setCheckboxesToJson($('.container-wrap input[name=MAkiller_ActionRow]:checked'));
                realActionRow += ', "MAkiller_ActionRow": ' + temp[0];
                displayActionRow += ' - Ряд действия: ' + temp[1];

                //Условие "Нужное для совершения убийства количество силы карт воинов в ряду"
                if (0 == $('input[name=MAkiller_recomendedTeamateForceAmount_OnOff]:checked').val()) {
                    realActionRow += ', "MAkiller_recomendedTeamateForceAmount_OnOff": "0"';
                } else {
                    realActionRow += ', "MAkiller_recomendedTeamateForceAmount_OnOff": "' + $('input[name=MAkiller_recomendedTeamateForceAmount]').val() + '"';
                    displayActionRow += ' - Количество силы необходимое для совершения убийства воинов: ' + $('input[name=MAkiller_recomendedTeamateForceAmount]').val() + ' ';
                    realActionRow += ', "MAkiller_recomendedTeamateForceAmount_Selector": "' + $('select[name=MAkiller_recomendedTeamateForceAmount_Selector]').val() + '"';
                    displayActionRow += '(' + $('select[name=MAkiller_recomendedTeamateForceAmount_Selector] option:selected').text() + ')<br>';
                }

                //Условие "Порог силы воинов противника для совершения убийства"
                realActionRow += ', "MAkiller_enemyStrenghtLimitToKill": "' + $('input[name=MAkiller_enemyStrenghtLimitToKill]').val() + '"';
                displayActionRow += ' - Порог силы воинов противника для совершения убийства: ' + $('input[name=MAkiller_enemyStrenghtLimitToKill]').val() + ';<br>';

                //Условие "Вариация количества убийств"
                realActionRow += ', "MAkiller_killAllOrSingle": "' + $('input[name=MAkiller_killAllOrSingle]:checked').val() + '"';
                displayActionRow += ' - Вариация количества убийств: ' + $('input[name=MAkiller_killAllOrSingle]:checked').next().text() + ';<br>';

                //Условие "Может бить своих юнитов"
                realActionRow += ', "MAkiller_atackTeamate": "' + $('input[name=MAkiller_atackTeamate]:checked').val() + '"';
                displayActionRow += ' - Может бить своих юнитов по указаным выше параметрах: ' + $('input[name=MAkiller_atackTeamate]:checked').next().text() + ';<br>';

                //Условие "Игнорирует иммунитет к убийству"
                realActionRow += ', "MAkiller_ignoreKillImmunity": "' + $('input[name=MAkiller_ignoreKillImmunity]:checked').val() + '"';
                displayActionRow += ' - Игнорирует иммунитет к убийству: ' + $('input[name=MAkiller_ignoreKillImmunity]:checked').next().text() + ';<br>';

                //Условие "Качество убиваемой карты"
                realActionRow += ', "MAkiller_killedQuality_Selector": "' + $('select[name=MAkiller_killedQuality_Selector]').val() + '"';
                displayActionRow += ' - Качество убиваемой карты: ' + $('select[name=MAkiller_killedQuality_Selector] option:selected').text();

                realActionRow += '}';
                break;
        }
        actionsPreviewTable(_this, displayActionRow, realActionRow);
    });

    /*
     * Волшебство
     */
    //Добавление
    $('input[name=magicAdd]').click(function(){
        var token = $('input[name=_token]').val();

        var races = [];
        $('#racesToUse input[type=checkbox]:checked').each(function(){
            races.push($(this).val());
        });

        var magic_actions = []; //Действия волшебства
        $('#magicCurrentActions tr').each(function(){
            magic_actions.push( $(this).children('td:eq(2)').text() );
        });

        //Имитация отправки данных через форму
        var formData = new FormData();
        //Наполнение формы
        formData.append( 'token', token );
        formData.append( 'title', $('input[name=magic_title]').val().trim());
        formData.append( 'description', $('textarea[name=magic_descr]').val().trim());
        formData.append( 'img_url', $('input[name=magicAddImg]').prop('files')[0] );
        formData.append( 'races', JSON.stringify(races));
        formData.append( 'magic_actions', '[' + magic_actions + ']');                     // Json-массив "Действий карты"
        formData.append( 'energyCost', $('input[name=energy_cost]').val());
        formData.append( 'price_gold', $('input[name=price_gold]').val());
        formData.append( 'price_silver', $('input[name=price_silver]').val());
        formData.append( 'usage_count', $('input[name=usage_count]').val());

        $.ajax({
            url:        '/admin/magic/add',
            headers:    {'X-CSRF-TOKEN': token},
            type:       'POST',
            processData:false,
            contentType:false,
            data:       formData,
            success:    function(data){
                if(data == 'success') location = '/admin/magic';
            }
        });
    });
    //Изменение
    $('input[name=magicEdit]').click(function(){
        var token = $('input[name=_token]').val();

        var races = [];
        $('#racesToUse input[type=checkbox]:checked').each(function(){
            races.push($(this).val());
        });

         var magic_actions = []; //Действия волшебства
         $('#magicCurrentActions tr').each(function(){
             magic_actions.push( $(this).children('td:eq(2)').text() );
         });
        //Имитация отправки данных через форму
        var formData = new FormData();
        //Наполнение формы
        formData.append( 'token', token );
        formData.append( '_method', 'PUT' );
        formData.append( 'id', $('input[name=effect_id]').val() );
        formData.append( 'title', $('input[name=magic_title]').val().trim());
        formData.append( 'description', $('textarea[name=magic_descr]').val().trim());
        formData.append( 'img_url', $('input[name=magicAddImg]').prop('files')[0] );
        formData.append( 'img_old_url', $('img#oldImgUrl').attr('alt'));
        formData.append( 'races', JSON.stringify(races));
        formData.append( 'magic_actions', '[' + magic_actions + ']');                     // Json-массив "Действий карты"
        formData.append( 'energyCost', $('input[name=energy_cost]').val());
        formData.append( 'price_gold', $('input[name=price_gold]').val());
        formData.append( 'price_silver', $('input[name=price_silver]').val());
        formData.append( 'usage_count', $('input[name=usage_count]').val());

        $.ajax({
            url:        '/admin/magic/edit',
            headers:    {'X-CSRF-TOKEN': token},
            type:       'POST',
            processData:false,
            contentType:false,
            data:       formData,
            success:    function(data){
                if(data == 'success') location = '/admin/magic';
            }
        });
    });
});