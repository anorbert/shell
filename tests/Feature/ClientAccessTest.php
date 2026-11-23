<?php
use App\Models\User;
test('administrator accounts cannot enter client trading modules',function(){$admin=User::factory()->create();$admin->forceFill(['role'=>'admin'])->save();$this->actingAs($admin)->get('/markets')->assertForbidden();$this->actingAs($admin)->get('/trading')->assertForbidden();$this->actingAs($admin)->get('/portfolio')->assertForbidden();});


test('unverified clients must verify their email before using trading modules', function () {
    $client = User::factory()->unverified()->create();

    $this->actingAs($client)->get('/markets')->assertRedirect('/verify-email');
    $this->actingAs($client)->get('/trading')->assertRedirect('/verify-email');
});
