<table>
    <thead>
    <tr>
        <th>Ref</th>
        <th>First name</th>
        <th>Last name</th>
        <th>Mobile number</th>
        <th>Amount</th>
    </tr>
    </thead>
    <tbody>
    @foreach($persons as $person)
        <tr>
            <td>{{ $person->id }}</td>
            <td>{{ $person->first_name }}</td>
            <td>{{ $person->last_name }}</td>
            <td>{{ $person->mobile_number }}</td>
            <td>{{ $person->amount }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
