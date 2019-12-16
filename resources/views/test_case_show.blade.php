@extends('seleniumidemanager::layouts.master')
@section('content')
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <span class="text-success">{{$data['suite']['name']}}</span> Test case <span class="text-danger">{{$data['name']}}</span>
        </h1>
    </div>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
            <tr>
                <th>#</th>
                <th>Command</th>
                <th>Target</th>
                <th>Value</th>
            </tr>
            </thead>
            <tbody>
            @if(!empty($data['commands']))
                @foreach($data['commands'] as $index => $item)
                    <tr>
                        <td>{{$index + 1}}</td>
                        <td>{{$item['command']}}</td>
                        <td>{{$item['target']}}</td>
                        <td>{{$item['value']}}</td>
                    </tr>
                @endforeach
            @endif
            </tbody>
        </table>
    </div>
@stop
