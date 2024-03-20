
<table>
    <thead>
        <tr>
            <th>Referer</th>
            <th>Refferer ID</th>
            <th>Reffered Date</th>
            <th>Refer Person</th>
            <th>Installed Date</th>
            <th>Refer Person ID</th>
        </tr>
    </thead>
        <tbody>
        @foreach($reports as $report)
            <tr>
                <td>{{ $report->user_refferal ? $report->user_refferal->name : '-' }}</td>
                <td>{{ $report->user_refferal ? $report->user_refferal->id : '-' }}</td>
                <td>{{ $report->name}}</td>
                <td>{{ $report->created_at }}</td>
                <td>{{ $report->id }}</td>
            </tr>
        @endforeach

    </tbody>
</table>

