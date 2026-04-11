@extends('layouts.admin')
@section('title', 'Modifier TD')
@section('page_title', 'Modifier le TD')
@section('page_subtitle', 'Mise à jour, désactivation temporaire ou suppression d’un TD existant.')
@section('content')
@include('admin.td.form', ['td' => $td, 'assignments' => $assignments, 'action' => $action, 'isEdit' => $isEdit])
@endsection
