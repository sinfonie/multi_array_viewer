<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

  <title>multiArrayViewer examples</title>
</head>

<body>

  <?php

  $files = scandir('mavLibs/samples');
  $samples = array_filter($files, 'filterSamples');

  function filterSamples($i)
  {
    $leave = ['loader.php', '.', '..'];
    if (in_array($i, $leave)) return false;
    if (strpos($i, '.php')) return true;
  }

  function getLinks($samples)
  {
    $output = '<div>';
    foreach ($samples as $sample) {
      $output .= '<a class="btn btn-primary" href="mavLibs/samples/' . $sample . '">' . $sample . '</a>';
    }
    $output .= '</div>';
    return $output;
  }

  $template = '<div class="m-4">
              <h1>Click below to see multiArrayViewer examples</h1>' .
    getLinks($samples) .
    '</div>';

  echo $template;

  ?>

</body>

</html>