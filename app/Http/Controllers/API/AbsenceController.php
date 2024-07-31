<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Absences\Absence;
use App\Models\Absences\CustomAbsence;
use App\Rules\CustomRubricRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class AbsenceController extends Controller
{
    // PARTIE CUSTOM ABSENCES

    /**
     * Récupère toutes les absences personnalisées dans la base de données.
     *
     * @return JsonResponse Réponse JSON indiquant le succès ou l'échec de la récupération.
     */
    public function getCustomAbsences()
    {
        $customAbsences = CustomAbsence::all();
        return response()->json($customAbsences, 200);
    }

    /**
     * Crée une nouvelle absence personnalisée dans la base de données.
     *
     * @return JsonResponse Réponse JSON indiquant le succès ou l'échec de la création.
     */
    public function createCustomAbsence(Request $request)
    {

        // Validation des données
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'code' => ['required', new CustomRubricRule],
            'base_calcul' => 'required|string|max:255',
            'company_folder_id' => 'required|integer',
            'therapeutic_part_time' => 'nullable|boolean',
        ]);

        // Nettoyage du champ 'code' avant l'enregistrement
        $validated['code'] = preg_replace('/\s*-\s*/', '-', trim($validated['code']));

        // Vérifie si la custom absence avec ce code et ce label existe déjà
        $isCustomAbsenceExists = CustomAbsence::where('company_folder_id', $validated['company_folder_id'])
            ->where('code', $validated['code'])
            ->where('label', $validated['label'])
            ->where('base_calcul', $validated['base_calcul'])
            ->exists();


        $isAbsenceExists = Absence::where('code', $validated['code'])
            ->where('base_calcul', $validated['base_calcul'])
            ->exists();

        if ($isCustomAbsenceExists || $isAbsenceExists) {
            return response()->json(['message' => 'Absence déjà existante.'], 400);
        }

        if (str_starts_with($validated['code'], 'AB-')) {
            // Création de l'absence personnalisée
            $customAbsence = CustomAbsence::create([
                'label' => $validated['label'],
                'code' => $validated['code'],
                'base_calcul' => $validated['base_calcul'],
                'company_folder_id' => $validated['company_folder_id'],
                'therapeutic_part_time' => $request->input('therapeutic_part_time', null),
            ]);
            if ($customAbsence) {
                return response()->json(['message' => 'Absence personnalisée créée', "id" => $customAbsence->id], 201);
            }
        } else {
            return response()->json(['message' => 'Le code rubrique doit commencer par AB-'], 400);
        }

        return response()->json(['message' => 'Impossible de créer la rubrique personnalisée'], 400);
    }

    public function updateCustomAbsence(Request $request, $id)
    {
        // Validation des données
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'code' => ['required', new CustomRubricRule],
            'base_calcul' => 'required|string|max:255',
            'company_folder_id' => 'required|integer',
            'therapeutic_part_time' => 'nullable|boolean',
        ]);

        // Nettoyage du champ 'code' avant l'enregistrement
        $validated['code'] = preg_replace('/\s*-\s*/', '-', trim($validated['code']));

        // Vérifie si la custom absence avec ce code et ce label existe déjà
        $isCustomAbsenceExists = CustomAbsence::where('company_folder_id', $validated['company_folder_id'])
            ->where('code', $validated['code'])
            ->where('label', $validated['label'])
            ->where('base_calcul', $validated['base_calcul'])
            ->exists();


        $isAbsenceExists = Absence::where('code', $validated['code'])
            ->where('base_calcul', $validated['base_calcul'])
            ->exists();

        if ($isCustomAbsenceExists || $isAbsenceExists) {
            return response()->json(['message' => 'Absence déjà existante.'], 400);
        }

        if (str_starts_with($validated['code'], 'AB-')) {
            // Création de l'absence personnalisée
            
            $customAbsence = CustomAbsence::where('id',$id)->update([
                'label' => $validated['label'],
                'code' => $validated['code'],
                'base_calcul' => $validated['base_calcul'],
                'therapeutic_part_time' => $request->input('therapeutic_part_time', null),
            ]);
            if ($customAbsence) {
                return response()->json(['message' => 'Absence personnalisée modifiée', "id" => $id], 201);
            }
        } else {
            return response()->json(['message' => 'Le code rubrique doit commencer par AB-'], 400);
        }

        return response()->json(['message' => 'Impossible de modifier la rubrique personnalisée'], 400);
    }

    public function deleteCustomAbsence($id)
    {
        $deleteCustomAbsence = CustomAbsence::find($id)->delete();
        if ($deleteCustomAbsence){
            return response()->json(['message' => 'l\'absence custom a été supprimé'], 200);
        }
        else{
            return response()->json(['message' => 'L\'absence custom n\'existe pas.'], 404);
        }
    }

    // PARTIE ABSENCES

    /**
     * Récupère toutes les absences génériques dans la base de données.
     *
     * @return JsonResponse Réponse JSON indiquant le succès ou l'échec de la récupération.
     */
    public function getAbsences()
    {
        $absences = Absence::all();
        return response()->json($absences, 200);
    }

    public function createAbsence(Request $request)
    {
        // Validation des données
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'code' => ['required', new CustomRubricRule],
            'base_calcul' => 'required|string|max:255',
            'therapeutic_part_time' => 'nullable|boolean',
        ]);

        // Nettoyage du champ 'code' avant l'enregistrement
        $validated['code'] = preg_replace('/\s*-\s*/', '-', trim($validated['code']));

        $isAbsenceExists = Absence::where('code', $validated['code'])->exists();

        if ($isAbsenceExists) {
            return response()->json(['message' => 'Absence déjà existante.'], 400);
        }

        if (str_starts_with($validated['code'], 'AB-')) {
            // Création de l'absence personnalisée
            $absence = Absence::create([
                'label' => $validated['label'],
                'code' => $validated['code'],
                'base_calcul' => $validated['base_calcul'],
                'therapeutic_part_time' => $request->input('therapeutic_part_time', null),
            ]);
            if ($absence) {
                return response()->json(['message' => 'Absence générique créée'], 201);
            }
        } else {
            return response()->json(['message' => 'Le code rubrique doit commencer par AB-'], 400);
        }

        return response()->json(['message' => 'Impossible de créer la rubrique'], 400);
    }

    public function updateAbsence(Request $request, $id)
    {
        $absence = Absence::findOrFail($id);

        // Validation des données
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'code' => ['required', new CustomRubricRule()],
            'base_calcul' => 'required|string|max:255',
            'therapeutic_part_time' => 'nullable|boolean',
        ]);

        // Nettoyage du champ 'code' avant l'enregistrement
        $validated['code'] = preg_replace('/\s*-\s*/', '-', trim($validated['code']));

        // Vérifier si le code existe déjà pour une autre absence
        $isAbsenceExists = Absence::where('code', $validated['code'])
            ->where('id', '!=', $id)
            ->exists();

        if ($isAbsenceExists) {
            return response()->json(['message' => 'Absence déjà existante.'], 400);
        }

        // Mettre à jour l'absence
        if ($absence->update($validated)) {
            return response()->json(['message' => 'Absence mise à jour avec succès']);
        } else {
            return response()->json(['message' => 'Erreur lors de la mise à jour de l\'absence'], 500);
        }
    }

    public function deleteAbsence()
    {
        // TODO : Delete une absence générique
    }
}
