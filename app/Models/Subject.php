<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $table = 'table_subjects';

    protected $fillable = [
        'name', 'code', 'description', 'status',
    ];

    public function scores()
    {
        return $this->hasMany(Score::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'table_scores', 'subject_id', 'student_id');
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'subject_teacher', 'subject_id', 'teacher_id');
    }

    public function classes()
    {
        return $this->belongsToMany(ClassStudent::class, 'class_subject', 'subject_id', 'class_id');
    }
}
