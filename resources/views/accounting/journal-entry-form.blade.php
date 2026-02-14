@extends('layouts.app')

@section('title', isset($entryId) ? 'Edit Journal Entry' : 'New Journal Entry')

@section('content')
<div class="h-[calc(100vh-3.5rem)]">
    <livewire:accounting.journal-entry-form :entry-id="$entryId ?? null" />
</div>
@endsection
