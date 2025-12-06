@extends('layouts.public')

@section('content')
<div class="container py-4">
    <div id="react-root"></div>
</div>
@endsection

@push('scripts')
<script src="{{ mix('js/react.js') }}" defer></script>
@endpush
