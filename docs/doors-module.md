# Módulo Puertas (Doors)

> Sistema de routing pastoral asistido por IA para la Primera Iglesia del Nazareno "Ven y Ve" (Columbus, OH).

---

## 1. Visión

Una **puerta** es un equipo de servicio con un propósito pastoral específico. La iglesia se organiza en células (grupos de crecimiento en hogares) y **9 puertas** (ministerios especializados). El líder de célula detecta necesidades en sus miembros y los **deriva** a la puerta correspondiente; un motor de IA observa eventos del sistema (registro de visitantes, inasistencias, cambios de etapa, cumpleaños…) y propone derivaciones automáticas según reglas escritas en lenguaje natural por el pastor o administrador.

El objetivo: que ninguna persona se pierda. Que cada visitante reciba bienvenida, cada miembro enfermo reciba visita, cada inasistencia prolongada gatille seguimiento, cada congreso tenga equipo organizativo.

## 2. Las 9 Puertas

| # | Código | Nombre | Propósito |
|---|---|---|---|
| 1 | `intercesion_profetica` | Intercesión Profética | Oración intercesora, cobertura espiritual |
| 2 | `bienvenida` | Bienvenida | Primer contacto con visitantes y reincorporados |
| 3 | `atencion_pastoral` | Atención Pastoral | Consejería, crisis emocional/espiritual/familiar |
| 4 | `retiros` | Retiros | Organización de retiros y encuentros |
| 5 | `discipulado` | Discipulado | Formación, asignación a discipulados |
| 6 | `visitacion` | Visitación | Visitas a enfermos, ausentes, hospitalizados |
| 7 | `comunicacion_teatro` | Comunicación y Teatro | Medios, redes, ministerio creativo |
| 8 | `admin_finanzas_consolidacion` | Admin. Finanzas y Consolidación | Administración, seguimiento financiero |
| 9 | `eventos_congresos` | Eventos / Congresos | Eventos especiales, congresos |

Las 9 son **fijas e inmutables** — están sembradas (`DoorSeeder`) y no se pueden crear ni borrar desde la UI. El administrador puede editar descripción, color e ícono, y gestionar reglas/voluntarios/actividades.

## 3. Conceptos centrales

### Puerta (`doors`)
Identidad fija con código (slug), nombre, orden, descripción, color, ícono.

### Voluntarios (`door_members`)
Personas asignadas a una puerta con un rol: `leader`, `co_leader`, `volunteer`. **Una persona puede servir en varias puertas a la vez** (no hay restricción de unicidad por persona).

### Actividades (`door_activities`)
Eventos organizados por la puerta: reuniones de equipo, jornadas de visitación, ensayos del ministerio creativo. Entidad propia (no reutiliza el módulo `Event`) — tiene su propio control de asistencia vía `door_activity_participants`.

### Necesidades / Derivaciones (`door_referrals`) ⭐
**El corazón del sistema.** Una derivación es una unidad de trabajo asignada a una puerta, dirigida a una persona, con un motivo, prioridad y estado. Toda "necesidad" que aparece en el panel de una puerta es una `DoorReferral`.

| Campo | Significado |
|---|---|
| `source` | Origen: `manual` · `cell` (líder de célula) · `rule` (motor de IA / fallback) · `self` |
| `category` | Slug en español (ej. `bienvenida_visitante`, `visita_enfermo`) |
| `priority` | `low` · `normal` · `high` · `urgent` |
| `status` | `pending` · `in_progress` · `pending_review` (IA con baja confianza) · `completed` · `cancelled` |
| `assigned_to_person_id` | Voluntario asignado dentro de la puerta |
| `ai_inference_id` | FK al audit row si fue generada por IA |
| `ai_confidence` | 0.00–1.00 (visible junto al badge "🤖 IA") |
| `ai_reasoning` | Explicación textual de la IA |
| `due_date` | Fecha límite sugerida |

