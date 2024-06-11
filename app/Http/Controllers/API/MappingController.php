<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Mapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use League\Csv\CharsetConverter;
use League\Csv\Reader;

class MappingController extends Controller
{
    protected $tableNames = [
        'App\Models\Absence' => 'Absences',
        'App\Models\CustomAbsence' => 'Absences personnalisées',
        'App\Models\Hour' => 'Heures',
        'App\Models\CustomHour' => 'Heures personnalisées',
    ];

    public function getMapping(Request $request)
    {
        $companyFolder = $request->get('company_folder_id');

        if (!$companyFolder) {
            return response()->json("L'id du dossier est requis", 400);
        }

        if (!$request->hasFile('csv')) {
            return response()->json('Aucun fichier importé');
        }

        $file = $request->file('csv');
        $reader = $this->prepareCsvReader($file->getPathname());
        $records = $reader->getRecords();

        $results = $this->processCsvRecords($records, $companyFolder);

        return response()->json($results);
    }

    protected function prepareCsvReader($path)
    {
        $reader = Reader::createFromPath($path, 'r');
        $encoder = (new CharsetConverter())->inputEncoding('utf-8');
        $reader->addFormatter($encoder);
        $reader->setDelimiter(';');

        return $reader;
    }

    protected function processCsvRecords($records, $companyFolder)
    {
        $processedRecords = collect();
        $unmatchedRubriques = [];
        $rubriqueRegex = '/^\d{1,3}[A-Z]{0,2}$/';
        $results = [];

        foreach ($records as $record) {
            $inputRubrique = $this->findInputRubrique($record, $rubriqueRegex);

            if ($inputRubrique && !$processedRecords->contains($inputRubrique)) {
                $processedRecords->push($inputRubrique);
                $mappingResult = $this->findMapping($inputRubrique, $companyFolder);

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
                        'company_folder_id' => $companyFolder,
                    ];
                }
            }
        }

        return array_merge($results, $unmatchedRubriques);
    }

    protected function findInputRubrique($record, $regex)
    {
        foreach ($record as $value) {
            if (preg_match($regex, $value)) {
                return $value;
            }
        }

        return null;
    }

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
                            'type_rubrique' => $this->tableNames[$data['output_type']] ?? $data['output_type'],
                            'output_rubrique' => $output->code,
                            'base_calcul' => $output->base_calcul,
                            'label' => $output->label,
                            'is_mapped' => true,
                            'company_folder_id' => $companyFolder,
                        ];
                    }
                }
            }
        }

        return null;
    }

    protected function resolveOutputModel($outputType, $outputRubriqueId)
    {
        if (!class_exists($outputType)) {
            return null;
        }

        $outputModelClass = App::make($outputType);
        return $outputModelClass->find($outputRubriqueId);
    }

    public function updateMapping(Request $request, $id)
    {
        $companyFolder = $request->get('company_folder_id');

        if (!$companyFolder) {
            return response()->json("L'id du dossier est requis", 400);
        }

        $validatedData = $this->validateMappingData($request);

        $mapping = Mapping::findOrFail($id);

        if ($mapping->company_folder_id !== intval($validatedData['company_folder_id'])) {
            return response()->json(['error' => 'Le dossier de l\'entreprise ne correspond pas.'], 403);
        }

        if (!$this->updateMappingData($mapping, $validatedData)) {
            return response()->json(['error' => 'La rubrique d\'entrée spécifiée n\'a pas été trouvée.'], 404);
        }

        return response()->json(['message' => 'Mapping mis à jour avec succès']);
    }

    protected function validateMappingData(Request $request)
    {
        return $request->validate([
            'input_rubrique' => 'required|string|regex:/^\d{1,3}[A-Z]{0,2}$/',
            'name_rubrique' => 'required|string|max:255',
            'output_rubrique_id' => 'required|integer',
            'company_folder_id' => 'required|integer',
            'output_type' => 'required|string',
        ]);
    }

    protected function updateMappingData($mapping, $validatedData)
    {
        $data = $mapping->data;

        foreach ($data as &$entry) {
            if ($entry['input_rubrique'] === $validatedData['input_rubrique']) {
                $entry['name_rubrique'] = $validatedData['name_rubrique'];
                $entry['output_rubrique_id'] = $validatedData['output_rubrique_id'];
                $entry['output_type'] = $validatedData['output_type'];
                $mapping->data = $data;
                return $mapping->save();
            }
        }

        return false;
    }

    public function storeMapping(Request $request)
    {
        $validatedData = $this->validateMappingData($request);
        $companyFolder = $validatedData['company_folder_id'];

        $existingMappings = Mapping::where('company_folder_id', $companyFolder)->get();

        foreach ($existingMappings as $mapping) {
            $data = $mapping->data;

            foreach ($data as $entry) {
                if ($entry['input_rubrique'] === $validatedData['input_rubrique']) {
                    return response()->json([
                        'error' => 'La rubrique d\'entrée ' . $validatedData['input_rubrique'] . ' est déjà associée.',
                    ], 409);
                }
                if ($entry['output_rubrique_id'] === $validatedData['output_rubrique_id']) {
                    return response()->json([
                        'error' => 'La rubrique de sortie ' . $validatedData['output_rubrique_id'] . ' est déjà associée.',
                    ], 409);
                }
            }
        }

        if (!$this->validateOutputClassAndRubrique($validatedData)) {
            return response()->json([
                'error' => 'La rubrique spécifiée n\'existe pas ou le type spécifié n\'existe pas.',
            ], 404);
        }

        $this->saveMappingData($companyFolder, $validatedData);

        return response()->json(['success' => 'Mapping ajouté avec succès'], 201);
    }

    protected function validateOutputClassAndRubrique($validatedData)
    {
        $outputClass = $validatedData['output_type'];

        if (!class_exists($outputClass)) {
            return false;
        }

        $outputRubrique = $outputClass::find($validatedData['output_rubrique_id']);

        return $outputRubrique !== null;
    }

    protected function saveMappingData($companyFolder, $validatedData)
    {
        $newMapping = [
            'input_rubrique' => $validatedData['input_rubrique'],
            'name_rubrique' => $validatedData['name_rubrique'],
            'output_rubrique_id' => $validatedData['output_rubrique_id'],
            'output_type' => $validatedData['output_type'],
        ];

        $mapping = Mapping::where('company_folder_id', $companyFolder)->first();

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
}
