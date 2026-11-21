<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{Report, User, Category};
class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total' => Report::count(),
            'submitted' => Report::where('status', 'dikirim')->count(),
            'progress' => Report::where('status', 'diproses')->count(),
            'completed' => Report::where('status', 'selesai')->count(),
            'rejected' => Report::where('status', 'ditolak')->count(),
            'users' => User::count(),
        ];
        $recent = Report::with(['reporter', 'category', 'activeAssignment.officer'])->latest()->take(8)->get();
        $categories = Category::withCount('reports')->orderByDesc('reports_count')->take(6)->get();
        return view('admin.dashboard', compact('stats', 'recent', 'categories'));
    }
}
