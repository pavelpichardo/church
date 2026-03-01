# Backlog (Historias de Usuario) — Sistema Iglesia “Ven y Ve”
**Stack:** Laravel 11 + MySQL + Tailwind CSS (Blade)  
**Fuente:** PRD del proyecto (módulos: Personas, Membresía, Discipulados, Libros, Asistencia, Eventos, Comunicación, Reportes)  

---

## Convenciones
- **Prioridad:** `MVP`, `P1`, `P2`
- **Rol (Actor):** Admin, Pastor/Liderazgo, Líder/Maestro, Secretario(a)
- Cada historia incluye:
  - **ID**
  - **Historia**
  - **Criterios de aceptación (AC)**
  - **Tareas técnicas sugeridas**

---

# EPIC 0 — Autenticación, Usuarios y Roles

## US-0001 (MVP) — Login
**Como** Usuario  
**Quiero** iniciar sesión  
**Para** acceder al sistema de forma segura.

**AC**
- Dado un usuario válido, cuando ingresa email/clave correctos, entonces accede al dashboard.
- Si las credenciales son inválidas, mostrar error sin revelar si el email existe.
- Bloquear acceso a rutas protegidas sin auth.

**Tareas**
- Implementar auth (Laravel starter o Breeze).
- Middleware `auth`.
- Vista `auth/login` con Tailwind.

---

## US-0002 (MVP) — Gestión de usuarios
**Como** Administrador  
**Quiero** crear/editar/desactivar usuarios  
**Para** controlar quién usa el sistema.

**AC**
- CRUD de usuarios: nombre, email, rol, estado (activo/inactivo).
- Un usuario inactivo no puede iniciar sesión.
- Validación de email único.

**Tareas**
- `UserController` (thin) + `UserService`.
- FormRequest: `StoreUserRequest`, `UpdateUserRequest`.
- Soft disable vía campo `is_active` (o `status`).
- Policy: `UserPolicy`.

---

## US-0003 (MVP) — Asignación de roles
**Como** Administrador  
**Quiero** asignar roles (admin/pastor/lider/secretario)  
**Para** limitar acciones según responsabilidades.

**AC**
- Los roles condicionan acceso a módulos.
- Un pastor puede ver perfiles y aprobar membresía.
- Un líder puede registrar asistencia y gestionar discipulados/libros.

**Tareas**
- Enum/const roles en `User`.
- Middleware `role` o Policies por módulo.
- Seed roles (si se usa tabla) o roles por columna.

---

# EPIC 1 — Personas (CRM)

## US-0101 (MVP) — Registrar persona
**Como** Secretario(a)  
**Quiero** registrar un visitante o miembro  
**Para** tener su información en la base de datos.

**AC**
- Crear persona con: nombre, teléfono, email, dirección, nacimiento, estado civil, fecha primera visita.
- Tipo: visitor/member/active_member.
- Estado: visitor/in_process/member/active/inactive.
- Validar: nombre obligatorio; email válido si se provee; teléfono en formato permitido.

**Tareas**
- Migración `people`.
- Modelo `Person`.
- `PersonController` + `PersonService`.
- FormRequest `StorePersonRequest`.
- Vistas: create/edit/index/show.

---

## US-0102 (MVP) — Editar datos de persona
**Como** Usuario autorizado  
**Quiero** editar datos de una persona  
**Para** mantener información actualizada.

**AC**
- Editar campos generales sin perder historial.
- Registrar en auditoría (P1) o al menos `updated_by`.

**Tareas**
- `UpdatePersonRequest`.
- Policy `PersonPolicy@update`.

---

## US-0103 (MVP) — Buscar personas
**Como** Usuario  
**Quiero** buscar personas por nombre/teléfono/correo  
**Para** encontrarlas rápido.

**AC**
- Búsqueda parcial por nombre/apellido.
- Búsqueda exacta o parcial por teléfono/correo.
- Paginación 20 por página.

**Tareas**
- Query scope `Person::search($term)`.
- Index con filtros (Tailwind + input).

---

## US-0104 (MVP) — Ver perfil de persona
**Como** Pastor/Liderazgo  
**Quiero** ver el perfil completo de una persona  
**Para** dar seguimiento pastoral.

**AC**
- Mostrar datos generales + estado + etapa de membresía + discipulados + libros prestados + asistencias recientes.
- Acceso restringido por rol.

**Tareas**
- `PersonProfileViewModel` (opcional) o `PersonService->getProfile()`.
- Policy `PersonPolicy@view`.

---

## US-0105 (P1) — Notas pastorales
**Como** Pastor/Liderazgo  
**Quiero** agregar notas pastorales  
**Para** registrar observaciones relevantes.

**AC**
- Crear múltiples notas con autor y timestamp.
- Solo roles autorizados pueden crear/editar.
- Visible en perfil.

