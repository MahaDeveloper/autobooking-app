
<table>
    <thead>
        <tr>
            <th>Ride ID</th>
            <th>Ride Date</th>
            <th>Driver Name</th>
            <th>Driver Vachicle No.</th>
            <th>Passenger Name</th>
            <th>Pickup Address</th>
            <th>Drop Address</th>
            <th>Distance</th>
            <th>Ride Amount</th>
            {{-- <th>Ride Duration</th> --}}
            <th>Average Speed</th>
        </tr>
    </thead>
        <tbody>
        @foreach($reports as $report)
            <tr>
                <td>{{ $report->id }}</td>
                <td>{{ $report->craeted_at }}</td>
                <td>{{ $report->driver ? $report->driver->name : '-' }}</td>
                <td>{{ $report->driver->driver_proofs->number ?? '-' }}</td>
                <td>{{ $report->user ? $report->user->name : '-' }}</td>
                <td>{{ $report->pickup_address ? $report->pickup_address : '-' }}</td>
                <td>{{ $report->drop_address ? $report->drop_address : '-' }}</td>
                <td>{{ $report->distance ? $report->distance : '-' }}</td>
                <td>{{ $report->final_amount ? $report->final_amount : '-' }}</td>
                {{-- @php
                    $total_hours = $report->total_hrs ?? 0;
                    $hours = floor($total_hours);
                    $minutes = round(($total_hours - $hours) * 60);
                    $ride_duration =  $hours.' Hours , ' .$minutes. ' Minutes';
                @endphp
                <td>{{  $ride_duration ?? '-' }}</td> --}}
                <td>{{ $report->avg_spped ? $report->avg_spped : '-' }}</td>
            </tr>
        @endforeach

    </tbody>
</table>

