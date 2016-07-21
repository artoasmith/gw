<?php
namespace App\Classes\Socket;

use App\User;
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
    private $actionSpecter = [
        0=>'join',  //подключился и прошел проверку новый пользователь
        1=>'close'  //пользователь покинул стол
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

        $userResp  = [];
        $othersResp = [];
        
        ///main logic
        switch ($action){
            case 'join':
                $othersResp = ['action'=>$action,'user_id'=>$user->id];
                $userResp = ['action'=>'welcome','user_id'=>$user->id];
                break;
        }
        //--main logic end

        //response
        //to user
        $from->send(json_encode($userResp));
        //to battle members
        $resp = 'some event';
        foreach ($this->battles[$battleId] as $client){
            if($client->resourceId != $from->resourceId){
                $client->send(json_encode($othersResp));
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

    //battle members activiti check

}