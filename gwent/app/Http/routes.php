<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

//Главная
Route::get('/', [
    'as'    => 'user-home',
    'uses'  => 'Site\SitePagesController@index'
]);

//Регистрация
Route::get('/registration', [
    'as'    => 'user-registration',
    'uses'  => 'Site\SitePagesController@registration'
]);
//Отправка данных регистрации
Route::post('/registration', [
    'as'    => 'user-register-me',
    'uses'  => 'Site\UserAuthController@userRegistration'
]);

//Авторизация пользователя
Route::post('/login', [
    'as'    => 'user-login',
    'uses'  => 'Site\UserAuthController@login'
]);
//Выход пользователя
Route::get('/logout', [
    'as'    => 'user-logout',
    'uses'  => 'Site\UserAuthController@logout'
]);


//Страницы авторизированого пользователя
Route::group(['middleware' => 'notAuth'], function() {

    //Представления страниц
        //Столы
        Route::post('/games', [
            'as'    => 'user-active-games',
            'uses'  => 'Site\SitePagesController@games'
        ]);
        //Играть
        Route::get('/play/{id}', [
            'as'    => 'user-in-game',
            'uses'  => 'Site\SitePagesController@play'
        ]);
        //Мои карты
        Route::get('/deck', [
            'as'    => 'user-deck',
            'uses'  => 'Site\SitePagesController@deck'
        ]);
        //Магазин
        Route::get('/market', [
            'as'    => 'user-market',
            'uses'  => 'Site\SitePagesController@market'
        ]);
        //Волшебство
        Route::get('/market_effects', [
            'as'    => 'user-market-effects',
            'uses'  => 'Site\SitePagesController@marketEffects'
        ]);
        //Настройки пользлователя
        Route::get('/settings', [
            'as'    => 'user-settings',
            'uses'  => 'Site\SitePagesController@settings'
        ]);
        //Обучение
        Route::get('/training', [
            'as'    => 'user-training',
            'uses'  => 'Site\SitePagesController@training'
        ]);


    //WebMoney
        //Платежная страница
        Route::get('/pay.html{money?}', [
            'as'    => 'user-wm-pay',
            'uses'  => 'Site\SitePagesController@WM_pay'
        ]);
        //Страница успешно выполненного платежа
        Route::get('success.html{WM_response?}', [
            'as'    => 'user-wm-success',
            'uses'  => 'Site\SitePagesController@WM_success'
        ]);
        //Страница невыполненного платежа
        Route::get('/fail.html{WM_response?}', [
            'as'    => 'user-wm-fail',
            'uses'  => 'Site\SitePagesController@WM_fail'
        ]);

    //battle
        Route::get('/playtest',[
            'as' => 'play-test',
            'uses' => 'Site\SiteGameController@test'
        ]);
});

//Скрипты выборки данных из БД

//Поверка "Пользователь находится в битве"
Route::get('/check_user_is_plying_status', [
    'uses' => 'Site\SiteFunctionsController@checkUserIsPlaying'
]);
//Получить все карты определенной расы
Route::get('/get_cards_by_race', [
    'uses' => 'Site\SiteFunctionsController@getCardsByRace'
]);
//Получить данные карты
Route::get('/get_card_data', [
    'uses' => 'Site\SiteFunctionsController@getCardData'
]);
//Получить данные волшебства по расе
Route::get('/get_magic_by_race', [
    'uses' => 'Site\SiteFunctionsController@getMagicEffectsByRace'
]);
//Получить данные конкретного волшебства
Route::get('/get_magic_effect_data', [
    'uses' => 'Site\SiteFunctionsController@getMagicEffectData'
]);
//Получение данных пользователя
Route::get('/get_user_data', [
    'uses' => 'Site\SiteFunctionsController@getUserData'
]);
//Получение колоды пользователя
Route::get('/get_user_deck', [
    'uses' => 'Site\SiteFunctionsController@getUserDeck'
]);
//Получение колвичества игроков онлайн
Route::get('/get_user_quantity', [
    'uses' => 'Site\SiteFunctionsController@getUserQuantity'
]);
//Пользователь присоединяется к столу
Route::put('/user_connect_to_battle', [
    'uses'  => 'Site\SiteGameController@userConnectToBattle'
]);
//Пользователь создает стол
Route::post('/user_create_battle', [
    'as'    => 'user-create-table',
    'uses'  => 'Site\SiteGameController@createTable'
]);
//Проверка колоды пользователя
Route::get('/validate_deck', [
    'uses' => 'Site\SiteFunctionsController@validateUserDeck'
]);


