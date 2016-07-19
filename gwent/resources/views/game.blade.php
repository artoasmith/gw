@extends('layouts.default')
@section('content')
<?php
$user = Auth::user();
$errors = $errors->all();
dd($current_race);
?>

@stop