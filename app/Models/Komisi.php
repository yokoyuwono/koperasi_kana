<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Komisi extends Model
{
    use HasFactory;

    protected $table = 'komisi';

    protected $fillable = [
        'id_deposit',
        'id_agent',
        'tanggal_periode',
        'nominal',
        'persen_komisi',
        'status',
        'tanggal_pembayaran',
    ];


    protected $casts = [
        'nominal'           => 'decimal:2',
        'persen_komisi'     => 'decimal:2',
        'tanggal_periode'   => 'date',
        'tanggal_pembayaran'=> 'date',
    ];

    public function deposit()
    {
        return $this->belongsTo(Deposit::class, 'id_deposit');
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class, 'id_agent');
    }
}
