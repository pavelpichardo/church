Este es un esquema sugerido pero puedes analizarlo y cambiarlo si entiendes puede mejorarse

## 1) Seguridad y usuarios del sistema

### `users`

* `id` (bigint, PK)
* `name` (varchar)
* `email` (varchar, unique, nullable)
* `phone` (varchar, nullable)
* `password` (varchar)
* `is_active` (boolean, default true)
* `last_login_at` (datetime, nullable)
* `created_at`, `updated_at`

### `roles`

* `id`
* `name` (varchar) — ej: admin, pastor, lider, secretaria
* `description` (varchar, nullable)
* timestamps

### `role_user` (pivot)

* `role_id` (FK)
* `user_id` (FK)
* PK compuesta (`role_id`,`user_id`)

> Si quieres permisos granulares reales (recomendado), agrega:

* `permissions`, `permission_role` (o usa Spatie Laravel Permission).

---

## 2) Personas (CRM de la iglesia)

### `people`

* `id`
* `first_name` (varchar)
* `last_name` (varchar)
* `full_name` (varchar, index) *(opcional, útil para búsquedas rápidas)*
* `phone` (varchar, nullable)
* `email` (varchar, nullable, index)
* `address_line1` (varchar, nullable)
* `address_line2` (varchar, nullable)
* `city` (varchar, nullable)
* `state` (varchar, nullable)
* `postal_code` (varchar, nullable)
* `birth_date` (date, nullable)
* `marital_status` (enum: single, married, divorced, widowed, other, nullable)
* `first_visit_date` (date, nullable)
* `person_type` (enum: visitor, member, active_member)  
* `status` (enum: visitor, membership_process, member, active_member, inactive) 
* `notes_pastoral` (text, nullable)
* `created_by` (FK `users.id`, nullable)
* timestamps

---

## 3) Proceso de membresía (etapas configurables + historial + documento)

### `membership_stages`

* `id`
* `name` (varchar) — ej: Visitante, Clase, Firma, Aprobación, Miembro, Miembro Activo 
* `order` (int)
* `is_active` (boolean, default true)
* timestamps

### `person_membership`

*(estado actual del proceso por persona)*

* `id`
* `person_id` (FK)
* `current_stage_id` (FK `membership_stages.id`)
* `class_taken_at` (date, nullable) 
* `class_teacher_id` (FK `users.id`, nullable)
* `document_signed_at` (date, nullable)
* `document_file_id` (FK `files.id`, nullable) *(ver tabla `files`)*
* `pastor_approved_at` (datetime, nullable)
* `pastor_approved_by` (FK `users.id`, nullable)
* timestamps

### `membership_stage_history`

* `id`
* `person_id` (FK)
* `from_stage_id` (FK, nullable)
* `to_stage_id` (FK)
* `changed_by` (FK `users.id`, nullable)
* `changed_at` (datetime)
* `note` (varchar, nullable)
* timestamps

---

## 4) Discipulados (catálogo, asignación, progreso, certificados)

### `discipleships`

* `id`
* `name` (varchar) 
* `level` (enum: initial, intermediate, advanced) 
* `duration_weeks` (int, nullable) *(o `duration_text`)*
* `leader_id` (FK `users.id`, nullable) 
* `description` (text, nullable)
* timestamps

### `discipleship_assignments`

* `id`
* `discipleship_id` (FK)
* `person_id` (FK)
* `assigned_by` (FK `users.id`, nullable)
* `start_date` (date, nullable) 
* `end_date` (date, nullable)
* `status` (enum: in_progress, completed, cancelled) 
* `notes` (text, nullable)
* timestamps
* index recomendado: (`discipleship_id`,`person_id`,`status`)

### `certificates`

*(genérico para discipulado / bautismo / matrimonio, etc.)*

* `id`
* `type` (enum: discipleship, baptism, marriage, other)
* `person_id` (FK, nullable) *(para matrimonios puede ser null y usar `marriage_id`)*
* `discipleship_assignment_id` (FK, nullable)
* `baptism_id` (FK, nullable)
* `marriage_id` (FK, nullable)
* `issued_at` (date) 
* `file_id` (FK `files.id`, nullable) 
* `issued_by` (FK `users.id`, nullable)
* timestamps

---

## 5) Libros y materiales (inventario + préstamos)

### `study_materials`

* `id`
* `title` (varchar) 
* `author` (varchar, nullable) 
* `material_type` (enum: book, manual, pdf) 
* `total_quantity` (int, default 0) 
* `available_quantity` (int, default 0)
* `description` (text, nullable)
* `file_id` (FK `files.id`, nullable) *(si es PDF)*
* timestamps

### `material_loans`

* `id`
* `study_material_id` (FK)
* `person_id` (FK) 
* `assigned_by` (FK `users.id`, nullable) 
* `assigned_at` (datetime) 
* `due_at` (date, nullable)
* `returned_at` (datetime, nullable) 
* `status` (enum: borrowed, returned, lost, overdue) 
* `notes` (text, nullable)
* timestamps

---

## 6) Eventos + asistencia (base para cultos, clases, discipulados, congresos)

### `events`

