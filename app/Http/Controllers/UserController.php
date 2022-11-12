<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use App\Models\User;

class UserController extends Controller
{
    public function getUser($id) {
        $response = ['error' => false];

        $response['user'] = User::find($id);
        if($response['user']) {
            return response()->json($response, 200);

        } else {
            $response['error'] = 'Usuário inexistente.';
            return respoonse()->json($response, 404);
        }

    }

    public function updateUser(Request $request) {
        $response = ['error' => false];

        $validator = Validator::make($request->all(), [
            'name' => 'string',
            'email' => 'email|unique:users,email',
            'cpf' => 'numeric|digits:11',
            'newPassword' => 'string|min:4|confirmed',
            'password' => 'required|string',
        ], [
            'string' => 'Campo deve conter um valor do tipo texto.',
            'email' => 'É necessario um endereço de e-mail válido.',
            'cpf.numeric' => 'É necessario um CPF válido para cadastro.',
            'cpf.digits' => 'É necessario um CPF válido para cadastro.',
            'password.confimed' => 'Senahs não coincidem.',
        ]);
        if($validator->fails()) {
            $response['error'] = $validator->errors();
            return response()->json($response, 422);

        } elseif(count($validator->validated()) === 1) {
            $response['error'] = 'Nenhum campo enviado.';
            return response()->json($response, 422);
        }

        $user = User::find(Auth::id());

        $name = $validator->validated()['name'] ?? null;
        $password = $validator->validated()['password'] ?? null;
        $email = $validator->validated()['email'] ?? null;
        $cpf = $validator->validated()['cpf'] ?? null;
        $newPassword = $validator->validated()['newPassword'] ?? null;
 
        if(password_verify($password, $user->password)){
            if($name) {
                if($user->name !== $name) {
                    $user->name = $name;
                }
            }
            if($email) {
                if($user->email !== $email) {
                    $exists = User::where('email', $email)->first();
                    if(! $exists) {
                        $user->email = $email;
                    }
                }
            }
            if($cpf) {
                if($user->cpf != $cpf) {
                    $exists = User::where('cpf', $cpf)->first();
                    if(! $exists) {
                        $user->cpf = $cpf;
                    }
                }
            }
            if($newPassword) {
                $user->password = Hash::make($newPassword);
            }
            if($user->save()) {
                $response['user'] = $user;
                return response()->json($response, 200);

            } else {
                $response['error'] = 'Erro ao completar ação. Por favor tente novamente.';
                return response()->json($response, 500);
            }
        } else {
            $response['error'] = 'Acasso não autorizado.';
            return response()->json($response, 401);
        }
    }

    public function deleteUser(Request $request) {
        $response = ['error' => false];

        $validator = Validator::make($request->all(), [
            'password' => 'required|string'
        ], [
            'required' =>   'Campo senha é obrigatório.',
            'string' => 'Campo senha deve conter um valor do tipo texto.'
        ]);
        if($validator->fails()) {
            $response['error'] = $validator->errors();
            return response()->json($response, 422);
        }
    
        $user = User::find(Auth::id());
        if(password_verify($validator->validated()['password'], $user->password)) {
            $response['deleted'] = $user->delete();
            if($response['deleted']) {
                return response()->json($response, 200);

            } else {
                $response['error'] = 'Erro ao completar ação. Por favor tente novamente.';
                return response()->json($response, 500);
            }
        } else {
            $response['error'] = 'Senha incorreta.';
            return response()->json($response, 401);
        }
    }
}
