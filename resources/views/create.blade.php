@extends('seleniumidemanager::layouts.master')
@section('content')
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Add new suite</h1>
    </div>

    <div class="table-responsive">
        <form name="" method="POST" enctype="multipart/form-data" action="{{route('selenium-ide-manager.suite.store', ['id' => $id])}}">
            @csrf
            <div class="form-group">
                <label for="exampleInputEmail1">Site file</label>
                <input class="form-control-file" type="file" name="file">
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
@stop
