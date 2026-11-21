<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ReportMedia extends Model { public function report(){return $this->belongsTo(Report::class);} protected $fillable=['report_id','uploaded_by','disk','path','original_name','mime_type','size','kind']; }