//Изменение пользовательских данных
//Обновление колоды пользователя
Route::put('/change_user_deck', [
    'uses' => 'Site\SiteFunctionsController@changeUserDeck'
]);
//Пользователь меняет статус волшебства
Route::put('/magic_change_status', [
    'uses' => 'Site\SiteFunctionsController@userChangeMagicEffectStatus'
]);
//Отправка данных настройки пользователя
Route::put('/settings', [
    'as' => 'user-settings-change',
    'uses' => 'Site\UserAuthController@userChangeSettings'
]);
//Пользователь покупает энергию
Route::put('/user_buying_energy', [
    'uses' => 'Site\SiteFunctionsController@userBuyingEnergy'
]);
//Пользователь покупает серебро
Route::put('/user_buying_silver', [
    'uses' => 'Site\SiteFunctionsController@userBuyingSilver'
]);


//Внесение пользователем данных в БД
//Пользователь купил карту
Route::post('/card_is_buyed', [
    'uses' => 'Site\SiteFunctionsController@userBuyingCard'
]);
//Пользователь купил волшебство
Route::post('/magic_is_buyed', [
    'uses' => 'Site\SiteFunctionsController@userBuyingMagic'
]);


//Игра
Route::put('/game_start', [
    'uses' => 'Site\SiteGameController@startGame'
]);





//ИМИТАЦИЯ CRON
Route::get('/cron_imitator', [
    'uses'  => 'Site\SiteFunctionsController@cronTask'
]);


//Admin

//Authorisation
Route::get('/admin/login', [
    'as'    => 'admin-login',
    'uses'  => 'Admin\AdminAuthController@getLogin'
]);
Route::post('/admin/login', [
    'uses'  => 'Admin\AdminAuthController@login'
]);
Route::get('/admin/logout', [
    'uses'  => 'Admin\AdminAuthController@logout'
]);

//End Authorisation

