@extends('layouts.app-master')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card mt-4">
                    <div class="card-header">{{ __('Editar noticia') }}</div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <form action="/posts/{{ $post->id }}" method="post">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <label for="">Título</label>
                                <input type="text" name="title" class="form-control" value="{{ $post->title }}">
                            </div>

                            <div class="form-group mt-3">
                                <label for="">Cuerpo</label>
                                <textarea name="body" id="" cols="30" rows="10" class="form-control">{{ $post->body }}</textarea>
                            </div>

                            <div class="form-group mt-3">
                                <label for="">Publicado en fecha</label>
                                <input type="date" name="published_at" class="form-control"
                                    value="{{ date('Y-m-d', strtotime($post->published_at)) }}">
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">Enviar</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
