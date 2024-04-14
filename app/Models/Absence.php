<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    use HasFactory;

    protected $table = "absences";

    protected $fillable = [
        "code",
        "label",
        "base_calcul",
    ];
}
