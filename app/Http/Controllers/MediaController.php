<?php

namespace App\Http\Controllers;

use App\Models\ReportMedia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function show(Request $request, ReportMedia $media)
    {
        $report = $media->report()->with('activeAssignment')->firstOrFail();

        // Both guards may be authenticated in the same browser. Authorize using
        // whichever active identity actually owns this report or has permission.
        $user = collect([
            Auth::guard('admin')->user(),
            Auth::guard('web')->user(),
        ])->filter(fn ($candidate) => $candidate instanceof User)
            ->first(function (User $candidate) use ($report) {
                return $candidate->is_active && (
                    $candidate->hasRole('admin')
                    || (int) $report->reporter_id === (int) $candidate->id
                    || ($candidate->hasRole('officer')
                        && (int) ($report->activeAssignment?->officer_id ?? 0) === (int) $candidate->id)
                );
            });

        abort_unless($user, 403);
        abort_unless(Storage::disk($media->disk)->exists($media->path), 404);

        $headers = [
            'Content-Type' => $media->mime_type,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ];

        if ($request->boolean('download')) {
            return Storage::disk($media->disk)->download($media->path, $media->original_name, $headers);
        }

        return Storage::disk($media->disk)->response($media->path, $media->original_name, $headers, 'inline');
    }
}
