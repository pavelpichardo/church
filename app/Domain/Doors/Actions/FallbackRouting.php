<?php

namespace App\Domain\Doors\Actions;

use App\Models\Person;
use App\Support\Enums\DoorAiInferenceStatus;
use App\Support\Enums\DoorCode;

class FallbackRouting
{
    /**
     * Deterministic minimal routing used when the Anthropic API is unavailable.
     * Five hardcoded rules covering the highest-impact events.
     *
     * @param  array<string, mixed>  $eventPayload
     * @param  array<string, mixed>  $personContext
     * @return array{decisions: array<int, array<string, mixed>>, audit: array<string, mixed>}
     */
    public function handle(
        string $eventType,
        array $eventPayload,
        Person $person,
        array $personContext,
        ?string $errorMessage = null,
    ): array {
        $decisions = match ($eventType) {
            'person.registered' => $this->bienvenidaVisitante($personContext),
            'attendance.missed_3' => $this->seguimientoInasistencia(),
            'person.health_status_reported' => $this->visitaEnfermo(),
            'birthday.upcoming_7d' => $this->cumpleaniosProximo(),
            'congress.created' => $this->congresoNuevo(),
            default => [],
        };

        $audit = [
            'triggering_event_type' => $eventType,
            'triggering_event_payload' => $eventPayload,
            'person_id' => $person->id,
            'model_used' => null,
            'prompt_tokens' => null,
            'cached_tokens' => null,
            'output_tokens' => null,
            'cost_usd' => null,
            'raw_response' => null,
            'decisions' => $decisions,
            'latency_ms' => 0,
            'status' => DoorAiInferenceStatus::FallbackUsed->value,
            'error_message' => $errorMessage,
        ];

        return ['decisions' => $decisions, 'audit' => $audit];
    }

    private function bienvenidaVisitante(array $ctx): array
    {
        if (($ctx['status'] ?? null) !== 'visitor') {
            return [];
        }
        return [[
            'door_code' => DoorCode::Bienvenida->value,
            'action' => 'create_referral',
            'category' => 'bienvenida_visitante',
            'priority' => 'normal',
            'confidence' => 1.0,
            'reasoning' => 'Fallback determinístico: visitante recién registrado.',
            'due_days' => 3,
        ]];
    }

    private function seguimientoInasistencia(): array
    {
        return [[
            'door_code' => DoorCode::Visitacion->value,
            'action' => 'create_referral',
            'category' => 'seguimiento_inasistencia',
            'priority' => 'high',
            'confidence' => 1.0,
            'reasoning' => 'Fallback determinístico: 3+ inasistencias consecutivas.',
            'due_days' => 7,
        ]];
    }

    private function visitaEnfermo(): array
    {
        return [
            [
                'door_code' => DoorCode::AtencionPastoral->value,
                'action' => 'create_referral',
                'category' => 'atencion_enfermo',
                'priority' => 'high',
                'confidence' => 1.0,
                'reasoning' => 'Fallback determinístico: estado de salud reportado.',
                'due_days' => 1,
            ],
            [
                'door_code' => DoorCode::Visitacion->value,
                'action' => 'create_referral',
                'category' => 'visita_enfermo',
                'priority' => 'high',
                'confidence' => 1.0,
                'reasoning' => 'Fallback determinístico: visita pastoral domiciliaria.',
                'due_days' => 1,
            ],
        ];
    }

    private function cumpleaniosProximo(): array
    {
        return [[
            'door_code' => DoorCode::Bienvenida->value,
            'action' => 'create_alert',
            'category' => 'cumpleanios_proximo',
            'priority' => 'low',
            'confidence' => 1.0,
            'reasoning' => 'Fallback determinístico: cumpleaños en los próximos 7 días.',
            'due_days' => 7,
        ]];
    }

    private function congresoNuevo(): array
    {
        return [[
            'door_code' => DoorCode::EventosCongresos->value,
            'action' => 'create_referral',
            'category' => 'preparacion_congreso',
            'priority' => 'normal',
            'confidence' => 1.0,
            'reasoning' => 'Fallback determinístico: nuevo congreso registrado.',
            'due_days' => 14,
        ]];
    }
}
