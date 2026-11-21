<?php
namespace Database\Seeders;
use App\Models\{User,Role,Permission,Category};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder {
 public function run(): void {
  $roles=[]; foreach(['reporter'=>'Pelapor','officer'=>'Petugas','admin'=>'Admin'] as $slug=>$name){$roles[$slug]=Role::firstOrCreate(['slug'=>$slug],['name'=>$name]);}
  $permissionNames=['reports.view.all'=>'Melihat seluruh laporan','reports.verify'=>'Verifikasi laporan','reports.assign'=>'Menugaskan petugas','users.manage'=>'Mengelola pengguna','categories.manage'=>'Mengelola kategori','settings.manage'=>'Mengelola pengaturan','audit.view'=>'Melihat audit log'];
  foreach($permissionNames as $slug=>$name){$p=Permission::firstOrCreate(['slug'=>$slug],['name'=>$name]);$roles['admin']->permissions()->syncWithoutDetaching([$p->id]);}
  $admin=User::firstOrCreate(['identity_number'=>'admin'],['name'=>'Administrator MANUSACARE','email'=>'admin@manusacare.local','password'=>Hash::make('ChangeMe123!'),'is_active'=>true]);
  if(!$admin->email){$admin->email='admin@manusacare.local';$admin->save();}
  $admin->roles()->syncWithoutDetaching([$roles['admin']->id]);
  foreach(['Kerusakan fasilitas','Kelistrikan','Sanitasi dan kebersihan','Teknologi','Keamanan dan keselamatan','Lainnya'] as $name){Category::firstOrCreate(['slug'=>str($name)->slug()],['name'=>$name,'is_active'=>true]);}
 }
}
