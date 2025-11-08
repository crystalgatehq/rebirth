<?php

namespace App\Http\Responses;

use Illuminate\Http\Response;
use Laravel\Fortify\Contracts\ConfirmPasswordViewResponse as ConfirmPasswordViewResponseContract;

class ConfirmPasswordViewResponse implements ConfirmPasswordViewResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        return $request->expectsJson()
                    ? response()->json(['two_factor' => ! $request->session()->get('auth.password_confirmed_at')])
                    : response()->view('auth.confirm-password');
    }
}