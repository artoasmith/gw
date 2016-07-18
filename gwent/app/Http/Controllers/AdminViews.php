<?php
namespace App\Http\Controllers;

use App\CardActionsModel;
use App\CardGroupsModel;
use App\CardsModel;
use App\MagicEffectsModel;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class AdminViews extends BaseController
{
    //Функция возвращает действие карты в виде array('html_data' => 'html код действий карты', 'descr' => 'Описание действия')
    public static function cardsViewActionsList(Request $request){
        if(csrf_token() == $request->input('token')){
            $data = $request->all();

            //Выборка из БД действия карты
            $action_data = $card_actions = CardActionsModel::find($data['action']);

            return json_encode(array('html_data' => unserialize($action_data->html_options), 'descr' => $action_data['description']));
        }
    }


    //Функция вывода карт в группе (для admin/crads/groups)  ($card_id - id карты, $type - тип вывода)
    public static function cardsViewCardsList($cards_array, $type ='link'){
        $cards_array = unserialize($cards_array);

        $result = '';

        //Вывод указан как вывод ссылок
        if($type == 'link'){
            foreach ($cards_array as $card_id) {
                $card = CardsModel::find($card_id);
                $result .= '<a href="/admin/cards/edit/'.$card_id.'">'.$card['title'].'</a>, ';
            }
        }

        //Вывод указан как вывод таблицей
        if($type == 'table'){
            foreach ($cards_array as $card_id) {
                $card = CardsModel::find($card_id);
                $result .= '
                <tr>
                    <td><a class="drop" href="#"></a></td>
                    <td>'.$card['title'].'</td>
                    <td style="display: none;">'.$card_id.'</td>
                </tr>
                ';
            }

        }

        return substr($result, 0, -2);
    }

    //Функиция возвращает список всех груп карт
    public static function cardsViewGroupsList(Request $request){
        if(csrf_token() == $request->input('token')){
            //Выборка из БД групп по названию; сортировка алфавитная
            $groups_data = CardGroupsModel::orderBy('title','asc')->get();

            return json_encode($groups_data->all());
        }
    }

    //Функция вывода списка групп карты ($card_id - id карты, $type - тип вывода)
    public static function cardsViewGetCardGroups($card_id, $type='link'){
        //Выборка из БД групп по названию; сортировка алфавитная
        $groups_data = CardGroupsModel::orderBy('title', 'asc')->get();
        $result = '';

        //Вывод указан как вывод ссылок
        if($type == 'link'){

            foreach($groups_data->all() as $group){
                //ID карт в группе
                $has_cards_ids = unserialize($group['has_cards_ids']);
                //если в массиве "ID карт" есть текщая id -> добавляем ссылку на нее
                if(in_array($card_id, $has_cards_ids)){
                    $result .= '<a href="/admin/cards/groups/'.$card_id.'">'.$group['title'].'</a>, ';
                }
            }
            return substr($result, 0, -2);

        }

        //Вывод указан как вывод таблицей
        if($type == 'table'){

            foreach($groups_data->all() as $group){
                //ID карт в группе
                $has_cards_ids = unserialize($group['has_cards_ids']);
                //если в массиве "ID карт" есть текщая id -> добавляем ряд группы
                if(in_array($card_id, $has_cards_ids)){
                    $result .= '
                    <tr>
                        <td>
                            <a class="drop" href="#"></a>
                        </td>
                        <td>'.$group['title'].'</td>
                        <td style="display: none;">'.$group['id'].'</td>
                    </tr>
                    ';
                }
            }
            return $result;

        }
    }

    //Вывод списка магических эффектов
    protected function cardsViewMagicList(Request $request){
        if( csrf_token() == $request -> input('token')){
            $magic = MagicEffectsModel::orderBy('title','asc')->get();

            $result = '';
            foreach ($magic as $key => $value) {
                $result .= '<option value="'.$value -> id.'">'.$value->title.'</option>';
            }

            return $result;
        }
    }

    //Создание списка групп для cardsViewGetCardActions
    protected static function createActionGroups($action){
        $groups_data = CardGroupsModel::orderBy('title', 'asc')->get();

        $result = '';

        foreach($action as $action_group){
            foreach ($groups_data as $group) {
                if($action_group == $group['id']){
                    $result .= $group['title'].', ';
                }
            }
        }
        $result = substr($result, 0, -2).'<br>';
        return $result;
    }

    //Создание списка магических еффектов
    protected static function createMagicEffects($action){
        $magic_data = MagicEffectsModel::orderBy('title', 'asc')->get();

        $result = '';

        foreach($action as $action_group){
            foreach ($magic_data as $magic) {
                if($action_group == $magic['id']){
                    $result .= $magic['title'].', ';
                }
            }
        }
        $result = substr($result, 0, -2).'<br>';
        return $result;
    }

    //Создание списка дальности действия карты/действия для cardsViewGetCardActions
    protected static function createActionsRowRange($action){
        $result = '';
        foreach ($action as $range) {;
            switch($range){
                case '0': $result.= 'Ближний; '; break;
                case '1': $result.= 'Дальний; '; break;
                case '2': $result.= 'Сверхдальний; '; break;
            }
        }
        $result = substr($result, 0, -2).'<br>';
        return $result;
    }

    //Создание списка Рас карты/действия для cardsViewGetCardActions
    protected  static function createViewEnemyRace($action){
        $result = '';
        foreach($action as $race){
            switch ($race){
                case 'knight':      $result .= 'Рыцари империи, '; break;
                case 'forest':      $result .= 'Хозяева леса, '; break;
                case 'cursed':      $result .= 'Проклятые, '; break;
                case 'undead':      $result .= 'Нечисть, '; break;
                case 'highlander':  $result .= 'Горцы, '; break;
                case 'monsters':    $result .= 'Монстры, '; break;
                case 'neutral':     $result .= 'Нейтральные, '; break;
            }
        }
        $result = substr($result, 0, -2).'<br>';
        return $result;
    }

    //Функция создает список действий карты для admin/cards
    public static function cardsViewCurrentCardActions($actions){
        $actions = unserialize($actions);

        $result = '';

        foreach ($actions as $action) {
            $current_action = CardActionsModel::where('id', '=', $action->action)->get();
            $result .= $current_action[0]['title'].', ';
        }

        return substr($result, 0, -2);
    }

    //Функция возвращает список действий карты
    public static function cardsViewGetCardActions($actions){
        $actions = unserialize($actions);

        $result = '';

        foreach($actions as $action){

            $current_action = CardActionsModel::where('id', '=', $action->action)->get();
            $result .= '
            <tr>
                <td><a class="drop" href="#"></a></td>
                <td>
                    <ins>'.$current_action[0]['title'].'</ins>:<br>
            '; // Вывод названия действия


            //Бессмертный
            if(isset($action -> CAundead_backToDeck)){
                if(0 == $action -> CAundead_backToDeck){
                    $result .= ' - Возвращается На поле;<br>';
                }else{
                    $result .= ' - Возвращается В руку;<br>';
                }
            }


            //Боевое Братство
            if(isset($action -> CAbloodBro_actionToGroupOrSame)){
                if($action -> CAbloodBro_actionToGroupOrSame == 0){
                    $result .= ' - Дейстует на одинаковые;<br>';
                }else{
                    $result .= ' - Действует на группу: '. self::createActionGroups($action -> CAbloodBro_actionToGroupOrSame);
                }

                $result .= ' - Умножает силу в '.$action->CAbloodBro_strenghtMult.' раз;<br>';
            }


            //Воодушевление
            if(isset($action -> CAinspiration_ActionRow)){
                $result .= ' - Дальность: '. self::createActionsRowRange($action -> CAinspiration_ActionRow);

                $result .= ' - Модификатор силы: ';
                if(0 == $action -> CAinspiration_modificator){
                    $result .= 'Умножение<br>';
                }else{
                    $result .= 'Добавление<br>';
                }

                $result .= ' - Значение: '.$action -> CAinspiration_multValue;
            }


            //Иммунитет
            if(isset($action -> CAimmumity_type)){
                $result .= ' - Тип иммунитета: ';

                if(0 == $action -> CAimmumity_type){
                    $result .= 'Простой';
                }else{
                    $result .= 'Полный';
                }
            }


            //Лекарь
            if(isset($action -> CAhealer_groupOrSingle)){
                if(0 == $action -> CAhealer_groupOrSingle){
                    $result .= ' - Дейстует на одиночную';
                }else{
                    $result .= ' - Действует на группу: '. self::createActionGroups($action -> CAhealer_groupOrSingle);
                }
            }


            //Неистовство
            if(isset($action -> CAfury_enemyRace)){
                $result .= ' - Действует на рассу: '. self::createViewEnemyRace($action -> CAfury_enemyRace);

                if(!empty($action -> CAfury_group)){
                    $result .= ' - Противник имеет карту из группы: '. self::createActionGroups($action -> CAfury_group);
                }

                $result .= ' - Действует на ряд: '.self::createActionsRowRange($action -> CAfury_ActionRow);

                if(0 != $action -> CAfury_enemyHasSuchNumWarriors){
                    $result .= ' - Противник имеет воинов в количестве: '.$action -> CAfury_enemyHasSuchNumWarriors.' в ряду: '.self::createActionGroups($action -> CAfury_ActionRow);
                }

                if(isset($action -> CAfury_addStrenght)){
                    $result .= ' - Повышает силу на: '.$action -> CAfury_addStrenght.' единиц<br>';
                }

                if(isset($action -> CAfury_abilityCastEnemy)){
                    $result .= ' - Противник использовал способность: '. self::createMagicEffects($action -> CAfury_abilityCastEnemy);
                }
            }


            //Одурманивание
            if(isset($action -> CAobscure_ActionRow)){
                $result .= ' - Действует на ряд: '.self::createActionsRowRange($action -> CAobscure_ActionRow);
                $result .= ' - Максимальная сила карты которую можно перетянуть: '.$action -> CAobscure_maxCardStrong.'<br>';
                $result .= ' - Сила перетягиваемой карты: ';
                switch($action -> CAobscure_strenghtOfCardToObscure){
                    case '0': $result .= 'Слабую<br>'; break;
                    case '1': $result .= 'Сильную<br>'; break;
                    case '2': $result .= 'Случайно<br>'; break;
                }
                $result .= ' - Количество перетягиваемых карт: '.$action -> CAobscure_quantityOfCardToObscure;
            }


            //Печаль
            if(isset($action -> CAsorrow_ActionRow)){
                $result .= ' - Действует на ряд: '.self::createActionsRowRange($action -> CAsorrow_ActionRow);
                $result .= ' - Действует на своих: ';
                if(0 == $action -> CAsorrow_actionTeamate){
                    $result .= 'Нет';
                }else{
                    $result .= 'Да';
                }
            }
            

            //Повелитель
            if(isset($action -> CAmaster_group)){
                $result .= ' - Группа карт, которые будут призываться: '.self::createActionGroups($action -> CAmaster_group);

                $result .= ' - Карты берутся из: ';
                foreach($action -> CAmasder_cardSource as $source){
                    switch($source){
                        case 'hand':    $result .= 'Рука, '; break;
                        case 'passed':  $result .= 'Отбой, '; break;
                        case 'deck':    $result .= 'Колода, '; break;
                    }
                }
                $result = substr($result, 0, -2).'<br>';

                $result .= ' - Призывать карту: ';
                switch($action -> CAmaster_summonByModificator){
                    case '0': $result .= 'Слабую<br>'; break;
                    case '1': $result .= 'Сильную<br>'; break;
                    case '2': $result .= 'Случайно<br>'; break;
                }
                $result .= ' - Макс. количество карт, которое призывается: '. $action -> CAmaster_maxCardsSummon.'<br>';
                $result .= ' - Макс. значение силы карт, которые призываются: '. $action -> CAmaster_maxCardsStrenght;
            }


            //Поддержка
            if(isset($action -> CAsupport_ActionRow)){
                $result .= ' - Повысить силу в ряду: '. self::createActionsRowRange($action -> CAsupport_ActionRow);

                if(0 == $action -> CAsupport_actionToGroupOrAll){
                    $result .= ' - Дейстует на всех<br>';
                }else{
                    $result .= ' - Действует на группу: '. self::createActionGroups($action -> CAsupport_actionToGroupOrAll);
                }

                $result .= ' - Повышение силы действует на себя: ';
                if(0 == $action -> CAsupport_selfCast){
                    $result .= 'Нет<br>';
                }else{
                    $result .= 'Да<br>';
                }

                $result .= ' - Значение повышения силы: '. $action -> CAsupport_strenghtValue.' единиц';
            }


            //Страшный
            if(isset($action -> CAfear_enemyRace)){
                $result .= ' - Действует на рассу: '. self::createViewEnemyRace($action -> CAfear_enemyRace);

                if(0 == $action->CAfear_actionToGroupOrAll){
                    $result .= ' - Действует на всех<br>';
                }else{
                    $result .= ' - Действует на группу: '.self::createActionGroups($action -> CAfear_actionToGroupOrAll);
                }

                $result .= ' - Ряд действия: '. self::createActionsRowRange($action -> CAfear_ActionRow);

                $result .= ' - Действует на своих: ';
                if(0 == $action -> CAfear_actionTeamate){
                    $result .= 'Нет<br>';
                }else{
                    $result .= 'Да<br>';
                }

                $result .= ' - Значение понижения силы: '. $action -> CAfear_strenghtValue;
            }


            //Убийца
            if(isset($action -> CAkiller_ActionRow)){
                $result .= ' - Ряд действия: '. self::createActionsRowRange($action -> CAkiller_ActionRow);

                if(0 == $action->CAkiller_groupOrSingle){
                    $result .= ' - Действует на любую<br>';
                }else{
                    $result .= ' - Действует на группу: '.self::createActionGroups($action -> CAkiller_groupOrSingle);
                }

                if(0 != $action ->CAkiller_recomendedTeamateForceAmount_OnOff){
                    $result .= ' - Количество силы необходимое для совершения убийства воинов: '. $action -> CAkiller_recomendedTeamateForceAmount_OnOff;
                    switch($action -> CAkiller_recomendedTeamateForceAmount_Selector){
                        case '0': $result .= ' (Больше указанного значения)<br>'; break;
                        case '1': $result .= ' (Меньше указанного значения)<br>'; break;
                        case '2': $result .= ' (Равно указанному значению)<br>'; break;
                    }
                }

                $result .= ' - Порог силы воинов противника для совершения убийства: '.$action -> CAkiller_enemyStrenghtLimitToKill.'<br>';

                if(isset($action->CAkiller_killedQuality_Selector)){
                    $result .= ' - Качество "Убиваемой" карты: ';
                    switch($action->CAkiller_killedQuality_Selector){
                        case '0': $result .= 'Самая слабая<br>'; break;
                        case '1': $result .= 'Самая сильная<br>'; break;
                        case '2': $result .= 'Случайно<br>'; break;
                    }
                }

                $result .= ' - Вариация количества убийств: ';
                if(0 == $action -> CAkiller_killAllOrSingle){
                    $result .= 'Одиночная<br>';
                }else{
                    $result .= 'Всех<br>';
                }

                $result .= ' - Может бить своих юнитов по указаным выше параметрах: ';
                if(0 == $action -> CAkiller_atackTeamate){
                    $result .= 'Нет<br>';
                }else{
                    $result .= 'Да<br>';
                }

                $result .= ' - Игнорирует иммунитет к убийству: ';
                if(0 == $action -> CAkiller_ignoreKillImmunity){
                    $result .= 'Нет<br>';
                }else{
                    $result .= 'Да<br>';
                }


            }


            //Шпион
            if(isset($action -> CAspy_get_cards_num)){
                $result .= ' - Плучить из колоды '.$action -> CAspy_get_cards_num.' карт';
            }

            $result .='
                </td>
                <td style="display: none;">'.json_encode($action).'</td>
            </tr>
            ';
        }

        return $result;
    }


    /* Главная страница*/

    protected static function createCardSelectOptions($cards_array, $id = ''){
        $result = '';
        foreach($cards_array as $card){
            if($id == $card['id']){
                $selected = 'selected="selected"';
            }else{
                $selected = '';
            }
            $result .= '<option value="'.$card['id'].'" '.$selected.'>'.$card['title'].'</option>';
        }

        return $result;
    }
    //Функция возвращает селектор всех карт
    public static function getAllCardsSelectorView($id=''){
        $out = '<select name="currentCard">';
        $cards_type = \DB::table('tbl_card')->select('card_type')->groupBy('card_type')->get();

        foreach($cards_type as $type){
            switch($type->card_type){
                case 'race':
                    $current_card_type = 'Рассовые';
                    $result = '';

                    $cards_race = CardsModel::where('card_type', '=', 'race')->groupBy('card_race')->get();
                    foreach($cards_race as $card_race){
                        switch($card_race->card_race){
                            case 'knight':      $result .= '<optgroup label="Рыцари империи">'; break;
                            case 'forest':      $result .= '<optgroup label="Хозяева леса">'; break;
                            case 'cursed':      $result .= '<optgroup label="Проклятые">'; break;
                            case 'undead':      $result .= '<optgroup label="Нечисть">'; break;
                            case 'highlander':  $result .= '<optgroup label="Горцы">'; break;
                            case 'monsters':    $result .= '<optgroup label="Монстры">'; break;
                        }
                        $cards_by_races = CardsModel::where('card_type', '=', 'race')->where('card_race', '=', $card_race->card_race)->orderBy('title','asc')->get();

                        $result .= self::createCardSelectOptions($cards_by_races, $id);
                        $result .= '</optgroup>';
                    }
                    break;

                case 'neutrall':
                    $current_card_type = 'Нейтральные';
                    $result = '';

                    $cards_to_view = CardsModel::where('card_type', '=', $type->card_type)->orderBy('title', 'asc')->get();

                    $result .= self::createCardSelectOptions($cards_to_view, $id);
                    break;

                case 'special':
                    $current_card_type = 'Специальные';
                    $result = '';

                    $cards_to_view = CardsModel::where('card_type', '=', $type->card_type)->orderBy('title', 'asc')->get();

                    $result .= self::createCardSelectOptions($cards_to_view, $id);
                    break;

            }
            $out .= '<optgroup label="'.$current_card_type.'">'.$result.'</optgroup>';

        }
        $out .= '</select>';
        return $out;
    }

}