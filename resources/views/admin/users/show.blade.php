<?php
const VIEW_NAME = 'admin.users.show';
?>

@include('admin.users._routes')

@extends ('app')

@section('headings')
    <h1>User: {{ $model->display_name }} ({{ $model->name }})</h1>
@endsection

@section('breadcrumbs')
    {!! Breadcrumbs::render(VIEW_NAME) !!}
@endsection


@section('content')
    <div class="row">
        <div class="col-md-12 col-md-offset-0">
            <div class="panel panel-primary">
                @include('partials.crud.show_buttons')

                <div role="tabpanel">

                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active"><a href="#data" aria-controls="data" role="tab"
                                                                  data-toggle="tab">Data</a></li>
                        <li role="presentation"><a href="#comments" aria-controls="comments" role="tab"
                                                                  data-toggle="tab">Comments</a></li>
                        <li role="presentation"><a href="#permissions" aria-controls="permissions" role="tab"
                                                   data-toggle="tab">Permissions</a></li>
                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane active" id="data">
                            @include('admin.users._form_data',['readonly' => true])
                        </div>
                        <div role="tabpanel" class="tab-pane" id="comments">
                            @include('admin.users._form_picture',['readonly' => true])
                        </div>
                        <div role="tabpanel" class="tab-pane" id="permissions">
                            @include('admin.users._form_permissions',['readonly' => true])
                        </div>
                    </div>
                </div>

                @include('partials.crud.bottom_buttons')
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('/js/select2.min.js') }}"></script>
    <script type="text/javascript">
        $('#roles').select2({
            placeholder: 'Select a role'
        });
        $('#departments').select2({
            placeholder: 'Select a department'
        });

    </script>
@endsection

