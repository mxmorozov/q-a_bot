<?php
/**
 * Created by PhpStorm.
 * User: Maksim Morozov <maxpower656@gmail.com>
 * Date: 13.01.2022
 * Time: 02:04
 */

namespace App\Bot;

use App\Models\Answer;
use App\Models\Enums\AnswerStatus;
use App\Models\Enums\QuestionStatus;
use App\Models\Enums\VoteValue;
use App\Models\Question;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Support\Facades\DB;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

class NonCommandHandler
{
    public static function handle(Update $update)
    {
//        Log::debug(print_r($update->message->hasCommand(), true));
//        if (empty($update->message) || $update->message->hasCommand()) {
//            return;
//        }
//        Log::debug(3);

        if ($update?->message && !$update->message->hasCommand()) {
            if (($question = Question::where(['user_id' => $update->message->from->id, 'status' => QuestionStatus::DRAFT])->first())) {
                $question->text = $update->message->text;
                $question->status = QuestionStatus::PUBLISHED;
                $question->save();
                Telegram::sendMessage([
                    'chat_id' => $update->message->chat->id,
                    'text' => "Question posted! You will be notified of responses\nCredit left: {$question->user->credit}",
                ]);
            } elseif ($answer = Answer::where(['user_id' => $update->message->from->id, 'status' => AnswerStatus::DRAFT])->first()) {
                $answer->text = $update->message->text;
                $answer->status = AnswerStatus::PUBLISHED;
                $answer->save();
                Telegram::sendMessage([
                    'chat_id' => $update->message->chat->id,
                    'text' => 'Answer posted! Thank you',
                ]);

                Telegram::sendMessage([
                    'chat_id' => $answer->question->user_id,
                    'text' => "You got new answer to the question:\n{$answer->question->text}\n\n{$answer->text}\n{$answer->user->getName()}",
                    'reply_markup' => self::answerReactionKeyboard($answer)
                ]);
            }

        } elseif ($command = $update->callbackQuery?->data) {
            if (preg_match('/\/answer (\d+)/', $command, $matches)) {
                $questionId = $matches[1];
                $user = User::findOrCreateFromTelegram($update);

                $question = Question::where(['id' => $questionId, 'status' => QuestionStatus::PUBLISHED])->first();

                if ($question === null) {
                    Telegram::sendMessage(['chat_id' => $user->id,
                        'text' => "Sorry. Question not found"]);
                    return;
                }

                if (Answer::where(['user_id' => $user->id, 'question_id' => $question->id, 'status' => AnswerStatus::DRAFT])->count() === 0) {
                    $answer = new Answer();
                    $answer->user_id = $user->id;
                    $answer->question_id = $question->id;
                    $answer->save();
                }
                Telegram::sendMessage([
                    'chat_id' => $user->id,
                    'text' => "Enter your answer…"
                ]);
            } elseif (preg_match('/\/question (\d+)/', $command, $matches)) {
                self::question($update, $matches[1]);
            } elseif (preg_match('/\/like (\d+)/', $command, $matches)) {
                self::react($update, $matches[1], VoteValue::THUMBS_UP);
            } elseif (preg_match('/\/dislike (\d+)/', $command, $matches)) {
                self::react($update, $matches[1], VoteValue::THUMBS_DOWN);
            }

        }

    }

    private static function question(Update $update, int $id)
    {
        $user = User::findOrCreateFromTelegram($update);
        $question = Question::where(['id' => $id, 'status' => QuestionStatus::PUBLISHED])->first();

        $inlineLayout = [[
            Keyboard::inlineButton(['text' => 'Reply', 'callback_data' => "/answer {$question->id}"]),
        ]];

        $reply_markup = Keyboard::make([
            'inline_keyboard' => $inlineLayout,
        ]);

        Telegram::sendMessage([
            'chat_id' => $user->id,
            'text' => $question->text,
            'reply_markup' => $reply_markup,
        ]);

        Telegram::sendMessage([
            'chat_id' => $user->id,
            'text' => "\nAnswers",
        ]);

        foreach (Answer::byQuestion($question)->get() as $answer) {
            Telegram::sendMessage([
                'chat_id' => $user->id,
                'text' => "{$answer->user->getName()}\n{$answer->text}",
                'reply_markup' => self::answerReactionKeyboard($answer)
            ]);
        }
    }

    private static function react(Update $update, int $answerId, VoteValue $value)
    {
        $user = User::findOrCreateFromTelegram($update);

        $answer = Answer::where(['id' => $answerId, 'status' => AnswerStatus::PUBLISHED])->first();

        if ($answer === null) {
            Telegram::sendMessage([
                'chat_id' => $user->id,
                'text' => "Sorry. Answer not found"
            ]);
            return;
        }

        if (Vote::where(['user_id' => $user->id, 'answer_id' => $answer->id])->count() === 0) {
            DB::beginTransaction();
            $vote = new Vote();
            $vote->user_id = $user->id;
            $vote->answer_id = $answer->id;
            $vote->value = $value;
            $vote->save();
            if ($value === VoteValue::THUMBS_UP) {
                $answer->user->credit++;
                $answer->user->save();
            }
            DB::commit();

            Telegram::editMessageReplyMarkup([
                'chat_id' => $update->callbackQuery->message->chat->id,
                'message_id' => $update->callbackQuery->message->messageId,
                'reply_markup' => self::answerReactionKeyboard($answer),
            ]);

            if ($value === VoteValue::THUMBS_UP) {
                Telegram::sendMessage([
                    'chat_id' => $answer->user_id,
                    'text' => "{$user->getName()} liked your answer! You got one credit.\nCredit: {$answer->user->credit}"
                ]);
            }

        } else {
            Telegram::sendMessage([
                'chat_id' => $user->id,
                'text' => "You already voted for this answer"
            ]);
        }
    }

    private static function answerReactionKeyboard(Answer $answer): Keyboard
    {
        $inlineKeyboard = [[
            Keyboard::inlineButton(['text' => '👍' . $answer->getPositiveVotesCount(), 'callback_data' => "/like {$answer->id}"]),
            Keyboard::inlineButton(['text' => '👎' . $answer->getNegativeVotesCount(), 'callback_data' => "/dislike {$answer->id}"]),
        ]];

        return Keyboard::make([
            'inline_keyboard' => $inlineKeyboard,
        ]);
    }
}
