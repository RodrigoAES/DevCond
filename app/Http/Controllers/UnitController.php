<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use App\Models\User;
use App\Models\Unit;
use App\Models\UnitResident;
use App\Models\UnitVehicle;
use App\Models\UnitPet;

class UnitController extends Controller
{
    public function getInfo($id) {
        $response = ['error' => false];

        $authorization = UnitResident::where('user_id', Auth::id())
        ->where('unit_id', $id)
        ->first() ?? 
        Unit::where('user_id', Auth::id())
        ->where('id', $id)
        ->first();

        if($authorization) {
            $unit = $authorization->unit ?? $authorization;

            foreach($unit->residents as $resident) {
                $resident->birthdate = date('d/m/Y', strtotime($resident->birthdate));
            }
            
            $unit->vehicles;
            $unit->pets;

            $response['unit'] = $unit;
            return response()->json($response, 200);

        } else {
            $response['error'] = 'Acesso não authorizado.';
            return response()->json($response, 401);
        }
    }

    public function addResident($id, Request $request) {
        $response = ['error' => false];

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'birthdate' => 'required|date',
            'user_id' => 'required|numeric|unique:unit_residents'
        ]);
        if($validator->fails()) {
            $response['error'] = $validator->errors();
            return response()->json($response, 422);
        }

        $unit = Unit::where('user_id', Auth::id())
            ->where('id', $id)
        ->first();

        if($unit) {
            $user = User::where('id', $validator->validated()['user_id'])->first();
            if($user) {
                $data = $validator->validated();
                $data['unit_id'] = $id;

                $response['resident'] = UnitResident::create($data);

                if($response['resident']) {
                    return response()->json($response, 200);

                } else {
                    $response['error'] = 'Erro interno ao criar registro. Por favor tente novamente.';
                    return response()->json($response, 500);
                }
            } else {
                $response['error'] = 'Usuário inexistente.';
                return response()->json($response, 422);
            }
        } else {
            $response['error'] = 'Propriedade não encontrada.';
            return response()->json($response, 422);
        }
    }

    public function removeResident($id, Request $request) {
        $response = ['error' => false];

        $validator =  Validator::make($request->all(),[
            'user_id' => 'required|numeric'
        ]);
        if($validator->fails()) {
            $response['error'] =  $validator->errors();
            return response()->json($response, 422);
        }

        $authorization = Unit::where('user_id', Auth::id())
            ->where('id', $id)
        ->first();
        if($authorization) {
            $resident =  UnitResident::where('user_id', $validator->validated()['user_id'])
                ->where('unit_id', $id)
            ->first();
            if($resident) {
                $response['deleted'] = $resident->delete();
                if($response['deleted']) {
                    return response()->json($response, 200);

                } else {
                    $response['error'] = 'Erro interno ao tentar ação. Por favor tente novamente.';
                    return response()->json($response, 200);
                }
            } else {
                $response['error'] = 'Morador inexistente.';
                return response()->json($response, 404);
            }
            
        } else {
            $response['error'] = 'Acesso não autorizado para ação.';
            return response()->json($response, 401);
        }
    }

    public function addVehicle($id, Request $request) {
        $response = ['error' => false];

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'color' => 'required|string',
            'plate' => 'required|string|unique:unit_vehicles',
        ]);
        if($validator->fails()) {
            $response['error'] = $validator->errors();
            return response()->json($response, 422);
            exit;
        }

        $authorization = UnitResident::where('user_id', Auth::id())
            ->where('unit_id', $id)
            ->first() ?? 
        Unit::where('user_id', Auth::id())
            ->where('id', $id)
            ->first();

        if($authorization) {
            $unit = $authorization->unit ?? $authorization;

            $data = $validator->validated();
            $data['unit_id'] = $id;

            $response['vehicle'] = UnitVehicle::create($data);
            if($response['vehicle']) {
                return response()->json($response, 200);

            } else {
                $response['error'] = 'Erro interno ao criar registro. Por favor tente novamente.';
                return response()->json($response, 500);
            }
        }
    }

    public function removeVehicle($id, Request $request) {
        $response = ['error' => false];

        $validator = Validator::make($request->all(), [
            'vehicle_id' => 'required|numeric' 
        ]);
        if($validator->fails()) {
            $response['error'] = $validator->errors();
            return response()->json($response, 422);
        }

        $authorization = UnitResident::where('user_id', Auth::id())
            ->where('unit_id', $id)
            ->first() ??
        Unit::where('user_id', Auth::id())
            ->where('id', $id)
            ->first();

        if($authorization) {
            $unit = $authorization->unit ?? $authorization;

            $vehicle = UnitVehicle::where('unit_id', $id)
                ->where('id', $validator->validated()['vehicle_id'])
            ->first();
            if($vehicle) {
                $response['deleted'] = $vehicle->delete();
                if($response['deleted']) {
                    return response()->json($response, 200);

                } else {
                    $response['error'] = 'Erro interno ao tentar realizar ação. Por favor tente novamente.';
                    return response()->json($response, 500);
                }
            } else {
                $response['error'] = 'Veiculo inexistente.';
                return response()->json($response, 404);
            }
            
        }
    }

    public function addPet($id, Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'breed' => 'required|string'
        ]);
        if($validator->fails()) {
            $response['error'] = $validator->errors();
            return response()->json($response, 422);
        }

        $authorization = UnitResident::where('user_id', Auth::id())
            ->where('unit_id', $id)
            ->first() ?? 
        Unit::where('user_id', Auth::id())
            ->where('id', $id)
            ->first();

        if($authorization) {
            $unit = $authorization->unit ?? $authorization;

            $data = $validator->validated();
            $data['unit_id'] = $unit->id;

            $response['pet'] = UnitPet::create($data);
            if($response['pet']) {
                return response()->json($response, 200);

            } else {
                $response['error'] = 'Erro interno ao criar registro. Por favor tente novamente.';
                return response()->json($response, 500);
            }
        }
    }

    public function removePet($id, Request $request) {
        $response = ['error' => false];

        $validator = Validator::make($request->all(), [
            'pet_id' => 'required|numeric'
        ]);

        $authorization = UnitResident::where('user_id', Auth::id())
            ->where('unit_id', $id)
            ->first() ??
        Unit::where('user_id', Auth::id())
            ->where('id', $id)
            ->first();

        if($authorization) {
            $unit = $authorization->unit ?? $authorization;

            $pet = UnitPet::where('unit_id', $unit->id)
                ->where('id', $validator->validated()['pet_id'])
            ->first();
            if($pet) {
                $response['deleted'] = $pet->delete();

                if($response['deleted']) {
                    return response()->json($response, 200);

                } else {
                    $response['error'] = 'Erro interno ao tentar realizar ação. Por favor tente novamente.';
                    return response()->json($response, 500);
                }
            } else {
                $response['error'] = 'Pet inexistente.';
                return response()->json($response, 404);
            }
        }
    }
}
