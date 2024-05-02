@extends('layouts.app')

@section('title')
<a href="#!" class="breadcrumb">{{$item->name}}</a>
@endsection

@section('content')

<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <span class="card-title">{{$item->name}}</span>
                <p>Ez egy jó terem.</p>
                @include('reservations.timetable')
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function(){
            $('.tooltipped').tooltip();
        });
    </script>
@endpush
