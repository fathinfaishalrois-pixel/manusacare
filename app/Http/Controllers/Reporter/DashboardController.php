<?php
namespace App\Http\Controllers\Reporter;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $reports = $request->user()->reports()->with(['category', 'activeAssignment.officer'])->latest()->take(6)->get();
        $stats = [
            'total' => $request->user()->reports()->count(),
            'submitted' => $request->user()->reports()->where('status', 'dikirim')->count(),
            'progress' => $request->user()->reports()->where('status', 'diproses')->count(),
            'completed' => $request->user()->reports()->where('status', 'selesai')->count(),
        ];
        return view('reporter.dashboard', compact('reports', 'stats'));
    }
}
