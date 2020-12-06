<?php

/**
 * multiArrayViewerCreator
 * @author  Maciej Rumiński
 * @version 1.0.0
 */

namespace multiArrayViewer\src;

use \multiArrayViewer\src\helpers\template;
use \multiArrayViewer\src\helpers\validate;
use \multiArrayViewer\src\helpers\translate;

class multiArrayViewerCreator
{
    public $templateBefore;
    public $templateAfter;
    public $tableBody;
    public $custom_GET;

    public function __construct()
    {
        $this->setAutoProperties();
    }

    private static $translations = [
        'limit'      => ['translation' => 'Limit: '],
        'select_all' => ['translation' => 'Select all'],
    ];

    public $params = [
        'order' => [],
        'sort' => ['asc', 'desc'],
        'sort_disabled' => [''],
        'limit' => [1, 5, 10, 20],
        'pagination_max' => 5,
        //'polylang_section' => 'not_assigned',
        'native' => [
            'filter' => true,
            'sort' => true,
        ],
        'query' => [
            'filter' => true,
            'sort' => true,
        ],
        'basic_vals' => [
            'order' => '',
            'sort' => 'asc',
            'sort_disabled' => [''],
            'limit' => 10,
            'page_no' => 1,
        ],
    ];

    ### manual setters ###
    public function setParam($key, $value)
    {
        $this->params[$key] = $value;
        return $this;
    }

    public function setBasicVal($key, $value)
    {
        $this->params['basic_vals'][$key] = $value;
        return $this;
    }

    public function setTableBody(string $tableBody)
    {
        $this->tableBody = $tableBody;
        return $this;
    }
    ### manual setters end ###

    ### wrapers start ###
    public function setColumnOrder(array $order)
    {
        $this->setParam('order', $order);
        return $this;
    }

    public function columnsSortDisabled(array $columns)
    {
        $this->setParam('sort_disabled', $columns);
        return $this;
    }

    /*     public function setPolylangSection(string $section)
    {
        $this->setParam('polylang_section', $section);
        return $this;
    } */

    public function setFilters(string $type, array $filters)
    {
        foreach ($filters as $k => $v) {
            if (is_int($k)) {
                $this->filter[$v]['type'] = $type;
            } else {
                $this->filter[$k]['type'] = $type;
                $this->filter[$k]['value'] = $v;
            }
        }
        return $this;
    }

    public function setLimits(array $limit)
    {
        $this->setParam('limit', $limit);
        return $this;
    }

    public function setMaxPagination(int $number)
    {
        $this->setParam('pagination_max', $number);
        return $this;
    }
    ### wrapers end ###

    ### auto setters ###
    private function setAutoProperties()
    {
        $this->templateBefore = '';
        $this->templateAfter = '';
        $this->tableBody = '';
        $this->custom_GET = '';
        $this->formName = 'tm';
    }
    ### auto setters end ###


    ### start creating template ###
    public function createTemplate($translations = null, $dataView = false): string
    {
        $translations = $this->createTranslations($translations);
        if ($this->data != null) {
            if ($this->params['native']['filter']) {
                $this->processNativeFilters();
            }

            if ($this->params['native']['sort']) {
                $this->processNativeSort();
            }

            if ($dataView) {
                $this->data = $dataView::finalDataFormat($this->data);
            }

            $this->prepareTable($this->data);
            return $this->processTextTemplate($translations);
        }
        return '';
    }

    private function createTranslations($translations)
    {
        $translations = (!is_array($translations)) ? self::$translations : array_merge($translations, self::$translations);
        return translate::translate($translations);
    }

    private function prepareTable($data)
    {

        $this->colspan = count($this->params['order']);
        $pagination = array_chunk($data, $this->limit);
        $this->pagination = array_combine(range(1, count($pagination)), array_values($pagination));

        $this->pages = array_keys($this->pagination);
        $this->correctPageNo();
        $this->data = $this->pagination[$this->pageNo];
    }

    private function correctPageNo()
    {
        $field = $this->formName . '_page_no';
        if ($this->catched[$field] > count($this->pages)) {
            $page_no = count($this->pages);
            $this->catched[$field] = $page_no;
            $this->pageNo = $page_no;
        } elseif ($this->catched[$field] < 1) {
            $this->catched[$field] = $this->params['basic_vals']['page_no'];
            $this->pageNo = $this->params['basic_vals']['page_no'];
        }
    }

