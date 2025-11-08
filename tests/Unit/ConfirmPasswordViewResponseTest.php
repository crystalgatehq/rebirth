<?php

namespace Tests\Unit;

use App\Http\Responses\ConfirmPasswordViewResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\ConfirmPasswordViewResponse as ConfirmPasswordViewResponseContract;
use Tests\TestCase;

class ConfirmPasswordViewResponseTest extends TestCase
{
    public function test_confirm_password_view_response_can_be_resolved(): void
    {
        $this->assertInstanceOf(
            ConfirmPasswordViewResponseContract::class,
            app(ConfirmPasswordViewResponseContract::class)
        );
    }

    public function test_confirm_password_view_response_implements_correct_interface(): void
    {
        $response = new ConfirmPasswordViewResponse();
        
        $this->assertInstanceOf(ConfirmPasswordViewResponseContract::class, $response);
    }
}