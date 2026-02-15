<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table {
            font-size: {{ count($headers) > 10 ? '10px' : '12px' }};
        }
        th, td {
            border: 1px solid #000;
            padding: 4px;
            word-wrap: break-word;
            text-align: center;
        }
        img {
            max-width: 60px;
            max-height: 60px;
            object-fit: contain;
        }
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                @foreach($headers as $column)
                    <th>{{ $column }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr>
                    @foreach($row as $key => $value)
                        <td>
                            @if(is_string($value) && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $value))
                                @php
                                    $imagePath = public_path($value);
                                @endphp
                                @if(file_exists($imagePath))
                                    <img src="{{ $imagePath }}" alt="Image">
                                @else
                                    {{ $value }}
                                @endif
                            @else
                                {{ $value }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>