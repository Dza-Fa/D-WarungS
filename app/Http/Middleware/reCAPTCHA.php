<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class reCAPTCHA
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('post') && in_array($request->route()?->getName(), ['login', 'register'])) {
            $recaptchaResponse = $request->input('g-recaptcha-response');
            if (empty($recaptchaResponse)) {
                return back()->withErrors(['recaptcha' => 'reCAPTCHA wajib diisi.']);
            }

            $secret = env('RECAPTCHA_SECRET_KEY');
            $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secret}&response={$recaptchaResponse}&remoteip=".$request->ip());
            $responseKeys = json_decode($response, true);

            if (intval($responseKeys['success']) !== 1) {
                return back()->withErrors(['recaptcha' => 'reCAPTCHA tidak valid.']);
            }
        }

        return $next($request);
    }
}
