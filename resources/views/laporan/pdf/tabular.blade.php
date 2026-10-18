<!doctype html>
<html>
<head><meta charset="utf-8">@include('laporan.pdf._header')</head>
<body>
    <table>
        <thead>
            <tr>
                @foreach ($headers as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach ($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($headers) }}">Tidak ada data pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>
    <p class="footer">Total {{ count($rows) }} baris.</p>
</body>
</html>
