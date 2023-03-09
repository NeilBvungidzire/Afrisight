@extends('layouts.admin')

@section('title', 'Admin' . ' - ' . 'Target audience' . ' - ' . config('app.name'))

@section('content')
    <h1>Target Audience <span class="badge badge-info">{{ $project->code }}</span></h1>
@endsection
