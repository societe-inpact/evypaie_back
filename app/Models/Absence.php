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
        "therapeutic_part-time"
    ];

    public function mappings()
    {
        return $this->morphMany(Mapping::class, 'output');
    }
}