**Tareas**
- Tabla `person_notes`.
- `PersonNoteService`.
- Policy `PersonNotePolicy`.

---

# EPIC 2 — Proceso de Membresía

## US-0201 (MVP) — Configurar etapas de membresía
**Como** Administrador  
**Quiero** definir etapas configurables del proceso de membresía  
**Para** adaptar el flujo a la iglesia.

**AC**
- CRUD de etapas con `name` y `order`.
- Al menos una etapa final marcada (`is_final`).
- Reordenamiento simple (up/down o drag).

**Tareas**
- Tabla `membership_stages`.
- `MembershipStageController` + Request.
- Validaciones de orden único.

---

## US-0202 (MVP) — Ver etapa actual de persona
**Como** Usuario autorizado  
**Quiero** ver la etapa actual  
**Para** saber dónde va en el proceso.

**AC**
- Mostrar etapa actual (la última completada o la asignada vigente).
- Mostrar historial de etapas con fecha y usuario.

**Tareas**
- Tabla `person_membership_progress`.
- `MembershipService->getCurrentStage(person)`.

---

## US-0203 (MVP) — Avanzar etapa de membresía
**Como** Secretario(a)/Pastor  
**Quiero** marcar etapas como completadas (clase, firma, etc.)  
**Para** registrar el avance.

**AC**
- Registrar `completed_at`, `approved_by` cuando aplique.
- No permitir saltar etapas (por defecto); permitir override solo Admin (P1).
- Registrar cambios en historial.

**Tareas**
- `MembershipService->advanceStage(person, stage)`.
- FormRequest `AdvanceMembershipStageRequest`.
- Policy `MembershipPolicy@advance`.

---

## US-0204 (MVP) — Subir documento firmado
**Como** Secretario(a)  
**Quiero** subir documento firmado (PDF/imagen)  
**Para** completar requisito administrativo.

**AC**
- Acepta PDF/JPG/PNG.
- Tamaño máximo configurable (ej. 10MB).
- Guardar ruta segura en storage.
- Asociar a etapa “Firma de Documento”.

**Tareas**
- Storage disk `private` o `local`.
- Tabla `person_documents` (o campo en progress si simple).
- Validación de archivos.

---

## US-0205 (MVP) — Aprobación pastoral y conversión a miembro
**Como** Pastor/Liderazgo  
**Quiero** aprobar la membresía  
**Para** convertir al visitante en miembro oficialmente.

**AC**
- Solo Pastor/Admin pueden aprobar.
- Al aprobar: actualizar `people.type=member` y `people.status=member`.
- Registrar quién aprobó y cuándo.

**Tareas**
- `MembershipService->approve(person)`.
- Policy `MembershipPolicy@approve`.

---

# EPIC 3 — Discipulados

## US-0301 (MVP) — Crear discipulado
**Como** Líder/Maestro  
**Quiero** crear discipulados con nivel y duración  
**Para** organizar clases.

**AC**
- Campos: nombre, nivel, duración, líder.
- Solo roles autorizados pueden crear/editar.

**Tareas**
- Tabla `discipleships`.
- Controller + Service + Requests.
- Policy `DiscipleshipPolicy`.

---

## US-0302 (MVP) — Asignar persona a discipulado
**Como** Líder/Maestro  
**Quiero** asignar una persona a un discipulado  
**Para** registrar su formación.

**AC**
- Registrar fecha inicio.
- Estado inicial `in_progress`.
- Evitar duplicados activos del mismo discipulado para la misma persona.

**Tareas**
- Tabla `discipleship_assignments`.
- `DiscipleshipService->assign(person, discipleship)`.

---

## US-0303 (MVP) — Completar discipulado
**Como** Líder/Maestro  
**Quiero** marcar como completado  
**Para** cerrar el proceso.

**AC**
- Cambiar estado a `completed` con `end_date`.
- Opcional: generar certificado en P1.

**Tareas**
- `DiscipleshipService->complete(assignment)`.

---

## US-0304 (P1) — Certificado de discipulado
**Como** Usuario autorizado  
**Quiero** generar y descargar certificado (PDF)  
**Para** reconocer la culminación.

**AC**
- Botón “Generar certificado” disponible si status `completed`.
- PDF incluye nombre, discipulado, fecha, firma/plantilla.
- Guardar archivo y permitir descarga.

**Tareas**
- Librería PDF (dompdf/snappy).
- Plantilla Blade para certificado.
- Campo `certificate_path` en `discipleship_assignments`.

---

# EPIC 4 — Libros y Materiales

## US-0401 (MVP) — Registrar libros
**Como** Líder/Maestro  
**Quiero** registrar libros/materiales  
**Para** controlar inventario.

**AC**
- Crear libro: título, autor, tipo, cantidad.
- Editar cantidad disponible.
- No permitir cantidad negativa.

