<!doctype html>
<html>
<head><meta charset="utf-8">@include('laporan.pdf._header')</head>
<body>
    <table>
        <thead><tr><th colspan="2">Aset</th></tr></thead>
        <tbody>
            <tr><td>Kas</td><td class="text-end">Rp {{ number_format($data['aset']['kas'], 0, ',', '.') }}</td></tr>
            <tr><td>Nilai Persediaan</td><td class="text-end">Rp {{ number_format($data['aset']['persediaan'], 0, ',', '.') }}</td></tr>
            <tr><td class="fw-bold">Total Aset</td><td class="text-end fw-bold">Rp {{ number_format($data['aset']['total'], 0, ',', '.') }}</td></tr>
        </tbody>
    </table>
    <table>
        <thead><tr><th colspan="2">Kewajiban &amp; Modal</th></tr></thead>
        <tbody>
            <tr><td>Hutang Supplier</td><td class="text-end">Rp {{ number_format($data['kewajiban']['hutang_supplier'], 0, ',', '.') }}</td></tr>
            <tr><td>Modal</td><td class="text-end">Rp {{ number_format($data['modal'], 0, ',', '.') }}</td></tr>
            <tr><td class="fw-bold">Total</td><td class="text-end fw-bold">Rp {{ number_format($data['kewajiban']['total'] + $data['modal'], 0, ',', '.') }}</td></tr>
        </tbody>
    </table>
</body>
</html>
