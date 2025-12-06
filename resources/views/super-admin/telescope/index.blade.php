@extends('layouts.app')

@section('content')
<div class="w-100 d-flex">
    @if(user()->is_superadmin)
        <x-super-admin.setting-sidebar :activeMenu="'telescope'"/>
    @endif

    <x-setting-card>
        <x-slot name="header">
            <div class="s-b-n-header d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-0">Telescope</h4>
                    <p class="text-muted mb-0 small">Filter and view entries</p>
                </div>
            </div>
        </x-slot>

        <div class="px-4 py-3">
            <form method="get" action="{{ route('superadmin.telescope.dashboard') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <x-forms.select fieldId="type" fieldName="type" :fieldLabel="'Type'">
                        <option value="">All</option>
                        @foreach(['requests','commands','schedule','jobs','batches','cache','dumps','events','exceptions','gates','http-client','logs','mail','models','notifications','queries','redis','views'] as $t)
                            <option value="{{ $t }}" @if($type===$t) selected @endif>{{ ucfirst(str_replace('-', ' ', $t)) }}</option>
                        @endforeach
                    </x-forms.select>
                </div>
                <div class="col-md-3">
                    <x-forms.text fieldId="from" fieldName="from" :fieldLabel="'From'" fieldPlaceholder="YYYY-MM-DD" value="{{ optional($from)->toDateString() }}" />
                </div>
                <div class="col-md-3">
                    <x-forms.text fieldId="to" fieldName="to" :fieldLabel="'To'" fieldPlaceholder="YYYY-MM-DD" value="{{ optional($to)->toDateString() }}" />
                </div>
                <div class="col-md-3">
                    <x-forms.text fieldId="search" fieldName="search" :fieldLabel="'Search'" fieldPlaceholder="UUID, content" value="{{ $search }}" />
                </div>
                <div class="col-md-12">
                    <x-forms.button-primary icon="filter" type="submit">Filter</x-forms.button-primary>
                </div>
            </form>
        </div>

        <div class="table-responsive px-4 pb-4">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Type</th>
                        <th>UUID</th>
                        <th>Content</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entries as $e)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($e->created_at)->toDateTimeString() }}</td>
                            <td>{{ $e->type }}</td>
                            <td class="text-monospace">{{ $e->uuid }}</td>
                            <td><pre class="mb-0" style="white-space:pre-wrap; max-height:180px; overflow:auto;">{{ Str::limit($e->content, 1000) }}</pre></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No entries</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div>
                {{ $entries->links() }}
            </div>
        </div>
    </x-setting-card>
</div>
@endsection

