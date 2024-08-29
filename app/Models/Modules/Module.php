<?php

namespace App\Models\Modules;

use App\Models\Companies\Company;
use App\Models\Companies\CompanyFolder;
use App\Models\Misc\User;
use App\Models\Misc\UserModulePermission;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;

class Module extends Model
{
    use HasFactory;

    protected $table = 'modules';
    protected $hidden = ['id', 'laravel_through_key', 'pivot'];
    protected $fillable = [
        'name',
        'label'
    ];

    public function userAccess()
    {
        return $this->belongsToMany(User::class, 'user_module_access', 'module_id', 'user_id')
            ->withPivot('has_access')
            ->wherePivot('has_access', true);
    }

    public function companyAccess()
    {
        return $this->belongsToMany(Company::class, 'company_module_access', 'module_id', 'company_id')
            ->withPivot('has_access')
            ->wherePivot('has_access', true);
    }

    public function companyFolderAccess()
    {
        return $this->belongsToMany(CompanyFolder::class, 'company_folder_module_access', 'module_id', 'company_folder_id')
            ->withPivot('has_access')
            ->wherePivot('has_access', true);
    }

    public function userPermissions()
    {
        return $this->hasMany(UserModulePermission::class, 'module_id')
            ->join('permissions', 'permissions.id', '=', 'user_module_permissions.permission_id')
            ->join('company_folders', 'company_folders.id', '=', 'user_module_permissions.company_folder_id')
            ->join('user_module_access', function ($join) {
                $join->on('user_module_access.module_id', '=', 'user_module_permissions.module_id')
                    ->where('user_module_access.user_id', Auth::id()); // Assurer que l'utilisateur est connecté
            })
            ->select([
                'user_module_permissions.id',
                'user_module_permissions.module_id',
                'company_folders.folder_name as folder',
                'permissions.name',
                'permissions.label'
            ])
            ->where('user_module_access.has_access', true) // Filtrer par has_access de user_module_access
            ->distinct();
    }

}
