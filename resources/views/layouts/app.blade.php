@extends('adminlte::page')

{{-- Extend and customize the browser title --}}

@section('title')
    {{ config('adminlte.title') }}
    @hasSection('subtitle') | @yield('subtitle') @endif
@stop

{{-- Extend and customize the page content header --}}

@section('content_header')
    @hasSection('content_header_title')
        <h1 class="text-muted">
            @yield('content_header_title')

            @hasSection('content_header_subtitle')
                <small class="text-dark">
                    <i class="fas fa-xs fa-angle-right text-muted"></i>
                    @yield('content_header_subtitle')
                </small>
            @endif
        </h1>
    @endif
@stop

{{-- Rename section content to content_body --}}

@section('content')
    @yield('content_body')
@stop

{{-- Create a common footer --}}

@section('footer')
    <div class="float-right">
        Version: {{ config('app.version', '1.0.0') }}
    </div>

    <strong>
        <a href="{{ config('app.company_url', '#') }}">
            {{ config('app.company_name', 'My company') }}
        </a>
    </strong>
@stop

{{-- Add common Javascript/Jquery code --}}

@section('plugins.Jquery', true)

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function fixSelect2Scroll() {
        var dropdowns = document.querySelectorAll('.select2-dropdown');
        dropdowns.forEach(function(dropdown) {
            dropdown.style.maxHeight = '350px';
            dropdown.style.overflow = 'hidden';
            var results = dropdown.querySelector('.select2-results');
            if (results) {
                results.style.maxHeight = '350px';
                results.style.overflowY = 'auto';
            }
        });
    }
    
    setTimeout(function() {
        $(document).on('select2:open', function() {
            setTimeout(fixSelect2Scroll, 10);
        });
        setInterval(fixSelect2Scroll, 100);
    }, 1000);
});
</script>
@endsection
