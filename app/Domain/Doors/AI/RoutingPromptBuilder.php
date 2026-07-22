<?php

namespace App\Domain\Doors\AI;

use App\Models\Door;

class RoutingPromptBuilder
{
    /**
     * Build the system prompt (as an array of content blocks) — stable across requests.
     * The last block carries cache_control so tools + system get cached together.
     *
     * @return array<int, array<string, mixed>>
     */
    public function buildSystem(): array
    {
        $doors = Door::with(['rules' => fn ($q) => $q->where('is_enabled', true)->orderBy('name')])
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        $doorList = $doors->map(function (Door $d) {
            return "  {$d->order}. {$d->code->value} — {$d->name}\n     {$d->description}";
        })->implode("\n");

        $rulesByDoor = $doors->map(function (Door $d) {
            if ($d->rules->isEmpty()) {
                return null;
            }
            $rules = $d->rules->map(function ($r) {
                $events = ! empty($r->event_types) ? ' [eventos: ' . implode(', ', $r->event_types) . ']' : '';
                return "  - \"{$r->name}\" (prioridad sugerida: {$r->priority_hint?->value}){$events}\n    {$r->description}";
            })->implode("\n");
            return "[{$d->code->value} — {$d->name}]\n{$rules}";
        })->filter()->implode("\n\n");

        $intro = <<<TXT
Eres el motor de routing pastoral del sistema de gestión de la Primera Iglesia del Nazareno "Ven y Ve" (Columbus, OH).

Tu trabajo: analizar eventos sobre miembros y visitantes, y decidir cuál(es) de las 9 PUERTAS deben atender al miembro, con qué prioridad, y con qué nivel de confianza.

Las puertas son equipos de servicio con un fin específico. Cada puerta tiene un líder, voluntarios, y una lista de "necesidades" (derivaciones) que debe atender.

LAS 9 PUERTAS:
{$doorList}

REGLAS ACTIVAS por puerta (escritas en lenguaje natural por el pastor o administrador):

{$rulesByDoor}

INSTRUCCIONES DE DECISIÓN:
1. Analiza el evento + el contexto completo del miembro (asistencia, etapa de membresía, célula, historial, notas pastorales).
2. Aplica las reglas activas. Una persona puede activar reglas de varias puertas — devuelve una decisión por cada puerta relevante.
3. Asigna 'confidence' entre 0 y 1:
   - 0.85–1.00 → derivación se crea automáticamente.
   - <0.85 → derivación queda en estado "pending_review" para que el líder de la puerta apruebe.
4. Asigna 'priority' según la urgencia pastoral real:
   - urgent: hospitalización, crisis aguda
   - high: inasistencia prolongada, enfermedad
   - normal: bienvenida, discipulado, eventos
   - low: cumpleaños, intercesión rutinaria
5. 'category' debe ser un slug corto en español describiendo la necesidad (ej. "visita_enfermo", "bienvenida_visitante", "seguimiento_inasistencia").
6. 'reasoning' debe citar datos específicos del contexto que motivan la decisión.
7. Si ninguna regla aplica o no hay acción pastoral clara, devuelve un array `decisions` vacío.

Responde SIEMPRE invocando la herramienta `route_to_doors` con tu decisión estructurada. No respondas con texto libre.
TXT;

        return [
            [
                'type' => 'text',
                'text' => $intro,
                'cache_control' => ['type' => 'ephemeral'],
            ],
        ];
    }

    /**
     * Build the user message with the event + person context.
     *
     * @param  array<string, mixed>  $personContext
     * @param  array<string, mixed>  $eventPayload
     */
    public function buildUserMessage(string $eventType, array $eventPayload, array $personContext): string
    {
        $today = now()->toDateString();
        $contextJson = json_encode($personContext, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $payloadJson = json_encode($eventPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return <<<TXT
Fecha actual: {$today}

EVENTO DISPARADO:
Tipo: {$eventType}
Payload: {$payloadJson}

CONTEXTO COMPLETO DE LA PERSONA:
{$contextJson}

Analiza este evento contra las reglas activas y decide qué puerta(s) deben actuar. Invoca `route_to_doors` con tus decisiones.
TXT;
    }
}
