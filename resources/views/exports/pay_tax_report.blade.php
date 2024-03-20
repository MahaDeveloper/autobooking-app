
<table>
    <thead>
        <tr>
            <th>S No.</th>
            <th>Driver Name</th>
            <th>Mobile Number</th>
            <th>Vachicle Number</th>
            <th>Ride Count</th>
            <th>Tax Amount</th>
            <th>Date / Time</th>
        </tr>
    </thead>
        <tbody>
        @foreach($reports as $report)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $report->driver ? $report->driver->name : '-' }}</td>
                <td>{{ $report->driver ? $report->driver->mobile : '-' }}</td>
                <td>{{ $report->driver->driver_proofs->number ?? '-' }}</td>
                <td>{{ $report->total_ride}}</td>
                <td>{{ $report->amount }}</td>
                <td>{{ $report->created_at }}</td>
            </tr>
        @endforeach

    </tbody>
</table>

