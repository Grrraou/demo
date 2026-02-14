@extends('layouts.app')

@section('title', 'Journal Entries')

@section('content')
<div class="h-[calc(100vh-3.5rem)]">
    <livewire:accounting.journal-entries />
</div>
@endsection
