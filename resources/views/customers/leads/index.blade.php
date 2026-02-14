@extends('layouts.app')

@section('title', 'Leads')

@section('content')
    <div class="h-[calc(100vh-3.5rem)]">
        <livewire:leads.lead-kanban />
    </div>
@endsection
