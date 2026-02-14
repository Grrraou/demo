@extends('layouts.app')

@section('title', 'General Ledger')

@section('content')
<div class="h-[calc(100vh-3.5rem)]">
    <livewire:accounting.general-ledger />
</div>
@endsection
