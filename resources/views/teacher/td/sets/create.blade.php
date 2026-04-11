@extends('layouts.teacher')

@section('title', 'Nouveau TD')
@section('page_title', 'Nouveau TD')
@section('page_subtitle', 'Importez un document puis convertissez-le si vous voulez le modifier depuis l’éditeur de la plateforme.')

@section('content')
@include('teacher.td.sets.form', ['action' => route('teacher.td.sets.store'), 'isEdit' => false])
@endsection
