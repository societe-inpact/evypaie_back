<?php

namespace App\Http\Controllers\API;

use App\Classes\Rubric;
use App\Http\Controllers\Controller;
use App\Models\Absences\Absence;
use App\Models\Absences\CustomAbsence;
use App\Models\Hours\CustomHour;
use App\Models\Hours\Hour;
use App\Models\Mapping\Mapping;
use App\Models\Companies\CompanyFolder;
use App\Models\Misc\InterfaceMapping;
use App\Models\Misc\InterfaceSoftware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use League\Csv\CharsetConverter;
use League\Csv\Reader;
use Illuminate\Support\Facades\Validator;

class MappingController extends Controller
{
    protected $tableNames = [
        'Absence' => 'App\Models\Absences\Absence',
        'CustomAbsence' => 'App\Models\Absences\CustomAbsence',
        'Hour' => 'App\Models\Hours\Hour',
        'CustomHour' => 'App\Models\Hours\CustomHour',
        'VariableElement' => 'App\Models\VariablesElements\VariableElement',
    ];

    protected $tableNamesRevers = [
        'App\Models\Absences\Absence' => 'Absence',
        'App\Models\Absences\CustomAbsence' => 'Absence personnalisée',
        'App\Models\Hours\Hour' => 'Heure',
        'App\Models\Hours\CustomHour' => 'Heure personnalisée',
        'App\Models\VariablesElements\VariableElement' => 'Éléments variables',
    ];

    // Fonction permettant de récupérer les mappings existants d'un dossier
    public function getMapping(Request $request, $id)
    {
        $file = $request->file('csv');
        $companyFolder = CompanyFolder::with('interfaces')->findOrFail($id);

        if (!$companyFolder) {
            return response()->json("L'id du dossier est requis", 400);
        }

        if (!$file) {
            return response()->json('Aucun fichier importé', 400);
        }

        foreach ($companyFolder->interfaces as $interface) {
            $interface = InterfaceSoftware::findOrFail($interface->id);

            if ($interface) {
                $idInterfaceMapping = $interface->interface_mapping_id;

                if ($idInterfaceMapping !== null) {
                    $columnIndex = InterfaceMapping::findOrFail($idInterfaceMapping);
                    $typeSeparateur = $columnIndex->type_separateur;
                    $extension = strtolower($columnIndex->extension);
                    $indexRubrique = $columnIndex->colonne_rubrique - 1;
                    $colonneMatricule = $columnIndex->colonne_matricule - 1;
                } else {
                    // interfaces spécifique
                    $interfaceNames = strtolower($interface->name);

                    switch ($interfaceNames) {
                        case "marathon":
                            $convertMEController = new ConvertMEController();
                            $columnIndex = $convertMEController->formatFilesMarathon();
                            $typeSeparateur = $columnIndex["separateur"];
                            $extension = $columnIndex["extension"];
                            $indexRubrique = $columnIndex["index_rubrique"];
                            $colonneMatricule = 0;
                            break;
                        default:
                            return response()->json([
                                'success' => false,
                                'message' => 'Il manque le paramétrage spécifique de l\'interface',
                                'status' => 400
                            ]);
                    }
                }

                $reader = $this->prepareCsvReader($file->getPathname(), $typeSeparateur);
                $records = iterator_to_array($reader->getRecords(), true);

                $companyFolderId = $companyFolder->id;
                $results = $this->processCsvRecords($records, $companyFolderId, $indexRubrique, $colonneMatricule);

                return response()->json($results);
            } else {
                return response()->json(['message' => 'L\'interface n\'existe pas', 'status' => 400]);
            }
        }

        return response()->json(['message' => 'Aucune interface à traiter', 'status' => 400]);
    }


    // Fonction permettant de configurer l'import du fichier
    protected function prepareCsvReader($path, $typeSeparateur)
    {
        $reader = Reader::createFromPath($path, 'r');
        $encoder = (new CharsetConverter())->inputEncoding('utf-8');
        $reader->addFormatter($encoder);
        $reader->setDelimiter($typeSeparateur);

        return $reader;
    }


