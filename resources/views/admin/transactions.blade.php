@extends(config('reava-pay.admin_layout', 'layouts.AdminLayout'))

@section('title', 'Reava Pay Transactions')
@section('page-title', 'Reava Pay Transactions')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('reava-pay.admin.settings') }}">Reava Pay</a></li>
<li class="breadcrumb-item active">Transactions</li>
@endsection

@section('content')
<div class="container-fluid">

    {{-- Flash messages --}}
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
        <div class="card-body text-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h4 class="mb-1 font-weight-bold"><i class="fas fa-exchange-alt mr-2" style="color: #00d4ff;"></i>Reava Pay Transactions</h4>
                    <p class="mb-0 text-white-50">All payment transactions via Reava Pay</p>
                </div>
                <a href="{{ route('reava-pay.admin.settings') }}" class="btn btn-outline-light btn-sm" style="border-radius: 6px;">
                    <i class="fas fa-cog mr-1"></i> Settings
                </a>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="row mb-3">
        <div class="col-sm-3">
            <div class="small-box bg-success" style="border-radius: 10px;">
                <div class="inner"><h4>KES {{ number_format($stats['total_volume'], 2) }}</h4><p>Total Volume</p></div>
                <div class="icon"><i class="fas fa-coins"></i></div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="small-box bg-info" style="border-radius: 10px;">
                <div class="inner"><h4>KES {{ number_format($stats['this_month'], 2) }}</h4><p>This Month</p></div>
                <div class="icon"><i class="fas fa-chart-line"></i></div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="small-box bg-primary" style="border-radius: 10px;">
                <div class="inner"><h4>{{ $stats['total_count'] }}</h4><p>Transactions</p></div>
                <div class="icon"><i class="fas fa-receipt"></i></div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="small-box bg-warning" style="border-radius: 10px;">
                <div class="inner"><h4>{{ $stats['success_rate'] }}%</h4><p>Success Rate</p></div>
                <div class="icon"><i class="fas fa-bullseye"></i></div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card" style="border-radius: 10px;">
        <div class="card-body py-2">
            <form method="GET" class="row align-items-end">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search reference, phone..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-control form-control-sm">
                        <option value="all">All Status</option>
                        @foreach(['pending','processing','completed','failed','reversed'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="channel" class="form-control form-control-sm">
                        <option value="all">All Channels</option>
                        @foreach(['mpesa','card','bank_transfer'] as $c)
                        <option value="{{ $c }}" {{ request('channel') === $c ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $c)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm btn-block" style="border-radius: 6px;"><i class="fas fa-filter mr-1"></i> Filter</button>
                </div>
                <div class="col-md-1">
                    <a href="{{ route('reava-pay.admin.transactions') }}" class="btn btn-outline-secondary btn-sm btn-block" style="border-radius: 6px;">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card" style="border-radius: 10px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Reference</th>
                            <th>Type</th>
                            <th>Channel</th>
                            <th>Payer</th>
                            <th class="text-right">Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $txn)
                        <tr style="cursor: pointer;" onclick="window.location='{{ route('reava-pay.admin.transactions.detail', $txn->id) }}'">
                            <td>
                                <div class="font-weight-bold small">{{ $txn->local_reference }}</div>
                                @if($txn->reava_reference)
                                <small class="text-muted">{{ Str::limit($txn->reava_reference, 24) }}</small>
                                @endif
                            </td>
                            <td><span class="badge badge-light">{{ $txn->type_label }}</span></td>
                            <td>
                                <span class="badge" style="background: {{ $txn->channel === 'mpesa' ? '#e8f5e9' : ($txn->channel === 'card' ? '#e3f2fd' : '#fff3e0') }}; color: {{ $txn->channel === 'mpesa' ? '#2e7d32' : ($txn->channel === 'card' ? '#1565c0' : '#e65100') }};">
                                    {{ $txn->channel_label }}
                                </span>
                            </td>
                            <td class="small">{{ $txn->phone ?: ($txn->email ?: '—') }}</td>
                            <td class="text-right font-weight-bold">{{ $txn->formatted_amount }}</td>
                            <td><span class="badge badge-{{ $txn->status_badge }}">{{ ucfirst($txn->status) }}</span></td>
                            <td class="small text-muted">{{ $txn->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-4 text-muted">No transactions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($transactions->hasPages())
        <div class="card-footer">{{ $transactions->links() }}</div>
        @endif
    </div>
</div>
@endsection
