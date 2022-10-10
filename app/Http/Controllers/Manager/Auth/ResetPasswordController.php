<?php

namespace App\Http\Controllers\Manager\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Rules\SafetyPassword;


class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/manager/home';
 
    

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest:usuario_manager');
    }

    /**
     * Display the password reset view for the given token.
     *
     * If no token is present, display the link request form.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  string|null $token
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showResetForm(Request $request, $token = null)
    {
       /* return view('manager.auth.passwords.reset')->with(
            ['token' => $token, 'email' => $request->email]
        );*/
        $data = \Cuenta::configSegunDominio();
        $data['token'] = $token;
        $data['email'] = '';
        return view('manager.auth.passwords.reset', $data);
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('usuario_manager');
    }

    /**
     * @return mixed
     */
    public function broker()
    {
        return Password::broker('usuario_manager');
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function reset(Request $request)
    {
        $this->validate($request, $this->rules(), $this->validationErrorMessages());

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $response = $this->broker()->reset(
            $this->credentials($request), function ($user, $password) {
                $this->resetPassword($user, $password);
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $response == Password::PASSWORD_RESET
                    ? $this->sendResetResponse($request->input('email'),$response)
                    : $this->sendResetFailedResponse($request, $response);
    }

    /**
     * Get the response for a successful password reset.
     *
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetResponse($email,$response)
    {
        \Log::info('respuesta exitosa');

        $rectangulo = asset('img/reportes/Rectangulo2.png');
        $logo = asset('img/reportes/logo_reporte.png');
        $to = $email;
        $subject = 'SIMPLE - Confirmación cambio contrseña usuario Manager';
        $message = '<div>
        <div style="background-image: url('.$rectangulo.'); height:521px;width:512px;top: 40px;z-index: -1; margin:0 auto;">
        <div style="text-indent:40px;line-height:0px"><img src="'.$logo.'"/></div>
          <br style="line-height: 5.1;">
          <h1 style="width: 397px;color: #373737;font-family: Roboto, sans-serif;font-size: 25px;font-weight: bold;text-indent:40px; ">Contraseña Actualizada </h1>
          <br>
          <div style="text-indent:40px;text-align:justify;font-family: Roboto, sans-serif;font-size: 16px;line-height: 24px;">Estimado usuario, junto con saludar tenemos el agrado </div>
          <div style="text-indent:40px;text-align:justify;font-family: Roboto, sans-serif;font-size: 16px;line-height: 24px;">de informar que su contraseña fue actualizada con éxito </div>
          <div style="text-indent:40px;text-align:justify;font-family: Roboto, sans-serif;font-size: 16px;line-height: 24px;">en SIMPLE. </div><br> 
          <div style="text-indent:40px;text-align:justify;font-family: Roboto, sans-serif;font-size: 16px;line-height: 24px;">Saluda atentamente,</div>
          <div style="text-indent:40px;text-align:justify;font-family: Roboto, sans-serif;font-size: 16px;line-height: 24px;"> <b>'.\Cuenta::cuentaSegunDominio()->nombre_largo.'</b></div>
          
         </p>
          </div>
      </div>';

        Mail::send('emails.send', ['content' => $message], function ($message) use ($subject, $to) {
            $message->subject($subject);
            $mail_from = env('MAIL_FROM_ADDRESS');
            if(empty($mail_from)) {
                $message->from(\Cuenta::cuentaSegunDominio()->nombre . '@' . env('APP_MAIN_DOMAIN', 'localhost'),\Cuenta::cuentaSegunDominio()->nombre_largo);
            } else {
                $message->from($mail_from);
            }
            if(empty(env('EMAIL_TEST')))
                $message->to($to);
            else{
                $destinatarios_test = explode(",",env('EMAIL_TEST'));
                $message->to($destinatarios_test);
            }
        });

        return redirect($this->redirectPath())
                            ->with('status', trans($response));
    }

    /**
     * Get the password reset validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => [
                'required',
                'min:8',
                'confirmed',
                new SafetyPassword
            ],
        ];
    }
    
}
