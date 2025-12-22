<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'nama',
        'email',
        'password',
        'role',
        'id_agent',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
    

    // jika di DB kamu pakai kolom 'name' bukan 'nama', ganti fillable & getter
}
