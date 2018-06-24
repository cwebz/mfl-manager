@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <strong>Manage Accounts</strong>
                    <a class="btn btn-primary mfl-mgr-btn" href="/add-mfl-account">Add Account</a>
                </div>

                <div class="panel-body">
                    Some logix here to display accounts if found
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <strong>Manage Leagues</strong>
                    <a class="btn btn-primary mfl-mgr-btn" href="">Add League</a>
                </div>

                <div class="panel-body">
                    Some logix here to display leagues if found
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
