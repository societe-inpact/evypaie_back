<?php

namespace App\Models\Companies;

use App\Models\Employees\EmployeeFolder;
use App\Models\Mapping\Mapping;
use App\Models\Misc\InterfaceFolder;
use App\Models\Misc\Software;
use App\Models\Misc\User;
use App\Models\Modules\CompanyModuleAccess;
use App\Models\Modules\Module;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyFolder extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'company_folders';
    protected $fillable = ["company_id", "referent_id", "folder_number", "folder_name", "notes", "siret", "siren", "interface_id"];

    protected $hidden = [
        'company_id',
        'company',
        'laravel_through_key',
        'interface_id',
        'referent_id'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    //Ajout de companies pour le reprendre dans le get user
    // Déjà présent au dessus ??
//    public function companies()
//    {
//        return $this->belongsTo(Company::class, 'company_id', 'id');
//    }

    public function interfaces()
    {
        return $this->hasMany(InterfaceFolder::class, 'company_folder_id', 'id');
    }

    public function mappings()
    {
        return $this->belongsTo(Mapping::class, 'id', 'company_folder_id');
    }

    public function employees()
    {
        return $this->hasManyThrough(User::class, EmployeeFolder::class, 'company_folder_id', 'id', 'id', 'user_id')->with('modules');
    }

    public function referent(){
        return $this->hasOne(User::class, 'id', 'referent_id');
    }

    public function modules()
    {
        return $this->hasManyThrough(Module::class, CompanyFolderModuleAccess::class, 'company_folder_id', 'id', 'id', 'module_id')
            ->where('company_folder_module_access.has_access', true)
            ->select('modules.id', 'modules.name');
    }

}
