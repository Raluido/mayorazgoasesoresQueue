<h4>Listado de empresas para acceder al area de clientes</h4>

@if ($usersNifPass)
    <table style="width:100%">
        <tr>
            <th>Nif</th>
            <th>Contraseña</th>
        </tr>
        @foreach ($usersNifPass as $userNifPass)
            <tr>
                <td>{{ $userNifPass[0] }}</td>
                <td>{{ $userNifPass[1] }}</td>
            </tr>
        @endforeach
    </table>
@endif
