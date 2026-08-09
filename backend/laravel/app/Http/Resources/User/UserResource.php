<?php

namespace App\Http\Resources\User;

use App\Helpers\Language\LanguageConfig;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'is_admin' => $this->is_admin,
            'password_changed' => $this->password_changed,
            'selected_language' => $this->selected_language ? LanguageConfig::load($this->selected_language) : null,
            'created_at' => $this->created_at,

            'is_current_user' => $this->when($this->is_current_user ?? null, $this->is_current_user),
        ];
    }
}
