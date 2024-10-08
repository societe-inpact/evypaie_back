<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReferentResource extends JsonResource
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
            'company' => new CompanyResource($this->company),
        ];
    }
}
