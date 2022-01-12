<?php
/**
 * Created by PhpStorm.
 * User: Maksim Morozov <maxpower656@gmail.com>
 * Date: 11.01.2022
 * Time: 18:29
 */

namespace App\Bot\Commands;

use App\Models\Enums\QuestionStatus;
use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Telegram\Bot\Commands\Command;

class AskCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = "ask";

    /**
     * @var string Command Description
     */
    protected $description = "Ask Command to ask a question";

    /**
     * @inheritdoc
     */
    public function handle()
    {
        $user = User::findOrCreateFromTelegram($this->update);

        if (empty($user->credit)) {
            $this->replyWithMessage(['text' => 'Sorry, your credit is over']);
        } else {
            if (Question::where(['user_id' => $user->id, 'status' => QuestionStatus::DRAFT])->count() === 0) {
                DB::beginTransaction();
                $question = new Question();
                $question->user_id = $user->id;
                $question->save();
                $user->credit--;
                $user->save();
                DB::commit();
            }

            $this->replyWithMessage(['text' => "Enter your question…"]);
        }
    }
}
