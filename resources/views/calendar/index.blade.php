@extends('layouts.app')

@section('title', 'Calendar')

@section('content')
    <div class="h-[calc(100vh-3.5rem)]">
        <livewire:calendar.calendar-view />
    </div>
@endsection
