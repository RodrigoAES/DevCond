<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

use App\Models\Unit;
use App\Models\Billet;

class BilletController extends Controller
{
    public function getAll($unit) {
        $response = ['error' => false];
        $user = Auth::id();
        
        $property = Unit::where('user_id', $user)
            ->where('id', $unit)
        ->first();

        if($property) {
            foreach($property->billets as $billet) {
                $urlParams['file'] = $billet->id;

                $token = md5(time().rand(0,9999));
                $billet->hash = password_hash($token, PASSWORD_DEFAULT);
                $billet->save();
                $urlParams['token'] = $token;
                
                $billet->file_url = URL::temporarySignedRoute('file', now()->addMinutes(120), $urlParams);
            }

            $response['billets'] = $property->billets;
            return response()->json($response, 200);

        } else {
            $response['error'] = 'Propriedade inexistente.';
            return response()->json($response, 404);
        }
    }

    public function downloadFile($file, $token, Request $request) {
        if(! $request->hasValidSignature()) {
            abort(401);
        }
        
        $file = Billet::where('id', $file)->first();

        if($file) {
            if(password_verify($token, $file->hash)){
                return Storage::disk('local')->download("billets/$file->file_url");
            } else {
                abort(401);
            }
        } else {
            abort(404);
        }
    }
}
