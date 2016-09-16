@extends('admin.layout.default')
@section('content')
<?php
    $diff_array = array_diff($files_in_folder, $files_in_db);

    $n = count($files_in_folder);
?>

<div class="main-central-wrap clearfix">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    @for($i = 0; $i < $n; $i++)

        @if( ($files_in_folder[$i] != '.') && ($files_in_folder[$i] != '..') )

            @if( in_array($files_in_folder[$i], $diff_array, true) )
                <div class="file-image-wrap not-used">
            @else
                <div class="file-image-wrap">
            @endif
                    <img src="{{ URL::asset('/img/card_images/'.$files_in_folder[$i]) }}" alt="{{ $files_in_folder[$i] }}">
                    <p>{{ $files_in_folder[$i] }}</p>
                </div>
        @endif

    @endfor

    <div class="file-drop-button-wrap">
        <input class="button" type="button" name="dropFile" value="Удалить выделеное">
    </div>
</div>

@stop