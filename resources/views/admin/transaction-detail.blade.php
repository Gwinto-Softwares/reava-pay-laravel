@extends(config('reava-pay.admin_layout', 'layouts.AdminLayout'))

@section('title', 'Transaction Detail - Reava Pay')
@section('page-title', 'Transaction Detail')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('reava-pay.admin.settings') }}">Reava Pay</a></li>
<li class="breadcrumb-item"><a href="{{ route('reava-pay.admin.transactions') }}">Transactions</a></li>
<li class="breadcrumb-item active">{{ Str::limit($transaction->local_reference, 24) }}</li>
@endsection

@section('content')
<div class="container-fluid">

    @foreach(['success', 'error', 'info'] as $type)
        @if(session($type))
        <div class="alert alert-{{ $type === 'error' ? 'danger' : $type }} alert-dismissible fade show">
            {{ session($type) }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
        @endif
    @endforeach

    {{-- Header --}}
    <div class="card" style="background: linear-gradient(135deg, #0f2027, #203a43, #2c5364); border: none; border-radius: 12px;">
        <div class="card-body text-white py-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap">
                <div>
                    <div class="mb-2">
                        <span class="badge badge-{{ $transaction->status_badge }} px-3 py-1" style="font-size: 0.8rem;">{{ ucfirst($transaction->status) }}</span>
                        <span class="badge px-2 py-1 ml-1" style="background: {{ $transaction->channel === 'mpesa' ? 'rgba(76,175,80,0.2)' : 'rgba(33,150,243,0.2)' }}; color: {{ $transaction->channel === 'mpesa' ? '#4caf50' : '#2196f3' }}; font-size: 0.8rem;">
                            {{ $transaction->channel_label }}
                        </span>
                    </div>
                    <h3 class="font-weight-bold mb-1">{{ $transaction->currency }} {{ number_format($transaction->amount, 2) }}</h3>
                    <small class="text-white-50" style="font-family: monospace;">{{ $transaction->local_reference }}</small>
                </div>
                <div class="text-right">
                    <div class="small text-white-50">{{ $transaction->type_label }}</div>
                    <div class="small text-white-50">{{ $transaction->created_at->format('M d, Y H:i:s') }}</div>
                    @if(!$transaction->isCompleted() && $transaction->reava_reference)
                    <form action="{{ route('reava-pay.admin.transactions.sync', $transaction->id) }}" method="POST" class="mt-2">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-light" style="border-radius: 6px;">
                            <i class="fas fa-sync-alt mr-1"></i> Sync Status
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Left --}}
        <div class="col-lg-7">
            <div class="card" style="border-radius: 10px;">
                <div class="card-header"><h6 class="mb-0"><i class="fas fa-receipt mr-1 text-primary"></i> Transaction Details</h6></div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <tr><td class="text-muted">Local Reference</td><td class="text-right"><code>{{ $transaction->local_reference }}</code></td></tr>
                        <tr><td class="text-muted">Reava Pay Reference</td><td class="text-right"><code>{{ $transaction->reava_reference ?? '—' }}</code></td></tr>
                        <tr><td class="text-muted">Provider Reference</td><td class="text-right"><code>{{ $transaction->provider_reference ?? '—' }}</code></td></tr>
                        <tr><td class="text-muted">Type</td><td class="text-right">{{ $transaction->type_label }}</td></tr>
                        <tr><td class="text-muted">Channel</td><td class="text-right">{{ $transaction->channel_label }}</td></tr>
                        <tr><td class="text-muted">Amount</td><td class="text-right font-weight-bold">{{ $transaction->formatted_amount }}</td></tr>
                        @if($transaction->charge_amount > 0)
                        <tr><td class="text-muted">Charge</td><td class="text-right">{{ $transaction->currency }} {{ number_format($transaction->charge_amount, 2) }}</td></tr>
                        <tr><td class="text-muted">Net Amount</td><td class="text-right font-weight-bold">{{ $transaction->currency }} {{ number_format($transaction->net_amount, 2) }}</td></tr>
                        @endif
                        <tr><td class="text-muted">Status</td><td class="text-right"><span class="badge badge-{{ $transaction->status_badge }}">{{ ucfirst($transaction->status) }}</span></td></tr>
                        @if($transaction->failure_reason)
                        <tr><td class="text-muted">Failure Reason</td><td class="text-right text-danger">{{ $transaction->failure_reason }}</td></tr>
                        @endif
                    </table>
                </div>
            </div>

            <div class="card" style="border-radius: 10px;">
                <div class="card-header"><h6 class="mb-0"><i class="fas fa-user mr-1 text-info"></i> Payer Info</h6></div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        @if($transaction->phone)<tr><td class="text-muted">Phone</td><td class="text-right">{{ $transaction->phone }}</td></tr>@endif
                        @if($transaction->email)<tr><td class="text-muted">Email</td><td class="text-right">{{ $transaction->email }}</td></tr>@endif
                        @if($transaction->account_reference)<tr><td class="text-muted">Account Reference</td><td class="text-right"><code>{{ $transaction->account_reference }}</code></td></tr>@endif
                        @if($transaction->description)<tr><td class="text-muted">Description</td><td class="text-right">{{ $transaction->description }}</td></tr>@endif
                    </table>
                </div>
            </div>

            @if($transaction->webhook_payload)
            <div class="card" style="border-radius: 10px;">
                <div class="card-header"><h6 class="mb-0"><i class="fas fa-code mr-1 text-secondary"></i> Webhook Payload</h6></div>
                <div class="card-body"><pre class="bg-light p-3 rounded small mb-0" style="max-height: 300px; overflow-y: auto;">{{ json_encode($transaction->webhook_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre></div>
            </div>
            @endif

            @if($transaction->reava_response)
            <div class="card" style="border-radius: 10px;">
                <div class="card-header"><h6 class="mb-0"><i class="fas fa-server mr-1 text-secondary"></i> API Response</h6></div>
                <div class="card-body"><pre class="bg-light p-3 rounded small mb-0" style="max-height: 300px; overflow-y: auto;">{{ json_encode($transaction->reava_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre></div>
            </div>
            @endif
        </div>

        {{-- Right --}}
        <div class="col-lg-5">
            <div class="card" style="border-radius: 10px;">
                <div class="card-header"><h6 class="mb-0"><i class="fas fa-clock mr-1 text-warning"></i> Timeline</h6></div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="time-label"><span class="bg-primary">Initiated</span></div>
                        <div><i class="fas fa-play bg-primary"></i><div class="timeline-item"><span class="time"><i class="fas fa-clock"></i> {{ ($transaction->initiated_at ?? $transaction->created_at)->format('M d, Y H:i:s') }}</span><h3 class="timeline-header">Transaction Created</h3></div></div>

                        @if($transaction->completed_at)
                        <div class="time-label"><span class="bg-success">Completed</span></div>
                        <div><i class="fas fa-check bg-success"></i><div class="timeline-item"><span class="time"><i class="fas fa-clock"></i> {{ $transaction->completed_at->format('M d, Y H:i:s') }}</span><h3 class="timeline-header">Payment Successful</h3></div></div>
                        @elseif($transaction->failed_at)
                        <div class="time-label"><span class="bg-danger">Failed</span></div>
                        <div><i class="fas fa-times bg-danger"></i><div class="timeline-item"><span class="time"><i class="fas fa-clock"></i> {{ $transaction->failed_at->format('M d, Y H:i:s') }}</span><h3 class="timeline-header">{{ $transaction->failure_reason ?? 'Payment Failed' }}</h3></div></div>
                        @else
                        <div class="time-label"><span class="bg-warning">Processing</span></div>
                        <div><i class="fas fa-spinner bg-warning"></i><div class="timeline-item"><h3 class="timeline-header">Awaiting update from Reava Pay</h3></div></div>
                        @endif
                    </div>
                </div>
            </div>

            @if($transaction->payable_type)
            <div class="card" style="border-radius: 10px;">
                <div class="card-header"><h6 class="mb-0"><i class="fas fa-link mr-1 text-success"></i> Linked Record</h6></div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <tr><td class="text-muted">Type</td><td class="text-right">{{ class_basename($transaction->payable_type) }}</td></tr>
                        <tr><td class="text-muted">ID</td><td class="text-right">#{{ $transaction->payable_id }}</td></tr>
                    </table>
                </div>
            </div>
            @endif

            <div class="card" style="border-radius: 10px;">
                <div class="card-header"><h6 class="mb-0"><i class="fas fa-info-circle mr-1"></i> System Info</h6></div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <tr><td class="text-muted">Retry Count</td><td class="text-right">{{ $transaction->retry_count }}</td></tr>
                        <tr><td class="text-muted">Created</td><td class="text-right small">{{ $transaction->created_at->format('M d, Y H:i:s') }}</td></tr>
                        <tr><td class="text-muted">Updated</td><td class="text-right small">{{ $transaction->updated_at->format('M d, Y H:i:s') }}</td></tr>
                    </table>
                </div>
            </div>

            <a href="{{ route('reava-pay.admin.transactions') }}" class="btn btn-outline-secondary btn-block" style="border-radius: 6px;">
                <i class="fas fa-arrow-left mr-1"></i> Back to Transactions
            </a>
        </div>
    </div>
</div>
@endsection
