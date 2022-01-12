<?php
/**
 * Created by PhpStorm.
 * User: Maksim Morozov <maxpower656@gmail.com>
 * Date: 11.01.2022
 * Time: 23:22
 *
 */


namespace App\Models;

use App\Models\Enums\QuestionStatus as Status;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Question
 *
 * @property int $id
 * @property int $user_id
 * @property string $text
 * @property Status $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property User $user
 */
class Question extends Model
{
	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function answers()
	{
		return $this->hasMany(Answer::class);
	}

    public function getStatusAttribute(int $value): Status
    {
        return Status::from($value);
    }

    public function setStatusAttribute(Status $value)
    {
        $this->attributes['status'] = $value->value;
    }
}
