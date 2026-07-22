<?php

namespace App\Http\Resources\Doors;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoorAiInferenceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'triggering_event_type' => $this->triggering_event_type,
            'person_id' => $this->person_id,
            'model_used' => $this->model_used,
            'prompt_tokens' => $this->prompt_tokens,
            'cached_tokens' => $this->cached_tokens,
            'output_tokens' => $this->output_tokens,
            'cost_usd' => $this->cost_usd,
            'latency_ms' => $this->latency_ms,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'error_message' => $this->error_message,
            'decisions' => $this->decisions,
            'triggering_event_payload' => $this->when(
                $request->boolean('include_payload'),
                fn () => $this->triggering_event_payload,
            ),
            'raw_response' => $this->when(
                $request->boolean('include_raw'),
                fn () => $this->raw_response,
            ),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