### Reglas (`door_rules`)
Reglas escritas en **lenguaje natural en español** por el administrador. No son condiciones JSON evaluadas por código — son descripciones que el motor de IA interpreta. Cada regla pertenece a una puerta y tiene:
- `name` — etiqueta corta
- `description` — texto narrativo en español
- `event_types` — opcional, filtra qué eventos considera (vacío = todos)
- `priority_hint` — sugerencia de prioridad
- `is_enabled` — toggle activa/inactiva

### Alertas (`door_alerts`)
Notificaciones para el equipo de la puerta. Cada acción del motor (sea referral o alert puro) genera una entrada con severidad mapeada desde prioridad.

### Auditoría de IA (`door_ai_inferences`)
**Toda llamada al motor queda registrada** — éxito, fallo o fallback. Guarda: evento disparador, persona, modelo, tokens (entrada + caché + salida), costo USD, latencia ms, respuesta cruda, decisiones parseadas, status y mensaje de error.

## 4. Flujo end-to-end

### Ejemplo: visitante nuevo se registra
```
1. Recepcionista crea Person (status=visitor) desde el panel
   → CreatePerson::handle() emite event(new PersonRegistered($person))

2. Laravel 12 auto-discovery dispara QueueDoorRoutingEvaluation::handle
   (registrado contra el interface RoutingTriggerEvent)
   → EvaluateDoorRoutingForEvent::dispatch(...) → cola 'doors-ai'

3. Worker procesa el job (async):
   ├─ BuildPersonContext arma snapshot completo de la persona
   ├─ Si DOORS_AI_ENABLED:
   │   ├─ InferDoorRouting llama a Claude Haiku 4.5 con tool_use forzado
   │   ├─ Claude devuelve decisión estructurada via 'route_to_doors'
   │   └─ → DoorReferral creada con confidence, reasoning, ai_inference_id
   ├─ Si AI deshabilitado o API falla:
   │   ├─ FallbackRouting aplica regla determinística mínima
   │   └─ → DoorReferral creada con status='fallback_used' en audit

4. ApplyRoutingDecisions persiste todo en transacción:
   ├─ DoorAiInference (audit row)
   ├─ DoorReferral (status='pending' si conf≥0.85, 'pending_review' si <0.85)
   └─ DoorAlert (severity mapeada de prioridad)

5. Líder de Bienvenida ve la nueva necesidad en /admin/doors/2
   → Asigna voluntario → estado 'in_progress' → contacto realizado → 'completed'
```

## 5. Motor de IA

### Modelo
`claude-haiku-4-5` (alias) — rápido (~1s), barato (~$0.002/evento con caché), excelente para tareas de clasificación/routing.

### Prompt structure
- **System prompt** (cacheado con `cache_control: ephemeral`) — dinámico desde DB:
  - Identidad y rol del motor
  - Las 9 puertas con descripción
  - Reglas activas agrupadas por puerta (texto en español)
  - Instrucciones de decisión (confidence thresholds, prioridades, etc.)
- **User message** (variable por call) — fecha actual + evento + payload + contexto completo de la persona como JSON.
- **Tool forzado** — `route_to_doors` via `tool_choice: {type: 'tool', name: 'route_to_doors'}` — Claude **debe** devolver JSON estructurado.

### Output schema
```json
{
  "decisions": [
    {
      "door_code": "bienvenida",
      "action": "create_referral",
      "category": "bienvenida_visitante",
      "priority": "normal",
      "confidence": 0.92,
      "reasoning": "Juan se registró hoy como visitante invitado por un miembro...",
      "due_days": 3
    }
  ]
}
```

### Threshold de confianza
- `confidence ≥ 0.85` → referral creada con `status=pending` (accionable inmediatamente)
- `confidence < 0.85` → referral creada con `status=pending_review` (aparece en bandeja para que el líder de puerta apruebe o rechace)

