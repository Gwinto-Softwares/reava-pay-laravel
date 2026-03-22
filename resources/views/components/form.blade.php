@props([
    'amount' => null,
    'currency' => 'KES',
    'description' => '',
    'reference' => null,
    'onSuccess' => null,
    'onError' => null,
])

@php
    $formId = 'rp-form-' . uniqid();
    $baseUrl = str_replace('/api/v1', '', config('reava-pay.base_url', 'https://reavapay.com/api/v1'));
    $params = http_build_query(array_filter([
        'key' => config('reava-pay.public_key'),
        'amount' => $amount,
        'currency' => $currency,
        'description' => $description,
        'reference' => $reference,
        'inline' => 'true',
        'sdk' => 'laravel',
        'v' => '1.0.0',
    ]));
@endphp

<div id="{{ $formId }}" {{ $attributes->merge(['style' => 'width:100%;']) }}>
    <iframe
        src="{{ $baseUrl }}/sdk/checkout?{{ $params }}"
        style="width:100%;min-height:500px;border:none;border-radius:12px;"
        allow="payment"
    ></iframe>
</div>

@if($onSuccess || $onError)
<script>
(function() {
    var baseUrl = '{{ $baseUrl }}';
    window.addEventListener('message', function(e) {
        if (e.origin !== baseUrl) return;
        var data = e.data;
        @if($onSuccess)
        if (data && data.type === 'reava-pay-success') {
            ({{ $onSuccess }})(data.transaction);
        }
        @endif
        @if($onError)
        if (data && data.type === 'reava-pay-error') {
            ({{ $onError }})(data.error);
        }
        @endif
    });
})();
</script>
@endif
