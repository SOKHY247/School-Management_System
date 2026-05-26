<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $table = 'table_students';

    protected $fillable = [
        'name', 'gender', 'email', 'phone',
        'date_of_birth', 'address', 'class_id',
        'status', 'image',
    ];

    
    public function studentClass()
    {
        return $this->belongsTo(ClassStudent::class, 'class_id');
    }

    // A student belongs to a teacher through class
    public function teacher()
    {
        return $this->hasOneThrough(
            Teacher::class,
            ClassStudent::class,
            'id',
            'id',
            'class_id',
            'teacher_id'
        );
    }
    public function scores()
    {
        return $this->hasMany(Score::class);
    }

   
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'table_scores', 'student_id', 'subject_id');
    }
}
