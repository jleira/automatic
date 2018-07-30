<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use JWTAuth;
use App\User;
use JWTAuthException;
use Auth;
use Storage;

class UserController extends Controller
{
    private $user;
    public function __construct(User $user){
        $this->user = $user;
    }
    public function register(Request $request){
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = $this->user->create([
          'name' => $request->name,
          'email' => $request->email,
          'password' => bcrypt($request->password)
        ]);
        return $this->login($request);
    }
    
    public function login(Request $request){
        $credentials = $request->only('email', 'password');
        $token = null;
        try {
           if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['invalid_email_or_password'], 422);
           }
        } catch (JWTAuthException $e) {
            return response()->json(['failed_to_create_token'], 500);
        }
        $datos['token']=$token;
        $datos['user']=JWTAuth::toUser($token);
        return response($datos,200);
    }
    public function getAuthUser(Request $request){

        $user = JWTAuth::toUser($request->token);
        return response()->json(['result' => $user]);
    }
    public function prueba(Request $request){

        $user = JWTAuth::toUser(str_replace('Bearer ','',$request->header('Authorization')));
        
            Storage::disk('local')->makeDirectory($user->id.'/'.$request->mascota_id);
            $consecutivo=count(Storage::disk('local')->files($user->id.'/'.$request->mascota_id));
            $consecutivo=$consecutivo+1;
         $path = $request->file('files')->storeAs(
            $user->id.'/'.$request->mascota_id, $request->mascota_name.'_'.$request->mascota_id.'_'.$consecutivo
        );
         return response()->json(['archivo creado exitosamente con el consecutivo' => $user]);
    }    
}
