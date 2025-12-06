@extends('layouts.app')

@section('page-title')
    <div class="page-title">
        <h3>{{ $pageTitle }}</h3>
    </div>
@endsection

@section('content')
    <div class="px-4 py-3">
        <div class="row">
            @foreach($categories as $cat)
                <div class="col-12 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white d-flex align-items-center justify-content-between">
                            <div class="h6 mb-0">{{ $cat['name'] }}</div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($cat['subs'] as $sub)
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="p-3 border rounded-3">
                                            <div class="fw-semibold mb-2">{{ $sub['name'] }}</div>
                                            @forelse($sub['items'] as $item)
                                                <div class="d-flex align-items-center justify-content-between py-2">
                                                    <div>
                                                        <div class="fw-medium">{{ $item->name }}</div>
                                                        @if($item->description)
                                                            <div class="text-muted small">{{ $item->description }}</div>
                                                        @endif
                                                    </div>
                                                    <div class="text-end">
                                                        <div class="badge" style="background-color: var(--header_color);">{{ $item->price }}</div>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="text-muted small">No items</div>
                                            @endforelse
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection

