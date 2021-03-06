<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TPayments extends Model
{
    use HasFactory;
    
    protected $table = 't_payments';

    protected $fillable = [
        'customer_id',
        'package_id',
        'bulan',
        'tahun'
    ];
}
