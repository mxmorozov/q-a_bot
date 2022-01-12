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

use App\Models\Enums\AnswerStatus;
use App\Models\Enums\QuestionStatus;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;

class ListCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = "list";

    /**
     * @var string Command Description
     */
    protected $description = "List Command to list questions";

    /**
     * @inheritdoc
     */
    public function handle()
    {
        $user = User::findOrCreateFromTelegram($this->update);

        /** @var Collection $questions */
        $questions = Question::where(['status' => QuestionStatus::PUBLISHED])->get();

        foreach ($questions as $question) {
            $text = sprintf("%s\n%s", $question->text, $question->user->getName());

            $inlineLayout = [
                [
                    Keyboard::inlineButton(['text' => 'Reply', 'callback_data' => "/answer {$question->id}"]),
                    Keyboard::inlineButton(['text' => '💬' . $question->answers()->where(['status' => AnswerStatus::PUBLISHED])->count(), 'callback_data' => "/question {$question->id}"]),
                ]
            ];

            $reply_markup = Keyboard::make([
                'inline_keyboard' => $inlineLayout,
            ]);
            $this->replyWithMessage([
                'text' => $text,
                'reply_markup' => $reply_markup,
            ]);
        }

        if ($questions->count() === 0) {
            $this->replyWithMessage([
                'text' => 'No questions yet',
            ]);
        }

    }
}
