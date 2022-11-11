<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Wall;
use App\Models\WallLike;

class WallController extends Controller
{
    public function getAll() {
        $response = ['error' => false, 'list' => []];
        
        $user = Auth::user();

        $walls = Wall::all();
        if($walls) {
            foreach($walls as $wall) {
                $wall->liked = false;
                foreach($wall->likes as $like) {
                    if($like->user_id === $user->id){
                        $wall->liked = true;
                    } 
                }
            }
            $response['list'] = $walls;
    
            return response()->json($response, 200);

        } else {
            $response['error'] = 'Erro durante consulta ao sistema. Por favor tente novamente.';
            return response()->json($response, 500);
        } 
    }

    public function like($id) {
        $response = ['error' => false, 'wall' => $id];

        $user = Auth::id();

        $liked = WallLike::where('wall_id', $id)
            ->where('user_id', $user)
        ->first();

        if($liked) {
            $response['liked'] = $liked->delete() ? false : true;
        } else {
            $like = new WallLike();
            $like->user_id = $user;
            $like->wall_id = $id;
            $response['liked'] = $like->save();
        }

        $response['likes'] = WallLike::where('wall_id', $id)->count();

        return response()->json($response, 200);
    }
}
