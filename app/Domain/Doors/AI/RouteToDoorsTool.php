<?php

namespace App\Domain\Doors\AI;

use App\Support\Enums\DoorCode;
use App\Support\Enums\DoorReferralPriority;

class RouteToDoorsTool
{
    public const NAME = 'route_to_doors';

    /**
     * Build the tool definition consumed by the Claude Messages API.
     *
     * @return array<string, mixed>
     */
    public static function definition(): array
    {
        $doorCodes = array_map(fn (DoorCode $c) => $c->value, DoorCode::cases());
        $priorities = array_map(fn (DoorReferralPriority $p) => $p->value, DoorReferralPriority::cases());

        return [
            'name' => self::NAME,
            'description' => 'Decide which puerta(s) deben atender este evento. Devuelve un array de decisiones — una entrada por cada puerta que deba actuar. Si no aplica ninguna puerta, devuelve un array vacío.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'decisions' => [
                        'type' => 'array',
                        'description' => 'Lista de decisiones de routing. Una entrada por cada puerta que debe actuar.',
                        'items' => [
                            'type' => 'object',
                            'properties' => [
                                'door_code' => [
                                    'type' => 'string',
                                    'enum' => $doorCodes,
                                    'description' => 'Código slug de la puerta destino.',
                                ],
                                'action' => [
                                    'type' => 'string',
                                    'enum' => ['create_referral', 'create_alert'],
                                    'description' => 'create_referral abre una derivación accionable. create_alert solo notifica.',
                                ],
                                'category' => [
                                    'type' => 'string',
                                    'description' => 'Categoría corta en español de la necesidad (ej. "bienvenida_visitante", "seguimiento_inasistencia").',
                                ],
                                'priority' => [
                                    'type' => 'string',
                                    'enum' => $priorities,
                                ],
                                'confidence' => [
                                    'type' => 'number',
                                    'minimum' => 0,
                                    'maximum' => 1,
                                    'description' => 'Confianza en esta decisión (0–1). Si <0.85, será marcada para revisión humana.',
                                ],
                                'reasoning' => [
                                    'type' => 'string',
                                    'description' => 'Explicación breve en español del porqué de esta decisión, citando datos específicos del miembro.',
                                ],
                                'due_days' => [
                                    'type' => 'integer',
                                    'minimum' => 0,
                                    'maximum' => 30,
                                    'description' => 'Días desde hoy para cumplir esta derivación (urgente=1, normal=3-7).',
                                ],
                            ],
                            'required' => ['door_code', 'action', 'category', 'priority', 'confidence', 'reasoning'],
                        ],
                    ],
                ],
                'required' => ['decisions'],
            ],
        ];
    }
}
