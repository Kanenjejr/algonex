<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Role extends Model
{
    use HasFactory;
    public function permissions() {
        return $this->belongsToMany(Permission::class,'roles_permissions');
     }
     public function users() {
        return $this->belongsToMany(User::class,'users_roles');
     }
     public function roles(){
      return $this->belongsToMany('App\Models\Role','roles_users','user_id','role_id');
  }
  public function hasAnyRole($roles){
      if(is_array($roles)){
          foreach ($roles as $role){
              if($this->hasRole($role)){
                  return true;
              }
          }
      }else{
          if($this->hasRole($roles)){
              return false;
          }
      }
      return false;
  }


  public function hasRole($role){
      if($this->roles->where('slug',$role)->first()){
          return true;
      }
      return false;
  }
}