@extends('seleniumidemanager::layouts.master')
@section('content')
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2">Edit config suite: <span class="text-danger">{{$suite_name}}</span></h1>
    </div>
    <form onsubmit="return confirmSubmit()" action="{{route('selenium-ide-manager.config.update', ['id' => $id])}}"
          method="POST">
        @csrf
        @method('PUT')
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>#</th>
                    @foreach($data as $item)
                        <th style="min-width: 200px">{{$item['variable_name']}}</th>
                    @endforeach
                </tr>
                </thead>
                @if($total_rows > 0)
                    <tbody>
                    @for($i = 0; $i < $total_rows; $i++)
                        <tr>
                            <td>
                                @if($i == 0)
                                    <span style="cursor: pointer" class="text-primary btn-plus"
                                          data-feather="plus"></span>
                                @else
                                    <span style="cursor: pointer" class="text-danger btn-trash"
                                          data-feather="trash"></span>
                                @endif
                            </td>
                            @foreach($data as $item)
                                <td style="min-width: 200px">
                                    <input name="variable_name[{{$item['id']}}][]" type="text" class="form-control"
                                           value="{{$item['variable_value'][$i]}}">
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                    @endfor
                @endif
            </table>
        </div>
        <div class="pt-3">
            <button type="submit" class="btn btn-primary">
                <span data-feather="save"></span> Save
            </button>
        </div>
    </form>
@stop
@push('script')
    <script type="text/javascript">
        $(function () {
            $('.table-responsive').on('click', '.btn-plus', function () {
                let html = '';
                $('table tbody tr:nth(0) td').each(function (index) {
                    if (index == 0) {
                        html += `<td><span style="cursor: pointer" class="text-danger btn-trash" data-feather="trash"></span></td>`;
                    } else {
                        html += `<td>${$(this).html()}</td>`;
                    }
                    console.log('index', index);
                })
                $('table tbody').append(`<tr>${html}</tr>`);
                feather.replace()
            });

            $('.table-responsive').on('click', '.btn-trash', function () {
                $(this).parents('tr').remove();
            })
        });

        function confirmSubmit() {
            return confirm('Bạn có chắc muốn lưu thông tin?')
        }
    </script>
@endpush
