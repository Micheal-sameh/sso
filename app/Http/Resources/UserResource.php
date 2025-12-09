<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'membership_code' => $this->membership_code,
            'email' => $this->email,
            'ar_token' => $this->whenLoaded('arToken', fn () => $this->arToken->{request()->project->name}),
        ];
    }
}
