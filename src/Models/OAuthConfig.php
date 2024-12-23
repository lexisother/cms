<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OAuthConfig extends Model
{
    protected $table = 'oauth_config';

    protected $fillable = [
        'client_private_key',
        'client_public_key',
        'key_id'
    ];
}