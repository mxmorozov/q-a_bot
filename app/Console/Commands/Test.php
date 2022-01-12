<?php

namespace App\Console\Commands;

use App\Bot\NonCommandHandler;
use Illuminate\Console\Command;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return int
     */
    public function handle()
    {
//        $question = new Question();
//        $question->user_id = 110414203;
//        $question->save();
//        $question->refresh();
//
//        if ($question->status === QuestionStatus::DRAFT) {
//            echo 'ok';
//        } else {
//            echo 'fail';
//        }


//        $response = Telegram::getMe();
//
//        $botId = $response->getId();
//        echo $botId, PHP_EOL;
//        $firstName = $response->getFirstName();
//        echo $firstName, PHP_EOL;
//        $username = $response->getUsername();
//        echo $username, PHP_EOL;

//        $response = Telegram::getUpdates();
////        print_r($response);
//        /** @var Update $lastCommand */
//        $lastCommand = $response[array_key_last($response)];
//        $lastCommand->
        $updates = Telegram::commandsHandler(false, ['timeout' => 30]);
        print_r($updates);
        /** @var Update $update */
        foreach ($updates as $update) {

            NonCommandHandler::handle($update);
        }

//        print_r($updates);

        return 0;
    }
}
