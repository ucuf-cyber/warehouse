<style>
    body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #0F172A; }
    h1 { font-size: 16px; margin: 0 0 2px; }
    .subtitle { color: #64748B; margin: 0 0 14px; font-size: 10px; }
    table { width: 100%; border-collapse: collapse; margin-top: 8px; }
    th, td { border: 1px solid #E2E8F0; padding: 5px 7px; text-align: left; }
    th { background: #F1F5F9; font-weight: bold; }
    .text-end { text-align: right; }
    .fw-bold { font-weight: bold; }
    .footer { margin-top: 16px; font-size: 9px; color: #94A3B8; }
</style>
<h1>{{ config('app.name') }}</h1>
<p class="subtitle">{{ $title }} @isset($period) — {{ $period }} @endisset · Dicetak {{ now()->translatedFormat('d M Y H:i') }}</p>
