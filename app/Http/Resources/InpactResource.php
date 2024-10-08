<?php

namespace App\Http\Resources;

use App\Models\Companies\Company;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InpactResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'civility' => $this->civility,
            'lastname' => $this->lastname,
            'firstname' => $this->firstname,
            'telephone' => $this->telephone,
            'roles' => $this->roles->map(function ($role) {
                return [
                    'name' => $role->name,
                    'permissions' => $role->permissions->map(function ($permission) {
                        return [
                            'name' => $permission->name,
                            'label' => $permission->label,
                        ];
                    }),
                ];
            }),
            'companies' => CompanyResource::collection(Company::all()),
        ];
    }
}
