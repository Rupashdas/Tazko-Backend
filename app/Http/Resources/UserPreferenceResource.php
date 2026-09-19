<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPreferenceResource extends JsonResource {
    public function toArray(Request $request): array {
        return [
            'palette'     => $this->palette,
            'appearance'  => $this->appearance,
            'timezone'    => $this->timezone,
            'week_start'  => $this->week_start,
            'time_format' => $this->time_format,
        ];
    }
}
