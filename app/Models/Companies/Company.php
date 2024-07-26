<?php

namespace App\Models\Companies;

use App\Models\Employees\Employee;
use App\Models\Employees\EmployeeFolder;
use App\Models\Misc\User;
use App\Models\Modules\CompanyModuleAccess;
use App\Models\Modules\Module;
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
        "telephone",
        "referent_id"
    ];

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'company_module_access', 'company_id', 'module_id');
    }

    public function referent(){
        return $this->belongsTo(Employee::class, 'referent_id');
    }

    public function folders()
    {
        return $this->hasMany(CompanyFolder::class, 'company_id', 'id');
    }

    public function employees(){

        return $this->hasManyThrough(User::class, EmployeeFolder::class, 'id', 'user_id');
    }
}
