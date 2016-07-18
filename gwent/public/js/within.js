$(document).ready(function(){

/*
*   Общие методы
*/
    //Удаление елементов из таблиц материалов
    function dropRowInEditionTable(){
        $('.edition tr td').on('click', 'a.drop', function(e){
            e.preventDefault();
            $(this).parent().parent().remove();
        });
    }

    dropRowInEditionTable();

    $('input.drop').click(function(e){
        var result = confirm('Вы действительно хотите удалить данный элемент?');
        if(result == false){
            return false;
        }
    });

/*
* Методы страницы /admin/cards
* */
    if($('#addCard').length > 0){
        $('select[name=chooseRace]').change(function(){
            location = '/admin/cards?race=' + $(this).val();
        });
    }
/*
*   Методы страницы action/cards/actions
*/


    //Добавление характеристик в Карты->Действия->Добавить/Изменить
    $('input[name=action_add_characteristic]').click(function(){
        $('#card_action_characteristic_table').append('<tr><td style="width: 10%; vertical-align: top;"><input name="action_characteristic_label" type="text"></td><td><textarea name="action_characteristic_html"></textarea></td></tr>');
    });

    /*
     * Отправка данных для сохранения из Карты->Действия->Добавить
     * в обработчик /admin/cards/actions/add методом POST
     */
    $('input[name=cardActionAdd]').click(function(){
        var token = $('input[name=_token]').val();
        var title = $('input[name=action_title]').val().trim();
        var description = $('textarea[name=action_descr]').val().trim();

        var characteristics = [];

        $('#card_action_characteristic_table tr').each(function(){
            //Собираем данные характеристик
            var label = $(this).children('td').children('input[name=action_characteristic_label]').val();
            var value = $(this).children('td').children('textarea[name=action_characteristic_html]').val();
            //Проверям на пустые значения
            if((label != '')&&(value != '')){
                characteristics.push(label.trim());
                characteristics.push(value.trim());
            }
        });

        /*
         * Собственно, отправка в /admin/cards/actions/add
         * X-CSRF-TOKEN нужен для избежания кроссайтовой отсылки
         */
        $.ajax({
            url:        '/admin/cards/actions/add',
            headers:    {'X-CSRF-TOKEN': token},
            type:       'POST',
            datatype:   'JSON',
            data:       {token:token, title:title, description:description, characteristics:characteristics},
            success:    function(data){
                if(data == 'success'){
                    location = '/admin/cards/actions';
                }
            }
        })
    });

    /*
     * Отправка данных для сохранения из Карты->Действия->Изменить
     * в обработчик /admin/cards/actions/edit методом PUT
     */
    $('input[name=cardActionEdit]').click(function(){
        var id = $('input[name=action_id]').val();
        var token = $('input[name=_token]').val();
        var title = $('input[name=action_title]').val().trim();
        var description = $('textarea[name=action_descr]').val().trim();

        var characteristics = [];

        $('#card_action_characteristic_table tr').each(function(){
            var label = $(this).children('td').children('input[name=action_characteristic_label]').val();
            var value = $(this).children('td').children('textarea[name=action_characteristic_html]').val();
            if((label != '')&&(value != '')){
                characteristics.push(label.trim());
                characteristics.push(value.trim());
            }
        });

        $.ajax({
            url:        '/admin/cards/actions/edit',
            headers:    {'X-CSRF-TOKEN': token},
            type:       'PUT',
            datatype:   'JSON',
            data:       {id:id, token:token, title:title, description:description, characteristics:characteristics},
            success:    function(data){
                if(data == 'success'){
                    location = '/admin/cards/actions';
                }
            }
        });
    });


/*
* Методы страницы action/cards/groups
*/

    //Добавление карты в группу
    $('.edition input[name=addCardToGroup]').click(function(){
        $('#currentCardsInGroup').append('<tr><td><a class="drop" href="#"></a></td><td>' + $('select[name=groupCards] option:selected').text() + '</td><td style="display: none;">' + $('select[name=groupCards]').val() + '</td></tr>');
        dropRowInEditionTable();
    });

    //Создание группы
    $('input[name=cardGroupAdd]').click(function(){
        var token = $('input[name=_token]').val();

        var cards = [];
        $('#currentCardsInGroup tr').each(function(){
            cards.push( $(this).children('td:eq(2)').text() );
        });
        cards = JSON.stringify(cards);

        $.ajax({
            url:        '/admin/cards/groups/add',
            headers:    {'X-CSRF-TOKEN': token},
            type:       'POST',
            datatype:   'JSON',
            data:       {token:token, title:$('input[name=group_title]').val().trim(), cards:cards},
            success:    function(data){
                console.log(data);
                if(data == 'success'){
                    location = '/admin/cards/groups';
                }
            }
        });
    });

    //Редактирование группы
    $('input[name=cardGroupEdit]').click(function(){
        var token = $('input[name=_token]').val();

        var cards = [];
        $('#currentCardsInGroup tr').each(function(){
            cards.push( $(this).children('td:eq(2)').text() );
        });
        cards = JSON.stringify(cards);
        $.ajax({
            url:        '/admin/cards/groups/edit',
            headers:    {'X-CSRF-TOKEN': token},
            type:       'PUT',
            datatype:   'JSON',
            data:       {token:token, title:$('input[name=group_title]').val().trim(), cards:cards, id:$('input[name=group_id]').val()},
            success:    function(data){
                console.log(data);
                if(data == 'success'){
                    location = '/admin/cards/groups';
                }
            }
        });
    });

    
    /*
    * Главная страница 
    */
    
    //Меню закладок
    $('.bookmark_menu li').click(function(){
        $(this).parent().children('li').removeClass('active');
        $(this).addClass('active');
        $('body .main-central-wrap').hide();
        $('body #' + $(this).attr('data-link')).show();
    });

    //Базовые карты - Добавить строку
    $('.main-central-wrap input[name=baseCardsAddRow]').click(function(){
        var _this = $(this);
        $.get(
            '/admin/get_all_cards_selector',
            function(data){
                _this.parents('fieldset').children('.edition').children('tbody').append(data);
                dropRowInEditionTable();
            }
        );
    });

    //Добавление Расы
    $('input[name=addRace]').click(function(){
        var token = $('input[name=_token]').val();
        //Имитация отправки данных через форму
        var formData = new FormData();
        //Наполнение формы
        formData.append( 'token', token );
        formData.append( 'title', $('input[name=race_title]').val().trim() );                   //Название расы
        formData.append( 'description_title', $('input[name=race_text_title]').val().trim() );  //Заглавие описания расы
        formData.append( 'description', $('textarea[name=race_text]').val().trim() );           //Описание расы
        formData.append( 'slug', $('input[name=race_slug]').val().trim() );                     //Обозначение расы
        formData.append( 'type', $('input[name=race_type]').val().trim() );                     //Тип карт колоды (расовая/нейтральная/специальная)
        formData.append( 'img_url', $('input[name=raceAddImg]').prop('files')[0] );             //Изображение
        $.ajax({
            url:        '/admin/race/add',
            headers:    {'X-CSRF-TOKEN': token},
            type:       'POST',
            processData: false,
            contentType: false,
            datatype:   'JSON',
            data:       formData,
            success:    function(data){
                if(data == 'success') location = '/admin';
            }
        });
    });

    //редактирование расы
    $('input[name=editRace]').click(function(){
        var token = $('input[name=_token]').val();
        //Имитация отправки данных через форму
        var formData = new FormData();
        //Наполнение формы
        formData.append( 'token', token );
        formData.append( '_method', 'PUT');                                                     //Указывам метод PUT
        formData.append( 'id', $('input[name=race_id]').val() );                                //ID расы
        formData.append( 'title', $('input[name=race_title]').val().trim() );                   //Название расы
        formData.append( 'slug', $('input[name=race_slug]').val().trim() );                     //Обозначение расы
        formData.append( 'description_title', $('input[name=race_text_title]').val().trim() );  //Заглавие описания расы
        formData.append( 'description', $('textarea[name=race_text]').val().trim() );           //Описание расы
        formData.append( 'type', $('input[name=race_type]').val().trim() );                     //Тип карт колоды (расовая/нейтральная/специальная)
        formData.append( 'img_url', $('input[name=raceAddImg]').prop('files')[0] );             //Новый файл изображения
        formData.append( 'img_old_url', $('img#raceImage').attr('alt'));                        //Старый файл изображения

        $.ajax({
            url:        '/admin/race/edit',
            headers:    {'X-CSRF-TOKEN': token},
            type:       'POST',
            processData: false,
            contentType: false,
            data:       formData,
            success:    function(data){
                if(data == 'success') location = '/admin';
            }
        });
    });

    //Добавить строку Лиги
    $('input[name=leagueAddRow]').click(function(){
        $(this).parents('.main-central-wrap').children('.edition').children('tbody').append('<tr><td><a href="#" class="drop"></a></td><td><input name="league_title" type="text" value=""></td><td><input name="league_min" type="number" value=""></td><td><input name="league_max" type="number" value=""></td></tr>');
        dropRowInEditionTable();
    });

    //Сохранение данных Лиг
    $('input[name=leagueApply]').click(function(){
        var leagueData = [];
        $('#leagueOptions .edition tbody tr').each(function(){
            leagueData.push('{"title": "' + $(this).children('td').children('input[name=league_title]').val() + '", "min": "' + $(this).children('td').children('input[name=league_min]').val() + '", "max": "' + $(this).children('td').children('input[name=league_max]').val() + '"}');
        });
        leagueData = '[' + leagueData + ']';

        var token = $('input[name=_token]').val();

        $.ajax({
            url:        '/admin/league_apply',
            headers:    {'X-CSRF-TOKEN': token},
            type:       'PUT',
            data:       {token:token, leagueData:leagueData},
            success: function(data){
                if(data == 'success') location = '/admin';
            }
        });
    });

    //Сохранение Базовых колод рас
    $('#baseCards input[name=baseCardsApply]').click(function(){
        var deckType = $(this).attr('id');
        var token = $('input[name=_token]').val();
        var deckArray = [];
        $(this).parent().parent().children('.edition').children('tbody').children('tr').each(function(){
            deckArray.push( '{"id": "'+ $(this).children('td').children('select[name=currentCard]').val() + '", "q": "' + $(this).children('td').children('input[name=currentQuantity]').val() + '"}');
        });
        deckArray = '[' + deckArray + ']';
        $.ajax({
            url:        '/admin/base_card_deck',
            headers:    {'X-CSRF-TOKEN': token},
            type:       'PUT',
            data:       {token:token, deckType:deckType, deckArray:deckArray},
            success: function(data){
                if(data == 'success') location = '/admin';
            }
        })
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
        //Имитация отправки данных через форму
        var formData = new FormData();
        //Наполнение формы
        formData.append( 'token', token );
        formData.append( 'title', $('input[name=magic_title]').val().trim());
        formData.append( 'description', $('textarea[name=magic_descr]').val().trim());
        formData.append( 'img_url', $('input[name=magicAddImg]').prop('files')[0] );
        formData.append( 'races', JSON.stringify(races));
        formData.append( 'energyCost', $('input[name=energy_cost]').val());
        formData.append( 'price_gold', $('input[name=price_gold]').val());
        formData.append( 'price_silver', $('input[name=price_silver]').val());

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
        formData.append( 'energyCost', $('input[name=energy_cost]').val());
        formData.append( 'price_gold', $('input[name=price_gold]').val());
        formData.append( 'price_silver', $('input[name=price_silver]').val());

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




    //Файлы
    $('.main-central-wrap .not-used').click(function(){
        $(this).toggleClass('file-active');
    });

    $('input[name=dropFile]').click(function(){
        var result = confirm('Вы действительно хотите удалить выделеные файлы?');

        if(result == true){

            var files = [];

            $('.main-central-wrap .file-active').each(function(){
                files.push($(this).children('img').attr('alt'));
            });

            files = JSON.stringify(files);

            var token = $('input[name=_token]').val();
            $.ajax({
                url:        '/admin/files/drop',
                headers:    {'X-CSRF-TOKEN': token},
                type:       'DELETE',
                data:       {files:files, token: token},
                success:    function(data){
                    alert('Выделеные файлы были удалены');
                    if(data == 'success') location = '/admin/files';
                }
            });

        }
    });
    
});