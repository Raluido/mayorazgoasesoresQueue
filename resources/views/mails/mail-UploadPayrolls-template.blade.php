<h4>Listado de nóminas con errores</h4>

@if (empty($uploadError))
    <p>No hay errores</p>
@else
    @foreach ($uploadError as $index)
        <p>{{ $index }}</p>
    @endforeach
@endif
