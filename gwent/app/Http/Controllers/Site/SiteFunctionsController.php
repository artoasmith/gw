<?php
namespace App\Http\Controllers\Site;

use App\BattleModel;
use App\CardsModel;
use App\EtcDataModel;
use App\LeagueModel;
use App\MagicEffectsModel;
use App\RaceModel;
use App\User;
use App\UserAdditionalDataModel;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiteFunctionsController extends BaseController
{
	//Функция возвращает данные пользователя [аватар, золото, серебро, энергия, настройки колоды, лиги]
	protected function getUserData(Request $request){
		$data = $request->all();

		if(empty($data)){
			//если просматриваем свои данные
			$user = Auth::user();
			$login = $user['login'];
		}else{
			//если просматриваем данные другого
			$login = htmlspecialchars(strip_tags(trim($data['login'])));
		}

		if((!empty($login)) && ($login != '')){
			//Достаем из БД данные пользователя
			$user = User::where('login', '=', $login)->get();
			//Достаем из БД дополнительные данные пользователя
			$user_data = UserAdditionalDataModel::where('login', '=', $login)->get();
			$etc_data = EtcDataModel::where('label_data', '=', 'deck_options')->get();
			//$leagues = LeagueModel::orderBy('title', 'asc')->get();
			$leagues = \DB::table('tbl_league')->select('title', 'min_lvl')->orderBy('title', 'asc')->get();
            $exchanges = EtcDataModel::where('label_data', '=', 'exchange_options')->get();

			$result = [
				'avatar'    => $user[0] -> img_url,
				'gold'      => $user_data[0] -> user_gold,
				'silver'    => $user_data[0] -> user_silver,
				'energy'    => $user_data[0] -> user_energy,
			];

			if(empty($data)) {
				foreach ($etc_data as $key => $value) {
					$result[$value->meta_key] = $value->meta_value;
				}

				$result['leagues'] = [];
				foreach ($leagues as $key => $value) {
					$result['leagues'][] = ['title' => $value->title, 'min_lvl' => $value->min_lvl];
				}

                $result['exchanges'] = [];
                foreach ($exchanges as $key => $value) {
                    $result['exchanges'][$value->meta_key] = $value->meta_value;
                }
			}
			return json_encode($result);
		}
	}


	//Функция возвращает пользовательские колоды (доступные карты и карты колоды)
	protected function getUserDeck(Request $request){
		//Обновление активности пользователя
		self::updateConnention();
		$data = $request->all();

		//Если не указан логин - используем данные текущего пользователя
		if(empty($data['login'])){
			$user = Auth::user();
			$login = $user['login'];
		}else{
			$login = htmlspecialchars(strip_tags(trim($data['login'])));
		}

		//Если логин не пустой
		if((!empty($login)) && ($login != '')){
			//Текущая колода
			$deck = htmlspecialchars(strip_tags(trim($data['deck'])));

			$user_data = \DB::table('tbl_user_data')->select('login','user_available_deck', 'user_cards_in_deck')->where('login', '=', $login)->get();

			//Все доступные карты пользователя, что не находятся в колодах
			$user_available_cards = unserialize($user_data[0]->user_available_deck);

			$result_array= ['in_deck' => [], 'available' => []];

			//формирование масива доступных карт
			foreach ($user_available_cards as $key => $value) {
				$card = CardsModel::where('id', '=', $key)->get();

				if(($card[0]->card_race == $deck)or($card[0]->card_race == '')) {

					$result_array['available'][] = [
						'id'        => $key,
						'title'     => $card[0]->title,
						'type'      => $card[0]->card_type,
						'race'      => $card[0]->card_race,
						'strength'  => $card[0]->card_strong,
						'weight'    => $card[0]->card_value,
						'is_leader' => $card[0]->is_leader,
						'img_url'   => $card[0]->img_url,
						'descr'     => $card[0]->short_description,
						'quantity'  => $value
					];

				}

			}

			//карты пользовательских колод
			$user_deck = unserialize($user_data[0]->user_cards_in_deck);

			//формирование масива карт пользовательских колод
			foreach($user_deck[$deck] as $key => $value){
				$card = CardsModel::where('id', '=', $key)->get();

				if(($card[0]->card_race == $deck)or($card[0]->card_race == '')) {
					$result_array['in_deck'][] = [
						'id'        => $key,
						'title'     => $card[0]->title,
						'type'      => $card[0]->card_type,
						'race'      => $card[0]->card_race,
						'strength'  => $card[0]->card_strong,
						'weight'    => $card[0]->card_value,
						'is_leader' => $card[0]->is_leader,
						'img_url'   => $card[0]->img_url,
						'descr'     => $card[0]->short_description,
						'quantity'  => $value
					];
				}
			}
			return json_encode($result_array);
		}
	}


    //Перенос карт из колоды в колоду
	protected static function addCardToDeck($deck_from, $deck_to, $card_id){
		$result = ['deck_from' => [], 'deck_to' => []];

		foreach ($deck_from as $key => $val) {
			//если такая карта действительно существует
			if($key == $card_id){
				//Уменьшаем количество перетягиваемой карты на 1
				$deck_from[$key]--;

				//Если такая карта существует в пользовательской колоде
				if(isset($deck_to[$key])){
					//увеличиваем её количество на 1
					$deck_to[$key]++;
				}else{
					//если нету, создаем её
					$deck_to[$key] = 1;
				}

				//Если карт одного вида в колоде доступных нету- удаляем её
				if(0 >= $deck_from[$key]){
					unset($deck_from[$key]);
				}
			}
		}

		$result['deck_from'] = $deck_from;
		$result['deck_to'] = $deck_to;
		return $result;
	}


	//Изменение колод пользователя
	protected function changeUserDeck(Request $request){
		self::updateConnention();
		if(csrf_token() == $request->input('token')){
			$data = $request->all();
			$user = Auth::user();
			$user_data = \DB::table('tbl_user_data')->select('user_id', 'user_available_deck', 'user_cards_in_deck')->where('user_id', '=', $user['id'])->get();

			//Доступные карт
			$available_deck = unserialize($user_data[0]->user_available_deck);

			//ПОльзовательские колоды карт
			$card_deck = unserialize($user_data[0]->user_cards_in_deck);

			//Перетягивание из доступных в колоду расы
			if($data['source'] == 'available'){

				$decks = self::addCardToDeck($available_deck, $card_deck[$data['deck']], $data['card_id']);

				$card_deck[$data['deck']] = $decks['deck_to'];
				$card_deck = serialize($card_deck);
				$available_deck = serialize($decks['deck_from']);
			}

			//Перетягивание из колоды расы в доступные
			if($data['source'] == 'user_deck'){

				$decks = self::addCardToDeck($card_deck[$data['deck']], $available_deck, $data['card_id']);

				$card_deck[$data['deck']] = $decks['deck_from'];
				$card_deck = serialize($card_deck);
				$available_deck = serialize($decks['deck_to']);
			}
            //Сохраняем колоды в БД
            $user_data_to_save = UserAdditionalDataModel::find($user['id']);
            $user_data_to_save -> user_available_deck = $available_deck;
            $user_data_to_save -> user_cards_in_deck = $card_deck;
            $user_data_to_save -> save();
		}
	}


	//Количество активных пользователей на сайте
	protected function getUserQuantity(){
		return User::where('user_online', '=', 1)->count();
	}


	//Если пользователь начал проявлять активность - делаем его активным
	public static function updateConnention(){
		$user = Auth::user();
		if($user){
			User::where('login', '=', $user['login'])->update(['updated_at' => date('Y-m-d H:i:s'), 'user_online' => '1']);
		}
	}


	//Функция возвращает колоду карт в зависимости от расы
	protected function getCardsByRace(Request $request){
		self::updateConnention();

		//Если колода относится к определенной расе
		if( ($request -> input('race') == 'special') || ($request -> input('race') == 'neutrall') ){
			$field = 'card_type';
		}else{
			$field = 'card_race';
		}
		$cards = CardsModel::where($field, '=', $request -> input('race'))->orderBy('card_strong','desc') -> get();

		//$race = RaceModel::where('slug', '=', $request -> input('race'))->get();
		$race = \DB::table('tbl_race')->select('slug','img_url')->where('slug', '=', $request -> input('race'))->get();

		$result['race_img_url'] = $race[0] -> img_url;
		foreach($cards as $key => $value){
			$result['cards'][] = [
				'id'        => $value['id'],
				'title'     => $value['title'],
				'slug'      => $value['slug'],
				'card_type' => $value['card_type'],
				'card_race' => $value['card_race'],
				'strength'  => $value['card_strong'],
				'value'     => $value['card_value'],
				'is_leader' => $value['is_leader'],
				'img_url'   => $value['img_url'],
				'actions'   => $value['card_actions'],
				'descr'     => $value['short_description'],
				'gold'      => $value['price_gold'],
				'silver'    => $value['price_silver'],
                'only_gold' => $value['price_only_gold']
			];
		}

		return json_encode($result);
	}


	//Функция возвращает данные цен карты и средства пользователя
	protected function getCardData(Request $request){

	    $data = $request->all();

		self::updateConnention();
		$user = Auth::user();
		$card = \DB::table('tbl_card')->select('id','title','price_gold','price_silver','price_only_gold')->where('id', '=', $data['card_id'])->get();
		$user_money = \DB::table('tbl_user_data')->select('user_id','user_gold','user_silver')->where('user_id', '=', $user['id'])->get();

        $result = [
            'title'         => $card[0]->title,
            'user_gold'     => $user_money[0]->user_gold,
            'user_silver'   => $user_money[0]->user_silver,
            'message'       => 'success'
        ];
        //
        switch($data['buy_type']){
            case 'simpleBuy':
                $result['price_gold']   = $card[0]->price_gold;
                $result['price_silver'] = $card[0]->price_silver;
                break;
            case 'goldOnlyBuy':
                $result['price_gold']   = $card[0]->price_only_gold;
                $result['price_silver'] = 0;
                break;
            default:
                $result['message'] = 'Неверная операция.';
        }
		return json_encode($result);
	}


	//Пользователь покупает карту
	protected function userBuyingCard(Request $request){
        self::updateConnention();

	    $data= $request->all();

		self::updateConnention();
		$user = Auth::user();
		//Данные пользователя
		$user_data = UserAdditionalDataModel::where('user_id', '=', $user['id'])->get();

		//Данные карты
		$card = CardsModel::where('id', '=', $data['card_id'])->get();

		//Определяем тип покупки (Только золото||золото+серебро)
        switch($data['buy_type']){
            case 'simpleBuy':
                //Отнимаем от средств пользователя цену карты
                $user_gold = $user_data[0]->user_gold - $card[0]->price_gold;
                $user_silver = $user_data[0]->user_silver - $card[0]->price_silver;
                break;
            case 'goldOnlyBuy':
                $user_gold = $user_data[0]->user_gold - $card[0]->price_only_gold;
                $user_silver = $user_data[0]->user_silver;
                break;
            default:
                return json_encode(['message' => 'Неизвестный тип операции.']);
        }

		//Доступные карты пользователя
		$user_available_deck = unserialize($user_data[0]->user_available_deck);

		//Если карта существуетв колоде - увеличиваем её количество на 1
		if( isset($user_available_deck[$data['card_id']]) ){
			$user_available_deck[$data['card_id']]++;
		}else{
			//Если нету - создаем её
			$user_available_deck[$data['card_id']] = 1;
		}

		//Сохраняем колоду доступных карт
		$user_data_to_save = UserAdditionalDataModel::find($user['id']);

        $user_data_to_save -> user_gold             = $user_gold;
        $user_data_to_save -> user_silver           = $user_silver;
		$user_data_to_save -> user_available_deck   = serialize($user_available_deck);
        $result = $user_data_to_save -> save();

		if($result !== false){
			return json_encode(['message' => 'success', 'gold' => $user_gold, 'silver' => $user_silver, 'title' => $card[0]->title]);
		}else{
			return $result;
		}
	}


	//Функция возвращает магисческие эффекты в зависимости от расы
	protected function getMagicEffectsByRace(Request $request){
		self::updateConnention();
		$user = Auth::user();
		//Данные пользователя
		$user_data = UserAdditionalDataModel::where('user_id', '=', $user['id'])->get();
		//магические еффекты
		$magic_effects = MagicEffectsModel::orderBy('price_gold','asc')->orderBy('price_silver','asc')->get();

		//Изображение текущей расы
		$race = \DB::table('tbl_race')->select('slug','img_url')->where('slug', '=', $request -> input('race'))->get();
		$result['race_img_url'] = $race[0] -> img_url;

		//Текущие магические эффекты пользоввтеля
		$user_magic = unserialize($user_data[0]->user_magic_effects);

		foreach($magic_effects as $key => $value){
			//Магический эффект доступен расам
			$magic_current_race = unserialize($value->race);
			//Если текущая раса в массиве доступных рас
			if( in_array($request->input('race'), $magic_current_race, true) ){

				//если пользователь имеет текущий магический еффект
				if( isset($user_magic[$value->id]) ){

					//если магический еффект полностью израсходован
					if( 0 == $user_magic[$value->id]['used_times'] ){

						$status = 'disabled';		//статус "отсутствует"
						$expire = '&mdash;&mdash;'; //Дата окончания
						$used_times = '';			//Осталось использований

					}else{

						if( 0 == $user_magic[$value->id]['active'] ){
							$status = '';			//статус "не активен"
						}else{
							$status = 'active';		//статус "активен"
						}

						$expire = date('Y-m-d H:i',strtotime($user_magic[$value->id]['expire_date']));		//Дата окончания
						$used_times = '<p>Осталось '.$user_magic[$value->id]['used_times'].' использований</p>';

					}

				}else{
					$status = 'disabled';
					$expire = '&mdash;&mdash;';
					$used_times = '';
				}

				//если цена в золоте == 0
				if( 0 == $value->price_gold){
					$gold = '&mdash;';
				}else{
					$gold = $value->price_gold;
				}

				//если цена в серебре == 0
				if( 0 == $value->price_silver){
					$silver = '&mdash;';
				}else{
					$silver = $value->price_silver;
				}

				$result['effects'][] = [
					'id'        => $value->id,
					'title'     => $value->title,
					'img_url'   => $value->img_url,
					'descr'     => $value->description,
					'energy'    => $value->energy_cost,
					'gold'      => $gold,
					'silver'    => $silver,
					'status'    => $status,
					'used_times'=> $used_times,
					'expire'    => $expire
				];
			}
		}
		return json_encode($result);
	}


	//Функция возвращает данные цены волшебства и средства пользователя
	protected function getMagicEffectData(Request $request){
		self::updateConnention();
		$user = Auth::user();
		$magic = \DB::table('tbl_magic_effects')->select('id', 'title', 'price_gold', 'price_silver')->where('id', '=', $request->input('magic_id'))->get();
		$user_money = \DB::table('tbl_user_data')->select('user_id','user_gold','user_silver')->where('user_id', '=', $user['id'])->get();

		return json_encode([
			'title'         => $magic[0]->title,
			'price_gold'    => $magic[0]->price_gold,
			'price_silver'  => $magic[0]->price_silver,
			'user_gold'     => $user_money[0]->user_gold,
			'user_silver'   => $user_money[0]->user_silver,
		]);
	}


	//Пользователь покупает магию
	protected function userBuyingMagic(Request $request){
		self::updateConnention();
		$user = Auth::user();
		//Данные пользователя
		$user_data = UserAdditionalDataModel::where('user_id', '=', $user['id'])->get();

		//Достаем из БД текущий магический еффект
		$magic = MagicEffectsModel::where('id', '=', $request->input('magic_id'))->get();

		//Отнимаем от средств пользователя цену волшебства
		$user_gold = $user_data[0]->user_gold - $magic[0]->price_gold;
		$user_silver = $user_data[0]->user_silver - $magic[0]->price_silver;

		//Текущие магиские эффекты пользователя
		$user_magic_effects = unserialize($user_data[0]->user_magic_effects);

		//Если существует текущий маг. эффект
		if( isset($user_magic_effects[$request->input('magic_id')]) ){
			//Добавляет +100 использований
			$user_magic_effects[$request->input('magic_id')]['used_times'] += 100;
			//Обновляем дату окончания
			$user_magic_effects[$request->input('magic_id')]['expire_date'] = date('Y-m-d H:i:s',time()+ 31*24*60*60);

		}else{
			//Если не существует - создаем его
			$user_magic_effects[$request->input('magic_id')] = ['used_times' => 100, 'expire_date' => date('Y-m-d H:i:s',time()+ 31*24*60*60), 'active' => 0];
		}

        $user_data_to_save = UserAdditionalDataModel::find($user['id']);
        $user_data_to_save -> user_gold         = $user_gold;
        $user_data_to_save -> user_silver       = $user_silver;
        $user_data_to_save -> user_magic_effects= serialize($user_magic_effects);
        $result = $user_data_to_save -> save();

		if($result !== false){
			return json_encode([
                'message'   => 'success',
                'date'      => date('Y-m-d H:i',time()+ 31*24*60*60).'<p>Осталось '.$user_magic_effects[$request->input('magic_id')]['used_times'].' использований</p>',
                'gold'      => $user_gold,
                'silver'    => $user_silver,
                'title'     => $magic[0]->title
            ]);
		}else{
			return $result;
		}
	}


	//Пользователь активирует маг. еффект в магазине
	protected function userChangeMagicEffectStatus(Request $request){
		self::updateConnention();

		$data = $request -> all();
        //Находим текущую сессию
		$user = Auth::user();
        //Находим данные пользователя в БД
		$user_data = UserAdditionalDataModel::where('user_id', '=', $user['id'])->get();

        //Все магические эффекты пользователя
		$user_magic_effects = unserialize($user_data[0]->user_magic_effects);

        //Активные еффекты
		$active_effects = [];

		foreach ($user_magic_effects as $key => $value){
			if( 0 != $value['active'] ){
				$active_effects[] = $key;
			}
		}

        //максимальное количество активных магических эффектов
        $maximum_active_magic = \DB::table('tbl_etc_data')->select('meta_key', 'meta_value')->where('meta_key', '=', 'base_max_magic')->get();

		if($data['is_active'] == 'false') {

			if ($maximum_active_magic[0]->meta_value > count($active_effects)) {
				$user_magic_effects[$data['status_id']]['active'] = 1;
			}else{
				return json_encode(['too_much', 0]);
			}
			
		}else{
			$user_magic_effects[$data['status_id']]['active'] = 0;
		}

		UserAdditionalDataModel::where('user_id', '=', $user['id'])->update(['user_magic_effects' => serialize($user_magic_effects)]);

		return json_encode(['success', $user_magic_effects[$request->input('status_id')]['active']]);

	}


    //Пользователь покупает Серебро
    protected function userBuyingSilver(Request $request){
        self::updateConnention();

        $user = Auth::user();
        $user_data = UserAdditionalDataModel::where('user_id', '=', $user['id'])->get();

        $user_gold = $user_data[0]->user_gold;
        $user_silver = $user_data[0]->user_silver;

        $gold_to_silver = \DB::table('tbl_etc_data')->select('meta_key','meta_value')->where('meta_key','=','gold_to_silver')->get();

        if( $user_gold >= $request -> input('gold') ){
            $user_gold -= $request -> input('gold');
            $user_silver = $user_silver + $request -> input('gold')*$gold_to_silver[0]->meta_value;

            $result = UserAdditionalDataModel::where('user_id', '=', $user['id'])->update(['user_gold' => $user_gold, 'user_silver' => $user_silver]);

            if($result != false){
                return json_encode(['message' => 'success', 'gold' => $user_gold, 'silver' => $user_silver]);
            }
        }else{
            return json_encode(['message' => 'Недостаточно золота для операции.']);
        }
    }


    //Пользователь покупает энергию
	protected function userBuyingEnergy(Request $request){
        self::updateConnention();

		$user = Auth::user();
		$user_data = UserAdditionalDataModel::where('user_id', '=', $user['id'])->get();

		$exchange = EtcDataModel::where('label_data', '=', 'exchange_options')->get();

		$exchange_array = [];
		foreach($exchange as $key => $value){
			$exchange_array[$value -> meta_key] = $value -> meta_value;
		}

		switch($request -> input('pay_type')){
			case 'gold_to_100_energy':
				$user_energy = $user_data[0]['user_energy'] + 100;

				$user_silver = $user_data[0]['user_silver'];

				if($user_data[0]['user_gold'] >= $exchange_array['gold_to_100_energy']){
					$user_gold = $user_data[0]['user_gold'] - $exchange_array['gold_to_100_energy'];
				}else{
					return json_encode(['message' => 'Недостаточно средств для совершения операции.']);
				}
				break;

			case 'silver_to_100_energy':
				$user_energy = $user_data[0]['user_energy'] + 100;

				$user_gold = $user_data[0]['user_gold'];

				if($user_data[0]['user_silver'] >= $exchange_array['silver_to_100_energy']){
					$user_silver = $user_data[0]['user_silver'] - $exchange_array['silver_to_100_energy'];
				}else{
					return json_encode(['message' => 'Недостаточно средств для совершения операции.']);
				}
				break;

			case 'gold_to_200_energy':
				$user_energy = $user_data[0]['user_energy'] + 200;

				$user_silver = $user_data[0]['user_silver'];

				if($user_data[0]['user_gold'] >= $exchange_array['gold_to_200_energy']){
					$user_gold = $user_data[0]['user_gold'] - $exchange_array['gold_to_200_energy'];
				}else{
					return json_encode(['message' => 'Недостаточно средств для совершения операции.']);
				}
				break;

			case 'silver_to_200_energy':
				$user_energy = $user_data[0]['user_energy'] + 200;

				$user_gold = $user_data[0]['user_gold'];

				if($user_data[0]['user_silver'] >= $exchange_array['silver_to_200_energy']){
					$user_silver = $user_data[0]['user_silver'] - $exchange_array['silver_to_200_energy'];
				}else{
					return json_encode(['message' => 'Недостаточно средств для совершения операции.']);
				}
				break;

			default:
				return json_encode(['message' => 'Неизвестный тип операции.']);
		}

		$result = UserAdditionalDataModel::where('user_id', '=', $user['id'])->update(['user_gold' => $user_gold, 'user_silver' => $user_silver, 'user_energy' => $user_energy]);
		if($result !== false){
			return json_encode(['message' => 'success', 'gold' => $user_gold, 'silver' => $user_silver, 'energy' => $user_energy]);
		}else{
			return json_encode(['message' => 'Произошел сбой.']);
		}
	}

	protected function validateUserDeck(Request $request){
        self::updateConnention();

        $user = Auth::user();
        $user_data = UserAdditionalDataModel::where('user_id','=',$user['id'])->get();

        $current_deck = unserialize($user_data[0]->user_cards_in_deck);

        $deck_options = EtcDataModel::where('label_data', '=', 'deck_options')->get();
        $deck_rules = [];
        foreach($deck_options as $key => $value){
            $deck_rules[$value->meta_key] = $value->meta_value;
        }

        $leagues = LeagueModel::orderBy('min_lvl','asc')->get();

        if(!empty($current_deck[$request->input('race')])){

            $error = '';

            $leader_card_quantity = 0;
            $warrior_card_quantity = 0;
            $special_card_quantity = 0;
            foreach($current_deck[$request->input('race')] as $key => $value){

                $card = CardsModel::where('id', '=', $key)->get();
                //Проверяем максимальное колличество карт каждого типа
                if($value > $card[0]->max_quant_in_deck){
                    $error .= '<p>В колоде находится слишком много карт "'.$card[0]->title.'" (Максимальное колличество - '.$card[0]->max_quant_in_deck.').</p>';
                }
                //Количество карт-лидеров
                if(0 != $card[0]->is_leader){
                    $leader_card_quantity += $value;
                }

                //Количество спец. карт
                if($card[0]->card_type == 'special'){
                    $special_card_quantity += $value;
                }else{
                    //Количество карт-воинов
                    $warrior_card_quantity += $value;
                }
            }

            if( ($warrior_card_quantity + $special_card_quantity) > $deck_rules['maxCardQuantity']){
                $error .= '<p>Количество карт в колоде должно быть не больше '.$deck_rules['maxCardQuantity'].' штук</p>';
            }

            if($warrior_card_quantity < $deck_rules['minWarriorQuantity']) {
                $error .= '<p>Количество карт воинов в  колоде должно быть не меньше '.$deck_rules['minWarriorQuantity'].' штук</p>';
            }

            if($special_card_quantity > $deck_rules['specialQuantity']){
                $error .= '<p>Количество спец. карт в колоде должно быть не больше '.$deck_rules['specialQuantity'].' штук</p>';
            }

            if($leader_card_quantity > $deck_rules['leaderQuantity']){
                $error .= '<p>Количество карт лидеров в колоде должно быть не больше '.$deck_rules['leaderQuantity'].' штук</p>';
            }

            //Если есть ошибки валидации
            if($error != ''){
                return json_encode(['message' => $error]);
            }else{
                return json_encode(['message' => 'success']);
            }

        }else{
            return json_encode(['message' => 'Пустая колда']);
        }
    }


    /*
     * Взято с https://habrahabr.ru/post/61309/
     * $input string
     * $descript == false закодировать
     * $descript == true разкодировать
     * */
    public static function dsCrypt($input,$decrypt=false){
        $o = $s1 = $s2 = array(); // Arrays for: Output, Square1, Square2
        // формируем базовый массив с набором символов
        $basea = array('?', '(', '@', ';', '$', '#', "]", "&", '*'); // base symbol set
        $basea = array_merge($basea, range('a', 'z'), range('A', 'Z'), range(0, 9));
        $basea = array_merge($basea, array('!', ')', '_', '+', '|', '%', '/', '[', '.', ' '));
        $dimension = 9; // of squares
        for ($i = 0; $i < $dimension; $i++) { // create Squares
            for ($j = 0; $j < $dimension; $j++) {
                $s1[$i][$j] = $basea[$i * $dimension + $j];
                $s2[$i][$j] = str_rot13($basea[($dimension * $dimension - 1) - ($i * $dimension + $j)]);
            }
        }
        unset($basea);
        $m = floor(strlen($input) / 2) * 2; // !strlen%2
        $symbl = $m == strlen($input) ? '' : $input[strlen($input) - 1]; // last symbol (unpaired)
        $al = array();
        // crypt/uncrypt pairs of symbols
        for ($ii = 0; $ii < $m; $ii += 2) {
            $symb1 = $symbn1 = strval($input[$ii]);
            $symb2 = $symbn2 = strval($input[$ii + 1]);
            $a1 = $a2 = array();
            for ($i = 0; $i < $dimension; $i++) { // search symbols in Squares
                for ($j = 0; $j < $dimension; $j++) {
                    if ($decrypt) {
                        if ($symb1 === strval($s2[$i][$j])) $a1 = array($i, $j);
                        if ($symb2 === strval($s1[$i][$j])) $a2 = array($i, $j);
                        if (!empty($symbl) && $symbl === strval($s2[$i][$j])) $al = array($i, $j);
                    } else {
                        if ($symb1 === strval($s1[$i][$j])) $a1 = array($i, $j);
                        if ($symb2 === strval($s2[$i][$j])) $a2 = array($i, $j);
                        if (!empty($symbl) && $symbl === strval($s1[$i][$j])) $al = array($i, $j);
                    }
                }
            }
            if (sizeof($a1) && sizeof($a2)) {
                $symbn1 = $decrypt ? $s1[$a1[0]][$a2[1]] : $s2[$a1[0]][$a2[1]];
                $symbn2 = $decrypt ? $s2[$a2[0]][$a1[1]] : $s1[$a2[0]][$a1[1]];
            }
            $o[] = $symbn1 . $symbn2;
        }
        if (!empty($symbl) && sizeof($al)) // last symbol
            $o[] = $decrypt ? $s1[$al[1]][$al[0]] : $s2[$al[1]][$al[0]];
        return implode('', $o);
    }


	//Задача для cron
	protected function cronTask(){
        //Если пользователь не был активен 3 минуты - записываем в БД то, что он не активен
		$users = \DB::table('users')->select('id','user_online','updated_at')->where('user_online', '=', '1')->get();
		foreach($users as $user){
			$time_diff = time() - strtotime($user->updated_at);
			if($time_diff > 180){
				User::where('id', '=', $user->id)->update(['user_online' => 0]);
			}
		}

		//Если стол не был активен в течении получаса
        $battles = \DB::table('tbl_battles')->select('id','updated_at')->get();
        foreach ($battles as $key => $value) {
            if(strtotime($value -> updated_at) < (time()-60*60*24*90) ){
                $battle = BattleModel::find($value->id);
                $battle -> delete();
            }
        }
	}
}