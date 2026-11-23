<?php

use App\ComplexProperty;
use App\DataDownload;
use Illuminate\Support\Collection;
use Tests\TestCase;

uses(TestCase::class);

function propertyNode(string $name, string $type, mixed $value, array $children = []): ComplexProperty
{
    $property = (new ComplexProperty())->forceFill([
        'name' => $name,
        'type' => $type,
        match ($type) {
            'boolean' => 'atomic_value_boolean',
            'integer' => 'atomic_value_integer',
            'float' => 'atomic_value_float',
            'numeric' => 'atomic_value_numeric',
            default => 'atomic_value_string',
        } => $value,
    ]);
    $property->setRelation('children', new Collection($children));

    return $property;
}

it('renders complex properties at arbitrary depth and links URLs', function () {
    $leaf = propertyNode('sameAs', 'string', 'https://example.org/simulation/246');
    $license = propertyNode('license', 'string', null, [$leaf]);
    $root = propertyNode('bioschema_properties', 'string', null, [$license]);

    $html = view('trayectorias.partials.complex-property-rows', [
        'properties' => collect([$root]),
    ])->render();

    expect($html)
        ->toContain('bioschema_properties')
        ->toContain('license')
        ->toContain('sameAs')
        ->toContain('href="https://example.org/simulation/246"')
        ->toContain('rel="noopener noreferrer"');
});

it('renders typed model and array values as nested tables', function () {
    $download = (new DataDownload())->forceFill([
        '@type' => 'DataDownload',
        'name' => 'Trajectory files',
        'contentUrl' => 'https://example.org/files',
        'hasPart' => ['trajectory.xtc', 'topology.tpr'],
    ]);
    $property = propertyNode('distribution', 'DataDownload', null);
    $property->setRelation('dataDownload', $download);

    $html = view('trayectorias.partials.complex-property-rows', [
        'properties' => collect([$property]),
    ])->render();

    expect($html)
        ->toContain('DataDownload')
        ->toContain('contentUrl')
        ->toContain('href="https://example.org/files"')
        ->toContain('trajectory.xtc')
        ->toContain('topology.tpr');
});

it('renders DOI-looking values through the DOI resolver', function () {
    $bareDoi = propertyNode('citation', 'string', '10.1016/j.bbamem.2022.183961');
    $doiUrl = propertyNode('sameAs', 'string', 'https://doi.org/10.5281/zenodo.6778072');

    $html = view('trayectorias.partials.complex-property-rows', [
        'properties' => collect([$bareDoi, $doiUrl]),
    ])->render();

    expect($html)
        ->toContain('href="https://doi.org/10.1016/j.bbamem.2022.183961"')
        ->toContain('class="doi-link"')
        ->toContain('data-doi="10.5281/zenodo.6778072"');
});