Configurable vía `DOORS_AI_CONFIDENCE_THRESHOLD`.

### Fallback determinístico
Si la API de Anthropic falla (timeout, rate limit, key inválida) o `DOORS_AI_ENABLED=false`, el job cae automáticamente a `FallbackRouting`. Cubre 5 eventos críticos hardcoded:

| Evento | Acción |
|---|---|
| `person.registered` (status=visitor) | → Puerta 2 Bienvenida, prioridad normal |
| `attendance.missed_3` | → Puerta 6 Visitación, prioridad alta |
| `person.health_status_reported` | → Puertas 3+6, prioridad alta |
| `birthday.upcoming_7d` | → Puerta 2 Bienvenida, alerta low |
| `congress.created` | → Puerta 9, prioridad normal |

Las referrals generadas por fallback se marcan `status='fallback_used'` en el audit row, distinguibles de las generadas por IA.

## 6. Cómo escribir reglas

Una regla bien escrita:

```
Nombre: "Inasistencia prolongada"
Eventos: attendance.missed_3
Prioridad: high

Descripción:
Miembros activos que falten a 3 o más reuniones consecutivas
deben recibir una visita en su casa. Si la persona vive sola
o tiene más de 65 años, prioridad alta.
```

**Buenas prácticas:**
- Escribe condiciones en español natural — la IA las interpreta semánticamente.
- Sé específico con plazos y prioridades (la IA los traduce a `due_days` y `priority`).
- Menciona contexto relevante del miembro (edad, situación) si afecta la decisión.
- Una regla puede aplicar a múltiples tipos de eventos (deja `event_types` vacío para aplicar a todos).

**Pruébala antes de activarla:** en la tab Reglas, botón **"🧪 Probar con persona…"** abre un dry-run que ejecuta la regla contra una persona real sin crear datos. Útil para validar interpretación y prioridad antes de exponer la regla en producción.

## 7. Eventos disparadores

10 eventos de dominio implementan `App\Events\Contracts\RoutingTriggerEvent`. Un listener auto-descubierto (`QueueDoorRoutingEvaluation`) los enruta al pipeline de IA. El catálogo está centralizado en `App\Domain\Doors\RoutingEventCatalog` (alimenta las sugerencias del editor de reglas).

| Evento | Slug | Cuándo se emite | Origen |
|---|---|---|---|
| `PersonRegistered` | `person.registered` | Crear persona | `CreatePerson::handle` |
| `CellMemberAdded` | `cell_member.added` | Agregar miembro a célula | `CellController::addMember` + `CellShow::addMember` (panel) |
| `MembershipStageAdvanced` | `membership_stage.advanced` | Avanzar etapa de membresía | `AdvanceMembershipStage::handle` |
| `CongressCreated` | `congress.created` | Crear evento tipo congreso | `CreateEvent::handle` |
| `PersonHealthStatusReported` | `person.health_status_reported` | Acción rápida "Reportar enfermo" en el perfil | `PersonShow::quickAction('health')` |
| `MissedAttendanceDetected` | `attendance.missed_3` | Detección diaria automática | `DetectMissedAttendanceJob` (07:00) |
| `BirthdayUpcoming` | `birthday.upcoming_7d` | Detección diaria automática | `DetectUpcomingBirthdaysJob` (06:00) |
| `PersonReturnedAfterAbsence` | `person.returned_after_absence` | Asistencia registrada tras 2+ meses de ausencia, o acción rápida "Regresó" | `RecordAttendance::handle` / `PersonShow::quickAction('returned')` |
| `PersonNoteAdded` | `person.note_added` | Nota libre agregada en el perfil (la nota va en el payload) | `PersonShow::addNote` |
| `PersonContactFailed` | `person.contact_failed` | Acción rápida "No se pudo contactar" | `PersonShow::quickAction('contact_failed')` |

### Notas y seguimiento (CRM) en el perfil de la persona

