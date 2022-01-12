<?php
/**
 * Created by PhpStorm.
 * User: Maksim Morozov <maxpower656@gmail.com>
 * Date: 11.01.2022
 * Time: 18:29
 *
 *
 */

namespace App\Bot\Commands;

use App\Models\Answer;
use App\Models\Enums\AnswerStatus;
use App\Models\Enums\QuestionStatus;
use App\Models\Question;
use App\Models\User;
use Telegram\Bot\Commands\Command;

class AnswerCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = "answer";

    /**
     * @var string Command Description
     */
    protected $description = "Answer Command to answer question. Must be used in conjunction with the answer id";

    protected $pattern = '{id}';

    /**
     * @inheritdoc
     */
    public function handle()
    {
        $user = User::findOrCreateFromTelegram($this->update);

        $question = Question::where(['id' => $this->arguments['id'] ?? null, 'status' => QuestionStatus::PUBLISHED])->first();

        if ($question === null) {
            $this->replyWithMessage(['text' => 'Sorry. Question not found']);
            return;
        }

        if (Answer::where(['user_id' => $user->id, 'question_id' => $question->id, 'status' => AnswerStatus::DRAFT])->count() === 0) {
            $answer = new Answer();
            $answer->user_id = $user->id;
            $answer->question_id = $question->id;
            $answer->save();
        }
        $this->replyWithMessage(['text' => "Enter your answer…"]);

    }
}
