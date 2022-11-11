<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

use App\Models\Unit;
use App\Models\Warning;

class WarningController extends Controller
{
    public function getMyWarnings(Request $request) {
        $response = ['error' => false];
        $validator = Validator::make($request->all(), [
            'unit' => 'required|numeric'
        ], [
            'unit.required' => 'ID da propriedade é necessario.',
            'unit.numeric' => 'ID só é valido se for numérico.'
        ]);
        if($validator->fails()) {
            $response['error'] = $validator->errors();
            return response()->json($response, 422);
            exit;
        }

        $propertys = Unit::where('user_id', Auth::id())->get();
        if(count($propertys) > 0) {
            foreach($propertys as $property) {
                $warnings = Warning::where('unit_id', $property->id)
                    ->orderBy('created_at')
                ->get();
                foreach($warnings as $warning) {
                    $warning->created_at = date('d/m/Y', strtotime($warning->created_at));
                    if($warning->photos) {
                        $photos = [];
                        foreach(explode(',', $warning->photos) as $photo) {
                            $photos[]['url'] = Storage::disk('local')->temporaryUrl($photo, now()->addMinutes(5));
                        }
                        $warning->photos = $photos;
                    }
                    $response['warnings'][] = $warning;
                }
            }
            return response()->json($response, 200);
        } else {
            $response['error'] = 'Nenhuma propriedade associada a este usuário.';
            return response()->json($response, 404);
        }
    }

    public function setWarning(Request $request) {
        $response = ['error' => false];

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'unit_id' => 'required|numeric',
            'photos' => 'array'
        ]);

        if(! $validator->fails()) {
            $unit = Unit::where('user_id', Auth::id())
                ->where('id', $validator->validated()['unit_id'])
            ->first();
            if($unit) {
                $data = $validator->safe()->only('title', 'unit_id');
                $data['created_at'] = date('Y-m-d');
                $data['status'] = 'IN_REVIEW';

                $data['photos'] = implode(',',$validator->validated()['photos']);

                $response['warning'] = Warning::create($data);
                return response()->json($response, 200);
                
            } else {
                $response['error'] = 'Propriedade não existente.';
                return response()->json($response, 404);
            }
        } else {
            $response['error'] = $validator->errors();
            return response()->json($response, 422);
        }
    }

    public function addWarningFile(Request $request) {
        $response = ['error' => false];

        $validator = Validator::make($request->all(), [
            'photo' => 'required|image|mimes:png,jpg',
        ]);

        if(! $validator->fails()) {
            $file = Storage::disk('local')->put("warning-photos", $validator->validated()['photo']);
            if($file) {
                $response['file'] = explode('/', $file)[1];
                return response()->json($response, 200);

            } else {
                $response['error'] = 'Erro interno ao salvar o arquivo. Por favor tente novamente.';
                return response()->json($response, 500);
            }
        } else {
            $response['error'] = $validator->errors();
            return response()->json($response, 422);
        }

    }
}
