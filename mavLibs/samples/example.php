<?php

namespace mavLibs\samples;

use mavLibs\src\multiArrayViewerCreator as mAVC;

//include this file to any file you are editing (remember to configure your current working path correctly).
include_once 'loader.php';

class example
{

  //input array
  public static $myArray = [
    ['id' => 1, 'first_name' => 'John', 'last_name' => 'Smith', 'date_of_birth' => '1985-01-03'],
    ['id' => 2, 'first_name' => 'Thomas', 'last_name' => 'Carpenter', 'date_of_birth' => '1973-05-31'],
    ['id' => 3, 'first_name' => 'Brian', 'last_name' => 'Smith', 'date_of_birth' => '1975-07-04'],
    ['id' => 4, 'first_name' => 'Michael', 'last_name' => 'Newman', 'date_of_birth' => '1966-09-12'],
    ['id' => 5, 'first_name' => 'Peter', 'last_name' => 'Steward', 'date_of_birth' => '1983-11-29'],
  ];

  //table header translations
  private static $translations = [
    'id' => ['translation' => 'Index'],
    'first_name' => ['translation' => 'First name'],
    'last_name' => ['translation' => 'Last name'],
    'date_of_birth' => ['translation' => 'Date of birth'],
  ];

  //single row structure, note the variables in curly braces
  public static $singleRow = '
            <tr>
              <td>{id}</td>
              <td>{first_name}</td>
              <td>{last_name}</td>
              <td>{date_of_birth}</td>
            </tr>';

  //create an mAVC object, catch form params, assign parameters, add data, set filters,
  //as a result method returns html table
  public static function show()
  {
    $table = new mAVC();
    $table->setColumnOrder(['id', 'first_name', 'last_name', 'date_of_birth']);
    $table->catchFormParams();
    $table->templateBefore = '<div><h3>Example</h3>';
    $table->templateAfter  = '</div>';
    $table->setFilters('text', ['first_name', 'last_name']);
    $table->columnsSortDisabled(['last_name']);
    $table->data = self::$myArray;
    $table->setTableBody(self::$singleRow);
    $table = $table->createTemplate(self::$translations);
    return $table;
  }
}

?>

<!-- Example HTML -->

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

  <title>Example</title>
</head>

<body class="m-4">

  <?php
  echo '<div class="container bg-light">' . example::show() . '</div>' .
    '<div class="container bg-info w-auto">' .
    '<h4 class="my-4">Below you can see an input array</h4>' .
    '<pre>';
  print_r(example::$myArray);
  echo '</pre>' .
    '</div>';
  ?>

</body>

</html>