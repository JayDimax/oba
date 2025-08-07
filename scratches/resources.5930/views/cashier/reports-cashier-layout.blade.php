<!-- resources/views/cashier/reports/pdf/layout.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <style>
    body {
      font-family: sans-serif;
      font-size: 12px;
      margin: 0;
      padding: 20px;
    }
    h2 {
      text-align: center;
      margin-bottom: 10px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }
    th, td {
      border: 1px solid #ddd;
      padding: 6px;
      text-align: left;
    }
    th {
      background-color: #f0f0f0;
    }
    .section-title {
      margin-top: 20px;
      font-weight: bold;
      text-transform: uppercase;
    }
  </style>
</head>
<body>
  <h2>{{ $title }} - {{ $date }}</h2>

  <table>
    <thead>
      <tr>
        @foreach($headers as $header)
          <th>{{ $header }}</th>
        @endforeach
      </tr>
    </thead>
    <tbody>
      @foreach($rows as $row)
        <tr>
          @foreach($row as $cell)
            <td>{!! $cell !!}</td>
          @endforeach
        </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>
