<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model implements Authenticatable
{
    use HasFactory;
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = true;
    public $incrementing = true;

    protected $fillable = [
        'username',
        'password',
        'name'
    ];

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class, 'user_id', 'id');
    }
    /**
     * Get the unique identifier for the user.
     */
    public function getAuthIdentifier()
    {
        return $this->username;
    }

    /**
     * Get the name of the unique identifier for the user.
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'username';
    }

    /**
     * Get the password for the user.
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Get the token value for the "remember me" session.
     * @return string
     */
    public function getRememberToken()
    {
        return $this->token;
    }

    /**
     * Get the column name for the "remember me" token.
     * @return string
     */
    public function getRememberTokenName()
    {
        return 'token';
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param string $value
     * @return void
     */
    public function setRememberToken($value)
    {
        $this->token = $value;
    }
}