    // Fonction permettant de récupérer les mappings existants d'un dossier
    protected function processCsvRecords($records, $companyFolderId, $indexRubrique, $colonneMatricule)
    {
        $processedRecords = collect();
        $unmatchedRubriques = [];
        $results = [];

        $containsDigit = ctype_digit($records[0][$colonneMatricule]);
        if (($containsDigit) === false) {
            unset($records[0]);
        }

        foreach ($records as $record) {

            // colonne à ne pas prendre en compte
            if (!isset($record[$indexRubrique])) {
                continue;
            }

            $inputRubrique = $record[$indexRubrique];

            if ($inputRubrique && !$processedRecords->contains($inputRubrique)) {
                $processedRecords->push($inputRubrique);
                $mappingResult = $this->findMapping($inputRubrique, $companyFolderId);
                if ($mappingResult) {
                    $results[] = $mappingResult;
                } else {
                    $unmatchedRubriques[] = [
                        'input_rubrique' => $inputRubrique,
                        'type_rubrique' => null,
                        'output_rubrique' => null,
                        'base_calcul' => null,
                        'label' => null,
                        'is_mapped' => false,
                        'is_used' => false,
                        'company_folder_id' => $companyFolderId,
                    ];
                }
            }
        }
        return array_merge($results, $unmatchedRubriques);
    }

    // Fonction permettant de récupérer une rubrique d'entrée
    protected function findInputRubrique($rubrique, $regex)
    {
        // Vérification si la rubrique correspond au regex
        if (preg_match($regex, $rubrique)) {
            return $rubrique;
        }
        return null;
    }

    // Fonction permettant de récupérer les mappings existants d'un dossier
    protected function findMapping($inputRubrique, $companyFolder)
    {
        $mappings = Mapping::with('folder')
            ->where('company_folder_id', $companyFolder)
            ->get();

        foreach ($mappings as $mapping) {
            foreach ($mapping->data as $data) {
                if ($data['input_rubrique'] === $inputRubrique) {
                    $output = $this->resolveOutputModel($data['output_type'], $data['output_rubrique_id']);
                    if ($output) {
                        return [
                            'input_rubrique' => $data['input_rubrique'],
                            'type_rubrique' => $this->tableNamesRevers[$data['output_type']] ?? $data['output_type'],
                            'output_rubrique' => $output->code,
                            'base_calcul' => $output->base_calcul,
                            'label' => $output->label,
                            'is_used' => $data['is_used'],
                            'is_mapped' => true,
                            'company_folder_id' => $companyFolder,
                        ];
                    } else {
                        return [
                            'input_rubrique' => $data['input_rubrique'],
                            'type_rubrique' => $data['output_type'],
                            'output_rubrique' => '',
                            'base_calcul' => '',
                            'label' => '',
                            'is_used' => $data['is_used'],
                            'is_mapped' => true,
                            'company_folder_id' => $companyFolder,
                        ];
                    }
                }
            }
        }

        return null;
    }

    // Fonction permettant de récupérer le Model d'une rubrique
    public function resolveOutputModel($outputType, $outputRubriqueId, $companyFolderId)
    {
        $modelFolders = [
            'Absence' => 'Absences',
            'CustomAbsence' => 'Absences',
            'Hour' => 'Hours',
            'CustomHour' => 'Hours',
            // Ajoutez d'autres mappings ici si nécessaire
        ];

        // Déterminer le sous-dossier du modèle
        $folder = $modelFolders[$outputType] ?? null;

        // Construire le chemin complet du modèle
        $namespacePrefix = 'App\Models\\';
        $fullOutputType = $folder ? $namespacePrefix . $folder . '\\' . $outputType : $namespacePrefix . $outputType;

        // Vérifier si la classe existe
        if (!class_exists($fullOutputType)) {
            return null;
        }

        // Créer une instance du modèle
        $outputModelClass = App::make($fullOutputType);

        // Retourner l'instance trouvée par l'ID
        return $outputModelClass->find($outputRubriqueId);
    }

