@foreach ($properties as $property)
    <tr>
        <th scope="row">
            {{ $property->name ?? 'Unnamed property' }}
            @if ($property->description)
                <small class="d-block fw-normal text-white-50">{{ $property->description }}</small>
            @endif
        </th>
        <td>
            @if ($property->value !== null)
                @include('trayectorias.partials.complex-property-value', ['value' => $property->value])
                @if ($property->unit)
                    <span class="ms-1">{{ $property->unit }}</span>
                @endif
            @endif

            @if ($property->children->isNotEmpty())
                <div class="table-responsive{{ $property->value !== null ? ' mt-2' : '' }}">
                    <table class="table table-bordered table-striped table-sm table-glass mb-0">
                        <tbody>
                            @include('trayectorias.partials.complex-property-rows', ['properties' => $property->children])
                        </tbody>
                    </table>
                </div>
            @elseif ($property->value === null)
                <span class="text-white-50">N/A</span>
            @endif
        </td>
    </tr>
@endforeach