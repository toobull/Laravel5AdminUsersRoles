<?php

// -----------------
// View Name Prefix
// -----------------
$VN = 'views/admin/users/index.';
const VIEW_NAME    = 'admin.users.index';
?>

@include('admin.users._routes')

@extends ('app')

@section('headings')
    <h1>{{trans($VN.'title')}}</h1>
@endsection


@section('breadcrumbs')
    {!! Breadcrumbs::render('admin.users') !!}
@endsection


@section('content')

    <div role="tabpanel">

        <!-- Nav tabs -->
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" {!! Input::get('tab','tab_table')=='tab_tree'?'class="active"':''!!}>
                <a href="#tab_tree" aria-controls="tab_tree" role="tab" data-toggle="tab">{{trans($VN.'tab_tree')}}</a></li>
            <li role="presentation" {!! Input::get('tab','tab_table')=='tab_table'?'class="active"':''!!}>
                <a href="#tab_table" aria-controls="tab_table" role="tab" data-toggle="tab">{{trans($VN.'tab_table')}}</a></li>
        </ul>

        <!-- Tab panes -->
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane {!! Input::get('tab','tab_table')=='tab_table'?'active':''!!}" id="tab_table">
                <br/>
                @include('admin.users._index_table',['readonly' => true,'models'=>$models])
            </div>
            <div role="tabpanel" class="tab-pane {!! Input::get('tab','tab_table')=='tab_tree'?'active':''!!}" id="tab_tree">
                @include('admin.users._index_tree')
            </div>
        </div>
    </div>
@endsection