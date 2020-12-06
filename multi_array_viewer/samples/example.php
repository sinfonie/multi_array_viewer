<?php

namespace multiArrayViewer\samples;

use \multiArrayViewer\src\multiArrayViewerCreator as mAVC;

include_once 'loader.php';

class example
{

  public static $myArray = [
    ['id' => 1, 'first_name' => 'John', 'last_name' => 'Smith', 'date_of_birth' => '1985-01-03'],
    ['id' => 2, 'first_name' => 'Thomas', 'last_name' => 'Carpenter', 'date_of_birth' => '1973-05-31'],
    ['id' => 3, 'first_name' => 'Brian', 'last_name' => 'Norton', 'date_of_birth' => '1975-07-04'],
    ['id' => 4, 'first_name' => 'Michael', 'last_name' => 'Newman', 'date_of_birth' => '1966-09-12'],
    ['id' => 5, 'first_name' => 'Peter', 'last_name' => 'Steward', 'date_of_birth' => '1983-11-29'],
  ];

  private static $translations = [
    'id' => ['translation' => 'Index'],
    'first_name' => ['translation' => 'First name'],
    'last_name' => ['translation' => 'Last name'],
    'date_of_birth' => ['translation' => 'Date of birth'],
  ];

  public static $tableBody = '
            <tr>
              <td>{id}</td>
              <td>{first_name}</td>
              <td>{last_name}</td>
              <td>{date_of_birth}</td>
            </tr>';

  public static function show()
  {
    $table = new mAVC();

    $table->setColumnOrder(['id', 'first_name', 'last_name', 'date_of_birth']);
    $table->catchFormParams();
    $table->templateBefore = '<div>Example';
    $table->templateAfter  = '</div>';
    $table->setFilters('text', ['first_name', 'last_name']);
    $table->columnsSortDisabled(['last_name']);
    $table->data = self::$myArray;
    $table->setTableBody(self::$tableBody);
    $table = $table->createTemplate(self::$translations);
    return $table;
  }
}

echo example::show();

echo '<div>Below you can see an input array</div>';
echo '<pre>';
print_r(example::$myArray);
echo '</pre>';
