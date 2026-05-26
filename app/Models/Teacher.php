<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $table = 'table_teachers';

    protected $fillable = [
        'name', 'gender', 'email', 'phone',
        'date_of_birth', 'address', 'status', 'image', 'subject',
    ];

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'subject_teacher', 'teacher_id', 'subject_id');
    }
    public function students()
    {
        return $this->hasManyThrough(
            Student::class,
            ClassStudent::class,
            'teacher_id',
            'class_id',
            'id',
            'id'
        );
    }
}
