<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\MaterialLoan;
use App\Models\Person;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_people'    => Person::count(),
            'active_members'  => Person::where('status', 'active_member')->count(),
            'active_loans'    => MaterialLoan::whereIn('status', ['borrowed', 'overdue'])->count(),
            'events_this_week' => Event::whereBetween('starts_at', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ])->count(),
        ];

        $recentPeople = Person::latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentPeople'));
    }
}
