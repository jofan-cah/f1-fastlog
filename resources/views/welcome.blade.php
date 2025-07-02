@extends('layouts.app')

@section('title', 'Dashboard - LogistiK Admin')

@section('content')
@endsection

@push('scripts')
<script>
    // Auto refresh dashboard setiap 5 menit
    setInterval(function() {
        location.reload();
    }, 300000);
</script>
@endpush