El perfil (`/admin/people/{id}`) incluye en la columna derecha una bitácora pastoral estilo CRM:
- **Editor WYSIWYG** (Trix, cargado vía `@assets` desde CDN) para notas con formato.
- **Acciones rápidas**: "Reportar enfermo", "No se pudo contactar", "Regresó a la iglesia" — cada una registra una nota tipo `quick_action` Y dispara su evento de routing.
- **Bitácora cronológica** de todas las notas con autor y fecha.
- **Cada nota libre dispara `person.note_added`**, que envía la nota + perfil completo a la IA para evaluar si recomienda una puerta. Tabla: `person_notes`. `BuildPersonContext` incluye las últimas 10 notas (sin HTML) en el contexto que ve la IA.

## 8. Pantallas del panel

Todas bajo `/admin/doors`, middleware `auth` + `active`.

### `/admin/doors` — Grid de puertas
- 9 cards con color band, número, nombre, descripción, líder, counts
- Strip superior con totales globales (abiertas, pendientes de revisión, alertas)
- Acceso rápido a bandeja IA y log de auditoría

### `/admin/doors/{door}` — Detalle con 5 tabs
- **Equipo** — voluntarios activos agrupados por rol; asignar/retirar
- **Actividades** — listado read-only (CRUD por API en `app/Http/Controllers/Api/DoorActivityController.php`)
- **Necesidades** — referrals abiertas con badge IA + reasoning expandido; acciones: comenzar / completar / aprobar / rechazar
- **Reglas** — CRUD natural-language, toggle activa/inactiva, botón "🧪 Probar con persona…" para dry-run
- **Reportes** — abiertas vs completadas, IA vs manuales, costo IA acumulado para esta puerta

### `/admin/doors/referrals/pending` — Bandeja de revisión
Todas las sugerencias con `status='pending_review'` (confianza <0.85). Botones Aprobar (→ `pending`) / Rechazar (→ `cancelled`) con confirmación.

### `/admin/doors/ai/log` — Auditoría de inferencias
Log paginado con cards de totales 30 días (count, costo USD, success vs fallback). Filtros por status/event. Rows expandibles muestran decisiones completas con reasoning de Claude.

## 9. API REST

29 rutas bajo `/api/v1/` (Sanctum token auth):

```
GET    /api/v1/doors                          — listar las 9
GET    /api/v1/doors/{door}                   — detalle
PATCH  /api/v1/doors/{door}                   — editar descripción/color/ícono

GET    /api/v1/doors/{door}/members           — voluntarios
POST   /api/v1/doors/{door}/members           — asignar
PATCH  /api/v1/doors/{door}/members/{member}
DELETE /api/v1/doors/{door}/members/{member}  — retirar (left_at)

GET    /api/v1/doors/{door}/activities
POST   /api/v1/doors/{door}/activities
GET    /api/v1/doors/{door}/activities/{a}
PATCH  /api/v1/doors/{door}/activities/{a}
DELETE /api/v1/doors/{door}/activities/{a}
POST   /api/v1/doors/{door}/activities/{a}/attendance

GET    /api/v1/doors/{door}/rules             — reglas de esta puerta
POST   /api/v1/doors/{door}/rules
GET    /api/v1/doors/{door}/rules/{rule}
PATCH  /api/v1/doors/{door}/rules/{rule}
DELETE /api/v1/doors/{door}/rules/{rule}
POST   /api/v1/doors/{door}/rules/{rule}/toggle

GET    /api/v1/doors/{door}/alerts
POST   /api/v1/doors/{door}/alerts/{alert}/read
POST   /api/v1/doors/{door}/alerts/read-all

GET    /api/v1/referrals                      — global, filtros por door/person/status/priority
POST   /api/v1/referrals                      — crear manualmente
GET    /api/v1/referrals/{referral}
PATCH  /api/v1/referrals/{referral}
DELETE /api/v1/referrals/{referral}
POST   /api/v1/referrals/{referral}/assign
POST   /api/v1/referrals/{referral}/status    — cambio de estado
```

