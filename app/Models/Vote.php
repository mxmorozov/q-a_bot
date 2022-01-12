<?php
/**
 * Created by PhpStorm.
 * User: Maksim Morozov <maxpower656@gmail.com>
 * Date: 12.01.2022
 * Time: 01:55
 *
 */


namespace App\Models;

use App\Models\Enums\VoteValue as Value;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Vote
 *
 * @property int $id
 * @property int $user_id
 * @property int $answer_id
 * @property Value $value
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property User $user
 * @property Answer $answer
 */
class Vote extends Model
{
	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function answer()
	{
		return $this->belongsTo(Answer::class);
	}

    public function getValueAttribute(int $value): Value
    {
        return Value::from($value);
    }

    public function setValueAttribute(Value $value)
    {
        $this->attributes['value'] = $value->value;
    }
}