Route::group(['middleware' => 'admin'], function(){
    //Главная
    Route::get('/admin', [
        'as'    => 'admin-main',
        'uses'  => 'Admin\AdminIndexController@index'
    ]);

    //Редактирование лиг
    Route::put('/admin/league_apply', [
        'as'    => 'admin-league-edit',
        'uses'  => 'Admin\AdminIndexController@leagueEdit'
    ]);

    //Редактирование базовых полей пользователя
    Route::put('/admin/base_user_fields', [
        'as'    => 'admin-baseUserFields',
        'uses'  => 'Admin\AdminIndexController@baseUserFieldsEdit'
    ]);

    //Редактирование соотношения обменов
    Route::put('/admin/exchange_options', [
        'as'    => 'admin-exchange-change',
        'uses'  => 'Admin\AdminIndexController@exchangeOptionsEdit'
    ]);

    //Редактирование настроек колод
    Route::put('/admin/deck_options', [
        'as'    => 'admin-deck-options',
        'uses'  => 'Admin\AdminIndexController@deckOptionsEdit'
    ]);

    //Добавление Расы
    Route::get('/admin/race/add', [
        'as'    => 'admin-race-add',
        'uses'  => 'Admin\AdminRaceController@raceAddPage'
    ]);
    //Редактирование Расы
    Route::get('/admin/race/edit/{id}', [
        'as'    => 'admin-race-edit-it',
        'uses'  => 'Admin\AdminRaceController@raceEditPage'
    ]);
    //Добавление Расы [Кнопка "Добавить"]
    Route::post('/admin/race/add', [
        'as'    => 'admin-race-add',
        'uses'  => 'Admin\AdminRaceController@addRace'
    ]);
    //Редактирование Расы [Кнопка "Применить"]
    Route::put('/admin/race/edit', [
        'as'    => 'admin-race-edit',
        'uses'  => 'Admin\AdminRaceController@editRace'
    ]);
    //Изменение Базовых колод рас
    Route::put('/admin/base_card_deck', [
        'as'    => 'admin-race-deck',
        'uses'  => 'Admin\AdminRaceController@raceChangeDeck'
    ]);
    //Удаление расы
    Route::delete('/admin/races/drop', [
        'as'    => 'admin-races-drop',
        'uses'  => 'Admin\AdminRaceController@dropRace'
    ]);


    //Отображение селектора карт по группам
    Route::get('/admin/get_all_cards_selector', [
        'uses'  => 'Admin\AdminIndexController@getAllCardsSelector'
    ]);


    //Роут "Карты"
    Route::get('/admin/cards{page?}', [
        'as'    => 'admin-cards',
        'uses'  => 'Admin\AdminCardsController@index'
    ]);
    //Роут "Карты->Добавить"
    Route::get('/admin/cards/add', [
        'as'    => 'admin-cards-add',
        'uses'  => 'Admin\AdminCardsController@cardAddPage'
    ]);
    //Роут "Карты->Изменить". Входным параметром есть id БД
    Route::get('/admin/cards/edit/{id}', [
       'as'     => 'admin-cards-edit-it',
        'uses'  =>  'Admin\AdminCardsController@cardEditPage'
    ]);
    //Роут "Карты->Добавить[Кнопка "Добавить"]"
    Route::post('/admin/cards/add', [
        'as'    => 'admin-cards-add',
        'uses'  => 'Admin\AdminCardsController@addCard'
    ]);
    //Роут "Карты->Изменить->[Кнопка "Применить"]".
    Route::put('/admin/cards/edit/', [
        'as'    => 'admin-cards-edit',
        'uses'  => 'Admin\AdminCardsController@editCard'
    ]);
    //Роут "Карты->Удалить
    Route::delete('/admin/cards/drop', [
        'as'    => 'admin-cards-drop',
        'uses'  => 'Admin\AdminCardsController@dropCard'
    ]);
    //Роут возвращает конкретное действие карты
    Route::get('/admin/cards/get_params_by_action/', [
        'uses'  => 'AdminViews@cardsViewActionsList'
    ]);
    //Роут возвращает список всех групп
    Route::get('/admin/cards/get_card_groups', [
        'uses'  => 'AdminViews@cardsViewGroupsList'
    ]);
    //Роут возвращает список карт относящихся к расе
    Route::get('/admin/get_cards_by_race', [
        'uses'  => 'AdminViews@cardsViewByRace'
    ]);
    //Роут возвращает список магических эффектов
    Route::get('/admin/get_magic_effects', [
        'uses'  => 'AdminViews@cardsViewMagicList'
    ]);


    //Роут "Карты->Группы"
    Route::get('/admin/cards/groups', [
        'as'    => 'admin-cards-group',
        'uses'  => 'Admin\AdminCardsGroupController@groupPage'
    ]);
    //Роут "Карты->Группы->Добавить"
    Route::get('/admin/cards/groups/add', [
        'as'    => 'admin-cards-group-add',
        'uses'  => 'Admin\AdminCardsGroupController@cardGroupAddPage'
    ]);
    //Роут "Карты->Группы->Изменить"
    Route::get('/admin/cards/groups/edit/{id}', [
        'as'    => 'admin-cards-group-edit-it',
        'uses'  => 'Admin\AdminCardsGroupController@cardGroupEditPage'
    ]);
    //Роут "Карты->Группы->Добавить[Кнопка "Добавить"]"
    Route::post('/admin/cards/groups/add', [
        'as'    => 'admin-cards-group-add',
        'uses'  => 'Admin\AdminCardsGroupController@addCardGroup'
    ]);
    //Роут "Карты->Группы->Изменить->[Кнопка "Применить"]".
    Route::put('/admin/cards/groups/edit', [
        'as'    => 'admin-cards-group-edit',
        'uses'  => 'Admin\AdminCardsGroupController@editCardGroup'
    ]);
    //Роут "Карты->Группы->Удалить
    Route::delete('/admin/cards/groups/drop', [
        'as'    => 'admin-cards-group-drop',
        'uses'  => 'Admin\AdminCardsGroupController@dropCardGroup'
    ]);
    
    
    //Роут "Карты->Действия"
    Route::get('/admin/cards/actions', [
        'as'    => 'admin-cards-actions',
        'uses'  => 'Admin\AdminCardsActionsController@actionsPage'
    ]);
    //Роут "Карты->Действия->Добавить"
    Route::get('/admin/cards/actions/add', [
        'as'    => 'admin-cards-actions-add',
        'uses'  => 'Admin\AdminCardsActionsController@cardActionsAddPage'
    ]);
    //Роут "Карты->Действия->Изменить". Входным параметром есть id БД
    Route::get('/admin/cards/actions/edit/{id}', [
        'as'    => 'admin-cards-actions-edit-it',
        'uses'  => 'Admin\AdminCardsActionsController@cardActionsEditPage'
    ]);
    //Роут "Карты->Действия->Добавить[Кнопка "Добавить"]"
    Route::post('/admin/cards/actions/add', [
        'as'    => 'admin-cards-actions-add',
        'uses'  => 'Admin\AdminCardsActionsController@addCardAction'
    ]);
    //Роут "Карты->Действия->Изменить->[Кнопка "Применить"]".
    Route::put('/admin/cards/actions/edit', [
        'as'    => 'admin-cards-actions-edit',
        'uses'  => 'Admin\AdminCardsActionsController@editCardAction'
    ]);
    //Роут "Карты->Действия->Удалить
    Route::delete('/admin/cards/actions/drop', [
        'as'    => 'admin-cards-actions-drop',
        'uses'  => 'Admin\AdminCardsActionsController@dropCardAction'
    ]);


    //Роут "Волшебство"
    Route::get('/admin/magic', [
        'as'    => 'admin-magic-effects',
        'uses'  => 'Admin\AdminMagicEffectsController@magicEffectsPage'
    ]);
    //Роут "Волшебство" -> Добавить
    Route::get('/admin/magic/add', [
        'as'    => 'admin-magic-effects-add',
        'uses'  => 'Admin\AdminMagicEffectsController@magicEffectsAddPage'
    ]);
    //Роут "Волшебство" -> Изменить
    Route::get('/admin/magic/edit/{id}', [
        'as'    => 'admin-magic-effects-edit-it',
        'uses'  => 'Admin\AdminMagicEffectsController@magicEffectsEditPage'
    ]);
    //Роут "Волшебство"->Добавить[Кнопка "Добавить"]
    Route::post('/admin/magic/add', [
        'as'    => 'admin-magic-effects-add',
        'uses'  => 'Admin\AdminMagicEffectsController@addMagicEffects'
    ]);
    //Роут "Волшебство->Изменить->[Кнопка "Применить"]
    Route::put('/admin/magic/edit', [
        'as'    => 'admin-magic-effects-edit',
        'uses'  => 'Admin\AdminMagicEffectsController@editMagicEffects'
    ]);
    //Роут "Волшебство"->Удалить
    Route::delete('/admin/magic/drop', [
        'as'    => 'admin-magic-drop',
        'uses'  => 'Admin\AdminMagicEffectsController@dropMagicEffect'
    ]);


    //Роут "Волшебство" -> "Действия"
    Route::get('/admin/magic/actions',[
        'as'    => 'admin-magic-actions',
        'uses'  => 'Admin\AdminMagicEffectsController@magicActionsPage'
    ]);
    //Роут "Волшебство" -> "Действия" -> Добавить
    Route::get('/admin/magic/actions/add', [
        'as'    => 'admin-magic-actions-add',
        'uses'  => 'Admin\AdminMagicEffectsController@magicActionsAddPage'
    ]);
    //Роут "Волшебство" -> "Действия" -> Изменить
    Route::get('/admin/magic/actions/edit/{id}', [
        'as'    => 'admin-magic-actions-edit-it',
        'uses'  => 'Admin\AdminMagicEffectsController@magicActionsEditPage'
    ]);
    //Роут "Волшебство" -> "Действия" -> Добавить[Кнопка "Добавить"]
    Route::post('/admin/magic/actions/add', [
        'as'    => 'admin-magic-actions-add',
        'uses'  => 'Admin\AdminMagicEffectsController@addMagicAction'
    ]);
    //Роут "Волшебство" -> "Действия" -> Изменить->[Кнопка "Применить"]
    Route::put('/admin/magic/actions/edit', [
        'as'    => 'admin-magic-actions-edit',
        'uses'  => 'Admin\AdminMagicEffectsController@editMagicAction'
    ]);
    //Роут "Волшебство" -> "Действия" -> Удалить
    Route::delete('/admin/magic/actions/drop', [
        'as'    => 'admin-magic-actions-drop',
        'uses'  => 'Admin\AdminMagicEffectsController@dropMagicAction'
    ]);


    
    //Роут "Пользователи"
    Route::get('/admin/users', [
        'as'    => 'admin-users',
        'uses'  => 'Admin\AdminUsersController@index'
    ]);
    //Роут "Пользователи"->детально
    Route::get('/admin/users/view/{id}', [
        'as'    => 'admin-users-view',
        'uses'  => 'Admin\AdminUsersController@view'
    ]);
    //Роут "Пользователи"->бан
    Route::post('/admin/users/ban', [
        'uses'  => 'Admin\AdminUsersController@ban'
    ]);
    //Роут "Пользователи"->Удалить
    Route::delete('/admin/user/delete', [
        'as'    => 'admin-users-delete',
        'uses'  => 'Admin\AdminUsersController@deleteUser'
    ]);


    //Роут "Администраторы"
    Route::get('/admin/admins', [
        'as'    => 'admin-managers',
        'uses'  => 'Admin\AdminManagersController@index'
    ]);
    //Роут "Администраторы->Добавить"
    Route::get('/admin/admins/add', [
        'as'    => 'admin-manager-add',
        'uses'  => 'Admin\AdminManagersController@addPage'
    ]);
    //Роут "Администраторы->Изменить". Входным параметром есть id БД
    Route::get('/admin/admins/edit/{id}', [
        'as'    => 'admin-manager-edit-it',
        'uses'  => 'Admin\AdminManagersController@editPage'
    ]);

    //Роут "Администраторы->Добавить[Кнопка "Добавить"]"
    Route::post('/admin/admins/add', [
        'as'    => 'admin-manager-add',
        'uses'  => 'Admin\AdminManagersController@addAdmin'
    ]);
    //Роут "Администраторы->Изменить->[Кнопка "Применить"]".
    Route::put('/admin/admins/edit_success', [
        'as'    => 'admin-manager-edit',
        'uses'  => 'Admin\AdminManagersController@editAdmin'
    ]);
    //Роут "Администраторы->Удалить
    Route::delete('/admin/admins/drop', [
        'as'    => 'admin-manager-drop',
        'uses'  => 'Admin\AdminManagersController@dropAdmin'
    ]);


    //Роут "Файлы"
    Route::get('/admin/files', [
        'as'    => 'admin-files',
        'uses'  => 'Admin\AdminFilesController@index'
    ]);
    
    Route::delete('/admin/files/drop', [
        'uses'  => 'Admin\AdminFilesController@dropFiles'
    ]);
});