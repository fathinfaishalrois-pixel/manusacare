<?php

namespace App\Http\Controllers\Reporter;

use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Location;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Throwable;

class ReportController extends Controller
{
    private function locationChoices(): array
    {
        $locations = Location::query()->where('is_active', true)->orderBy('building')->orderBy('floor')->orderBy('room')->get();

        return [
            'buildings' => $locations->pluck('building')->filter()->unique()->values()->all() ?: ['Gedung A', 'Gedung B', 'Gedung C'],
            'floors' => $locations->pluck('floor')->filter()->unique()->values()->all() ?: ['1', '2', '3', '4', '5'],
            'rooms' => $locations->pluck('room')->filter()->unique()->values()->all() ?: ['Ruang Kelas', 'Laboratorium', 'Perpustakaan', 'Toilet', 'Koridor', 'Lapangan', 'Kantor'],
        ];
    }

    public function index(Request $request)
    {
        $reports = $request->user()->reports()->with(['category', 'activeAssignment.officer'])->latest()->paginate(10);
        return view('reporter.reports.index', compact('reports'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('reporter.reports.create', array_merge(['categories' => $categories], $this->locationChoices()));
    }

    public function store(Request $request)
    {
        $choices = $this->locationChoices();
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'category_id' => ['required', Rule::exists('categories', 'id')->where('is_active', true)],
            'urgency' => ['required', 'in:rendah,sedang,tinggi'],
            'building' => ['required', Rule::in(array_merge($choices['buildings'], ['__other']))],
            'building_custom' => ['required_if:building,__other', 'nullable', 'string', 'max:100'],
            'floor' => ['nullable', Rule::in(array_merge($choices['floors'], ['__other']))],
            'floor_custom' => ['required_if:floor,__other', 'nullable', 'string', 'max:50'],
            'room' => ['nullable', Rule::in(array_merge($choices['rooms'], ['__other']))],
            'room_custom' => ['required_if:room,__other', 'nullable', 'string', 'max:100'],
            'location_detail' => ['nullable', 'string', 'max:500'],
            'description' => ['required', 'string', 'max:5000'],
            'chronology' => ['nullable', 'string', 'max:5000'],
            'found_at' => ['nullable', 'date'],
            'additional_notes' => ['nullable', 'string', 'max:2000'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['file', 'mimes:jpg,jpeg,png', 'mimetypes:image/jpeg,image/png', 'max:10240'],
            'videos' => ['nullable', 'array', 'max:2'],
            'videos.*' => ['file', 'mimes:mp4,mov', 'mimetypes:video/mp4,video/quicktime', 'max:102400'],
        ], [
            'title.required' => 'Judul laporan wajib diisi.',
            'description.required' => 'Jelaskan kerusakan yang ditemukan.',
            'building.required' => 'Pilih gedung atau pilih Lainnya.',
            'building_custom.required_if' => 'Tuliskan nama gedung.',
            'floor_custom.required_if' => 'Tuliskan lantai.',
            'room_custom.required_if' => 'Tuliskan nama ruangan.',
            'photos.max' => 'Maksimal 5 foto dalam satu laporan.',
            'photos.*.mimes' => 'Foto harus berformat JPG, JPEG, atau PNG.',
            'photos.*.mimetypes' => 'Isi file foto tidak sesuai dengan format JPG/JPEG/PNG.',
            'photos.*.max' => 'Ukuran setiap foto maksimal 10 MB.',
            'videos.max' => 'Maksimal 2 video dalam satu laporan.',
            'videos.*.mimes' => 'Video harus berformat MP4 atau MOV.',
            'videos.*.mimetypes' => 'Isi file video tidak sesuai dengan format MP4/MOV.',
            'videos.*.max' => 'Ukuran setiap video maksimal 100 MB.',
        ]);

        $data['building'] = $data['building'] === '__other' ? trim($data['building_custom']) : $data['building'];
        $data['floor'] = ($data['floor'] ?? null) === '__other' ? trim($data['floor_custom']) : ($data['floor'] ?? null);
        $data['room'] = ($data['room'] ?? null) === '__other' ? trim($data['room_custom']) : ($data['room'] ?? null);
        unset($data['building_custom'], $data['floor_custom'], $data['room_custom']);

        $storedPaths = [];
        try {
            $report = DB::transaction(function () use ($request, $data, &$storedPaths) {
                $report = Report::create(collect($data)->except(['photos', 'videos'])->all() + [
                    'reporter_id' => $request->user()->id,
                    'status' => ReportStatus::SUBMITTED->value,
                ]);

                foreach (['photos' => 'foto', 'videos' => 'video'] as $field => $kind) {
                    foreach ($request->file($field, []) as $file) {
                        $path = $file->store('reports/'.$report->id, 'local');
                        $storedPaths[] = $path;
                        $report->media()->create([
                            'uploaded_by' => $request->user()->id,
                            'disk' => 'local',
                            'path' => $path,
                            'original_name' => Str::limit(basename($file->getClientOriginalName()), 180, ''),
                            'mime_type' => $file->getMimeType(),
                            'size' => $file->getSize(),
                            'kind' => $kind,
                        ]);
                    }
                }

                return $report;
            });
        } catch (Throwable $exception) {
            if ($storedPaths) Storage::disk('local')->delete($storedPaths);
            throw $exception;
        }

        return redirect()->route('reports.show', $report)->with('success', 'Laporan dan dokumentasi berhasil dikirim.');
    }

    public function show(Request $request, Report $report)
    {
        abort_unless((int) $report->reporter_id === (int) $request->user()->id, 403);
        $report->load('category', 'media', 'updates.user', 'activeAssignment.officer');
        return view('reporter.reports.show', ['report' => $report]);
    }
}
