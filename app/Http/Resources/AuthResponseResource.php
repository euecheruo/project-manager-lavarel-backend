<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResponseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'user' => new UserResource($this['user']),
            'authorization' => [
                'token' => $this['access_token'],
                'refresh_token' => $this['refresh_token'],
                'type' => 'Bearer',
                'expires_in' => 3600,
            ]
        ];
    }
}
