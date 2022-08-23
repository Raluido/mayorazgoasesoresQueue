<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostsImput extends Model
{
    use HasFactory;

    protected $table = 'costs_imputs';

    protected $fillable = [
        'nif',
        'filename',
        'month',
        'year',
    ];
}