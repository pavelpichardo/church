Controladores delgados: solo orquestan Request → Action/Service → Response.

Dominio explícito: “membresía”, “discipulados”, “préstamos”, “asistencia” como módulos con servicios propios.

Validación fuera del controlador: Form Requests + Rules/DTOs.

Servicios idempotentes donde aplique (ej. marcar asistencia).

Eventos y listeners para auditoría, notificaciones y side-effects.

Tests primero en endpoints críticos: Membresía, Asistencia, Préstamos de libros.

folder structure:

app/
  Domain/
    People/
      Models/
      Data/
      Actions/
      Policies/
    Membership/
      Models/
      Data/
      Actions/
      Events/
      Rules/
    Discipleship/
      Models/
      Actions/
      Events/
    Library/
      Models/
      Actions/
      Events/
    Attendance/
      Models/
      Actions/
      Services/
    Events/
      Models/
      Actions/
    Sacraments/
      Models/
      Actions/
  Http/
    Controllers/
    Requests/
  Support/
    Enum/
    Audit/
    Pagination/
    Files/
database/
  migrations/
  factories/
  seeders/
tests/
  Feature/
  Unit/
