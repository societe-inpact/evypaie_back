<?php

namespace App\Models\Companies;

use App\Models\Employees\UserCompanyFolder;
use App\Models\Mapping\Mapping;
use App\Models\Misc\CompanyFolderInterface;
use App\Models\Misc\InterfaceSoftware;
use App\Models\Misc\User;
use App\Models\Modules\CompanyModuleAccess;
use App\Models\Modules\Module;
use App\Traits\ModuleRetrievingTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyFolder extends Model
{
    use HasFactory, ModuleRetrievingTrait;

    public $timestamps = false;

    protected $table = 'company_folders';
    protected $fillable = ["company_id", "referent_id", "folder_number", "folder_name", "telephone", "notes", "siret", "siren"];

    protected $hidden = [
        'company_id',
        'company',
        'laravel_through_key',

    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function interfaces()
    {
        return $this->hasManyThrough(
            InterfaceSoftware::class,
            CompanyFolderInterface::class,
            'company_folder_id',
            'id',
            'id',
            'interface_id'
        );
    }

    public function mappings()
    {
        return $this->belongsTo(Mapping::class, 'id', 'company_folder_id');
    }

    public function employees()
    {
        return $this->belongsToMany(User::class, 'user_company_folder', 'company_folder_id');
    }

    public function referent(){
        return $this->hasOne(User::class, 'id', 'referent_id');
    }

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'company_folder_module_access', 'company_folder_id')
            ->where('has_access', true)->whereHas('companyAccess');
    }

}