    // Fonction permettant de mettre à jour un mapping existant
    public function updateMapping(Request $request, $id)
    {

        $this->validateMappingData($request);
        $companyFolder = $request->get('company_folder_id');
        if (!$companyFolder) {
            return response()->json("L'id du dossier est requis", 400);
        }

        $mapping = Mapping::with('folder')
            ->where('company_folder_id', $companyFolder)
            ->findOrFail($id);

        if ($mapping->company_folder_id !== intval($request['company_folder_id'])) {
            return response()->json(['error' => 'Le dossier de l\'entreprise ne correspond pas.'], 403);
        }

        $updateResult = $this->updateMappingData($mapping, $request);
        if ($updateResult) {
            return response()->json(['message' => 'Mapping mis à jour avec succès']);
        } else {
            return response()->json(['error' => 'Rubrique introuvable'], 404);
        }
    }


    // Fonction permettant de valider les données d'enregistrement d'un mapping
    protected function validateMappingData(Request $request)
    {
        return $request->validate([
            // 'input_rubrique' => 'required|string|regex:/^[A-Za-z0-9]{1,3}$/',
            'input_rubrique' => 'required|string|max:255',
            'name_rubrique' => 'nullable|string|max:255',
            'output_rubrique_id' => 'nullable|integer',
            'company_folder_id' => 'required',
            'output_type' => 'nullable|string',
            'is_used' => 'required|boolean',
        ]);
    }

    // Fonction permettant de mettre à jour un mappings existant
    protected function updateMappingData($mapping, $rubricRequest)
    {
        $data = $mapping->data;
        $dataBis = [];
        $companyFolderId = $mapping->company_folder_id;
        // permet de modifier output_type en se basant sur le nom de la rubrique (si ce n'est pas null)
        $rubricRequest = $this->controleAbsenceHours($rubricRequest, $companyFolderId);
        $rubricRequest = collect($rubricRequest);
        if ($rubricRequest['name_rubrique'] !== null) {
            $out = array("name_rubrique" => $rubricRequest['name_rubrique'], "output_type" => $this->tableNames[$rubricRequest['output_type']]);
            $rubricRequest = $rubricRequest->toArray();
            $data = array_merge($rubricRequest, $out);
            $mapping->data = array_merge($mapping->data, $data);
            dd($mapping->data);
            return $mapping->save($data);
        }

        // permet d'enregister les modifications
        foreach ($data as $entry) {
            if ($entry['input_rubrique'] === $rubricRequest['input_rubrique']) {
                $entry['name_rubrique'] = $rubricRequest['name_rubrique'];
                $entry['output_rubrique_id'] = $rubricRequest['output_rubrique_id'];
                $entry['output_type'] = $this->tableNames[$rubricRequest['output_type']];
                $entry['is_used'] = $rubricRequest['is_used'];
                $dataBis[] = $entry;
            } else {
                $dataBis[] = $entry;
            }
        }

        if ($data !== $dataBis) {
            $mapping->data = $dataBis;
            $mapping->save();
            return 'updated';
        } else {
            return 'nomodif';
        }
    }

    // Fonction de contrôle des absences perso et des heures perso

    private function controleAbsenceHours($rubricRequest, $companyFolderId)
    {
        // On controle si dans le mapping du folder il existe déjà une absence avec le même code et le même output_rubric_id
        $rubricRequest = new Rubric($rubricRequest);
        if ($rubricRequest->output_type === "CustomAbsence") {
            $labelHourCust = CustomAbsence::where("id", $rubricRequest->output_rubrique_id)->where("company_folder_id", $companyFolderId)->first();
            if ($labelHourCust){
                $absence = Absence::where("code", $labelHourCust->code)->first();
                if ($absence !== null) {
                    $out = array("name_rubrique" => 'Absence', "output_rubrique_id" => $absence->id, "output_type" => 'Absence');
                    $rubricRequest = collect($rubricRequest)->toArray();
                    $rubricRequest = array_merge($rubricRequest, $out);
                }
                return $rubricRequest;
            }
            return collect($rubricRequest);
        }

        if ($rubricRequest->output_type === "CustomHour") {
            $labelHourCust = CustomHour::where("id", $rubricRequest->output_rubrique_id)->where("company_folder_id", $companyFolderId)->first();
            if ($labelHourCust){
                $hour = Hour::where("code", $labelHourCust->code)->first();
                if ($hour !== null) {
                    $out = array("name_rubrique" => 'Heure', "output_rubrique_id" => $hour->id, "output_type" => 'Hour');
                    $rubricRequest = collect($rubricRequest)->toArray();
                    $rubricRequest = array_merge($rubricRequest, $out);
                }
                return $rubricRequest;
            }
            return collect($rubricRequest);
        }
        return collect($rubricRequest);
    }

