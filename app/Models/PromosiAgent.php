<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromosiAgent extends Model
{
    protected $table = 'promosi_agent';

    protected $fillable = [
        'id_admin',
        'id_agent',
        'jabatan_lama',
        'jabatan_baru',
        'atasan_bdp_id',
        'tanggal_apply',
        'status',
        'catatan_admin',
        'alasan_reject',
        'tanggal_approval',
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class, 'id_agent');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'id_admin');
    }

    public function atasanBdp()
    {
        return $this->belongsTo(Agent::class, 'atasan_bdp_id');
    }
}

