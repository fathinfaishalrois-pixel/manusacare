<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class User extends Authenticatable {
 use HasFactory,Notifiable;
 protected $fillable=['name','identity_number','identity_type','email','phone','password','class_or_position','is_active']; protected $hidden=['password','remember_token'];
 protected function casts():array{return ['password'=>'hashed','is_active'=>'boolean','email_verified_at'=>'datetime'];}
 public function roles(){return $this->belongsToMany(Role::class,'user_roles');} public function hasRole(string $role):bool{return $this->roles()->where('slug',$role)->exists();} public function hasPermission(string $permission):bool{return $this->roles()->whereHas('permissions',fn($q)=>$q->where('slug',$permission))->exists();}
 public function reports(){return $this->hasMany(Report::class,'reporter_id');} public function assignments(){return $this->hasMany(Assignment::class,'officer_id');}
}