Todas autorizan vía permisos Spatie (ver §11).

## 10. Arquitectura técnica

```
┌─────────────────────────────────────────────────────────────────┐
│  Evento de dominio (PersonRegistered, MissedAttendanceDetected) │
│  emite event() → Laravel auto-discovery                         │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│  App\Listeners\QueueDoorRoutingEvaluation                       │
│  handle(RoutingTriggerEvent $e):                                │
│    EvaluateDoorRoutingForEvent::dispatch(slug, payload, id)     │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ▼  cola 'doors-ai'
┌─────────────────────────────────────────────────────────────────┐
│  App\Jobs\EvaluateDoorRoutingForEvent                           │
│    1. BuildPersonContext($person) → snapshot completo           │
│    2. Try: InferDoorRouting → llamada a Claude                  │
│       Catch: FallbackRouting → reglas determinísticas           │
│    3. ApplyRoutingDecisions → persiste audit + referrals + alerts│
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│  DoorAiInference (audit) + DoorReferral(s) + DoorAlert(s)       │
└─────────────────────────────────────────────────────────────────┘
```

### Capas

| Capa | Responsabilidad | Ejemplo |
|---|---|---|
| **HTTP** | Validar + delegar | `DoorReferralController` |
| **Form Requests** | Validación + autorización | `StoreDoorReferralRequest` |
| **Actions (Domain)** | Lógica de negocio pura | `CreateDoorReferral`, `ChangeDoorReferralStatus` |
| **AI Domain** | Orquestación con Claude | `InferDoorRouting`, `RoutingPromptBuilder`, `RouteToDoorsTool` |
| **Events + Listeners** | Fan-out de eventos | `RoutingTriggerEvent`, `QueueDoorRoutingEvaluation` |
| **Jobs** | Work async | `EvaluateDoorRoutingForEvent`, `DetectMissedAttendanceJob` |
| **Resources** | Serialización API | `DoorReferralResource` |
| **Livewire** | Admin UI | `DoorShow`, `PendingReviewInbox` |

### Tablas

```
doors                    — 9 filas semilla
door_members             — voluntarios (pivot persona ↔ puerta + rol + joined/left)
door_activities          — actividades organizadas por la puerta
door_activity_participants — pivot persona ↔ actividad + attended
door_rules               — reglas en lenguaje natural
door_referrals           — derivaciones/necesidades (entidad central)
door_alerts              — log de notificaciones
door_ai_inferences       — audit completo de llamadas a Claude
```

## 11. Permisos Spatie

| Permiso | Propósito |
|---|---|
| `doors.view`, `doors.manage` | Ver / editar puertas |
| `door_members.manage` | Asignar/retirar voluntarios |
| `door_activities.view`, `door_activities.manage` | Actividades |
| `referrals.view`, `referrals.create`, `referrals.assign`, `referrals.close` | Workflow de derivaciones |
| `referrals.review_pending` | Aprobar/rechazar sugerencias de IA |
| `door_rules.view`, `door_rules.manage` | CRUD de reglas |
| `door_alerts.view`, `door_alerts.manage` | Alertas |
| `door_ai_inferences.view` | Log de auditoría IA |

Mapeo por rol (en `RolePermissionSeeder`):
- **admin** / **pastor** — todos los permisos de puertas (15)
- **leader** — view + crear/asignar/cerrar/revisar referrals + view actividades (8)
- **secretary** — view + crear referrals (5)

## 12. Operación y configuración

### Variables de entorno

```env
ANTHROPIC_API_KEY=sk-ant-...                       # requerido para IA
ANTHROPIC_DEFAULT_MODEL=claude-haiku-4-5           # default si no se setea
DOORS_AI_ENABLED=true                              # kill switch global
DOORS_AI_CONFIDENCE_THRESHOLD=0.85                 # umbral para auto-create vs pending_review
DOORS_AI_MAX_TOKENS=1500                           # cap por inferencia
QUEUE_CONNECTION=database                          # o redis
```

