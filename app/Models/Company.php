<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = "companies";
    protected $fillable = [
        "name",
        "description",
        "referent_id"
    ];


    public function referent(){
        return $this->belongsTo(Employee::class, 'referent_id');
    }


    public function company_folder()
    {
        return $this->hasMany(CompanyFolder::class, 'company_id', 'id');
    }


    public function folders()
    {
        return $this->hasMany(CompanyFolder::class, 'company_id' ,);
    }

    public function employees(){
        return $this->hasManyThrough(Employee::class, EmployeeFolder::class, 'id', 'employee_id');
    }
}
