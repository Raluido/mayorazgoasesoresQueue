@extends('layouts.app-master')

@section('content')
    <div class="bg-light p-3 mt-5 rounded">
        @auth
            <div class="container">
                <div class="row w-75 gy-4 mx-auto">
                    @role('supervisor')
                        <h3>Área de administración</h3>
                        <p>Sólo el supervisor puede acceder a éstas opciones</p>
                        <div class="border col-4" style="height: 110px">
                            <h5>Gestión de empresas</h5>
                            <a href="{{ route('users.addUsersAutoForm') }}" class="nav-link px-2 text-dark">Añadir empresas
                                automaticamente</a>
                            <a class="nav-link px-2 text-dark" href="{{ route('users.index') }}">Listar empresas</a>
                        </div>
                        <div class="border col-4" style="height: 110px">
                            <h5>Nóminas</h5>
                            <a href="{{ route('payrolls.uploadForm') }}" class="nav-link px-2 text-dark">Subir nóminas</a>
                            <a href="{{ route('payrolls.showForm') }}" class="nav-link px-2 text-dark">Listar nóminas</a>
                        </div>
                        <div class="border col-4" style="height: 110px">
                            <h5>Modelos</h5>
                            <a href="" class="nav-link px-2 text-dark">Modelo 111</a>
                            <a href="" class="nav-link px-2 text-dark">Modelo 190</a>
                        </div>
                        <div class="border col-4" style="height: 110px">
                            <h5>Seguridad social</h5>
                            <a href="" class="nav-link px-2 text-dark">Notificaciones de Seguridad Social</a>
                        </div>
                        <div class="border col-4" style="height: 110px">
                            <h5>Otras gestiones</h5>
                            <a href="" class="nav-link px-2 text-dark">Imputaciones de costes y otras</a>
                        </div>
                    @endrole
                    @role('admin')
                        <h3>Área de administración</h3>
                        <p>Sólo el supervisor puede acceder a éstas opciones</p>
                        <div class="border col-4" style="height: 110px">
                            <h5>Gestión de empresas</h5>
                            <a href="{{ route('users.addUsersAutoForm') }}" class="nav-link px-2 text-dark">Añadir empresas
                                automaticamente</a>
                            <a class="nav-link px-2 text-dark" href="{{ route('users.index') }}">Listar empresas</a>
                        </div>
                        <div class="border col-4" style="height: 110px">
                            <h5>Nóminas</h5>
                            <a href="{{ route('payrolls.uploadForm') }}" class="nav-link px-2 text-dark">Subir nóminas</a>
                            <a href="{{ route('payrolls.showForm') }}" class="nav-link px-2 text-dark">Listar nóminas</a>
                        </div>
                        <div class="border col-4" style="height: 110px">
                            <h5>Modelos</h5>
                            <a href="" class="nav-link px-2 text-dark">Modelo 111</a>
                            <a href="" class="nav-link px-2 text-dark">Modelo 190</a>
                        </div>
                        <div class="border col-4" style="height: 110px">
                            <h5>Seguridad social</h5>
                            <a href="" class="nav-link px-2 text-dark">Notificaciones de Seguridad Social</a>
                        </div>
                        <div class="border col-4" style="height: 110px">
                            <h5>Otras gestiones</h5>
                            <a href="" class="nav-link px-2 text-dark">Imputaciones de costes y otras</a>
                        </div>
                    @endrole
                </div>
                <div class="row w-75 mt-3 gy-4 mx-auto">
                    @role('user')
                        <h3>Área de descargas de empresas</h3>
                        <div class="border col-4" style="height: 110px">
                            <h5>Nóminas</h5>
                            <a href="{{ route('payrolls.downloadForm') }}" class="nav-link px-2 text-dark">Nóminas</a>
                        </div>
                        <div class="border col-4" style="height: 110px">
                            <h5>Modelos</h5>
                            <a href="" class="nav-link px-2 text-dark">Modelo 111</a>
                            <a href="" class="nav-link px-2 text-dark">Modelo 190</a>
                        </div>
                        <div class="border col-4" style="height: 110px">
                            <h5>Seguridad social</h5>
                            <a href="" class="nav-link px-2 text-dark">Notificaciones de Seguridad Social</a>
                        </div>
                        <div class="border col-4" style="height: 110px">
                            <h5>Otras documentos</h5>
                            <a href="" class="nav-link px-2 text-dark">Imputaciones de costes y otras</a>
                        </div>
                    @endrole
                </div>
            @endauth
            @guest
                <div class="">
                    <h1>Bienvenido a Mayorazgo Asesores</h1>
                </div>
                <div class="mt-3">
                    <p class="lead">Por favor, acceda con el usuario y la contraseña que le hemos facilitado.</p>
                </div>
            @endguest
        </div>
    @endsection