    // Fonction permettant d'enregistrer un nouveau mapping en BDD
    public function storeMapping(Request $request)
    {
        $validatedRequestData = $this->validateMappingData($request);
        $companyFolderId = $validatedRequestData['company_folder_id'];
        $mappedRubriques = Mapping::where('company_folder_id', $companyFolderId)->get();
        $validatedRequestData = $this->controleAbsenceHours($validatedRequestData, $companyFolderId);
        $validatedRequestData = collect($validatedRequestData);
        foreach ($mappedRubriques as $mappedRubrique) {
            $allMappedRubriques = $mappedRubrique->data;
            foreach ($allMappedRubriques as $inputMappedRubrique) {
                $isUsed = filter_var($validatedRequestData['is_used'], FILTER_VALIDATE_BOOLEAN) || filter_var($inputMappedRubrique['is_used'], FILTER_VALIDATE_BOOLEAN);
                if ($inputMappedRubrique['input_rubrique'] === $validatedRequestData['input_rubrique']) {
                    if ($isUsed === false) {
                        return response()->json([
                            'error' => 'La rubrique d\'entrée ' . $validatedRequestData['input_rubrique'] . ' n\'est pas utilisée',
                        ], 409);
                    } else {
                        return response()->json([
                            'error' => 'La rubrique d\'entrée ' . $validatedRequestData['input_rubrique'] . ' est déjà associée à la rubrique '
                        ], 409);
                    }
                }
//                if (!$this->validateOutputClassAndRubrique($validatedRequestData) && $isUsed) {
//                    return response()->json([
//                        'error' => 'La rubrique ou le type de rubrique spécifié n\'existe pas.',
//                    ], 404);
//                }
            }
        }

        if ($request->has('code')){
            $requestNewCustomRubric = new Request([
              "code" => $request->code,
              "label" => $request->label,
              "base_calcul" => $request->base_calcul ?? null,
              "therapeutic_part_time" => $request->therapeutic_part_time ?? null,
            ]);
            $this->saveMappingData($companyFolderId, $validatedRequestData, $requestNewCustomRubric);
        }else{
            $this->saveMappingData($companyFolderId, $validatedRequestData);
        }
        return response()->json(['success' => 'Mapping ajouté avec succès'], 201);
    }

    // Fonction permettant de transformer la rubrique d'entrée mappée en rubrique de sortie SILAE
    private function getSilaeRubrique($rubrique)
    {
        $typeRubrique = $rubrique['output_type'];
        $outputRubrique = $rubrique['output_rubrique_id'];
        if (class_exists($typeRubrique)) {
            return $typeRubrique::find($outputRubrique);
        }
        return false;
    }

    // Fonction permettant de récupérer la rubrique via son Model
    protected function validateOutputClassAndRubrique($validatedData)
    {
        $outputClass = $validatedData['output_type'];

        if (!class_exists($outputClass)) {
            return false;
        }

        $outputRubrique = $outputClass::find($validatedData['output_rubrique_id']);

        return $outputRubrique !== null;
    }

