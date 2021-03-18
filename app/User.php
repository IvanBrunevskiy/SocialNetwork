<?php

namespace App;

use App\Models\Status;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'username',
        'password',
        'first_name',
        'last_name',
        'location',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    /**
     * @var mixed
     */

    # Получить имя и фамилию или только имя
    public function getName()
    {
        if ($this->first_name && $this->last_name) {
            return "{$this->first_name} {$this->last_name}";
        }
        if ($this->first_name) {
            return $this->first_name;
        }
        return null;
    }

    # Получить имя и фамилию или логин
    public function getNameOrUsername()
    {
        return $this->getName() ?: $this->username;
    }

    # Получить имя или логин
    public function getFirstNameOrUsername()
    {
        return $this->first_name ?: $this->username;
    }

    # Получить аватарку из Gravatar
    public function getAvatarUrl()
    {
        return "https://www.gravatar.com/avatar/{{ md5($this->email)?d=mp&s=40 }}";
    }

    # Пользователю принадлежит статус
    public function statuses()
    {
        return $this->hasMany('App\Models\Status', 'user_id');
    }

    # Получить лайки пользователя
    public function likes()
    {
        return $this->hasMany('App\Models\Like', 'user_id');
    }

    # Устанавливаем отношение многие ко многим, мои друзья
    public function friendsOfMine()
    {
        return $this->belongsToMany('App\User', 'friends', 'user_id', 'friend_id');
    }

    # Устанавливаем отношение многие ко многим, друг
    public function friendOf()
    {
        return $this->belongsToMany('App\User', 'friends', 'friend_id', 'user_id');
    }

    # Получить друзей
    public function friends()
    {
        return $this->friendsOfMine()->wherePivot('accepted', true)->get()
            ->merge($this->friendOf()->wherePivot('accepted', true)->get());
    }

    # Запросы в друзья
    public function friendRequests()
    {
        return $this->friendsOfMine()->wherePivot('accepted', false)->get();
    }

    # Запрос на ожидание друга
    public function friendRequestsPending()
    {
        return $this->friendOf()->wherePivot('accepted', false)->get();
    }

    # Есть запрос на добавление в друзья
    public function hasFriendRequestPending(User $user)
    {
        return (bool)$this->friendRequestsPending()->where('id', $user->id)->count();
    }

    # Получил запрос о дружбе
    public function hasFriendRequestReceived(User $user)
    {
        return (bool)$this->friendRequests()->where('id', $user->id)->count();
    }

    # Добавить друга
    public function addFriend(User $user)
    {
        $this->friendOf()->attach($user->id);
    }

    # Удалить из друзей
    public function deleteFriend(User $user)
    {
        $this->friendOf()->detach($user->id);
        $this->friendsOfMine()->detach($user->id);
    }

    # Принять запрос на дружбу
    public function acceptFriendRequest(User $user)
    {
        $this->friendRequests()->where('id', $user->id)->first()->pivot->update([
            'accepted' => true
        ]);
    }

    # Пользователь в друзьях
    public function isFriendWith(User $user)
    {
        return (bool)$this->friends()->where('id', $user->id)->count();
    }

    public function hasLikedStatus(Status $status)
    {
        return (bool) $status->likes
            ->where('likeable_id', $status->id)
            ->where('likeable_type', get_class($status))
            ->where('user_id', $this->id)
            ->count();
    }

    public function getAvatarsPath($user_id)
    {
       $path = "uploads/avatars/id{$user_id}";

       if (! file_exists("$path")){
           mkdir("$path", 0777, true);
       }
       return "/$path/";
    }

    public function clearAvatars($user_id)
    {
        $path = 'uploads/avatars/id{$user_id}';

        if (file_exists(public_path("/$path"))){
            foreach (glob(public_path("/$path/*")) as $avatar)
                unlink($avatar);
        }
    }

}
