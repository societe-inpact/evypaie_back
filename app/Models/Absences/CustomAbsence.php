<?php

namespace App\Models\Absences;

use App\Models\Mapping\Mapping;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomAbsence extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'custom_absences';
    protected $fillable = ['code', 'label', 'company_folder_id', 'base_calcul', 'therapeutic_part_time'];

    public function mappings()
    {
        return $this->morphMany(Mapping::class, 'output');
    }
}
