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
           'id' => $this->id,
           'name' => $this->name,
           'email' => $this->email,
           'role'  => $this->role,
           'phone' => $this->phone,
           'address' => $this->address,
           'profile_picture' => $this->profile_picture
            ? asset('storage/' . $this->profile_picture)
            : null,
           'created_at' => optional($this->created_at)->toDateTimeString(),
           'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }
}
