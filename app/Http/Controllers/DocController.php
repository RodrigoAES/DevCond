<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Doc;
use App\Illuminate\Support\Facades\Storage;

class DocController extends Controller
{
    public function getAll() {
        $response = ['error' => false];

        $docs = Doc::all();
        if($docs) {
            foreach($docs as $doc) {
                $doc->file_url = url("storage/$doc->file_url");
            }
            $response['docs'] = $docs;
    
            return response()->json($response, 200);
        } else {
            $response['error'] = 'Erro ao consultar o sistema. Por  favor tente novamente.';
            return response()->json($response, 500);
        }
        

    }
}