    private function processTextTemplate($translations)
    {
        return template::create($this->templateBefore, $translations) .
            '<div>
                  <table>
                    <thead>' .
            template::create($this->createHeader(), $translations) .
            '</thead>
                    <tbody>' .
            $this->createTableEntries($translations) .
            '</tbody>
                  </table>
                </div>' .
            template::create($this->templateAfter, $translations);
    }

    ### catches data from get methods start ###

    public function catchFormParams()
    {
        $this->filtration   = $this->catchFilters();
        $this->sort         = $this->catchSort();
        $this->sortInv      = $this->sortInv();
        $this->limit        = $this->catchLimit();
        $this->pageNo       = $this->catchPageNo();
        $this->getCatched();
    }

    private function sortInv()
    {
        if (isset($this->sort['order'])) {
            return ($this->sort['order'] === 'asc') ? 'desc' : 'asc';
        }
        return 'asc';
    }

    private function catchFilters()
    {
        $output = [];
        foreach ($this->params['order'] as $order) {
            if (isset($_GET[$this->formName . '_filter_' . $order])) {
                if ($_GET[$this->formName . '_filter_' . $order] !== '') {
                    $output[$order] = (is_numeric($_GET[$this->formName . '_filter_' . $order])) ? intval($_GET[$this->formName . '_filter_' . $order])  : $_GET[$this->formName . '_filter_' . $order];
                }
            }
        }
        return $output;
    }

    private function catchSort()
    {
        $output = [];
        if (isset($_GET[$this->formName . '_order']) && isset($_GET[$this->formName . '_orderby'])) {
            $output['order']   = $_GET[$this->formName . '_order'];
            $output['orderby'] = $_GET[$this->formName . '_orderby'];
        } else {
            $output['order']   = $this->params['basic_vals']['sort'];
            $output['orderby'] = $this->params['order'][0];
        }
        return $output;
    }

    private function catchLimit()
    {
        $output = '';
        if (isset($_GET[$this->formName . '_limit'])) {
            $output = $_GET[$this->formName . '_limit'];
        } else {
            $output = $this->params['basic_vals']['limit'];
        }
        return $output;
    }

    private function catchPageNo()
    {
        $output = '';
        if (isset($_GET[$this->formName . '_page_no'])) {
            $output = $_GET[$this->formName . '_page_no'];
        } else {
            $output = $this->params['basic_vals']['page_no'];
        }
        return $output;
    }


    private function getVals($array, $name)
    {
        $output = [];
        if (!empty($array)) {
            foreach ($array as $key => $val) {
                $output[$name . '_' . $key] = $val;
            }
        }
        return $output;
    }

    private function getCatched()
    {
        $catched = array_merge(
            $this->getVals($this->filtration, $this->formName . '_filter'),
            $this->getVals($this->sort, $this->formName),
            [$this->formName . '_limit'       => $this->limit],
            [$this->formName . '_page_no'     => $this->pageNo]
        );
        $this->catched = array_filter($catched);
    }
    ### catches data from get methods end ###

    ### query methods start###
    public function createQuery(string $type, array $args)
    {
        //turn off native sort method
        if ($this->params['query']['sort']) {
            if (!empty($this->sort)) {
                if ($this->recursiveInArray($this->sort['orderby'], $args)) {
                    $this->params['native']['sort'] = false;
                }
            }
        }

        if ($type === 'WP_User_Query') {
            $query = $this->eduUserQueryConstructor($args);
        }
        $this->query = $query;
    }

    private function eduUserQueryConstructor($params)
    {
        $query['search'] = [];
        $query['metas']  = [];
        if (!empty($this->filtration)) {
            foreach ($this->filtration as $key => $val) {
                if (in_array($key, $params['search'])) {
                    $query['search'][$key] = $val;
                }
                if (in_array($key, $params['metas'])) {
                    $query['metas'][$key] = $val;
                }
            }
        }
        if (!empty($this->sort)) {
            if ($this->params['query']['sort']) {
                $query['order'] = $this->sort['order'];
                if (in_array($this->sort['orderby'], $params['metas'])) {
                    $query['orderby'] = 'meta_value';
                    $query['meta_key'] = $this->sort['orderby'];
                } else {
                    $query['orderby'] = $this->sort['orderby'];
                }
            }
        }
        if (!empty($this->limit)) {
            foreach ($this->limit as $key => $val) {
                $query[explode('_', $key)[1]] = $val;
            }
        }
        return $query;
    }
    ### query methods end ###

