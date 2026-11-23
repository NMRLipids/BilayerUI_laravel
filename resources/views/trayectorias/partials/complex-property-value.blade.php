@php
    use App\ComplexProperty;
    use App\CvTerm;
    use Illuminate\Database\Eloquent\Model;

    if ($value instanceof CvTerm) {
        $attributes = collect($value->getAttributes())
            ->except(['id', 'cv_id'])
            ->map(fn ($attribute, $key) => $value->getAttribute($key));

        if ($value->controlledVocabulary?->uri) {
            $attributes->put('inDefinedTermSet', $value->controlledVocabulary->uri);
        }
    } elseif ($value instanceof Model && ! $value instanceof ComplexProperty) {
        $attributes = collect($value->getAttributes())
            ->except(['id', 'created_at', 'updated_at'])
            ->map(fn ($attribute, $key) => $value->getAttribute($key));
    } else {
        $attributes = null;
    }
@endphp

@if ($value instanceof ComplexProperty)
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-sm table-glass mb-0">
            <tbody>
                @include('trayectorias.partials.complex-property-rows', ['properties' => collect([$value])])
            </tbody>
        </table>
    </div>
@elseif ($attributes !== null)
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-sm table-glass mb-0">
            <tbody>
                @foreach ($attributes as $key => $attribute)
                    <tr>
                        <th scope="row">{{ $key }}</th>
                        <td>@include('trayectorias.partials.complex-property-value', ['value' => $attribute])</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@elseif (is_array($value))
    @if (count($value) === 0)
        <span class="text-white-50">N/A</span>
    @else
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm table-glass mb-0">
                <tbody>
                    @foreach ($value as $key => $item)
                        <tr>
                            <th scope="row">{{ is_int($key) ? $key + 1 : $key }}</th>
                            <td>@include('trayectorias.partials.complex-property-value', ['value' => $item])</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@elseif (is_bool($value))
    {{ $value ? 'Yes' : 'No' }}
@elseif (is_string($value) && preg_match('#^(https?://(dx\.)?doi\.org/|doi:\s*)?10\.\S+$#i', trim($value)))
    {!! renderDOI($value) !!}
@elseif (is_string($value) && filter_var($value, FILTER_VALIDATE_URL))
    <a href="{{ $value }}" target="_blank" rel="noopener noreferrer">{{ $value }}</a>
@elseif ($value === null || $value === '')
    <span class="text-white-50">N/A</span>
@else
    {{ $value }}
@endif