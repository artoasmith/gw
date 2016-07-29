<?php
namespace App\Classes\Socket;

use App\User;
use App\BattleModel;
use App\BattleMembersModel;
use App\Classes\Socket\Base\BaseSocket;
use Ratchet\ConnectionInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Session\SessionManager;

class BattleSocket extends BaseSocket
{
    protected $clients;
    protected $battles;
    protected $resoursesInBattle;
    protected $resoursesInUsers;
    protected $userBattle;
    private $actionSpecter = [
        0=>'join',  //подключился и прошел проверку новый пользователь
        1=>'close',  //пользователь покинул стол
        2=>'checkBattle', //проверка статуса битвы
    ];

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }


    function onOpen(ConnectionInterface $conn)
    {
        //save client
        $this->clients->attach($conn);

        echo "new connection ({$conn->resourceId}) \n";
    }

    function onClose(ConnectionInterface $conn)
    {

        echo "close connection ({$conn->resourceId}) \n";

        $this->clients->detach($conn);
        //delete connections from battle
        if(isset($this->resoursesInBattle[$conn->resourceId]) && isset($this->battles[$this->resoursesInBattle[$conn->resourceId]])) {
            $this->battles[$this->resoursesInBattle[$conn->resourceId]]->detach($conn);

            $userInfo = false;

            if(isset($this->resoursesInUsers[$conn->resourceId])) {
                $userInfo = $this->resoursesInUsers[$conn->resourceId];
                if(isset($this->userBattle[$userInfo->id.'_'.$this->resoursesInBattle[$conn->resourceId]]))
                    unset($this->userBattle[$userInfo->id.'_'.$this->resoursesInBattle[$conn->resourceId]]);
                unset($this->resoursesInUsers[$conn->resourceId]);
            }
            
            echo count($this->battles[$this->resoursesInBattle[$conn->resourceId]]).' in battle '.$this->resoursesInBattle[$conn->resourceId]."\n";

            if($userInfo && count($this->battles[$this->resoursesInBattle[$conn->resourceId]])>0){
                foreach ($this->battles[$this->resoursesInBattle[$conn->resourceId]] as $client){
                    $client->send(
                        json_encode([
                            'action'=>'close',
                            'user_id'=>$userInfo->id
                        ])
                    );
                }
            }

            unset($this->resoursesInBattle[$conn->resourceId]);
        }
    }

    function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "ERROR: ({$e->getMessage()}) \n";
        $conn->send($this->getErrorJson($e->getMessage()));
        $conn->close();
    }

    function onMessage(ConnectionInterface $from, $msg)
    {
        //get params
        $incom = json_decode($msg,true);

        //check action
        if(!isset($incom['action']) || !in_array($incom['action'],$this->actionSpecter))
            return $from->send($this->getErrorJson('Ошибка передачи действия'));
        $action = $incom['action'];

        //chech auth
        $user = $this->getUser($incom);
        if(!$user)
            return $from->send($this->getErrorJson('Ошибка авторизации'));

        $battleId = intval($incom['ident']['battleId']);

        //update params
        if(!isset($this->battles[$battleId]))
            $this->battles[$battleId] = new \SplObjectStorage;
        if(!$this->battles[$battleId]->contains($from))
            $this->battles[$battleId]->attach($from);
        $this->resoursesInBattle[$from->resourceId] = $battleId;
        $this->resoursesInUsers[$from->resourceId] = $user;
        $this->userBattle[$user->id.'_'.$battleId] = true;
        $userResp  = [];
        $othersResp = [];

        $sendToBattleMates = true;
        ///main logic
        switch ($action){
            case 'join':
                $othersResp = ['action'=>$action,'user_id'=>$user->id];
                $userResp = ['action'=>'welcome','user_id'=>$user->id];
                break;
            case 'checkBattle':
                $sendToBattleMates = false;
                $userResp = ['action'=>'checkBattle','user_id'=>$user->id];
                break;
        }
        //--main logic end

        //battle info
        $othersResp['battleInfo'] = $userResp['battleInfo'] = $this->getBattleInfo($battleId);

        //response
        //to user
        $from->send(json_encode($userResp));
        //to battle members
        $resp = 'some event';
        if($sendToBattleMates) {
            foreach ($this->battles[$battleId] as $client) {
                if ($client->resourceId != $from->resourceId) {
                    $client->send(json_encode($othersResp));
                }
            }
        }
    }

    //для одинаковой типизации ошибок
    private function getErrorJson($r){
        return json_encode(['ERROR'=>$r]);
    }

    private function getUser($params){
        //params check
        if(!isset($params['ident'])
            ||
            !isset($params['ident']['battleId'])
            ||
            !isset($params['ident']['userId'])
            ||
            !isset($params['ident']['hash'])
        )
            return false;

        //hash check
        if($params['ident']['hash'] != md5(getenv('SECRET_MD5_KEY').$params['ident']['userId']))
            return false;

        //get user
        $user = User::find($params['ident']['userId']);
        return ($user?$user:false);
    }


    private function getBattleInfo($id){
        /**
         * @var BattleModel $battle
         */
        $battle = BattleModel::find($id);
        if(!$battle)
            return false;

        $members = BattleMembersModel::where('battle_id','=',$id)->get();

        $time = 0;
        if(true || $battle->fight_status == 2){
            if($members){
                $sec = intval(getenv('GAME_SEC_TIMEOUT'));
                if($sec<=0)
                    $sec = 60;

                foreach ($members as $member){
                    if($member->id == $battle->user_id_turn){
                        $user = User::find($member->user_id);
                        if($user){
                            $time = $sec+$user->updated_at->getTimestamp()-time();
                            if($time<=0){
                                $time=0;
                            }
                        }
                        break;
                    }
                }
            }
        }
        return [
            'id'=>$id,
            'fightStatus'=>$battle->fight_status,
            'endTime'=>$time,
            'members'=>$this->getMembersInfo($members,$id)
        ];
    }

    private function getMembersInfo(&$members,$id){
        /**
         * @var BattleMembersModel $member
         */
        $resp = [];
        if(!empty($members)){
            foreach ($members as $member){
                $resp[] = [
                    'user_id'=>$member->user_id,
                    'online'=>(isset($this->userBattle[$member->user_id.'_'.$id]))
                ];
            }
        }
        return $resp;
    }

}