<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\CustomAbsence;
use Illuminate\Http\Request;

class AbsenceController extends Controller
{
    public function getAbsences(){
        $absences = Absence::all();
        return response()->json($absences, 200);
    }

    public function getCustomAbsences(){
        $customAbsences = CustomAbsence::all();
        return response()->json($customAbsences, 200);
    }
}
