<?php

use App\ComplexProperty;
use App\ControlledVocabulary;
use App\CvTerm;
use App\DataDownload;
use App\Dataset;
use App\PropertyValue;
use App\Trayectoria;
use Tests\TestCase;

uses(TestCase::class);

it('maps complex property relationships to the schema keys', function () {
    $property = new ComplexProperty();

    expect($property->getTable())->toBe('complex_property')
        ->and($property->parent()->getForeignKeyName())->toBe('parent_id')
        ->and($property->parent()->getOwnerKeyName())->toBe('id')
        ->and($property->children()->getForeignKeyName())->toBe('parent_id')
        ->and($property->children()->getLocalKeyName())->toBe('id')
        ->and($property->descendants()->getEagerLoads())->toHaveKey('descendants')
        ->and($property->trajectories()->getTable())->toBe('trajectory_complex_property_link')
        ->and($property->trajectories()->getForeignPivotKeyName())->toBe('complex_property_id')
        ->and($property->trajectories()->getRelatedPivotKeyName())->toBe('trajectory_id');
});

it('maps trajectories and controlled vocabulary relationships', function () {
    $trajectoryProperties = (new Trayectoria())->complexProperties();
    $vocabulary = new ControlledVocabulary();
    $term = new CvTerm();

    expect($trajectoryProperties->getTable())->toBe('trajectory_complex_property_link')
        ->and($trajectoryProperties->getForeignPivotKeyName())->toBe('trajectory_id')
        ->and($trajectoryProperties->getRelatedPivotKeyName())->toBe('complex_property_id')
        ->and($vocabulary->getTable())->toBe('cv')
        ->and($vocabulary->terms()->getForeignKeyName())->toBe('cv_id')
        ->and($term->getTable())->toBe('cv_term')
        ->and($term->controlledVocabulary()->getForeignKeyName())->toBe('cv_id');
});

it('uses exact schema table names and casts complex values', function () {
    $booleanProperty = (new ComplexProperty())->forceFill([
        'type' => 'boolean',
        'atomic_value_boolean' => 0,
    ]);

    expect((new PropertyValue())->getTable())->toBe('PropertyValue')
        ->and((new Dataset())->getTable())->toBe('Dataset')
        ->and((new DataDownload())->getTable())->toBe('DataDownload')
        ->and($booleanProperty->value)->toBeFalse();
});