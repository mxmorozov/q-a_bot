<?php
/**
 * Created by PhpStorm.
 * User: Maksim Morozov <maxpower656@gmail.com>
 * Date: 12.01.2022
 * Time: 01:55
 *
 */


namespace App\Models;

use App\Models\Enums\AnswerStatus as Status;
use App\Models\Enums\VoteValue;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Answer
 *
 * @property int $id
 * @property int $user_id
 * @property int $question_id
 * @property string $text
 * @property Status $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property User $user
 * @property Question $question
 * @property Collection $votes
 *
 * @method static Builder byQuestion(Question $question)
 */
class Answer extends Model
{
	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function question()
	{
		return $this->belongsTo(Question::class);
	}

	public function votes()
	{
		return $this->hasMany(Vote::class);
	}

    public function getStatusAttribute(int $value): Status
    {
        return Status::from($value);
    }

    public function setStatusAttribute(Status $value)
    {
        $this->attributes['status'] = $value->value;
    }

    public function getPositiveVotesCount(): int
    {
        return $this->votes()->where(['value' => VoteValue::THUMBS_UP])->count();
    }

    public function getNegativeVotesCount(): int
    {
        return $this->votes()->where(['value' => VoteValue::THUMBS_DOWN])->count();
    }

    public function scopeByQuestion(Builder $query, Question $question): Builder
    {
        return $query
            ->select('answers.*')
            ->leftJoin('votes', 'votes.answer_id', '=', 'answers.id')
            ->where(['question_id' => $question->id])
            ->where(['status' => Status::PUBLISHED])
            ->groupBy('answers.id')
            ->orderByRaw('IFNULL(SUM(votes.value), 0) DESC');
    }

}
