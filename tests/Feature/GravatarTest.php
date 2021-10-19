<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GravatarTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function userCanGenerateDefaultGravatarImageWhenNoEmailFound(){
        $user=User::factory()->create([
            'name'=>'Imran',
            'email'=>'yes@yes.com'
        ]);

        $gravatarUrl=$user->getAvatar();
        $this->assertEquals('https://www.gravatar.com/avatar/'.md5($user->email).'?s200&d=robohash', $gravatarUrl);
        //Causes a cURL SSL error on my laptop
//        $response = Http::get($user->getAvatar());
//        $this->assertTrue($response->successful());
    }
}
