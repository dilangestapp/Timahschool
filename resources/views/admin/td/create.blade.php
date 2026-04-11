@extends('layouts.admin')
@section('title', 'Nouveau TD')
@section('page_title', 'Créer un TD')
@section('page_subtitle', 'Création directe d’un TD depuis l’administration, avec choix de l’affectation concernée.')
@section('content')
@include('admin.td.form', ['td' => $td, 'assignments' => $assignments, 'action' => $action, 'isEdit' => $isEdit])
@endsection
