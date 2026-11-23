<?php

namespace App;

class ControlledVocabulary extends AppModel
{
    protected $table = 'cv';

    public $timestamps = false;

    public function terms()
    {
        return $this->hasMany(CvTerm::class, 'cv_id', 'id');
    }
}