<?php

namespace App\Console\Commands;

use App\Classes\Socket\BattleSocket;
use Illuminate\Console\Command;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

class BattleServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'battleServer:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start game battle server';

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
        $this->info('Start');
        
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new BattleSocket()
                )
            ),
            8080
        );
        $server->run();
    }
}
