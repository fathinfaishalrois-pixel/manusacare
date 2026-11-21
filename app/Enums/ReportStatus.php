<?php
namespace App\Enums;
enum ReportStatus: string {
    case SUBMITTED = 'dikirim';
    case IN_PROGRESS = 'diproses';
    case COMPLETED = 'selesai';
    case REJECTED = 'ditolak';
    public function label(): string { return match($this) { self::SUBMITTED=>'Dikirim', self::IN_PROGRESS=>'Diproses', self::COMPLETED=>'Selesai', self::REJECTED=>'Ditolak' }; }
}