* `id`
* `title` (varchar)
* `event_type` (enum: service, class, discipleship, special_event, birthday, congress) 
* `description` (text, nullable)
* `location` (varchar, nullable)
* `starts_at` (datetime)
* `ends_at` (datetime, nullable)
* `is_recurring` (boolean, default false)
* `recurrence_rule` (varchar, nullable) *(RRULE si luego lo quieres)*
* `created_by` (FK `users.id`, nullable)
* timestamps

### `attendance_records`

* `id`
* `event_id` (FK) 
* `person_id` (FK) 
* `checked_in_at` (datetime)
* `checkin_method` (enum: manual, quick, qr, import, default manual) 
* `recorded_by` (FK `users.id`, nullable)
* `notes` (varchar, nullable)
* timestamps
* unique recomendado: (`event_id`,`person_id`) *(evita duplicados)*

> Tip: si luego quieres “asistencia por persona” es trivial con esta tabla. 

---

## 7) Cumpleaños (recordatorios)

No necesitas una tabla extra: se deriva de `people.birth_date`.
Si quieres **plantillas y log**, usa Comunicación (abajo). 

---

## 8) Congresos (roles, responsabilidades, confirmación)

Puedes modelarlo como “evento tipo congress” + tablas extra:

### `congresses`

* `id`
* `event_id` (FK `events.id`, unique) *(un congreso es un evento)*
* `name` (varchar) 
* `place` (varchar, nullable) 
* timestamps

### `congress_roles`

* `id`
* `congress_id` (FK)
* `name` (varchar) — ej: ujier, logística, alabanza, registro 
* `description` (text, nullable)
* timestamps

### `congress_assignments`

* `id`
* `congress_role_id` (FK)
* `person_id` (FK) 
* `assigned_by` (FK `users.id`, nullable)
* `tasks` (text, nullable) 
* `confirmed_at` (datetime, nullable) 
* `status` (enum: assigned, confirmed, declined, completed)
* timestamps

---

## 9) Bautismos

### `baptisms`

* `id`
* `event_id` (FK `events.id`, nullable) *(si lo manejas como evento)*
* `date` (date) 
* `location` (varchar, nullable) 
* `pastor_id` (FK `users.id`, nullable) 
* `notes` (text, nullable)
* timestamps

### `baptism_people`

* `baptism_id` (FK)
* `person_id` (FK)
* PK compuesta (`baptism_id`,`person_id`) 

*(El certificado se genera en `certificates` con `type=baptism`.)* 

---

## 10) Matrimonios

### `marriages`

* `id`
* `event_id` (FK `events.id`, nullable)
* `date` (date) 
* `location` (varchar, nullable) 
* `officiant_id` (FK `users.id`, nullable) 
* `spouse1_person_id` (FK `people.id`)
* `spouse2_person_id` (FK `people.id`)
* `document_file_id` (FK `files.id`, nullable) 
* `notes` (text, nullable)
* timestamps

*(Certificado en `certificates` con `type=marriage`.)* 

---

## 11) Comunicación y recordatorios (email/SMS + logs)

### `message_templates`

* `id`
* `name` (varchar)
* `channel` (enum: email, sms) 
* `subject` (varchar, nullable) *(email)*
* `body` (text)
* `is_active` (boolean, default true)
* timestamps

### `message_logs`

* `id`
* `channel` (enum: email, sms) 
* `template_id` (FK, nullable)
* `person_id` (FK, nullable)
* `event_id` (FK, nullable)
* `to_address` (varchar) *(email o teléfono)*
* `status` (enum: queued, sent, failed)
* `provider_message_id` (varchar, nullable)
* `sent_at` (datetime, nullable)
* `error_message` (text, nullable)
* `created_by` (FK `users.id`, nullable)
* timestamps

---

## 12) Archivos (documentos firmados, PDFs, certificados)

### `files`

* `id`
* `disk` (varchar, default 'public' o 's3')
* `path` (varchar)
* `original_name` (varchar)
* `mime_type` (varchar)
* `size_bytes` (bigint)
* `uploaded_by` (FK `users.id`, nullable)
* timestamps

---

## 13) Auditoría (extra clave del PRD, recomendado desde el día 1)

### `audit_logs`

* `id`
* `user_id` (FK, nullable) 
* `action` (varchar) — created/updated/deleted/login/etc
* `auditable_type` (varchar) — ej: "people"
* `auditable_id` (bigint)
* `old_values` (json, nullable)
* `new_values` (json, nullable)
* `ip_address` (varchar, nullable)
* `user_agent` (varchar, nullable)
* `created_at`

---

## Extras listos para Fase 2 (si quieres dejarlos ya “parqueados”)

### Ministerios (recomendación PRD)

* `ministries` (id, name, description, leader_id, timestamps)
* `ministry_person` (ministry_id, person_id, joined_at, left_at nullable)

### QR Check-in

* `event_checkin_tokens` (event_id, token, expires_at)

---

## Relaciones clave (resumen rápido)

* `people` 1–1 `person_membership`
* `people` 1–N `membership_stage_history`
* `discipleships` 1–N `discipleship_assignments`
* `people` 1–N `discipleship_assignments`
* `events` 1–N `attendance_records`
* `events` 1–1 `congresses` (cuando event_type=congress)
* `congress_roles` 1–N `congress_assignments`
* `baptisms` N–N `people` via `baptism_people`
* `marriages` apunta a dos `people` (spouse1/spouse2)
* `certificates` referencia al “origen” (discipleship_assignment / baptism / marriage)



