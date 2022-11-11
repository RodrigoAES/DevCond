<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
Use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use App\Models\User;
use App\Models\unit;

class AuthController extends Controller
{
    public function register(Request $request) {
        $response = ['error' => false];

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'cpf' => 'required|numeric|digits:11|unique:users,cpf',
            'password' => 'required|string|confirmed',
        ], [
            'required' => 'Campo obrigatório.',
            'string' => 'Campo deve conter um valor do tipo texto.',
            'email' => 'É necessario um endereço de e-mail válido.',
            'email.unique' => 'Endereço de e-mail já está sendo utilizado por outra conta.',
            'cpf.numeric' => 'É necessario um CPF válido para cadastro.',
            'cpf.digits' => 'É necessario um CPF válido para cadastro.',
            'cpf.unique' => 'CPF já está sendo utilizado por outra conta.',
            'password.confimed' => 'Senahs não coincidem.',
        ]);
        if($validator->fails()) {
            $response['error'] = $validator->errors();
            return response()->json($response, 422);
            exit;
        }

        $credentials = $validator->safe()->except('password');
        $credentials['password'] = Hash::make($validator->validated()['password']);
    
        $response['user'] = User::create($credentials);
        if($response['user']) {
            $response['token'] = Auth::attempt($validator->safe()->only('cpf', 'password'));
            if($response['token']) {
                return response()->json($response, 200);

            } else {
                $response['error'] = 'Ocorreu um erro durante o processo. Por favor tente novamente.';
                return response()->json($response, 500);
            }
        } else {
            $response['error'] = 'Ocorreu um erro durante o processo. Por favor tente novamente.';
            return response()->json($response, 500);
        }
    }

    public function login(Request $request) {
        $response = ['error' => false];

        $validator = Validator::make($request->all(), [
            'cpf' => 'required|numeric|digits:11',
            'password' => 'required'
        ], [
            'required' => 'Campo obrigatório.',
            'cpf.numeric' => 'CPF inválido.',
            'cpf.digits' => 'CPF inválido'
        ]);
        if($validator->fails()) {
            $response['error'] = $validator->errors();
            return response()->json($response, 422);
            exit;
        }

        $response['token'] = Auth::attempt($validator->validated());
        if($response['token']) {
            $user = Auth::user();
            $user->properties = Unit::where('user_id', $user->id)->get();

            $response['user'] = $user;
            return response()->json($response, 200);

        } else {
            $response['error'] = 'Usuário e ou senha incorretos.';
            return response()->json($response, 401);
        }
    }

    public function validateToken(Request $request) {
        $response = ['error' => false];

        $user = Auth::user();
        $user->properties = Unit::where('user_id', $user->id)->get();
        $response['user'] = $user;

        return response()->json($response, 200);
    }

    public function logout() {
        $response = ['error' => false];

        $response['token'] = Auth::logout();
        if($response['token'] === null){
            return response()->json($response, 200);
        } else {
            $response['error'] = 'Erro ao tentar deslogar no servidor. Por favor tente novamente.';
            return response()->json($response, 500);
        }

    }
}
