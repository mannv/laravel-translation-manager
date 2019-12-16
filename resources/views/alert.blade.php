@php
    $errorMessage = [];
    if (session()->has('errors')) {
        foreach (session()->get('errors')->getBag('default')->messages() as $item) {
            $errorMessage = array_merge($errorMessage, $item);
        }
    }
    $messageType = ['success', 'danger', 'warning', 'info'];
@endphp

@foreach($messageType as $type)
    @if(session()->has($type))
        <div class="alert mt-2 alert-{{$type}}" role="alert">
            {{session()->get($type)}}
        </div>
    @endif
@endforeach

{{--@if(!empty($errorMessage))--}}
{{--    <div class="alert alert-danger" role="alert">--}}
{{--        <ul>--}}
{{--            @foreach($errorMessage as $msg)--}}
{{--                <li>{{$msg}}</li>--}}
{{--            @endforeach--}}
{{--        </ul>--}}
{{--    </div>--}}
{{--@endif--}}
