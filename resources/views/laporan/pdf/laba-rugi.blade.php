<!doctype html>
<html>
<head><meta charset="utf-8">@include('laporan.pdf._header')</head>
<body>
    <table>
        <tbody>
            <tr><td>Omzet Penjualan</td><td class="text-end">Rp {{ number_format($data['omzet'], 0, ',', '.') }}</td></tr>
            <tr><td>Harga Pokok Penjualan (HPP)</td><td class="text-end">(Rp {{ number_format($data['hpp'], 0, ',', '.') }})</td></tr>
            <tr><td class="fw-bold">Laba Kotor</td><td class="text-end fw-bold">Rp {{ number_format($data['laba_kotor'], 0, ',', '.') }}</td></tr>
            <tr><td>Beban Operasional</td><td class="text-end">(Rp {{ number_format($data['beban_operasional'], 0, ',', '.') }})</td></tr>
            <tr><td class="fw-bold">Laba Bersih</td><td class="text-end fw-bold">Rp {{ number_format($data['laba_bersih'], 0, ',', '.') }}</td></tr>
        </tbody>
    </table>
</body>
</html>