    ### template header & footer creation methods start ###
    private function createHeader()
    {
        $output  = $this->createLimits();
        $output .= $this->createFilters();
        $output .= $this->createSorted();

        return $output;
    }

    private function createLimits()
    {
        $output = '<tr class="header limit-header">
            <th colspan="' . $this->colspan . '">
            <span>{[limit]}</span>';
        foreach ($this->params['limit'] as $limit) {
            $currLimit = ($limit == $this->limit) ? 'current-limit' : null;
            $field = $this->formName . '_limit';
            $output .= '
              <span class="limit-btn ' . $currLimit . '">
                <a href="?' . $field . '=' . $limit . '&' . $this->createCatchedString([$field]) . '">
                  <span>' . $limit . '</span>
                </a>
              </span>';
        }
        $output .= '
          </th>
        </tr>';
        return $output;
    }

    private function createFilters()
    {
        $output = '<tr class="header filter-header">
                    <form method="get" class="form-async" id="' . $this->formName . '"></form> 
        ';
        foreach ($this->catched as $key => $val) {
            $arr = explode('_', $key);
            if ($arr[1] === 'filter') {
                continue;
            } else {
                $output .= '<input form="' . $this->formName . '" type="hidden" name="' . $key . '" value="' . $val . '">';
            }
        }
        foreach ($this->params['order'] as $order) {
            if (!empty($order)) {
                $value = (!empty($this->filtration[$order])) ? $this->filtration[$order] : '';
                if (isset($this->filter[$order]['custom'])) {
                    $output .= $this->filter[$order]['custom'];
                } else if (isset($this->filter[$order]['type'])) {

                    if ($this->filter[$order]['type'] === 'select') {
                        $option = '';
                        foreach ($this->filter[$order]['value'] as $k => $v) {
                            $option .= '<option value="' . $k . '">{[' . $v . ']}</option>';
                        }
                        $output .= '<th>
                                    <div class="input-group filter-input-group">
                                        <select class="form-control" name="' . $this->formName . '_filter_' . $order . '"
                                                form="' . $this->formName . '" >
                                                ' . $option . '
                                        </select>
                                        <div class="btn-search input-group-append">
                                            <button
                                              type="submit"
                                              class="filter-btn " 
                                               form="' . $this->formName . '">
                                               &#8981;
                                            </button>
                                        </div>
                                    </div>
                                </th>';
                    } else if ($this->filter[$order]['type'] !== null) {
                        $output .= '<th>
                                    <div class="input-group filter-input-group">
                                       <input class="form-control" 
                                         form="' . $this->formName . '" 
                                         placeholder="{[' . $order . ']}" 
                                         value="' . $value . '" 
                                         name="' . $this->formName . '_filter_' . $order . '" 
                                         type="' . $this->filter[$order]['type'] . '">
                                        <div class="btn-search input-group-append">
                                            <button
                                              type="submit"
                                               class="filter-btn " 
                                               form="' . $this->formName . '">
                                               &#8981;
                                            </button>
                                        </div>
                                    </div>
                                </th>';
                    }
                } else {
                    $output .= '<th></th>';
                }
            } else {
                $output .= '<th></th>';
            }
        }
        $output .= '</tr>';
        return $output;
    }

    private function createSorted()
    {
        $output = '<tr class="header sort-header" >';
        foreach ($this->params['order'] as $field) {
            $output .= '<th>';
            $order = $this->formName . '_order';
            $orderby = $this->formName . '_orderby=' . $field;
            $args = [$order, $this->formName . '_orderby'];
            if (!in_array($field, $this->params['sort_disabled'])) {
                $catched = $this->createCatchedString($args);
                $output .= '<div class="sort-cell">
                <a href="?' . $order . '=desc&' . $orderby . '&' . $catched . '" class="sort-arrow">🡇</a>
                <a href="?' . $order . '=' . $this->sortInv . '&' . $orderby . '&' . $catched . '" class="sort-name">{[' . $field . ']}</a>
                <a href="?' . $order . '=asc&'  . $orderby . '&' . $catched . '" class="sort-arrow">🡅</a>
              </div>';
            }
            $output .= '</th>';
        }
        $output .= '</tr>';
        return $output;
    }
    ### template header & footer creation methods end ###

    private function createTableEntries($translations)
    {
        $paginationTemplate = '';
        $class = 'odd';
        foreach ($this->pagination[$this->pageNo] as $subscription) {
            $class = ($class != 'odd') ? 'odd' : 'even';
            $subscription['row_class'] = $class;
            $paginationTemplate .= template::create($this->tableBody, array_merge($translations, $subscription));
        }
        if (count($this->pages) > 1) {
            $paginationTemplate .= $this->getPaginationTemplate();
        }
        return $paginationTemplate;
    }

