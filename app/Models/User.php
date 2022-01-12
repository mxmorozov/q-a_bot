<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Telegram\Bot\Objects\Update;


/**
 * Class User
 *
 * @property int $id
 * @property string $username
 * @property string $first_name
 * @property string $last_name
 * @property string $language_code
 * @property int $credit
 * @property string $email
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
//    protected $fillable = [
//        'name',
//        'email',
//        'password',
//    ];
    const INITIAL_CREDIT = 3;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getName(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public static function findOrCreateFromTelegram(Update $update): self
    {
        $from = $update->message?->from ?? $update->editedMessage->from ?? $update->callbackQuery?->from;

        $user = self::where(['id' => $from->id])->first();

        if (!$user) {
            $user = new self();
            $user->id = $from->id;
            $user->username = $from->username;
            $user->first_name = $from->firstName;
            $user->last_name = $from->lastName;
            $user->language_code = $from->languageCode;
            $user->credit = self::INITIAL_CREDIT;
            $user->save();
        }

        return $user;

    }
}
