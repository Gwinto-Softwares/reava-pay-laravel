@props([
    'amount' => null,
    'currency' => 'KES',
    'label' => 'Pay Now',
    'description' => '',
    'reference' => null,
    'customer' => [],
    'metadata' => [],
    'redirectUrl' => null,
    'style' => 'solid',
    'color' => '#00d4ff',
    'textColor' => '#0f1923',
    'size' => 'medium',
    'onSuccess' => null,
    'onError' => null,
    'onClose' => null,
])

@php
    $sizes = ['small' => '10px 18px', 'medium' => '14px 28px', 'large' => '18px 36px'];
    $fontSizes = ['small' => '13px', 'medium' => '15px', 'large' => '17px'];
    $radius = match($style) { 'pill' => '999px', 'rounded' => '14px', default => '10px' };
    $bg = $style === 'outline' ? 'transparent' : $color;
    $tc = $style === 'outline' ? $color : $textColor;
    $border = $style === 'outline' ? "2px solid {$color}" : 'none';
    $btnId = 'rp-btn-' . uniqid();
    $ref = $reference ?? 'RP-' . time() . '-' . rand(1000, 9999);
@endphp

<button
    id="{{ $btnId }}"
    style="display:inline-flex;align-items:center;gap:8px;padding:{{ $sizes[$size] ?? $sizes['medium'] }};background:{{ $bg }};color:{{ $tc }};font-weight:700;font-size:{{ $fontSizes[$size] ?? $fontSizes['medium'] }};border-radius:{{ $radius }};border:{{ $border }};cursor:pointer;font-family:sans-serif;transition:all 0.2s;"
    {{ $attributes }}
>
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
    </svg>
    {{ $label }}
</button>

<script>
(function() {
    var btn = document.getElementById('{{ $btnId }}');
    if (!btn || typeof ReavaPay === 'undefined') return;

    var rp = ReavaPay('{{ config('reava-pay.public_key') }}');

    btn.addEventListener('click', function() {
        rp.checkout({
            @if($amount) amount: {{ $amount }}, @endif
            currency: '{{ $currency }}',
            description: '{{ addslashes($description) }}',
            reference: '{{ $ref }}',
            customer: {!! json_encode($customer) !!},
            metadata: {!! json_encode($metadata) !!},
            @if($redirectUrl) redirectUrl: '{{ $redirectUrl }}', @endif
            @if($onSuccess) onSuccess: {{ $onSuccess }}, @endif
            @if($onError) onError: {{ $onError }}, @endif
            @if($onClose) onClose: {{ $onClose }}, @endif
        }).open();
    });
})();
</script>