### Workers y schedule

```bash
# Worker dedicado a la cola de IA
php artisan queue:work doors-ai --tries=3

# Scheduler (corre los jobs diarios de detección)
php artisan schedule:work    # dev
# Producción: cron entry → * * * * * php artisan schedule:run
```

### Comando de prueba

```bash
# Dry-run con persona real (no persiste nada)
php artisan doors:ai:dry-run 5 --event=person.registered

# Aplicar resultado a la DB
php artisan doors:ai:dry-run 5 --event=person.registered --apply

# Forzar path fallback (no llamar a Claude)
php artisan doors:ai:dry-run 5 --event=person.registered --fallback
```

## 13. Costos y métricas

### Estimación a 50 eventos/día

| Componente | Valor |
|---|---|
| Tokens input por inferencia | ~3,000 (cacheable) + ~500 (variable) |
| Tokens output por inferencia | ~400 |
| Costo por inferencia (Haiku 4.5 con caché) | ~$0.0021 |
| Costo diario (50 eventos) | ~$0.10 |
| **Costo mensual** | **~$3** |

### Métricas observables (en `/admin/doors/ai/log`)
- Inferencias últimos 30 días
- Costo USD acumulado
- % éxito vs % fallback
- Latencia p50 / p95 (vía latency_ms en tabla)
- Tokens cached vs sin cachear (útil para validar prompt caching)

## 14. Privacidad

**Decisión del cliente (2026-05-25):** se envían **datos completos** a la API de Anthropic para máxima precisión. Esto incluye nombre, edad, contacto, dirección, notas pastorales, estado de membresía, historial de asistencia, células, referrals previas.

### Requerimientos asociados
- **Notificar a visitantes y miembros** en el formulario de registro que sus datos serán procesados por un servicio de IA externo (Anthropic) para coordinación pastoral.
- Considerar activar **Zero Data Retention** con Anthropic si está disponible para el plan contratado.
- Toda inferencia queda en `door_ai_inferences` como audit trail accesible solo a `doors.view` + roles admin/pastor.
- El kill switch `DOORS_AI_ENABLED=false` desactiva inmediatamente toda comunicación con Anthropic; el sistema sigue funcional con `FallbackRouting`.

### Configurable a futuro
Por puerta podría restringirse qué campos se envían — ej. Atención Pastoral ve notas pastorales, Bienvenida no. No implementado en V1.

## 15. Roadmap

### Pendiente (post-V1)
- [ ] UI para emitir `PersonHealthStatusReported` (formulario manual desde detalle de persona)
- [ ] Detección automática de `PersonReturnedAfterAbsence` al registrar asistencia
- [ ] Notificaciones por email/SMS al líder de puerta cuando llega referral urgente
- [ ] Editor visual de reglas con validación semántica via dry-run inline
- [ ] Métricas de tiempo de cierre (promedio días para `pending` → `completed`)
- [ ] Reporte mensual automatizado al pastor con KPIs por puerta

### Configurable hoy (sin código)
- Editar descripción, color, ícono de puertas (vía API `PATCH /doors/{id}`)
- CRUD completo de reglas desde el panel
- Activar/desactivar reglas individualmente
- Ajustar `DOORS_AI_CONFIDENCE_THRESHOLD` por env var
- Ajustar `DOORS_AI_MAX_TOKENS` por env var

---

## Referencias

- **Memoria del proyecto:** `~/.claude/projects/-Users-pavel-Projects-church/memory/`
- **PRD general:** `docs/prd.md`
- **Arquitectura general:** `docs/architecture.md`
- **ERD:** `docs/erd.md`
- **CLAUDE.md** (instrucciones para Claude Code): raíz del repo
