<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    use HasFactory;

    protected $table = 'deposits';

    protected $fillable = [
        'id_admin',
        'id_nasabah',
        'id_agent',
        'no_bilyet',
        'nominal',
        'tanggal_transaksi',
        'tenor',
        'tanggal_mulai',
        'tanggal_tempo',
        'catatan_admin',
        'bukti_transfer',       // nanti berisi path file
        'dokumen_pendukung',    // nanti berisi path file
        'BDP_ref',              // flag checkbox
        'status',
        'catatan',
        'tanggal_approval',
    ];

    protected $casts = [
        'nominal'           => 'decimal:2',
        'BDP_ref'           => 'boolean',   // akan dikembalikan sebagai true/false
        'tanggal_transaksi' => 'date',
        'tanggal_mulai'     => 'date',
        'tanggal_tempo'     => 'date',
        'tanggal_approval'  => 'date',
    ];
    

    // Relasi
    public function admin()
    {
        return $this->belongsTo(User::class, 'id_admin');
    }

    public function nasabah()
    {
        return $this->belongsTo(Nasabah::class, 'id_nasabah');
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class, 'id_agent');
    }

    public function komisi()
    {
        return $this->hasMany(Komisi::class, 'id_deposit');
    }

}
