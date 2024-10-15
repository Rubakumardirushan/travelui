<?php

namespace App\Http\Controllers\Travel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\travelinfo;
class Traveldatacontroller extends Controller
{
    public function store(Request $request){
        Log::info($request->all());
        $validator = Validator::make($request->all(), [
            'travel_mode' => 'required|string|max:255',
            'travel_date' => 'required|date',
            'travel_time' => 'required|',
        ]);
     
        
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        
        $travelInfo =travelinfo::create([
            'travel_mode' => $request->travel_mode,
            'travel_date' => $request->travel_date,
            'travel_time' => $request->travel_time,
        ]);

        return response()->json($travelInfo, 201);
    }

    }

