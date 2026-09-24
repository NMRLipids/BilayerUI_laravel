<?php

namespace App;

class DataDownload extends AppModel
{
    protected $table = 'DataDownload';

    public $timestamps = false;

    protected $casts = [
        'contentSize' => 'integer',
        'hasPart' => 'array',
    ];
}