    private function getPaginationTemplate()
    {
        return '<tr class="pagination-footer">
                 <td colspan="' . $this->colspan . '">
                 ' . $this->getPagination() . '
                 </td>
                </tr>';
    }

    private function getPagination()
    {
        $field = $this->formName . '_page_no';
        $catched = $this->createCatchedString([$field]);
        $output = '<a class="page-nav" href="?' . $field . '=' . array_key_first($this->pagination) . '&' . $catched . '"> << </a>';
        $on_side = floor($this->params['pagination_max'] / 2);
        $min_page = $this->pageNo - $on_side;
        $max_page = $this->pageNo + $on_side;
        foreach ($this->pages as $page) {
            $class = ($page == $this->pageNo) ? 'current_page' : '';
            if ($page <= $max_page && $page >= $min_page) {
                $output .= '<a class="' . $class . '"href="?' . $field . '=' . $page . '&' . $catched . '">' . $page . '<a>';
            } else if ($page == ($min_page - 1) || $page == ($max_page + 1)) {
                $output .= '<a class="' . $class . '"href="?' . $field . '=' . $page . '&' . $catched . '">...<a>';
            }
        }
        $output .= '<a class="page-nav" href="?' . $field . '=' . array_key_last($this->pagination) . '&' . $catched . '"> >> </a>';
        return $output;
    }

    ### class helpers start ###
    public function selectAllCheckbox($id)
    {
        return '<th class="select_all_cell">
                    <input class="select_all_chbx" type="checkbox" id="' . $id  . '_select_all">
                    <span> {[select_all]}</span>
                </th>';
    }

    private function createCatchedString(array $args)
    {
        $output = '';
        foreach ($this->catched as $key => $val) {
            if (in_array($key, $args)) continue;
            $output .= $key . '=' . $val;
            $output .= ($key == array_key_last($this->catched)) ? '' : '&';
        }
        return $output . $this->custom_GET;
    }

    private function recursiveInArray($where, $param)
    {
        if (is_array($param)) {
            if (in_array($where, $param)) {
                return true;
            } else {
                foreach ($param as $p) {
                    if ($this->recursiveInArray($where, $p)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
    ### class helpers end ###

    ### native sort & filter methods start ###

    private function processNativeFilters()
    {
        if (isset($this->query) && $this->filtration !== null) {
            $this->unsetFilters('metas');
            $this->unsetFilters('search');
            if (isset($this->memory)) {
                $this->filtration = array_merge($this->filtration, $this->memory);
            }
        }

        if (!empty($this->filtration)) {
            $this->data = array_filter($this->data, [$this, 'returnFiltered']);
        }
    }

    private function processNativeSort()
    {
        usort($this->data, [$this, 'sortArray']);
        ($this->sort['order'] == 'asc') ? ksort($this->data) : krsort($this->data);
    }


    private function sortArray($a, $b)
    {
        $orderby = $this->sort['orderby'];
        if (preg_match('/\\d/', $a[$orderby])) {
            return version_compare($a[$orderby], $b[$orderby]);
        } elseif (is_numeric($a[$orderby])) {
            return bccomp($a[$orderby], $b[$orderby]);
        }
        return strcmp($a[$orderby], $b[$orderby]);
    }

    private function returnFiltered($iteration)
    {

        foreach ($this->filtration as $name => $value) {
            if (is_int($iteration[$name]) && is_numeric($value)) {
                $value = intval($value);
            }
            if (validate::checkDate($iteration[$name]) && validate::checkDate($value)) {
                $iteration[$name] = date("Y-m-d", strtotime($iteration[$name]));
                $value = date("Y-m-d", strtotime($value));
            }
            if ($iteration[$name] === $value) {
                $output[$name] = true;
            } else if ($this->filtration === null) {
                $output[$name] = true;
            } else {
                $output[$name] = false;
            }
        }
        return (in_array(false, $output)) ? false : true;
    }

    private function unsetFilters(string $type)
    {
        if (!empty(array_keys($this->query[$type]))) {
            foreach (array_keys($this->query[$type]) as $i) {
                $this->memory[$i] = $this->filtration[$i];
                unset($this->filtration[$i]);
            }
        }
    }
    ### native sort & filter methods end ###
}
