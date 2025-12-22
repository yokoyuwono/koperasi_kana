<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    use HasFactory;

    protected $table = 'agents';

    protected $fillable = [
        'id_admin',
        'kode_agent',
        'nama',
        'jabatan',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'NIK',
        'alamat_KTP',
        'telepon',
        'alamat_sekarang',
        'rekening_bank',
        'email',
        'tanggal_daftar',
        'refferred_by_agent_id',
        'atasan_id',      
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'id_admin');
    }

    // Agent yang mereferensikan (Agent Referal)
    public function referral()
    {
        return $this->belongsTo(Agent::class, 'refferred_by_agent_id');
    }
    // Atasan BDP untuk RM
    public function atasan()
    {
        return $this->belongsTo(Agent::class, 'atasan_id');
    }
    public function bawahanRm()
    {
        // semua RM di bawah BDP
        return $this->hasMany(Agent::class, 'atasan_id');
    }

    public function refferredBy()
    {
        // BDP yang mereferensikan BDP ini (dipakai untuk komisi referral 0.5%)
        return $this->belongsTo(Agent::class, 'refferred_by_agent_id');
    }

    // Daftar RM di bawah satu BDP
    public function bawahan()
    {
        return $this->hasMany(Agent::class, 'atasan_id');
    }
    public function komisi()
    {
        return $this->hasMany(Komisi::class, 'id_agent');
    }
    
}
