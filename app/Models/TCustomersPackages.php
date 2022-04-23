<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TCustomersPackages extends Model
{
    use HasFactory;
    
    protected $table = 't_customers_packages';
    
    protected $fillable = [
        'customer_id',
        'package_id'
    ];
}
