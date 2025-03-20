<!DOCTYPE html>
<html>
<head>
    <style>
        h1 {
            text-align: center;
            font-size: 1.5rem;
        }

        h2 {
            font-size: 1.2rem;
        }

        table {
            width: 100%;
        }

        thead tr {
            background-color: #3c3c3c;
            color: white;
            font-size: .8rem;
        }
                    
        th {
            padding: 10px;
            text-align: left;
        }

        tbody tr {
            color: black;
        }

        tbody tr:nth-child(even) {
            background-color: #7a7a7a7a;
        }

        tbody tr:nth-child(odd) {
            background-color: #bebebebe;
        }

        td {
            padding: 10px;
            font-size: .8rem;
        }
    </style>
</head>
<body>
    <h1>Generated on {{ $date }}</h1>
    <h2>{{ $title }}</h2>
    <table>
        <thead>
            <tr>
                <th>Employee ID</th>
                <th>First name</th>
                <th>Last name</th>
                <th>Department</th>
                <th>Total Access</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($employees as $employee)
                <tr>
                    <td>{{ $employee->id }}</td>
                    <td>{{ $employee->name }}</td>
                    <td>{{ $employee->last_name }}</td>
                    <td>{{ $employee->department->name }}</td>
                    <td>{{ $employee->count_access }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
