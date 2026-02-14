@extends('layouts.app')

@section('title', 'Chart of Accounts')

@section('content')
<div class="h-[calc(100vh-3.5rem)]">
    <livewire:accounting.chart-of-accounts />
</div>
@endsection
