<?php

namespace App\Http\Controllers\Reporter;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Report $report)
    {
        abort_unless($report->reporter_id === $request->user()->id, 403);
        abort_unless($report->status === 'selesai', 422);

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $report->review()->updateOrCreate(
            ['report_id' => $report->id],
            $data + ['user_id' => $request->user()->id]
        );

        return back()->with('success', 'Terima kasih atas penilaianmu.');
    }
}