**Tareas**
- Tabla `books`.
- `BookService` + Requests.

---

## US-0402 (MVP) — Prestar libro
**Como** Líder/Maestro  
**Quiero** prestar un libro a una persona  
**Para** registrar quién lo tiene.

**AC**
- No permitir préstamo si no hay disponibilidad.
- Registrar asignación, asignado_por, fecha, devolución prevista.
- Disminuir disponibilidad.

**Tareas**
- Tabla `book_loans`.
- Transacción DB para préstamo.
- `BookLoanService->loan()`.

---

## US-0403 (MVP) — Registrar devolución
**Como** Líder/Maestro  
**Quiero** registrar devolución  
**Para** liberar inventario.

**AC**
- Marcar `returned_at` y status `returned`.
- Aumentar disponibilidad.
- No permitir devolver dos veces.

**Tareas**
- `BookLoanService->return()`.

---

## US-0404 (P1) — Préstamos vencidos + recordatorios
**Como** Usuario  
**Quiero** ver préstamos vencidos y enviar recordatorios  
**Para** recuperar materiales.

**AC**
- Listado de vencidos (hoy > return_due_date y no devuelto).
- Botón enviar recordatorio por email/SMS (si habilitado).

**Tareas**
- Scope `BookLoan::overdue()`.
- Job `SendBookReturnReminderJob`.

---

# EPIC 5 — Asistencia

## US-0501 (MVP) — Registrar asistencia
**Como** Líder/Maestro  
**Quiero** registrar asistencia a cultos/clases/discipulados/eventos  
**Para** controlar participación.

**AC**
- Crear registro: persona, tipo, evento (opcional), fecha/hora.
- Evitar duplicados para misma persona y mismo evento en la misma fecha (regla configurable).
- Rol autorizado.

**Tareas**
- Tabla `attendances`.
- `AttendanceService->register()`.
- Index por (person_id, event_id, date).

---

## US-0502 (MVP) — Historial por persona
**Como** Usuario  
**Quiero** ver historial de asistencia por persona  
**Para** seguimiento individual.

**AC**
- Mostrar últimas asistencias con filtros por tipo y rango de fechas.
- Paginación.

**Tareas**
- Endpoint `People/{id}/attendance`.
- Query con filtros.

---

## US-0503 (MVP) — Asistencia por evento
**Como** Usuario  
**Quiero** ver asistencia por evento  
**Para** medir participación.

**AC**
- Lista de asistentes y conteo total.
- Exportación (P1).

**Tareas**
- `EventAttendanceController`.
- Agregaciones SQL.

---

# EPIC 6 — Eventos y Calendario

## US-0601 (MVP) — Crear/editar eventos
**Como** Pastor/Administrador  
**Quiero** crear y administrar eventos  
**Para** planificar actividades.

**AC**
- Campos: nombre, tipo, descripción, inicio, fin, lugar.
- Validar fin > inicio.
- Mostrar en calendario.

**Tareas**
- Tabla `events`.
- `EventService` + Requests.
- Vista calendario (FullCalendar).

---

## US-0602 (MVP) — Calendario visual
**Como** Usuario  
**Quiero** ver eventos en un calendario  
**Para** visualizar agenda.

**AC**
- Vista mensual/semanal.
- Click en evento abre detalle.
- Filtro por tipo de evento.

**Tareas**
- FullCalendar + endpoint JSON events.
- Tailwind UI.

---

## US-0603 (P1) — Recordatorios automáticos de eventos
**Como** Usuario  
**Quiero** enviar recordatorios automáticos  
**Para** aumentar asistencia.

**AC**
- Configurar regla: 24h antes (default).
- Enviar por email y/o SMS según configuración.
- Log de envío.

**Tareas**
- Tabla `notification_rules` (o config simple).
- Scheduler + Job `SendEventReminderJob`.
- `NotificationService`.

---

# EPIC 7 — Cumpleaños

## US-0701 (MVP) — Calendario de cumpleaños
**Como** Usuario  
**Quiero** ver calendario/lista de cumpleaños  
**Para** planificar felicitaciones.

**AC**
- Lista “próximos 30 días”.
- Filtro por mes.

**Tareas**
- Query por `birth_date` (mes/día) normalizado.
- Vista `birthdays/index`.

---

## US-0702 (P1) — Felicitación automática
**Como** Usuario  
**Quiero** enviar felicitaciones automáticas por correo/SMS  
**Para** fortalecer relación con miembros.

**AC**
- Enviar el día del cumpleaños a las 9:00 AM.
- Plantilla configurable.
- Evitar duplicados (un envío por año por persona).

**Tareas**
- Scheduler diario.
- Tabla `message_logs` para deduplicación.
- Job `SendBirthdayGreetingJob`.

---

# EPIC 8 — Congresos (P1)

