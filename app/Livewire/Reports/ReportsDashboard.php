<?php

namespace App\Livewire\Reports;

use App\Models\AttendanceRecord;
use App\Models\Baptism;
use App\Models\Communication;
use App\Models\DiscipleshipAssignment;
use App\Models\Event;
use App\Models\Marriage;
use App\Models\MaterialLoan;
use App\Models\Person;
use App\Support\Enums\EventType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ReportsDashboard extends Component
{
    public string $tab = 'attendance';
    public string $dateFrom = '';
    public string $dateTo = '';

    public function mount(): void
    {
        $this->dateFrom = now()->subMonths(3)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
    }

    // ── Attendance Report ──────────────────────────────────

    private function getAttendanceReport(): array
    {
        $from = Carbon::parse($this->dateFrom)->startOfDay();
        $to = Carbon::parse($this->dateTo)->endOfDay();

        // Attendance by event type
        $byType = Event::select('event_type', DB::raw('COUNT(events.id) as event_count'), DB::raw('COALESCE(SUM(sub.att_count), 0) as total_attendance'))
            ->leftJoinSub(
                AttendanceRecord::select('event_id', DB::raw('COUNT(*) as att_count'))->groupBy('event_id'),
                'sub', 'events.id', '=', 'sub.event_id'
            )
            ->whereBetween('events.starts_at', [$from, $to])
            ->groupBy('event_type')
            ->get()
            ->map(fn ($row) => [
                'type'       => $row->event_type instanceof EventType ? $row->event_type->label() : (EventType::tryFrom($row->event_type)?->label() ?? $row->event_type),
                'events'     => $row->event_count,
                'attendance' => (int) $row->total_attendance,
                'average'    => $row->event_count > 0 ? round($row->total_attendance / $row->event_count) : 0,
            ]);

        // Monthly trend
        $monthly = Event::select(
                DB::raw("DATE_FORMAT(events.starts_at, '%Y-%m') as month"),
                DB::raw('COUNT(DISTINCT events.id) as event_count'),
                DB::raw('COUNT(attendance_records.id) as total_attendance')
            )
            ->leftJoin('attendance_records', 'events.id', '=', 'attendance_records.event_id')
            ->whereBetween('events.starts_at', [$from, $to])
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $totalEvents = Event::whereBetween('starts_at', [$from, $to])->count();
        $totalAttendance = AttendanceRecord::whereHas('event', fn ($q) => $q->whereBetween('starts_at', [$from, $to]))->count();
        $uniqueAttendees = AttendanceRecord::whereHas('event', fn ($q) => $q->whereBetween('starts_at', [$from, $to]))->distinct('person_id')->count('person_id');

        // Top attended events
        $topEvents = Event::withCount('attendanceRecords')
            ->whereBetween('starts_at', [$from, $to])
            ->orderByDesc('attendance_records_count')
            ->limit(10)
            ->get();

        return compact('byType', 'monthly', 'totalEvents', 'totalAttendance', 'uniqueAttendees', 'topEvents');
    }

    // ── Inactive People Report ─────────────────────────────

    private function getInactiveReport(): array
    {
        $from = Carbon::parse($this->dateFrom)->startOfDay();
        $to = Carbon::parse($this->dateTo)->endOfDay();

        // People who have NOT attended any event in the date range
        $attendedIds = AttendanceRecord::whereHas('event', fn ($q) => $q->whereBetween('starts_at', [$from, $to]))
            ->distinct()
            ->pluck('person_id');

        $inactive = Person::whereNotIn('id', $attendedIds)
            ->whereIn('status', ['member', 'active_member'])
            ->orderBy('last_name')
            ->get()
            ->map(function ($p) {
                $lastAttendance = AttendanceRecord::where('person_id', $p->id)
                    ->latest('checked_in_at')
                    ->first();
                return [
                    'person'          => $p,
                    'last_attendance' => $lastAttendance?->checked_in_at,
                    'days_absent'     => $lastAttendance ? (int) $lastAttendance->checked_in_at->diffInDays(now()) : null,
                ];
            });

        $totalMembers = Person::whereIn('status', ['member', 'active_member'])->count();
        $activeCount = $totalMembers - $inactive->count();

        return compact('inactive', 'totalMembers', 'activeCount');
    }

    // ── Membership Report ──────────────────────────────────

    private function getMembershipReport(): array
    {
        $pipeline = Person::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $from = Carbon::parse($this->dateFrom)->startOfDay();
        $to = Carbon::parse($this->dateTo)->endOfDay();

        // New people by month
        $newByMonth = Person::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');

        // New visitors in period
        $newVisitors = Person::where('status', 'visitor')
            ->whereBetween('first_visit_date', [$from, $to])
            ->count();

        // Visitor retention: visitors who became members
        $convertedCount = Person::whereIn('status', ['member', 'active_member', 'membership_process'])
            ->whereNotNull('first_visit_date')
            ->count();
        $totalVisitorsEver = Person::whereNotNull('first_visit_date')->count();
        $retentionRate = $totalVisitorsEver > 0 ? round($convertedCount / $totalVisitorsEver * 100, 1) : 0;

        $totalPeople = Person::count();

        return compact('pipeline', 'newByMonth', 'newVisitors', 'convertedCount', 'retentionRate', 'totalPeople');
    }

    // ── Sacraments Report ──────────────────────────────────

    private function getSacramentsReport(): array
    {
        $from = Carbon::parse($this->dateFrom);
        $to = Carbon::parse($this->dateTo);

        $baptisms = Baptism::withCount('people')
            ->whereBetween('date', [$from, $to])
            ->orderByDesc('date')
            ->get();
        $totalBaptized = $baptisms->sum('people_count');

        $marriages = Marriage::with(['spouse1', 'spouse2'])
            ->whereBetween('date', [$from, $to])
            ->orderByDesc('date')
            ->get();

        // By year
        $baptismsByYear = Baptism::select(DB::raw('YEAR(date) as year'), DB::raw('COUNT(*) as ceremonies'))
            ->groupBy('year')->orderBy('year')->pluck('ceremonies', 'year');
        $marriagesByYear = Marriage::select(DB::raw('YEAR(date) as year'), DB::raw('COUNT(*) as count'))
            ->groupBy('year')->orderBy('year')->pluck('count', 'year');

        return compact('baptisms', 'totalBaptized', 'marriages', 'baptismsByYear', 'marriagesByYear');
    }

    // ── Discipleship Report ────────────────────────────────

    private function getDiscipleshipReport(): array
    {
        $assignments = DiscipleshipAssignment::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $total = array_sum($assignments);
        $completed = $assignments['completed'] ?? 0;
        $completionRate = $total > 0 ? round($completed / $total * 100, 1) : 0;

        // By discipleship
        $byDiscipleship = DB::table('discipleship_assignments')
            ->join('discipleships', 'discipleships.id', '=', 'discipleship_assignments.discipleship_id')
            ->select(
                'discipleships.name',
                'discipleships.level',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN discipleship_assignments.status = 'completed' THEN 1 ELSE 0 END) as completed"),
                DB::raw("SUM(CASE WHEN discipleship_assignments.status = 'in_progress' THEN 1 ELSE 0 END) as in_progress"),
                DB::raw("SUM(CASE WHEN discipleship_assignments.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled")
            )
            ->groupBy('discipleships.id', 'discipleships.name', 'discipleships.level')
            ->orderBy('discipleships.name')
            ->get();

        return compact('assignments', 'total', 'completed', 'completionRate', 'byDiscipleship');
    }

    // ── Demographics Report ────────────────────────────────

    private function getDemographicsReport(): array
    {
        $byGender = Person::select('gender', DB::raw('COUNT(*) as count'))
            ->whereNotNull('gender')
            ->groupBy('gender')
            ->pluck('count', 'gender');

        $byMaritalStatus = Person::select('marital_status', DB::raw('COUNT(*) as count'))
            ->whereNotNull('marital_status')
            ->groupBy('marital_status')
            ->pluck('count', 'marital_status');

        $byHowFound = Person::select('how_found_us', DB::raw('COUNT(*) as count'))
            ->whereNotNull('how_found_us')
            ->groupBy('how_found_us')
            ->pluck('count', 'how_found_us');

        // Age distribution
        $ageGroups = Person::selectRaw("
            CASE
                WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) < 13 THEN 'Niños (0-12)'
                WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 13 AND 17 THEN 'Adolescentes (13-17)'
                WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 18 AND 29 THEN 'Jóvenes (18-29)'
                WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 30 AND 59 THEN 'Adultos (30-59)'
                ELSE 'Adultos mayores (60+)'
            END as age_group,
            COUNT(*) as count
        ")
        ->whereNotNull('birth_date')
        ->groupBy('age_group')
        ->orderByRaw("MIN(TIMESTAMPDIFF(YEAR, birth_date, CURDATE()))")
        ->pluck('count', 'age_group');

        $withBirthdate = Person::whereNotNull('birth_date')->count();
        $totalPeople = Person::count();

        // Upcoming birthdays (next 30 days)
        $upcomingBirthdays = Person::whereNotNull('birth_date')
            ->whereRaw("DATE_FORMAT(birth_date, '%m-%d') BETWEEN DATE_FORMAT(CURDATE(), '%m-%d') AND DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 30 DAY), '%m-%d')")
            ->orderByRaw("DATE_FORMAT(birth_date, '%m-%d')")
            ->limit(15)
            ->get();

        return compact('byGender', 'byMaritalStatus', 'byHowFound', 'ageGroups', 'withBirthdate', 'totalPeople', 'upcomingBirthdays');
    }

    // ── Communications Report ──────────────────────────────

    private function getCommunicationsReport(): array
    {
        $from = Carbon::parse($this->dateFrom)->startOfDay();
        $to = Carbon::parse($this->dateTo)->endOfDay();

        $byStatus = Communication::select('status', DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('status')
            ->pluck('count', 'status');

        $byChannel = Communication::select('channel', DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('channel')
            ->pluck('count', 'channel');

        $totalRecipients = DB::table('communication_recipients')
            ->join('communications', 'communications.id', '=', 'communication_recipients.communication_id')
            ->whereBetween('communications.created_at', [$from, $to])
            ->count();

        $sentRecipients = DB::table('communication_recipients')
            ->join('communications', 'communications.id', '=', 'communication_recipients.communication_id')
            ->whereBetween('communications.created_at', [$from, $to])
            ->where('communication_recipients.status', 'sent')
            ->count();

        $failedRecipients = DB::table('communication_recipients')
            ->join('communications', 'communications.id', '=', 'communication_recipients.communication_id')
            ->whereBetween('communications.created_at', [$from, $to])
            ->where('communication_recipients.status', 'failed')
            ->count();

        return compact('byStatus', 'byChannel', 'totalRecipients', 'sentRecipients', 'failedRecipients');
    }

    // ── Library Report ─────────────────────────────────────

    private function getLibraryReport(): array
    {
        $activeLoans = MaterialLoan::whereIn('status', ['borrowed', 'overdue'])->count();
        $overdueLoans = MaterialLoan::where('status', 'overdue')->count();
        $totalReturned = MaterialLoan::where('status', 'returned')->count();
        $totalLost = MaterialLoan::where('status', 'lost')->count();

        $overdueList = MaterialLoan::with(['person', 'studyMaterial'])
            ->where('status', 'overdue')
            ->orderBy('due_at')
            ->limit(20)
            ->get();

        // Top borrowed materials
        $topMaterials = DB::table('material_loans')
            ->join('study_materials', 'study_materials.id', '=', 'material_loans.study_material_id')
            ->select('study_materials.title', DB::raw('COUNT(*) as loan_count'))
            ->groupBy('study_materials.id', 'study_materials.title')
            ->orderByDesc('loan_count')
            ->limit(10)
            ->get();

        // Top readers
        $topReaders = DB::table('material_loans')
            ->join('people', 'people.id', '=', 'material_loans.person_id')
            ->select(DB::raw("CONCAT(people.first_name, ' ', people.last_name) as name"), DB::raw('COUNT(*) as loan_count'))
            ->groupBy('people.id', 'name')
            ->orderByDesc('loan_count')
            ->limit(10)
            ->get();

        return compact('activeLoans', 'overdueLoans', 'totalReturned', 'totalLost', 'overdueList', 'topMaterials', 'topReaders');
    }

    // ── Render ─────────────────────────────────────────────

    public function render()
    {
        $data = match ($this->tab) {
            'attendance'     => $this->getAttendanceReport(),
            'inactive'       => $this->getInactiveReport(),
            'membership'     => $this->getMembershipReport(),
            'sacraments'     => $this->getSacramentsReport(),
            'discipleship'   => $this->getDiscipleshipReport(),
            'demographics'   => $this->getDemographicsReport(),
            'communications' => $this->getCommunicationsReport(),
            'library'        => $this->getLibraryReport(),
            default          => $this->getAttendanceReport(),
        };

        return view('livewire.reports.dashboard', ['reportData' => $data]);
    }
}
