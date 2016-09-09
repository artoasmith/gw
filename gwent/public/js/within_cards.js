$(document).ready(function(){
    function array_unique( inputArr ) {
        var result = [];
        $.each(inputArr, function(i, el){
            if($.inArray(el, result) === -1) result.push(el);
        });

        return result;
    }

    //Автозаполнение селекторов групп
    function getViewCardGroups(){
        var token = $('input[name=_token]').val();
        $.ajax({
            url:        '/admin/cards/get_card_groups',
            type:       'GET',
            data:       {token:token},
            success:    function(data){
                data = JSON.parse(data);
                var selectorOptions = '';
                for(var i=0; i<data.length; i++){
                    selectorOptions += '<option value="' + data[i]['id'] + '">' + data[i]['title'] + '</option>';
                }
                $('body #group_of_cards').empty().append(selectorOptions);
                $('body select[name=addCardToGroup]').empty().append(selectorOptions);
            }
        });
    }
    getViewCardGroups();

    //Выбор Действия карты
    function loadCardActionsList(action){
        var token = $('input[name=_token]').val();
        $.ajax({
            url:        '/admin/cards/get_params_by_action/',
            type:       'GET',
            data:       {token:token, action:action},
            success:    function(data){
                data = JSON.parse(data);
                var actionTable = '';
                for(var i =0; i<data['html_data'].length; i++){
                    actionTable += '<tr><td><label>' +data['html_data'][i][0] + ':</label></td><td>' + data['html_data'][i][1] + '</td></tr>';
                }
                //Название действия
                $('#tableActionList tbody').empty().append(actionTable);
                //HTML действия
                $('#tableActionList thead td:eq(2)').empty().append(data['descr']);
                //Заполнение груп
                getViewCardGroups();

            }
        });
    }

    //Выбор "Противник использовал способность"
    function userCastsMagic(){
        var token = $('input[name=_token]').val();
        $.ajax({
            url:    '/admin/get_magic_effects',
            type:       'GET',
            data:       {token:token},
            success:function(data){
                $('#group_of_abilities').empty().append(data);
                $('.container-wrap input[name=addAbility]').click(function(){
                    $(this).parent().children('.edition').append('<tr><td><a href="#" class="drop"></a></td><td>' + $('#group_of_abilities option:selected').text() + '</td><td style="display: none;">' + $('#group_of_abilities').val() + '</td></tr>');
                    $('.edition tr td').on('click', 'a.drop', function(e){
                        e.preventDefault();
                        $(this).parent().parent().remove();
                    });
                });
            }
        });
    }

    //Добавление действия в Карту
    function actionsPreviewTable(object, display, actions){
        object.parent().parent().children('#cardCurrentActions').append('<tr><td><a class="drop" href="#"></a></td><td>' + display + '</td><td style="display: none;">' + actions + '</td></tr>');
        $('#cardCurrentActions tr td').on('click', 'a.drop', function(e){
            e.preventDefault();
            $(this).parent().parent().remove();
        });
    }

    //Фикс json-массива: убираем из строки последние два символа -> ", "
    function fixString(row){
        row = row.substr(0, row.length -2);
        return row;
    }

    //Функция создания целевых групп действия
    function checkGroupTable(object){

        //создание json массива
        var realActionRow = '[';
        //создание строки описания
        var displayActionRow = '';

        var checked = 0; //если ничего не выбрано
        //выборка из таблицы добавленых групп (1е поле - удалить, 2е - название группы, 3я - hidden-> id групп)
        object.parent().children('table.edition').children('tbody').children('tr').each(function(){
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


    //Действия "Боевое Братство", "Лекарь" выбор действия на одиночные(одинаковые) или на группу. Прячет выбор группы
    $(document).on('change', 'input[name=CAbloodBro_actionToGroupOrSame], input[name=CAhealer_groupOrSingle], input[name=CAsupport_actionToGroupOrAll], input[name=CAfear_actionToGroupOrAll], input[name=CAkiller_groupOrSingle]', function(){
        $(this).parent().next().toggleClass('disactive');
    });

    //Действие "Убийца" -> Нужное для совершения убийства количество силы карт воинов в ряду
    $(document).on('change', 'input[name=CAkiller_recomendedTeamateForceAmount_OnOff]', function(){
        $(this).parent().next('.container-wrap').toggleClass('disactive');
        $(this).parent().next().next('.container-wrap').toggleClass('disactive');
        $(this).parent().next().next().next('.container-wrap').toggleClass('disactive');
    });

    //если на странице есть селектор Действий, - вывод содержимого действия
    if($('*').is($('select[name=card_actions_select]'))){
        var currentAction = $('select[name=card_actions_select]').val();
        loadCardActionsList(currentAction);
        userCastsMagic();
    }

    //Изменение значения селектора Действий
    $('select[name=card_actions_select]').change(function(){
        loadCardActionsList($(this).val());
        userCastsMagic();
    });

    //Добавление карты в группу
    $('input[name=addGroup]').click(function(){
        var groupId = $(this).parent().parent().find('select[name=addCardToGroup]').val();
        var groupTitle = $(this).parent().parent().find('select[name=addCardToGroup]').children('option:selected').text();
        $(this).parent().parent().children('#cardCurrentGroups').append('<tr><td><a class="drop" href="#"></a></td><td>' + groupTitle + '</td><td style="display: none;">' + groupId + '</td></tr>');
    });

    //Добавление Групповой характеристики в Действие
    $(document).on('click', 'input[name=addGroup]', function(){
        var groupId = $(this).prev('select').val();
        var groupTitle = $(this).prev('select').children('option:selected').text();
        $(this).next('.edition').append('<tr><td><a class="drop" href="#"></a></td><td>' + groupTitle + '</td><td style="display: none;">' + groupId + '</td></tr>').show();
        $('.edition tr td').on('click', 'a.drop', function(e){
            e.preventDefault();
            $(this).parent().parent().remove();
        });
    });


    //Добавление Действия
    $('input[name=addMoreCardActions]').click(function(){
        //Узнаем мип действия карты
        var actionType = $('select[name=card_actions_select] option:selected').attr('data-title');
        var _this = $(this);

        //отображение описания действия для пользователя
        displayActionRow= '<ins>' + $('select[name=card_actions_select] option:selected').text() + '</ins>: <br>';

        //описания действия для заноса в БД
        realActionRow   = '{"action": "' + $('select[name=card_actions_select]').val() + '"';

        switch(actionType) {

            //Тип действия - "Бессмертный"
            case 'bessmertnyj':
                //Выбор дествия возврата карты на поле, или в руку
                realActionRow += ', "CAundead_backToDeck":"' + $('input[name=CAundead_backToDeck]:checked').val() + '"}';
                displayActionRow += ' - Возвращается ' + $('input[name=CAundead_backToDeck]:checked').next('label').text() + ';<br>';
                break;


            //Тип действия - "Боевое Братство"
            case 'boevoe_bratstvo':
                //Выбор действия на группу или на одинаковые карты
                if (0 == $('input[name=CAbloodBro_actionToGroupOrSame]:checked').val()) {

                    //если выбор пал на одинаковые,- в БД пишем 0
                    realActionRow += ', "CAbloodBro_actionToGroupOrSame": "0"';
                    displayActionRow += ' - Дейстует на одинаковые; <br>';

                } else {
                    //если выбор пал на группу,- пишем в БД id групп

                    //создание целевых групп действия array[0 - json-массив, 1- строка описания]
                    var temp = checkGroupTable($('select[name=CAbloodBro_grop]'));
                    realActionRow += ', "CAbloodBro_actionToGroupOrSame": ' + temp[0];
                    displayActionRow += ' - Действует на группу: ' + temp[1];
                }

                //Значение умножения силы
                realActionRow += ', "CAbloodBro_strenghtMult": "' + $('input[name=CAbloodBro_strenghtMult]').val() + '"}';
                displayActionRow += ' - Умножает силу в ' + $('input[name=CAbloodBro_strenghtMult]').val() + ' раз;<br>';
                break;


            //Воодушевление
            case 'voodushevlenie':
                //Выбор рядов действия

                //создание целевых рядов действия array[0 - json-массив, 1- строка описания]
                var temp = setCheckboxesToJson($('.container-wrap input[name=CAinspiration_ActionRow]:checked'));
                realActionRow += ', "CAinspiration_ActionRow": ' + temp[0];
                displayActionRow += ' - Дальность: ' + temp[1];

                //Выбор модификатора силы: 0-умножение/1- добавление силы
                realActionRow += ', "CAinspiration_modificator": "' + $('input[name=CAinspiration_modificator]:checked').val() + '"';
                displayActionRow += ' - Модификатор силы: ' + $('input[name=CAinspiration_modificator]:checked').next('label').text() + ';<br>';

                //Значение силы
                realActionRow += ', "CAinspiration_multValue": "' + $('input[name=CAinspiration_multValue]').val() + '"}';
                displayActionRow += ' - Значение: ' + $('input[name=CAinspiration_multValue]').val() + ';<br>';
                break;


            //Иммунитет
            case 'immunitet':
                //Выбор типа иммунитета: 0-простой/1-полный
                realActionRow += ', "CAimmumity_type": "' + $('input[name=CAimmumity_type]:checked').val() + '"}';
                displayActionRow += ' - Тип иммунитета: ' + $('input[name=CAimmumity_type]:checked').next().text();
                break;


            //Исцеление
            case 'istselenie':
                realActionRow += '}';
                break;


            //Лекарь
            case 'lekar':
                if (0 == $('input[name=CAhealer_groupOrSingle]:checked').val()) {
                    realActionRow += ', "CAhealer_groupOrSingle": "0"}';
                    displayActionRow += ' - Действует на одиночную;<br>';
                } else {

                    var temp = checkGroupTable($('select[name=CAhealer_group]'));
                    realActionRow += ', "CAhealer_groupOrSingle": ' + temp[0];
                    displayActionRow += ' - Действует на группу: ' + temp[1];

                    realActionRow += '}';
                }
                break;


            //Неистовство
            case 'neistovstvo':
                //Условие "Противник относится к Расе"

                //создание целевых рас array[0 - json-массив, 1- строка описания]
                var temp = setCheckboxesToJson($('td .container-wrap input[name=CAfury_enemyRace]:checked'));
                realActionRow += ', "CAfury_enemyRace": ' + temp[0];
                displayActionRow += ' - Карты противника имеют расу - ' + temp[1];
                
                //Условие "У противника есть определенная группа карт"
                temp = checkGroupTable($('select[name=CAfury_group]'));

                realActionRow += ', "CAfury_group": ' + temp[0];
                displayActionRow += ' - Противник имеет карту из группы: ' + temp[1];

                //Условие "Противник имеет определенное количество воинов в ряду"
                realActionRow += ', "CAfury_enemyHasSuchNumWarriors" : "' + $('input[name=CAfury_enemyHasSuchNumWarriors]').val() + '"';
                displayActionRow += ' - Противник имеет воинов в количестве: ' + $('input[name=CAfury_enemyHasSuchNumWarriors]').val() + ' в ряду: ';

                //создание целевых рядов array[0 - json-массив, 1- строка описания]
                temp = setCheckboxesToJson($('td .container-wrap input[name=CAfury_ActionRow]:checked'));
                realActionRow += ', "CAfury_ActionRow": ' + temp[0];
                displayActionRow += temp[1];

                //Количество очков силы
                realActionRow += ', "CAfury_addStrenght": "' + $('input[name=CAfury_addStrenght]').val() + '"';
                displayActionRow += ' - Повышает силу на ' + $('input[name=CAfury_addStrenght]').val() + ' единиц<br>';

                //Противник использовал способность
                var temp = checkGroupTable($('select[name=CAfury_abilityCastEnemy]'));
                realActionRow += ', "CAfury_abilityCastEnemy": ' +  temp[0];
                displayActionRow += ' - Противник использовал способность: ' + temp[1];

                realActionRow += '}';

                break;

            //Одурманивание
            case 'odurmanivanie':
                //Условие Действует на ряд

                //создание целевых рядов array[0 - json-массив, 1- строка описания]
                var temp = setCheckboxesToJson($('td .container-wrap input[name=CAobscure_ActionRow]:checked'));
                realActionRow += ', "CAobscure_ActionRow": ' + temp[0];
                displayActionRow += " - Действует на ряд: " + temp[1];

                //Условие "Максимальная сила карты которую можно перетянуть"
                realActionRow += ', "CAobscure_maxCardStrong": "' + $('input[name=CAobscure_maxCardStrong]').val() + '"';
                displayActionRow += ' - Максимальная сила карты которую можно перетянуть: ' + $('input[name=CAobscure_maxCardStrong]').val() + ';<br>';

                //Условие степени силы перетягиваемой карты
                realActionRow += ', "CAobscure_strenghtOfCardToObscure": "' + $('select[name=CAobscure_strenghtOfCardToObscure]').val() + '"';
                displayActionRow += ' - Сила перетягиваемой карты: ' + $('select[name=CAobscure_strenghtOfCardToObscure] option:selected').text() + ';<br>';

                //Количество перетягиваемых карт
                realActionRow += ', "CAobscure_quantityOfCardToObscure": "' + $('input[name=CAobscure_quantityOfCardToObscure]').val() + '"';
                displayActionRow += ' - Количество перетягиваемых карт: ' + $('input[name=CAobscure_quantityOfCardToObscure]').val() + ';<br>';

                realActionRow += '}';
                break;

            //перегрупировка
            case 'peregruppirovka':
                realActionRow += '}';
                break;


            //Печаль
            case 'pechal':
                //Условие "Действует на ряд"
                //создание целевых рядов array[0 - json-массив, 1- строка описания]
                var temp = setCheckboxesToJson($('td .container-wrap input[name=CAsorrow_ActionRow]:checked'));
                realActionRow += ', "CAsorrow_ActionRow": ' + temp[0];
                displayActionRow += " - Действует на ряд: " + temp[1];

                //Условие "Область действия"
                realActionRow += ', "CAsorrow_actionToAll": "'+ $('input[name=CAsorrow_actionToAll]:checked').val() + '"';
                displayActionRow += ' - Область действия: ' + $('input[name=CAsorrow_actionToAll]:checked').next().text() + ';<br>';
                //Условие "Действует на своих"
                realActionRow += ', "CAsorrow_actionTeamate": "' + $('input[name=CAsorrow_actionTeamate]:checked').val() + '"}';
                displayActionRow += ' - Действует на своих: ' + $('input[name=CAsorrow_actionTeamate]:checked').next().text() + ';<br>';
                ;
                break;


            //Повелитель
            case 'povelitel':
                //Условие "Группа карт, которые будут призываться"
                var temp = checkGroupTable($('select[name=CAmaster_group]'));
                realActionRow += ', "CAmaster_group": ' + temp[0];
                displayActionRow += ' - Группа карт, которые будут призываться: ' + temp[1];

                //Условие "Откуда брать карты"
                temp = setCheckboxesToJson($('.container-wrap input[name=CAmasder_cardSource]:checked'));
                realActionRow += ', "CAmasder_cardSource": ' + temp[0];
                displayActionRow += ' - Карты берутся из: ' + temp[1];

                //Условие "Призывать карту по модификатору силы"
                realActionRow += ', "CAmaster_summonByModificator": "' + $('select[name=CAmaster_summonByModificator]').val() + '"';
                displayActionRow += ' - Призывать карту: ' + $('select[name=CAmaster_summonByModificator] option:selected').text() + ';<br>';

                //Условие "Максимальное количество карт, которое призывается"
                realActionRow += ', "CAmaster_maxCardsSummon": "' + $('input[name=CAmaster_maxCardsSummon]').val() + '"';
                displayActionRow += ' - Макс. количество карт, которое призывается: ' + $('input[name=CAmaster_maxCardsSummon]').val() + ';<br>';

                //Условие "Максимальное значение силы карт, которые призываются"
                realActionRow += ', "CAmaster_maxCardsStrenght": "' + $('input[name=CAmaster_maxCardsStrenght]').val() + '"';
                displayActionRow += ' - Макс. значение силы карт, которые призываются: ' + $('input[name=CAmaster_maxCardsStrenght]').val() + ';<br>';

                realActionRow += '}';
                break;


            //Поддержка
            case 'podderzhka':
                //Умение "Повысить силу"
                //создание целевых рядов array[0 - json-массив, 1- строка описания]
                var temp = setCheckboxesToJson($('.container-wrap input[name=CAsupport_ActionRow]:checked'));
                realActionRow += ', "CAsupport_ActionRow": ' + temp[0];
                displayActionRow += ' - Повысить силу в ряду: ' + temp[1];

                if (0 == $('input[name=CAsupport_actionToGroupOrAll]:checked').val()) {

                    //если выбор пал на всех,- в БД пишем 0
                    realActionRow += ', "CAsupport_actionToGroupOrAll": "0"';
                    displayActionRow += ' - Дейстует на всех; <br>';

                } else {

                    //если выбор пал на группу,- пишем в БД id групп
                    var temp = checkGroupTable($('select[name=CAsupport_group]'));
                    realActionRow += ', "CAsupport_actionToGroupOrAll": ' + temp[0];
                    displayActionRow += ' - Действует на группу: ' + temp[1];

                }

                //Условие "Повышение силы действует на себя"
                realActionRow += ', "CAsupport_selfCast": "' + $('input[name=CAsupport_selfCast]:checked').val() + '"';
                displayActionRow += ' - Повышение силы действует на себя: ' + $('input[name=CAsupport_selfCast]:checked').next('label').text() + ';<br>';

                //Значение "Значение повышения силы"
                realActionRow += ', "CAsupport_strenghtValue": "' + $('input[name=CAsupport_strenghtValue]').val() + '"';
                displayActionRow += ' - Значение повышения силы на: ' + $('input[name=CAsupport_strenghtValue]').val() + ' единиц;<br>';

                realActionRow += '}';
                break;


            //Призыв
            case 'prizyv':
                realActionRow += '}';
                break;


            //Разведчик
            case 'razvedchik':
                realActionRow += '}';
                break;


            //Страшный
            case 'strashnyj':
                //Раса на которую действует страх
                //создание целевых рас array[0 - json-массив, 1- строка описания]
                var temp = setCheckboxesToJson($('td .container-wrap input[name=CAfear_enemyRace]:checked'));
                realActionRow += ', "CAfear_enemyRace": ' + temp[0];
                displayActionRow += ' - Действует на расу: ' + temp[1];

                if (0 == $('input[name=CAfear_actionToGroupOrAll]:checked').val()) {
                    //Действует на всех
                    realActionRow += ', "CAfear_actionToGroupOrAll": "0"';
                    displayActionRow += ' - Дейстует на всех; <br>';

                } else {
                    //если выбор пал на группу,- пишем в БД id групп
                    var temp = checkGroupTable($('select[name=CAfear_group]'));
                    realActionRow += ', "CAfear_actionToGroupOrAll": ' + temp[0];
                    displayActionRow += ' - Действует на группу: ' + temp[1];
                }

                //Ряд действия
                //создание целевых рядов действия array[0 - json-массив, 1- строка описания]
                var temp = setCheckboxesToJson($('.container-wrap input[name=CAfear_ActionRow]:checked'));
                realActionRow += ', "CAfear_ActionRow": ' + temp[0];
                displayActionRow += ' - Ряд действия: ' + temp[1];

                //Условие "Действует на своих"
                realActionRow += ', "CAfear_actionTeamate": "' + $('input[name=CAfear_actionTeamate]:checked').val() + '"';
                displayActionRow += ' - Действует на своих: ' + $('input[name=CAfear_actionTeamate]:checked').next('label').text() + ';<br>';

                //Значение понижения силы
                realActionRow += ', "CAfear_strenghtValue": "' + $('input[name=CAfear_strenghtValue]').val() + '"';
                displayActionRow += ' - Значение понижения силы: ' + $('input[name=CAfear_strenghtValue]').val() + ';<br>';

                realActionRow += '}';
                break;


            //Убийца
            case 'ubijtsa':
                //Ряд действия
                //создание целевых рядов действия array[0 - json-массив, 1- строка описания]
                var temp = setCheckboxesToJson($('.container-wrap input[name=CAkiller_ActionRow]:checked'));
                realActionRow += ', "CAkiller_ActionRow": ' + temp[0];
                displayActionRow += ' - Ряд действия: ' + temp[1];

                if (0 == $('input[name=CAkiller_groupOrSingle]:checked').val()) {
                    //Действует на одного
                    realActionRow += ', "CAkiller_groupOrSingle": "0"';
                    displayActionRow += ' - Не дейстует на группу; <br>';

                } else {
                    //если выбор пал на группу,- пишем в БД id групп
                    var temp = checkGroupTable($('select[name=CAkiller_group]'));
                    realActionRow += ', "CAkiller_groupOrSingle": ' + temp[0];
                    displayActionRow += ' - Действует на группу: ' + temp[1];
                }

                //Условие "Нужное для совершения убийства количество силы карт воинов в ряду"
                if (0 == $('input[name=CAkiller_recomendedTeamateForceAmount_OnOff]:checked').val()) {
                    realActionRow += ', "CAkiller_recomendedTeamateForceAmount_OnOff": "0"';
                } else {
                    realActionRow += ', "CAkiller_recomendedTeamateForceAmount_OnOff": "' + $('input[name=CAkiller_recomendedTeamateForceAmount]').val() + '"';
                    displayActionRow += ' - Количество силы необходимое для совершения убийства воинов: ' + $('input[name=CAkiller_recomendedTeamateForceAmount]').val();
                    
                    var temp = setCheckboxesToJson($('.container-wrap input[name=CAkiller_recomendedTeamateForceAmount_ActionRow]:checked'));
                    realActionRow += ', "CAkiller_recomendedTeamateForceAmount_ActionRow": ' + temp[0];
                    displayActionRow += ' -> Ряд подсчета: ' + temp[1];
                    
                    realActionRow += ', "CAkiller_recomendedTeamateForceAmount_Selector": "' + $('select[name=CAkiller_recomendedTeamateForceAmount_Selector]').val() + '"';
                    displayActionRow += '(' + $('select[name=CAkiller_recomendedTeamateForceAmount_Selector] option:selected').text() + ')<br>';
                }

                //Условие "Порог силы воинов противника для совершения убийства"
                realActionRow += ', "CAkiller_enemyStrenghtLimitToKill": "' + $('input[name=CAkiller_enemyStrenghtLimitToKill]').val() + '"';
                displayActionRow += ' - Порог силы воинов противника для совершения убийства: ' + $('input[name=CAkiller_enemyStrenghtLimitToKill]').val() + ';<br>';

                //Условие "Вариация количества убийств"
                realActionRow += ', "CAkiller_killAllOrSingle": "' + $('input[name=CAkiller_killAllOrSingle]:checked').val() + '"';
                displayActionRow += ' - Вариация количества убийств: ' + $('input[name=CAkiller_killAllOrSingle]:checked').next().text() + ';<br>';

                //Условие "Может бить своих юнитов"
                realActionRow += ', "CAkiller_atackTeamate": "' + $('input[name=CAkiller_atackTeamate]:checked').val() + '"';
                displayActionRow += ' - Может бить своих юнитов по указаным выше параметрах: ' + $('input[name=CAkiller_atackTeamate]:checked').next().text() + ';<br>';

                //Условие "Игнорирует иммунитет к убийству"
                realActionRow += ', "CAkiller_ignoreKillImmunity": "' + $('input[name=CAkiller_ignoreKillImmunity]:checked').val() + '"';
                displayActionRow += ' - Игнорирует иммунитет к убийству: ' + $('input[name=CAkiller_ignoreKillImmunity]:checked').next().text() + ';<br>';

                //Условие "Качество убиваемой карты"
                realActionRow += ', "CAkiller_killedQuality_Selector": "' + $('select[name=CAkiller_killedQuality_Selector]').val() + '"';
                displayActionRow += ' - Качество убиваемой карты: ' + $('select[name=CAkiller_killedQuality_Selector] option:selected').text();

                realActionRow += '}';
                break;


            //Шпион
            case 'shpion':
                realActionRow += ', "CAspy_get_cards_num": "' + $('input[name=CAspy_get_cards_num]').val() + '"';
                displayActionRow += ' - Плучить из колоды ' + $('input[name=CAspy_get_cards_num]').val() + ' карт';

                realActionRow += '}';
                break;
        }
        actionsPreviewTable(_this, displayActionRow, realActionRow);
    });


    //Описание - Тип Карты
    function checkCardType(){
        switch($('select[name=cardType]').val()){
            case 'neutrall':
                $('#cardCanNotBeSavedByRace').show();
                $('#cardInRace').hide();
                break;
            case 'race':
                $('#cardCanNotBeSavedByRace').hide();
                $('#cardInRace').show();
                break;
            default:
                $('#cardCanNotBeSavedByRace').hide();
                $('#cardInRace').hide();
                break;
        }
    }
    checkCardType();

    $('select[name=cardType]').change(function (){
        checkCardType();
    });

    
    //Добавление карты
    $('input[name=cardAdd]').click(function(){

        var token = $('input[name=_token]').val();

        var card_refer_to_group = [];  // Карта относится к группе
        $('#cardCurrentGroups tr').each(function(){
            card_refer_to_group.push( $(this).children('td:eq(2)').text() );
        });

        var card_actions = []; //Действия карты
        $('#cardCurrentActions tr').each(function(){
            card_actions.push( $(this).children('td:eq(2)').text() );
        });

        var card_type = $('select[name=cardType]').val(); // Тип карты

        var card_type_forbidden_race_deck = []; // Карта не может быть сыграна в колоде расы (Только для нейтралов)
        if(card_type == 'neutrall'){
            $('#cardCanNotBeSavedByRace td .container-wrap').each(function(){
                if($(this).children('input').prop('checked') === true){
                    card_type_forbidden_race_deck.push('"' + $(this).children('input').val() + '"');
                }
            });
        }

        var card_action_row = []; //Дальность карты
        $('.actions .container-wrap input[name=C_ActionRow]:checked').each(function(){
            card_action_row.push($(this).val());
        });

        //Создание иммитации формы
        var formData = new FormData();
        formData.append( 'token', token );
        formData.append( 'title', $('input[name=card_title]').val().trim() );               // Название карты
        formData.append( 'short_descr', $('textarea[name=card_short_descr]').val().trim() );// Короткое описание
        formData.append( 'full_descr', $('textarea[name=card_full_descr]').val().trim() );  // Полное описание
        formData.append( 'img_url', $('input[name=cardAddImg]').prop('files')[0] );         //Фон карты
        formData.append( 'card_refer_to_group', '[' + card_refer_to_group + ']');           // Json-массив "Карта относится к группам"
        formData.append( 'card_actions', '[' + card_actions + ']');                         // Json-массив "Действий карты"
        formData.append( 'card_type', card_type);                                           // Тип карты (нейтральная, спец.карта, расовая)
        formData.append( 'card_type_forbidden_race_deck', '[' + card_type_forbidden_race_deck + ']'); // Json-массив. Если карта нейтральная, указывается расса, которой данная карта не играется
        formData.append( 'card_race', $('#cardInRace select[name=cardRace]').val());        // Указывается расса в которой данная карта находится
        formData.append( 'card_action_row', '[' + card_action_row + ']');                   // Json-массив "дальность карты"
        formData.append( 'card_strenght', $('input[name=cardStrongthValue]').val());        //Сила карты
        formData.append( 'card_weight', $('input[name=cardWeightValue]').val());            //Вес карты
        formData.append( 'card_is_leader', $('input[name=cardIsLeader]').prop('checked'));  //Карта лидер?
        formData.append( 'card_max_num_in_deck', $('input[name=cardMaxValueInDeck]').val());//Максимальное число в колоде
        formData.append( 'card_gold_price', $('input[name=cardPriceGold]').val());          //Цена в золоте
        formData.append( 'card_silver_price', $('input[name=cardPriceSilver]').val());      //Цена в серебре
        formData.append( 'card_only_gold_price', $('input[name=cardPriceGoldOnly]').val()); //Цена в "только золото"

        $.ajax({
            url:        '/admin/cards/add',
            headers:    {'X-CSRF-TOKEN': token},
            type:       'POST',
            processData: false,
            contentType: false,
            datatype:   'JSON',
            data:       formData,
            success:    function(data){
                if(data == 'success') location = '/admin/cards';
            }
        });
    });
    
    //Редактирование Карты
    $('input[name=cardEdit]').click(function(){

        var token = $('input[name=_token]').val();

        var card_refer_to_group = [];  // Карта относится к группе
        $('#cardCurrentGroups tr').each(function(){
            card_refer_to_group.push( $(this).children('td:eq(2)').text() );
        });

        var card_actions = []; //Действия карты
        $('#cardCurrentActions tr').each(function(){
            card_actions.push( $(this).children('td:eq(2)').text() );
        });

        var card_type = $('select[name=cardType]').val(); // Тип карты
        var card_type_forbidden_race_deck = []; // Карта не может быть сыграна в колоде расы (Только для нейтралов)
        if(card_type == 'neutrall'){
            $('#cardCanNotBeSavedByRace td .container-wrap').each(function(){
                if($(this).children('input').prop('checked') === true){
                    card_type_forbidden_race_deck.push('"' + $(this).children('input').val() + '"');
                }
            });
        }

        var card_action_row = []; //Дальность карты
        $('.actions .container-wrap input[name=C_ActionRow]:checked').each(function(){
            card_action_row.push($(this).val());
        });

        //Создание иммитации формы
        var formData = new FormData();
        formData.append( 'token', token );
        formData.append( 'id', $('input[name=card_id]').val() );
        formData.append( '_method', 'PUT');
        formData.append( 'title', $('input[name=card_title]').val().trim() );               // Название карты
        formData.append( 'short_descr', $('textarea[name=card_short_descr]').val().trim() );// Короткое описание
        formData.append( 'full_descr', $('textarea[name=card_full_descr]').val().trim() );  // Полное описание
        formData.append( 'img_url', $('input[name=cardAddImg]').prop('files')[0] );         //Фон карты
        formData.append( 'img_old_url', $('img#cardImage').attr('alt'));                    //Старый фон карты
        formData.append( 'card_refer_to_group', '[' + card_refer_to_group + ']');           // Json-массив "Карта относится к группам"
        formData.append( 'card_actions', '[' + card_actions + ']');                         // Json-массив "Действий карты"
        formData.append( 'card_type', card_type);                                           // Тип карты (нейтральная, спец.карта, расовая)
        formData.append( 'card_type_forbidden_race_deck', '[' + card_type_forbidden_race_deck + ']'); // Json-массив. Если карта нейтральная, указывается расса, которой данная карта не играется
        formData.append( 'card_race', $('#cardInRace select[name=cardRace]').val());        // Указывается расса в которой данная карта находится
        formData.append( 'card_action_row', '[' + card_action_row + ']');                   // Json-массив "дальность карты"
        formData.append( 'card_strenght', $('input[name=cardStrongthValue]').val());        //Сила карты
        formData.append( 'card_weight', $('input[name=cardWeightValue]').val());            //Вес карты
        formData.append( 'card_is_leader', $('input[name=cardIsLeader]').prop('checked'));  //Карта лидер?
        formData.append( 'card_max_num_in_deck', $('input[name=cardMaxValueInDeck]').val());//Максимальное число в колоде
        formData.append( 'card_gold_price', $('input[name=cardPriceGold]').val());          //Цена в золоте
        formData.append( 'card_silver_price', $('input[name=cardPriceSilver]').val());      //Цена в серебре
        formData.append( 'card_only_gold_price', $('input[name=cardPriceGoldOnly]').val()); //Цена в "только золото"

        $.ajax({
            url:        '/admin/cards/edit',
            headers:    {'X-CSRF-TOKEN': token},
            type:       'POST',
            processData: false,
            contentType: false,
            data:       formData,
            success:    function(data){
                if(card_type == 'race'){
                    var get = $('#cardInRace select[name=cardRace]').val();
                }else{
                    var get = card_type;
                }

                if(data == 'success') location = '/admin/cards?race='+get;
            }
        });
    });

});