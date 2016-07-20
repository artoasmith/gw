<?php
namespace App\Classes\Socket;

use App\Classes\Socket\Base\BaseSocket;
use Ratchet\ConnectionInterface;

class BattleSocket extends BaseSocket
{
    protected $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }

    function onOpen(ConnectionInterface $conn)
    {
        //save client
        $this->clients->attach($conn);

        echo "new one ({$conn->resourceId}) \n";
    }

    function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);

        echo "close one ({$conn->resourceId}) \n";
    }

    function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "ERROR: ({$e->getMessage()}) \n";
        $conn->close();
    }

    function onMessage(ConnectionInterface $from, $msg)
    {
        $num = count($this->clients)-1;

        echo sprintf("User %s send message \"%s\" to %d users \n",$from->resourceId, $msg, $num);
        /**
         * @var ConnectionInterface $client
         */
        foreach ($this->clients as $client){
            if($client != $from){
                $client->send($msg);
            }
        }
    }


}