<html>
    <head>
        <style>
        table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            width: 100%;
        }

        td, th {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        tr:nth-child(even) {
            background-color: #dddddd;
        }
        </style>
    </head>
    <body>
        <table>
            
            <tr>
                <th>{{__('Medicine Name')}}</th>
                <th>{{__('Days')}}</th>
                <th>{{__('Quantity/Morning')}}</th>
                <th>{{__('Quantity/Afternoon')}}</th>
                <th>{{__('Quantity/Night')}}</th>
                <th>{{__('Remarks')}}</th>
            <tr>
            @foreach (json_decode($medicine) as $item)
                <tr>
                    <td>{{ $item->medicine }}</td>
                    <td>{{ $item->day }}</td>
                    <td>{{ $item->qty_morning }}</td>
                    <td>{{ $item->qty_afternoon }}</td>
                    <td>{{ $item->qty_night }}</td>
                    <td>{{ $item->remarks }}</td>
                </tr>
            @endforeach
        </table>
    </body>
</html>
