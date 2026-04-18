@extends('layouts.master')

@section('breadcrumb')
    <div class="col-sm-6">
        <h4 class="page-title text-left">Notifications</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin') }}">Home</a></li>
            <li class="breadcrumb-item active">Notifications</li>
        </ol>
    </div>
@endsection

@section('button')
    @if(($unreadCount ?? 0) > 0)
        <form method="POST" action="{{ route('notifications.read_all') }}" style="display:inline;">
            @csrf
            <button class="btn btn-secondary btn-sm btn-flat" type="submit">
                Mark all read ({{ $unreadCount }})
            </button>
        </form>
    @endif
@endsection

@section('content')
@include('includes.flash')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                @if($notifications->count() === 0)
                    <div class="text-muted">No notifications.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th style="width: 110px;">Status</th>
                                    <th>Message</th>
                                    <th style="width: 190px;">Date</th>
                                    <th style="width: 160px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($notifications as $n)
                                    @php
                                        $data = (array) $n->data;
                                        $title = $data['title'] ?? 'Notification';
                                        $body = $data['body'] ?? '';
                                        $url = $data['url'] ?? null;
                                        $isUnread = is_null($n->read_at);
                                    @endphp
                                    <tr>
                                        <td>
                                            @if($isUnread)
                                                <span class="badge badge-primary">Unread</span>
                                            @else
                                                <span class="badge badge-light border">Read</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div style="font-weight:600;">{{ $title }}</div>
                                            <div class="text-muted small">{{ $body }}</div>
                                            @if($url)
                                                <div class="mt-1">
                                                    <a href="{{ $url }}">Open</a>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <div title="{{ $n->created_at }}">
                                                {{ \Carbon\Carbon::parse($n->created_at)->format('M d, Y h:i A') }}
                                            </div>
                                        </td>
                                        <td class="text-nowrap">
                                            @if($isUnread)
                                                <form method="POST" action="{{ route('notifications.read', $n->id) }}" style="display:inline;">
                                                    @csrf
                                                    <button class="btn btn-success btn-sm btn-flat" type="submit">Mark read</button>
                                                </form>
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

