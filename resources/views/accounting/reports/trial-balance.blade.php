@extends('layouts.app')

@section('title', 'Trial Balance')

@section('content')
<div class="h-[calc(100vh-3.5rem)]">
    <livewire:accounting.trial-balance />
</div>
@endsection
