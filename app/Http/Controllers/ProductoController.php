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
        $token=str_replace('Bearer ','',$request->header('Authorization'));
        DB::table('productos')->insert(
            ['usuario_id' => $user->id, 
            'nombre' => $request->nombre_aplicacion,
            'url_woocomerce'=>$request->url_woocomerce,
            'color_principal'=>$request->color_principal,
            'color_secundario'=>$request->color_secundario,
            'estado'=>1]//esto 1 no aprobado
        );
        $id_p=DB::table('productos')->max('id')->where('usuario_id',$user->id);
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
         $sendSmtpEmail["htmlContent"]='<h1>Confirmar variables para la aplicacion '.$request->nombre_aplicacion.'</h1>
         <br>
         <table style="width:100%">
  <tr>
    <th>key</th>
    <th>valor</th> 
  </tr>
  <tr>
    <td>Nombre aplicacicion</td>
    <td>'.$request->nombre_aplicacion.'</td> 
  </tr>
  <tr>
    <td>url woocomerce</td>
    <td>'.$request->url_woocomerce.'</td> 
  </tr>

  <tr>
  <td>Color principal</td>
  <td>'.$request->color_principal.'</td> 
</tr>

<tr>
<td>Color secundario</td>
<td>'.$request->color_secundario.'</td> 
</tr>
<tr>
<td>Comentario</td>
<td>'.$request->color_comentario.'</td> 
</tr>
</table>
<br>

<br>
<a href="http://167.114.185.216/automatic/public/api/productos/confirmar/'.$id_p.'/'.$token.'">Aceptar</a>';
        try {
            $result = $apiInstance->sendTransacEmail($sendSmtpEmail);
            return response(200)->json(['success' => true]);
        } catch (Exception $e) {
            return response(500)->json(['success' => false]);
        }
        

    }



}
