<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

use App\Models\LostAndFound;

class LostAndFoundController extends Controller
{
    public function getAll() {
        $response = [
            'error' => false,
            'lost' => [],
            'founded' => [],
            'recovered' => []
        ];

        $data = LostAndFound::select()
            ->orderBy('created_at', 'DESC')
        ->get();
        if($data) {
            foreach($data as $item) {
                $item->created_at = date('d/m/Y', strtotime($item->created_at));
                $item->photo = asset("storage/lost-and-found/$item->photo");
                $item->user;

                if($item->status === 'LOST') {
                    $response['lost'][] = $item;
                }
                if($item->status === 'FOUNDED') {
                    $response['founded'][] = $item;
                }
                if($item->status === 'RECOVERED') {
                    $response['recovered'][] = $item;
                }
            }
            return response()->json($response, 200);

        } else {
            $response['error'] = 'Erro ao consultar o sistema. Por favor tente novamente.';
            return response()->json($response, 500);
        }
    }

    public function insert(Request $request) {
        $response = ['error' => false];

        $validator = Validator::make($request->all(), [
            'status' => 'required|string',
            'description' => 'required|string',
            'where' => 'required|string',
            'photo' => 'image|mimes:png,jpg'
        ]);

        if(! $validator->fails()) {
            $data = $validator->safe()->except('photo');

            $data['user_id'] = Auth::id();

            $url = Storage::disk('local')->put('public/lost-and-found', $validator->validated()['photo']);
            $data['photo'] = url('storage/'.implode('/', [explode('/', $url)[1], explode('/', $url)[2]]));
            
            $data['created_at'] = date('Y-m-d');

            $response['data'] = LostAndFound::create($data);
            if($response['data']) {
                return response()->json($response, 200);

            } else {
                $response['error'] = 'Erro ao criar registro no sistema. Por favor tente novamente.';
                return response()->json($response, 500);
            }
        } else {
            $response['error'] = $validator->errors();
            response()->json($response, 422); 
        }
    }

    public function update($id, Request $request) {
        $response = ['error' => false];
        if(is_string($request->input('status'))) {
            if($request->input('status')) {
                if(in_array($request->input('status'), ['LOST', 'FOUNDED', 'RECOVERED'])) {
                    $item = LostAndFound::where('user_id', Auth::id())
                        ->where('id', $id)
                    ->first();
                    if($item) {
                        if($item->update($request->only('status'))){
                            $response['data'] = $item;
                            return response()->json($response, 200);
    
                        } else {
                            $response['error'] = 'Erro durante o processo. por favor tente novamente.';
                            return response()->json($response, 500);
                        }
                    } else {
                        $response['error'] = 'Registro não encontrado.';
                        return response()->json($response, 404);
                    } 
                } else {
                    $response['error'] = ['status' => 'Valor não reconhecido pelo sistema.'];
                    return response()->json($response, 422);
                } 
            } else {
                $response['error'] = ['status' => 'Campo não enviado.'];
                return response()->json($response, 422);
            }    
        } else {
            $response['error'] = ['status' => 'Valor inválido (somente tipo texto)'];
            return response()->json($response, 422);
        } 
        
    }
}
