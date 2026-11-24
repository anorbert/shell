@props(['title', 'tag' => null])

@php($attentionId = 'client-attention-' . \Illuminate\Support\Str::uuid())
<div class="client-attention">
    <button type="button" class="client-attention-trigger" data-attention-open="{{ $attentionId }}"
        aria-label="Read important information: {{ $title }}" title="Read important information">
        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M12 11v5m0-8h.01"></path></svg>
    </button>
    <dialog class="client-attention-dialog" id="{{ $attentionId }}" aria-labelledby="{{ $attentionId }}-title">
        <div class="client-attention-dialog-head">
            <span class="eyebrow">IMPORTANT INFORMATION</span>
            <button type="button" class="client-attention-close" data-attention-close aria-label="Close information">×</button>
        </div>
        <h2 id="{{ $attentionId }}-title">{{ $title }}</h2>
        <div class="client-attention-copy">{{ $slot }}</div>
        @if($tag)<span class="mode-tag">{{ $tag }}</span>@endif
    </dialog>
</div>
