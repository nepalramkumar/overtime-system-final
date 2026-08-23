<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'employee_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            // 'password' => 'hashed', // यो लाइनलाई हटाउने वा comment गर्ने
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    // Testing बेला सबै OT-workflow email एउटै ठेगानामा redirect गर्न (services.mail_test_redirect / .env: MAIL_TEST_REDIRECT_TO)
    public function routeNotificationForMail($notification)
    {
        if ($testEmail = config('services.mail_test_redirect')) {
            return $testEmail;
        }
        return $this->email;
    }
}