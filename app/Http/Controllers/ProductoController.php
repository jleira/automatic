<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;
use App\User;
use JWTAuthException;
use Auth;
use Storage;
use DB;
use Carbon\Carbon;
use GuzzleHttp\Client;

use Vansteen\Sendinblue\Facades\Sendinblue;

class ProductoController extends Controller
{
    public function crear(Request $request)
    {
        $this->validate($request, [
            'nombre_aplicacion' => 'required',
            'url_woocomerce' => 'required',
            'color_principal' => 'required',
            'color_secundario' => 'required',
                        
        ]);

        $user = JWTAuth::toUser(str_replace('Bearer ','',$request->header('Authorization')));        

        DB::table('productos')->insert(
            ['usuario_id' => $user->id, 
            'nombre' => $request->nombre_aplicacion,
            'url_woocomerce'=>$request->url_woocomerce,
            'color_principal'=>$request->color_principal,
            'color_secundario'=>$request->color_secundario,
            'estado'=>1]//esto 1 no aprobado
        );
        $config = Sendinblue::getConfiguration();
        $cliente = new Client();
        $apiInstance = new \SendinBlue\Client\Api\SMTPApi(
            // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
            // This is optional, `GuzzleHttp\Client` will be used as default.
          $cliente,
            $config
        );
         $sendSmtpEmail = new \SendinBlue\Client\Model\SendSmtpEmail();
         $sendSmtpEmail["sender"]=['name'=>'vt','email'=>'projectmanager@gmail.com'];
         $sendSmtpEmail["to"]=[['name'=>$user->name,'email'=>$user->email]];
         $sendSmtpEmail["subject"]='Creacion de aplicacion ';
         $sendSmtpEmail["htmlContent"]='<h1>Se envia el correo para el producto '.$request->nombre_aplicacion.'</h1>';
          
        try {
            $result = $apiInstance->sendTransacEmail($sendSmtpEmail);
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            return response()->json(['success' => false]);
        }
        

    }



}
