<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
   /** @use HasFactory<\Database\Factories\UserFactory> */
   use HasApiTokens, HasFactory, Notifiable, HasRoles;

   /**
    * The attributes that are mass assignable.
    *
    * @var list<string>
    */
   protected $fillable = [
      'name',
      'email',
      'password',
      'phone',
      'role',
      'student_id',
      'address',
      'membership_date',
      'is_active'
   ];

   /**
    * The attributes that should be hidden for serialization.
    *
    * @var list<string>
    */
   protected $hidden = [
      'password',
      'remember_token',
   ];

   /**
    * Get the attributes that should be cast.
    *
    * @return array<string, string>
    */
   protected function casts(): array
   {
      return [
         'email_verified_at' => 'datetime',
         'password' => 'hashed',
         'membership_date' => 'date',
         'is_active' => 'boolean',
      ];
   }

   /**
    * Get the loans for the user.
    */
   public function loans()
   {
      return $this->hasMany(Loan::class);
   }

   /**
    * Get the approved loans for the user.
    */
   public function approvedLoans()
   {
      return $this->hasMany(Loan::class, 'approved_by');
   }

   /**
    * Check if user is admin
    */
   public function isAdmin(): bool
   {
      return $this->role === 'admin';
   }

   /**
    * Check if user is staff
    */
   public function isStaff(): bool
   {
      return in_array($this->role, ['staff', 'admin']);
   }

   /**
    * Check if user is member
    */
   public function isMember(): bool
   {
      return $this->role === 'member';
   }
}
