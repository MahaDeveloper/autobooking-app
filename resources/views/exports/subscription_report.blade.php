
<table>
    <thead>
        <tr>
            <th>S No.</th>
            <th>Title</th>
            <th>Valitity Date</th>
            <th>Amount</th>
        </tr>
    </thead>
        <tbody>
        @foreach($reports as $report)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $report->name }}</td>
                <td>{{ $report->valitity_date }}</td>
                <td>{{ $report->amount }}</td>
            </tr>
        @endforeach

    </tbody>
</table>

