@extends(config('reava-pay.admin_layout', 'layouts.AdminLayout'))

@section('title', 'Reava Pay Settings')
@section('page-title', 'Reava Pay Settings')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('reava-pay.admin.settings') }}">Reava Pay</a></li>
<li class="breadcrumb-item active">Settings</li>
@endsection

@section('content')
<div class="container-fluid">

    {{-- Flash messages --}}
    @foreach(['success', 'error', 'info'] as $type)
        @if(session($type))
        <div class="alert alert-{{ $type === 'error' ? 'danger' : $type }} alert-dismissible fade show">
            <i class="fas fa-{{ $type === 'success' ? 'check-circle' : ($type === 'error' ? 'exclamation-triangle' : 'info-circle') }} mr-1"></i>
            {{ session($type) }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
        @endif
    @endforeach

    {{-- Header --}}
    <div class="card" style="background: linear-gradient(135deg, #0f2027, #203a43, #2c5364); border: none; border-radius: 12px;">
        <div class="card-body text-white py-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h4 class="mb-1 font-weight-bold">
                        <i class="fas fa-shield-alt mr-2" style="color: #00d4ff;"></i>Reava Pay Integration
                    </h4>
                    <p class="mb-0 text-white-50">Accept M-Pesa, Card, and Bank payments seamlessly</p>
                </div>
                @if($settings->is_verified)
                <span class="badge badge-success px-3 py-2" style="font-size: 0.85rem; border-radius: 8px;">
                    <i class="fas fa-check-circle mr-1"></i> Connected
                </span>
                @else
                <span class="badge badge-warning px-3 py-2" style="font-size: 0.85rem; border-radius: 8px;">
                    <i class="fas fa-exclamation-circle mr-1"></i> Not Connected
                </span>
                @endif
            </div>
        </div>
    </div>

    @if(!$settings->is_verified)
    {{-- Connect Form --}}
    <div class="card" style="border-radius: 12px;">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="fas fa-plug mr-2" style="color: #00d4ff;"></i>Connect to Reava Pay</h5>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">Enter your organization details to register as a Reava Pay merchant. This creates your merchant account, float account, and API credentials automatically.</p>

            <form action="{{ route('reava-pay.admin.connect') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="fas fa-building mr-1"></i> Business / Organization Name <span class="text-danger">*</span></label>
                            <input type="text" name="business_name" class="form-control" value="{{ old('business_name', config('app.name')) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="fas fa-envelope mr-1"></i> Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="finance@yourorg.com">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="fas fa-phone mr-1"></i> Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="+254712345678">
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-lg" style="border-radius: 8px; background: linear-gradient(135deg, #00d4ff, #0099cc); border: none;">
                    <i class="fas fa-plug mr-2"></i> Connect to Reava Pay
                </button>
            </form>
        </div>
    </div>
    @else
    {{-- Connected — Show Credentials --}}
    <div class="row">
        <div class="col-lg-8">
            <div class="card" style="border-radius: 12px;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-key mr-2" style="color: #00d4ff;"></i>Credentials</h5>
                    <div>
                        <a href="{{ route('reava-pay.admin.transactions') }}" class="btn btn-sm btn-outline-primary" style="border-radius: 6px;">
                            <i class="fas fa-list mr-1"></i> Transactions
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($credentials)
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small font-weight-bold text-uppercase">Merchant ID</label>
                            <div class="bg-light rounded p-2 px-3"><code>{{ $credentials['merchant_id'] ?? 'N/A' }}</code></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small font-weight-bold text-uppercase">Login Email</label>
                            <div class="bg-light rounded p-2 px-3"><code>{{ $credentials['login_email'] ?? 'N/A' }}</code></div>
                        </div>
                        @if($credentials['login_password'] ?? null)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small font-weight-bold text-uppercase"><i class="fas fa-key mr-1"></i>Login Password</label>
                            <div class="bg-light rounded p-2 px-3 d-flex justify-content-between align-items-center">
                                <code id="pwdDisplay">{{ str_repeat('•', strlen($credentials['login_password'])) }}</code>
                                <button type="button" class="btn btn-sm btn-link" onclick="togglePwd()"><i class="fas fa-eye" id="pwdIcon"></i></button>
                            </div>
                            <small class="text-muted">Use with the email above to login at <a href="https://reavapay.com/login" target="_blank">reavapay.com</a></small>
                        </div>
                        @endif
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small font-weight-bold text-uppercase">Float Account</label>
                            <div class="bg-light rounded p-2 px-3"><code>{{ $credentials['float_account'] ?? 'Pending' }}</code></div>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="text-muted small font-weight-bold text-uppercase">API Key</label>
                            <div class="bg-light rounded p-2 px-3" style="word-break: break-all;"><code>{{ $credentials['api_key'] ?? 'Not set' }}</code></div>
                        </div>
                        @if($credentials['api_secret'])
                        <div class="col-12 mb-3">
                            <label class="text-muted small font-weight-bold text-uppercase"><i class="fas fa-lock mr-1"></i>API Secret</label>
                            <div class="bg-light rounded p-2 px-3 d-flex justify-content-between align-items-center" style="word-break: break-all;">
                                <code id="secretDisplay">{{ str_repeat('•', 40) }}</code>
                                <button type="button" class="btn btn-sm btn-link" onclick="toggleSecret()"><i class="fas fa-eye" id="secretIcon"></i></button>
                            </div>
                        </div>
                        @endif
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small font-weight-bold text-uppercase">Environment</label>
                            <div>
                                <span class="badge badge-{{ $credentials['environment'] === 'production' ? 'success' : 'warning' }} px-3">
                                    {{ ucfirst($credentials['environment'] ?? 'production') }}
                                </span>
                            </div>
                        </div>
                        @if($credentials['connected_at'])
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small font-weight-bold text-uppercase">Connected Since</label>
                            <div class="text-muted">{{ \Carbon\Carbon::parse($credentials['connected_at'])->format('M d, Y H:i') }}</div>
                        </div>
                        @endif
                    </div>
                    @endif

                    <hr>
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <form action="{{ route('reava-pay.admin.test-connection') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-info" style="border-radius: 6px;">
                                <i class="fas fa-wifi mr-1"></i> Test Connection
                            </button>
                        </form>
                        <div>
                            <form action="{{ route('reava-pay.admin.disconnect') }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Disconnect from Reava Pay? You can reconnect later.')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius: 6px;">
                                    <i class="fas fa-unlink mr-1"></i> Disconnect
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Float Balance Card --}}
        <div class="col-lg-4">
            <div class="card" style="border-radius: 12px; background: linear-gradient(135deg, #0f2027, #203a43); color: white; border: none;">
                <div class="card-body text-center py-4">
                    <i class="fas fa-wallet mb-2" style="font-size: 2rem; color: #00d4ff;"></i>
                    <h6 class="text-white-50 small text-uppercase">Float Balance</h6>
                    <h3 class="font-weight-bold mb-1">
                        KES {{ $floatBalance ? number_format($floatBalance->available_balance ?? 0, 2) : '0.00' }}
                    </h3>
                    @if($floatBalance)
                    <small class="text-white-50">{{ $floatBalance->account_number ?? '' }}</small>
                    @endif
                </div>
            </div>

            <div class="card" style="border-radius: 12px;">
                <div class="card-header"><h6 class="mb-0"><i class="fas fa-cog mr-1"></i> Payment Channels</h6></div>
                <div class="card-body">
                    <form action="{{ route('reava-pay.admin.update') }}" method="POST">
                        @csrf
                        <div class="custom-control custom-switch mb-2">
                            <input type="hidden" name="mpesa_enabled" value="0">
                            <input type="checkbox" class="custom-control-input" id="mpesa" name="mpesa_enabled" value="1" {{ $settings->mpesa_enabled ? 'checked' : '' }}>
                            <label class="custom-control-label" for="mpesa"><i class="fas fa-mobile-alt mr-1 text-success"></i> M-Pesa</label>
                        </div>
                        <div class="custom-control custom-switch mb-2">
                            <input type="hidden" name="card_enabled" value="0">
                            <input type="checkbox" class="custom-control-input" id="card" name="card_enabled" value="1" {{ $settings->card_enabled ? 'checked' : '' }}>
                            <label class="custom-control-label" for="card"><i class="fas fa-credit-card mr-1 text-primary"></i> Card Payments</label>
                        </div>
                        <div class="custom-control custom-switch mb-3">
                            <input type="hidden" name="bank_transfer_enabled" value="0">
                            <input type="checkbox" class="custom-control-input" id="bank" name="bank_transfer_enabled" value="1" {{ $settings->bank_transfer_enabled ? 'checked' : '' }}>
                            <label class="custom-control-label" for="bank"><i class="fas fa-university mr-1 text-warning"></i> Bank Transfer</label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block btn-sm" style="border-radius: 6px;">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
@if($credentials['login_password'] ?? null)
let pwdVisible = false;
function togglePwd() {
    const el = document.getElementById('pwdDisplay');
    const icon = document.getElementById('pwdIcon');
    pwdVisible = !pwdVisible;
    el.textContent = pwdVisible ? @json($credentials['login_password']) : '{{ str_repeat("•", strlen($credentials["login_password"] ?? "")) }}';
    icon.className = pwdVisible ? 'fas fa-eye-slash' : 'fas fa-eye';
}
@endif
@if($credentials['api_secret'] ?? null)
let secretVisible = false;
function toggleSecret() {
    const el = document.getElementById('secretDisplay');
    const icon = document.getElementById('secretIcon');
    secretVisible = !secretVisible;
    el.textContent = secretVisible ? @json($credentials['api_secret']) : '{{ str_repeat("•", 40) }}';
    icon.className = secretVisible ? 'fas fa-eye-slash' : 'fas fa-eye';
}
@endif
</script>
@endpush
