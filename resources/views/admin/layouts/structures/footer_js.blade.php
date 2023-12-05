{!! loadFiles([
    'vendor/bootstrap.min',
    'vendor/utils/loadingoverlay.min',
    'vendor/utils/loadingoverlay_progress.min',
    'vendor/utils/moment.min',
    'vendor/utils/min',
    'vendor/utils/common',
    'vendor/utils/xhr',
    'vendor/utils/system',
    'vendor/toastr.min',
    'vendor/sweetalert2.all'
], '', 'js') !!}

{!! loadFiles(['autoload/admin'], 'admin', 'js') !!}

@include('admin.layouts.structures.footer_autoload')

<script type="text/javascript">
    @if(session()->has('action_failed'))
        toastr.error("{{ session()->get('action_failed') }}");
    @elseif(session()->has('action_success'))
        toastr.success("{{ session()->get('action_success') }}");
    @endif
</script>
