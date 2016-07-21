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
        $this->clients->detach($conn);
        //delete connections from battle
        if(isset($this->resoursesInBattle[$conn->resourceId]) && isset($this->battles[$this->resoursesInBattle[$conn->resourceId]])) {
            unset($this->resoursesInBattle[$conn->resourceId]);
            $this->battles[$this->resoursesInBattle[$conn->resourceId]]->detach($conn);
        }

        echo "close connection ({$conn->resourceId}) \n";
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

        $from->send(json_encode($user));
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