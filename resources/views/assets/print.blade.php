<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Label QR Aset</title>
    <style nonce="{{ Vite::cspNonce() }}">
        * { box-sizing: border-box; }
        body { font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif; margin: 0; padding: 24px; color: #0f172a; background: #f8fafc; }
        .toolbar { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; justify-content: space-between; margin-bottom: 24px; }
        .toolbar strong { display: block; font-size: 14px; }
        .toolbar small { color: #64748b; font-size: 12px; }
        .actions { display: flex; gap: 8px; }
        .btn { display: inline-block; padding: 8px 16px; border-radius: 10px; font-size: 12px; font-weight: 600; text-decoration: none; border: 1px solid transparent; cursor: pointer; }
        .btn-primary { background: #4f46e5; color: #fff; }
        .btn-secondary { background: #fff; color: #334155; border-color: #e2e8f0; }
        .labels { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
        .labels.size-small { grid-template-columns: repeat(5, minmax(0, 1fr)); }
        .labels.size-large { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .label { background: #fff; border: 1px dashed #cbd5e1; border-radius: 10px; padding: 12px; text-align: center; break-inside: avoid; page-break-inside: avoid; }
        .label svg { width: 150px; height: 150px; }
        .size-small .label svg { width: 96px; height: 96px; }
        .size-small .label { padding: 8px; }
        .size-small .code { font-size: 11px; }
        .size-large .label svg { width: 210px; height: 210px; }
        .size-large .code { font-size: 15px; }
        .size-large .name { font-size: 12px; }
        .sizes { display: flex; gap: 6px; }
        .sizes .btn { padding: 6px 12px; }
        .sizes .btn-active { background: #4f46e5; color: #fff; border-color: #4f46e5; }
        .code { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-weight: 700; font-size: 13px; margin-top: 6px; }
        .name { font-size: 11px; color: #475569; margin-top: 2px; }
        .meta { font-size: 10px; color: #94a3b8; margin-top: 2px; }
        .empty { padding: 48px; text-align: center; color: #64748b; font-size: 13px; background: #fff; border: 1px dashed #cbd5e1; border-radius: 10px; }
        @media print {
            @page { margin: 10mm; }
            body { padding: 0; background: #fff; }
            .toolbar { display: none; }
            .labels { gap: 6px; }
            .label { border-color: #e2e8f0; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <div>
            <strong>Label QR Aset</strong>
            <small>{{ $assets->count() }} label</small>
        </div>
        <div class="actions">
            <div class="sizes">
                @foreach(['small' => 'Kecil', 'medium' => 'Sedang', 'large' => 'Besar'] as $value => $text)
                    <a href="{{ route('web.assets.print', array_merge($filters, ['size' => $value])) }}"
                       class="btn btn-secondary {{ $size === $value ? 'btn-active' : '' }}">{{ $text }}</a>
                @endforeach
            </div>
            <a href="{{ route('web.assets.index') }}" class="btn btn-secondary">Kembali</a>
            <button type="button" onclick="window.print()" class="btn btn-primary">Cetak</button>
        </div>
    </div>

    @if($assets->isEmpty())
        <div class="empty">Tidak ada aset yang cocok untuk dicetak.</div>
    @else
        <div class="labels size-{{ $size }}">
            @foreach($assets as $asset)
                <div class="label">
                    {!! $qrCodes[$asset->id] !!}
                    <div class="code">{{ $asset->asset_code }}</div>
                    <div class="name">{{ $asset->name }}</div>
                    <div class="meta">{{ $asset->location->name }}</div>
                </div>
            @endforeach
        </div>
    @endif
</body>
</html>
