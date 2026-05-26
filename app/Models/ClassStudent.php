<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassStudent extends Model
{
    protected $table = 'table_classes';

    protected $fillable = [
        'class_name',
        'section',
        'teacher_id',
        'subject_id'
    ];

    // A class belongs to a teacher
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    // A class has many students
    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'class_subject', 'class_id', 'subject_id');
    }
}
