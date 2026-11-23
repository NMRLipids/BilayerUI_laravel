<?php

namespace App;

use Illuminate\Database\Eloquent\Casts\Attribute;

class ComplexProperty extends AppModel
{
    protected $table = 'complex_property';

    public $timestamps = false;

    protected $casts = [
        'hidden' => 'boolean',
        'atomic_value_integer' => 'integer',
        'atomic_value_numeric' => 'decimal:30',
        'atomic_value_float' => 'float',
        'atomic_value_boolean' => 'boolean',
        'multiple' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }

    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    public function trajectories()
    {
        return $this->belongsToMany(
            Trayectoria::class,
            'trajectory_complex_property_link',
            'complex_property_id',
            'trajectory_id'
        )->withPivot('metadata');
    }

    public function referencedProperty()
    {
        return $this->belongsTo(self::class, 'value_id', 'id');
    }

    public function cvTerm()
    {
        return $this->belongsTo(CvTerm::class, 'value_id', 'id');
    }

    public function propertyValue()
    {
        return $this->belongsTo(PropertyValue::class, 'value_id', 'id');
    }

    public function dataDownload()
    {
        return $this->belongsTo(DataDownload::class, 'value_id', 'id');
    }

    public function dataset()
    {
        return $this->belongsTo(Dataset::class, 'value_id', 'id');
    }

    // Accessor for the value attribute based on the type of the complex property.
    // This accessor dynamically returns the appropriate value based on the type of the complex property.
    
    protected function value(): Attribute
    {
        return Attribute::get(function () {
            return match ($this->type) {
                'string' => $this->atomic_value_string,
                'integer' => $this->atomic_value_integer,
                'numeric' => $this->atomic_value_numeric,
                'float' => $this->atomic_value_float,
                'boolean' => $this->atomic_value_boolean,
                'property' => $this->referencedProperty,
                'cv_term' => $this->cvTerm,
                'PropertyValue' => $this->propertyValue,
                'DataDownload' => $this->dataDownload,
                'Dataset' => $this->dataset,
                default => null,
            };
        });
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }
}