<table>
    <thead>
        <tr>
            @foreach($thead as $value)
                <th>{{ $value }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($tbody as $row)
            <tr>
                @foreach($row as $value)
                    <td>{{ $value }}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
