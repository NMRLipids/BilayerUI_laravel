<?php

namespace App;

class CvTerm extends AppModel
{
    protected $table = 'cv_term';

    public $timestamps = false;

    public function controlledVocabulary()
    {
        return $this->belongsTo(ControlledVocabulary::class, 'cv_id', 'id');
    }
}