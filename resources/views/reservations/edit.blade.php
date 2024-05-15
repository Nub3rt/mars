@extends('layouts.app')

{{-- If we are creating a new reservation, $reservation is not set. --}}
@php
if(isset($reservation)) $item = $reservation->reservableItem;
@endphp

@section('title')
<a href="{{route('reservations.items.index')}}" class="breadcrumb" style="cursor: pointer">@lang('reservations.reservations')</a>
<a href="{{route('reservations.items.show', $item)}}"
  class="breadcrumb" style="cursor: pointer">
    {{ $item->name }}
</a>
@endsection
@section('student_council_module') active @endsection

@section('content')

<div class="row">
    <div class="col s12">
        <div class="card">
            <form
                action="{{ isset($reservation) ? route('reservations.update', ['reservation' => $reservation])
                                               : route('reservations.store', ['item' => $item]) }}"
                method="POST">
                @csrf

                <div class="card-content">
                    <span class="card-title">@lang('reservations.item')</span>
                    <div class="row">
                        <span s="12" l="6">{{ $item->name }}</span>
                        <span s="12" l="6">{{ (isset($reservation) && isset($reservation->user))
                                              ? $reservation->user->name
                                              : ''  }}</span>
                    </div>
                    @if($item->type == 'room')
                    <div class="row">
                        <x-input.text s="12" type="text" text="reservations.title"
                            id="title" :value="isset($reservation) ? $reservation->title : ''"
                            maxlength="127"/>
                    </div>
                    @else
                    <input type="hidden" id="title" name="title" value="" />
                    @endif
                    <div class="row">
                        <x-input.text  m="6" id="reserved_from" type="datetime-local" without-label :helper="__('reservations.from')"
                                       :value="isset($reservation) ? $reservation->reserved_from : ''" required/>
                        <x-input.text  m="6" id="reserved_until" type="datetime-local" without-label :helper="__('reservations.until')"
                                       :value="isset($reservation) ? $reservation->reserved_until : ''" required/>
                    </div>
                    <div class="row">
                    <x-input.textarea s="12" id="note" text="{{ __('reservations.note') }}"
                            value="{{ isset($reservation) ? $reservation->note : '' }}"
                            maxlength="2047"/>
                    </div>
                </div>
                <div class="card-action right-align">
                    <a href="{{ url()->previous() }}" class="waves-effect btn">@lang('general.cancel')</a>
                    <button type="submit" class="waves-effect btn">@lang('general.save')</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
