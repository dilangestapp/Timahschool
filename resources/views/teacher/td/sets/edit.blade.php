@extends('layouts.teacher')

@section('title', 'Modifier TD')
@section('page_title', 'Modifier le TD')
@section('page_subtitle', 'Conservez le document source, modifiez la version éditable et publiez ensuite la version finale.')

@section('content')
@include('teacher.td.sets.form', ['action' => route('teacher.td.sets.update', $td), 'isEdit' => true])
@endsection