## US-0801 (P1) — Crear congreso
**Como** Pastor/Administrador  
**Quiero** crear un congreso  
**Para** gestionar evento especial.

**AC**
- Nombre, fecha, lugar, descripción.
- Visible en calendario (tipo congress).

**Tareas**
- Reusar `events` con type `congress` o tabla `congresses`.
- Recomendado: `congresses` + relación a `events` (si se requiere).

---

## US-0802 (P1) — Roles y responsabilidades
**Como** Pastor/Administrador  
**Quiero** definir roles y asignar personas  
**Para** organizar equipos.

**AC**
- Crear roles por congreso.
- Asignar persona a rol con tareas.
- Persona puede confirmar responsabilidad.

**Tareas**
- Tablas `congress_roles`, `congress_assignments`.
- `CongressService` + Policies.

---

# EPIC 9 — Bautismos y Matrimonios (P1)

## US-0901 (P1) — Registrar bautismo
**Como** Pastor  
**Quiero** registrar bautismos con personas asociadas  
**Para** mantener historial sacramental.

**AC**
- Crear bautismo con fecha/lugar/pastor.
- Asociar 1..N personas.
- Adjuntar/generar certificado (P2/P1).

**Tareas**
- Tabla `baptisms` + pivot `baptism_person`.
- `BaptismService`.

---

## US-0902 (P1) — Registrar matrimonio
**Como** Pastor  
**Quiero** registrar matrimonios con pareja y documento  
**Para** mantener historial.

**AC**
- Fecha/lugar/oficiante.
- Asociar pareja (2 personas).
- Adjuntar certificado.

**Tareas**
- Tabla `marriages` + pivot `marriage_person`.
- `MarriageService`.

---

# EPIC 10 — Comunicación y Recordatorios (P1)

## US-1001 (P1) — Envío de mensajes
**Como** Usuario autorizado  
**Quiero** enviar correos/SMS a personas o grupos  
**Para** comunicar actividades y seguimiento.

**AC**
- Enviar a: persona individual, lista, segmento (ej. visitantes últimos 30 días).
- Registrar historial de envío.
- Manejar fallos y reintentos.

**Tareas**
- Tabla `messages` + `message_recipients` + `message_logs`.
- `NotificationService`.
- Jobs para envío.

---

# EPIC 11 — Reportes (MVP)

## US-1101 (MVP) — Reporte visitantes por período
**Como** Pastor/Administrador  
**Quiero** ver total de visitantes por rango de fechas  
**Para** medir crecimiento.

**AC**
- Filtros: fecha inicio/fin.
- Métrica: total visitantes, nuevos registros.
- Gráfico simple (P1).

**Tareas**
- Query agregada por `first_visit_date`.
- Vista `reports/visitors`.

---

## US-1102 (MVP) — Miembros activos vs inactivos
**Como** Pastor/Administrador  
**Quiero** ver miembros activos vs inactivos  
**Para** detectar retención.

**AC**
- Mostrar conteos por `status`.
- Filtro por ministerio (P2) o por rango (P1).

**Tareas**
- Query por `people.status`.
- Vista `reports/members_status`.

---

## US-1103 (MVP) — Asistencia promedio mensual
**Como** Pastor/Administrador  
**Quiero** ver asistencia promedio mensual  
**Para** evaluar participación.

**AC**
- Seleccionar mes/año.
- Mostrar total asistencias y promedio por evento.
- Top eventos por asistencia (P1).

**Tareas**
- Agregación por `attended_at` y `event_id`.
- Vista `reports/attendance_monthly`.

---

# EPIC 12 — Auditoría (P1)

## US-1201 (P1) — Auditoría de cambios
**Como** Administrador  
**Quiero** ver quién creó/editó información  
**Para** control interno y trazabilidad.

**AC**
- Registrar create/update/delete en modelos clave (Person, Event, Membership, Discipleship, Books).
- Vista de logs con filtros por usuario/modelo/fecha.

**Tareas**
- Tabla `audit_logs`.
- Observer por modelo o paquete de auditing.
- `AuditLogController` (read-only).

---

# Definición de “Done” (DoD)
- Migraciones + modelos + factories (si aplica)
- Validaciones con FormRequests
- Policies aplicadas a rutas y acciones
- UI básica Tailwind para CRUD
- Tests: al menos 1 feature test por epic MVP
- Logs de error y manejo de excepciones
- Seeds de datos mínimos (roles, stages iniciales)

---

# Notas de implementación (IA)
- Mantener controllers delgados; lógica a Services.
- Reusar `events` para congresos si simplifica el MVP.
- Usar SoftDeletes en `people`, `books`, `events`.
- Documentos (PDF/imagenes) en storage protegido; servir via rutas autorizadas.
- Scheduler + Jobs para recordatorios (P1).
