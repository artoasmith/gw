<?php

namespace App\Console\Commands;

use App\Classes\Socket\JotSocket;
use Illuminate\Console\Command;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

class jotServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jotServer:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start game JOT server';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $port = 8080;
        $host = '0.0.0.0'; //localhost
        $check = @stream_socket_server("tcp://$host:$port", $errno, $errstr);
        if($check === false){
            $this->info("tcp://$host:$port".' already in use');
        } else {
            fclose($check);
            $this->info('Start');
            $server = IoServer::factory(
                new HttpServer(
                    new WsServer(
                        new JotSocket()
                    )
                ),
                $port
            );
            $server->run();
        }
    }
}
