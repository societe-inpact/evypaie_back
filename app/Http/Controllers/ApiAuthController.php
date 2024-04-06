<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ApiAuthController extends Controller
{
    public function getUser()
    {
        $user = Auth::user()->load('employee.company_entity.company')->toArray();

        $user['employee_infos'] = $user['employee'];
        $user['employee_infos']['email'] = $user['email'];

        $companyEntity = $user['employee_infos']['company_entity'];

        $company = $companyEntity['company'];
        $company['folders'] = [
            'id' => $companyEntity['id'],
            'folder_number' => $companyEntity['folder_number'],
            'label' => $companyEntity['label'],
            'siret' => $companyEntity['siret'],
            'siren' => $companyEntity['siren'],
        ];

        unset($companyEntity['id']);
        unset($companyEntity['folder_number']);
        unset($companyEntity['label']);
        unset($companyEntity['siret']);
        unset($companyEntity['siren']);

        $user['employee_infos']['company'] = $company;
        unset($user['employee_infos']['company_entity']);
        unset($user['email']);
        unset($user['employee']);

        return response()->json(['user' => $user]);


    }

    public function login(Request $request){
        if (!Auth::attempt($request->only('email', 'password'))){
            return response()->json([
                'message' => 'Email ou mot de passe invalides'
            ], ResponseAlias::HTTP_UNAUTHORIZED);
        }
        $user = Auth::user();
        $token = $user->createToken('token')->plainTextToken;
        $cookie = cookie('jwt', $token, 60 * 24)->withHttpOnly(false); // 1 day

        return response()->json([
            'message' => $token
        ])->withCookie($cookie);
    }

    public function register(Request $request){
        $fields =  [
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'is_employee' => 'required|boolean',
        ];

        if ($request->is_employee) {
            $fields = array_merge($fields, [
                'employee_code' => 'required',
                'firstname' => 'required',
                'lastname' => 'required',
                'company_entity_id' => 'required',
                'user_id' => 'required',
                'created_at' => 'nullable|date',
                'updated_at' => 'nullable|date',
            ]);
        }

        $validator = Validator::make($request->all(), $fields);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation des données, veuillez réessayer',
                'errors' => $validator->errors(),
            ], 400);
        }

        // Création de l'utilisateur
        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if ($request->is_employee) {
            $this->registerEmployee($request, $user); // Appel de la méthode registerEmployee
        }

        return response()->json([
            'status' => 'success',
            'message' => "L'utilisateur a bien été créé",
            'data' => $user,
        ], 201);
    }

    private function registerEmployee(Request $request, User $user)
    {
        // Création de l'employé
        Employee::create([
            'id' => $user->id,
            'employee_code' => $user->id,
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'company_entity_id' => $request->company_entity_id,
            'user_id' => $user->id,
        ]);

        // Récupération ou création du rôle "client" et de la permission "read-only"
        $role = Role::findOrCreate('client');
        $permission = Permission::findOrCreate('read-only');

        // Attribution de la permission au rôle
        $role->givePermissionTo($permission);
        return response()->json(['message' => 'Employé enregistré avec succès']);

    }

    protected function logout (Request $request) {
        $cookie = Cookie::forget('jwt');

        return response([
            'message' => 'Success',
            'status' => 'success'
        ])->withCookie($cookie);

    }
}
