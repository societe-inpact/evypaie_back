<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Companies\CompanyFolder;
use App\Traits\JSONResponseTrait;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    use JSONResponseTrait;

    public function createUpdateDeleteNote(Request $request){
        $companyFolderId = $request->get('company_folder_id');
        $companyFolder = CompanyFolder::findOrFail($companyFolderId);
        $validatedData = request()->validate([
            'notes' => 'nullable',
        ]);

        if ($validatedData){
            if ($companyFolder->notes === null){
                if ($companyFolder->update(['notes' => $validatedData['notes']])){
                    return $this->successResponse('', 'Note du dossier créée');
                }
            }
            if (isset($validatedData['notes']) && $companyFolder->notes !== null){
                if ($companyFolder->update(['notes' => $validatedData['notes']])){
                    return $this->successResponse('', 'Note du dossier mise à jour avec succès');
                }
            }
            if ($validatedData['notes'] === null){
                if ($companyFolder->update(['notes' => $validatedData['notes']])){
                    return $this->successResponse('', 'Note du dossier supprimée avec succès');
                }
            }
        }
        return $this->errorResponse('Erreur lors de la création de la note', 500);
    }
}
