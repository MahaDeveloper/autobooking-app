
<table>
    <thead>
        <tr>
            <th>Driver ID</th>
            <th>Driver Name</th>
            <th>Mobile Number</th>
            <th>Email</th>
            <th>Vachicle Number</th>
            <th>Duty Status</th>
        </tr>
    </thead>
        <tbody>
        @foreach($reports as $report)
            <tr>
                <td>{{ $report->id }}</td>
                <td>{{ $report->name }}</td>
                <td>{{ $report->mobile }}</td>
                <td>{{ $report->driverDetail->email }}</td>
                <td>{{ $report->driver_proofs->number ?? '-' }}</td>
                @if($report->current_status == 1)
                <td>Online</td>
                @elseif($report->current_status == 2)
                <td>Offline</td>
                @else
                <td>-</td>
                @endif
            </tr>
        @endforeach

    </tbody>
</table>