    // Fonction permettant d'enregistrer un nouveau mapping en BDD
    protected function saveMappingData($companyFolder, $validatedData, ?Request $request = null)
    {
        $newMapping = [
            'input_rubrique' => $validatedData['input_rubrique'],
            'name_rubrique' => $validatedData['name_rubrique'],
            'output_rubrique_id' => $validatedData['output_rubrique_id'],
            'output_type' => $validatedData['output_type'],
            'is_used' => $validatedData['is_used'],
        ];

        $mapping = Mapping::where( 'company_folder_id', $companyFolder)->first();
        switch ($validatedData['output_type']){
            case 'CustomHour' : {
                $existingCustomHour = CustomHour::all()->where('id', '=', $validatedData['output_rubrique_id']);
                if ($existingCustomHour->isEmpty()){
                    if ($request){
                        $createNewCustomHourRequest = new Request([
                            'code' => $request->code,
                            'label' => $request->label,
                            'company_folder_id' => $companyFolder,
                        ]);
                        $hourController = new HourController();
                        $hourController->createCustomHour($createNewCustomHourRequest);
                    }
                }
            }
            case 'CustomAbsence' : {
                $existingCustomAbsence = CustomAbsence::all()->where('id', '=', $validatedData['output_rubrique_id']);
                if ($existingCustomAbsence->isEmpty()){
                    if ($request){
                        $createNewCustomAbsenceRequest = new Request([
                            'code' => $request->code,
                            'label' => $request->label,
                            'base_calcul' => $request->base_calcul,
                            'therapeutic_part_time' => $request->therapeutic_part_time ?? null,
                            'company_folder_id' => $companyFolder,
                        ]);
                        $absenceController = new AbsenceController();
                        $absenceController->createCustomAbsence($createNewCustomAbsenceRequest);
                    }
                }
            }
            default: '';
        }
        if ($mapping) {
            $existingData = $mapping->data;
            $existingData[] = $newMapping;
            $mapping->data = $existingData;
            $mapping->save();
        } else {
            Mapping::create([
                'company_folder_id' => $companyFolder,
                'data' => [$newMapping],
            ]);
        }
    }

    // Fonction permettant de supprimer un mapping existant
    protected function deleteMapping($id)
    {
        $mappingCompagny = Mapping::where("id", $id)->first();
        $dataBis = [];
        $mappingCompagny->data = $dataBis;
        $mappingCompagny->save();
        if ($mappingCompagny) {
            return response()->json(['message' => 'Mapping supprimé du dossier avec succès']);
        }
        return response()->json(['message' => 'Erreur lors de la suppression du mapping']);
    }

    // Fonction permettant de mettre à jour un mappings existant
    public function deleteOneLineMappingData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'companyFolderId' => 'required|integer',
            'output_rubrique_id' => 'required|integer',
            'nameRubrique' => 'required|string',
            'input_rubrique' => ''
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $companyFolderId = $request ["companyFolderId"];
        $output_rubrique_id = $request ["output_rubrique_id"];
        $nameRubrique = $request ["nameRubrique"];
        $input_rubrique = $request ["input_rubrique"];

        // permet de récupérer le mapping
        $mappingCompagny = Mapping::where("company_folder_id", $companyFolderId)->first();
        $data = $mappingCompagny->data;
        $dataBis = [];

        // permet d'enregister les modifications
        foreach ($data as $entry) {
            // si c'est une valeur ne pas utiliser, il faut modifier le 'name_rubrique'
            if ($entry['name_rubrique'] === null) {
                $entry['name_rubrique'] = "Ne pas utiliser";
                $entry['output_rubrique_id'] = 0;
            }
            if ((string)$entry['output_rubrique_id'] === (string)$output_rubrique_id && $entry['name_rubrique'] === $nameRubrique) {
                if ($input_rubrique !== "") {
                    if ((string)$entry['input_rubrique'] === (string)$input_rubrique) {
                        // supprimer la valeur
                    } else {
                        $dataBis[] = $entry;
                    }
                } else {

                    // supprimer la valeur
                }
            } else {
                $dataBis[] = $entry;
            }
        }

        if ($data !== $dataBis) {
            $mappingCompagny->data = $dataBis;
            $mappingCompagny->save();
            return response()->json(['message' => 'updated']);
        } else {
            return response()->json(['message' => 'nomodif']);
        }
    }
}
