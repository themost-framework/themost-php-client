<?php

namespace MostWebFramework\Client;

use MostWebFramework\Client\Args;
use MostWebFramework\Client\DataQueryableOptions;
use Exception;

class DataQueryable
{
    /**
     * Gets or sets the underlying DataService instance
     * @var null|DataService
     */
    public $service;
    /**
     * @var null|string
     */
    protected $model;
    /**
     * Gets or sets in-process operator
     * @var null|string
     */
    private $left;
    /**
     * Gets or sets in-process operator
     * @var null|string
     */
    private $op;
    /**
     * Gets or sets in-process operator
     * @var null|string
     */
    private $lop;
    /**
     * Gets or sets in-process prepared operator
     * @var null|string
     */
    private $prepared_lop;
    /**
     * Gets or sets in-process operator
     * @var *
     */
    private $right;
    /**
     * @var null|DataQueryableOptions
     */
    protected $options;
    /**
     * Gets or sets the target URL based on the current model
     * @var *
     */
    protected $get_url;
    /**
     * Gets or sets the target URL for POST operations based on the current model
     * @var *
     */
    protected $post_url;
    /**
     * Gets or sets the key of the related item if any
     * @var *
     */
    private $key;
    /**
     * DataQueryable class constructor.
     * @param string $model - A string that represents the target model for this object.
     */
    public function __construct($model) {
        //set model
        $this->model = $model;
        //set get url
        $this->get_url = "/$model/index.json";
        //set post url
        $this->post_url = "/$model/edit.json";
        //init options
        $this->options = new DataQueryableOptions();
        //set inline count to true
        $this->options->inlinecount=true;
    }

    /**
     * Gets the name of the associated data model.
     * @return DataService
     */
    public function getModel() {
        return $this->model;
    }

    /**
     * Gets the instance of DataService which is associated with this data queryable.
     * @return DataService
     */
    public function getService() {
        return $this->service;
    }

    /**
     * Sets the instance of DataService which is associated with this data queryable.
     * @param DataService $service
     * @return DataQueryable
     */
    public function setService($service) {
        $this->service = $service;
        return $this;
    }

    const EXCEPTION_INVALID_RIGHT_OP = 'Invalid right operand assignment. Left operand cannot be empty at this context.';
    const EXCEPTION_NOT_NULL = 'Value cannot be null at this context.';

    /**
     * @param int $num
     * @return DataQueryable
     * @throws Exception
     * @throws HttpException
     */
    public function take($num) {
        $this->options->top = $num;
        $this->options->first = false;
        $this->options->inlinecount=false;
        return $this;
    }

    /**
     * @return stdClass
     * @throws Exception
     * @throws HttpException
     */
    public function getItem() {
        return $this->first();
    }

    /**
     * @return stdClass
     * @deprecated Use DataQueryable.getItem() instead
     * @throws Exception
     * @throws HttpException
     */
    public function item() {
        return $this->getItem();
    }

    /**
     * @return stdClass[]
     * @throws Exception
     * @throws HttpException
     */
    public function getItems() {
        $this->options->inlinecount = false;
        return $this->service->execute("GET", $this->get_url.$this->build_options_query(), null);
    }

    /**
     * @throws Exception
     * @throws HttpException
     */
    public function getList() {
        $this->options->first = false;
        $this->options->inlinecount = true;
        return $this->service->execute("GET", $this->get_url.$this->build_options_query(), null);
    }

    /**
     * @return stdClass[]
     * @deprecated Use DataQueryable.getItems() instead
     * @throws Exception
     * @throws HttpException
     */
    public function items() {
        return $this->getItems();
    }


    private function join_filters($filter1=null, $filter2=null)
    {
        if (is_string($filter1)) {
            if (is_null($this->prepared_lop))
                $this->prepared_lop='and';
            if (is_string($filter2)) {
                return '('.$filter1.') '.$this->prepared_lop.' ('.$filter2.')';
            }
            else {
                return $filter1;
            }
        }
        else {
            return $filter2;
        }
    }

    private function build_options_query() {
        if (is_null($this->options))
            return '';
        //enumerate options
        $vars = get_object_vars($this->options);
        if (is_string($vars['prepared'])) {
            $vars['filter'] = $this->join_filters($vars['prepared'], $vars['filter']);
            $vars['prepared']=null;
        }
        $query = array();
        while (list($key, $val) = each($vars)) {
            if (!is_null($val)) {
                if (is_bool($val))
                    array_push($query, '$'.$key.'='.($val ? 'true' : 'false'));
                else
                    array_push($query, '$'.$key.'='.$val);
            }

        }
        if (count($query)>0) {
            return "?".implode('&',$query);
        }
        else {
            return '';
        }
    }

    /**
     * @param int $num
     * @return DataQueryable
     */
    public function skip($num = 0) {
        $this->options->skip = $num;
        return $this;
    }

    /**
     * @param int $num
     * @return DataQueryable
     */
    public function top($num = 25) {
        if ($num<0)
            return $this;
        $this->options->top = $num;
        return $this;
    }

    /**
     * @return DataQueryable
     */
    public function prepare() {
        if (is_null($this->options->filter))
          return $this;
        //append filter statement
        $this->options->prepared = $this->join_filters($this->options->prepared, $this->options->filter);
        //destroy filter statement
        $this->options->filter=null;
        return $this;
    }

    /**
     * Prepares a logical OR query expression.
     * Note: The common DataQueryable.or() method cannot be used because and is a reserved word for PHP.
     * @param string $field
     *  @return DataQueryable
     */
    public function either($field = null) {
        Args::notNull($field,"Field");
        $this->lop = 'or';
        $this->left = $field;
        $this->lop = 'or';
        return $this;
    }

    /**
     * Prepares a logical AND query expression.
     * Note: The common DataQueryable.and() method cannot be used because and is a reserved word for PHP.
     * @param string $field
     *  @return DataQueryable
     */
    public function also($field) {
        Args::notNull($field,"Field");
        $this->lop = 'and';
        $this->left = $field;
        return $this;
    }

    /**
     * @param string $field
     * @return DataQueryable
     */
    public function andAlso($field) {
        if (is_null($field))
            return $this;
        $this->prepare();
        $this->prepared_lop = 'and';
        $this->left = $field;
        return $this;
    }

    /**
     * @param string $field
     * @return DataQueryable
     */
    public function orElse($field) {
        if (is_null($field))
            return $this;
        $this->prepare();
        $this->prepared_lop = 'or';
        $this->left = $field;
        return $this;
    }

    /**
     * @param string $field
     *  @return DataQueryable
     */
    public function where($field) {
        Args::notNull($field,"Field");
        //set in-process field
        $this->left = $field;
        return $this;
    }

    /**
     * @param ...string $field
     * @return DataQueryable
     * @throws Exception
     */
    public function select($field) {
        $arg_list = func_get_args();
        if (count($arg_list)>0) {
            $this->options->select = implode(',', $arg_list);
        }
        else {
            throw new Exception('Invalid argument. Expected string.');
        }
        return $this;
    }

    /**
     * @param ...string $field
     * @return DataQueryable
     * @throws Exception
     */
    public function groupBy($field) {
        $arg_list = func_get_args();
        if (count($arg_list)>0) {
            $this->options->group = implode(',', $arg_list);
        }
        else {
            throw new Exception('Invalid argument. Expected string.');
        }
        return $this;
    }

    /**
     * @param ...string $field
     * @return DataQueryable
     * @throws Exception
     */
    public function expand($field) {
        $arg_list = func_get_args();
        if (count($arg_list)>0) {
            $this->options->expand = implode(',', $arg_list);
        }
        else {
            throw new Exception('Invalid argument. Expected string.');
        }
        return $this;
    }

    /**
     * @param string $field
     * @return DataQueryable
     */
    public function orderBy($field) {
        Args::notNull($field,"Order expression");
        $this->options->order = $field;
        return $this;
    }
    /**
     * @param string $field
     * @return DataQueryable
     */
    public function orderByDescending($field) {
        Args::notNull($field,"Order expression");
        $this->options->order = "$field desc";
        return $this;
    }

    /**
     * @param null|string $field
     * @return DataQueryable
     */
    public function thenBy($field) {
        Args::notNull($field,"Order expression");
        if (isset($this->options->order))
            $this->options->order .= ",$field";
        else
            $this->options->order=$field;
        return $this;
    }

    /**
     * @param null|string $field
     * @return DataQueryable
     */
    public function thenByDescending($field = null) {
        Args::notNull($field,"Order expression");
        if (isset($this->options->order))
            $this->options->order .= ",$field desc";
        else
            $this->options->order="$field desc";
        return $this;
    }

    /**
     * @param * $value
     * @return DataQueryable
     * @throws Exception
     */
    public function equal($value = null) {
        return $this->compare('eq', $value);
    }

    /**
     * @param * $value1
     * @param * $value2
     * @return DataQueryable
     * @throws Exception
     */
    public function between($value1, $value2) {
        Args::notNull($this->left,"Left operand");
        $s = (new DataQueryable ($this->getModel ()))
            ->where ($this->left)->greaterOrEqual ($value1)
            ->also ($this->left)->lowerOrEqual ($value2)->options->filter;
        $lop = $this->lop;
        if (is_null($lop)) {
            $lop = "and";
        }
        $filter = $this->options->filter;
        if (is_string($filter)) {
            $this->options->filter = "($filter) $lop ($s)";
        }
        else {
            $this->options->filter =  "($s)";
        }
        $this->left = null; $this->op = null; $this->right = null; $this->lop = null;
        return $this;
    }

    /**
     * @param * $value
     * @return DataQueryable
     * @throws Exception
     */
    public function notEqual($value = null) {
        return $this->compare('ne', $value);
    }

    /**
     * @param * $value
     * @return DataQueryable
     * @throws Exception
     */
    public function greaterThan($value = null) {
        return $this->compare('gt', $value);
    }

    /**
     * @param * $value
     * @return DataQueryable
     * @throws Exception
     */
    public function greaterOrEqual($value = null) {
        return $this->compare('ge', $value);
    }

    /**
     * @param * $value
     * @return DataQueryable
     * @throws Exception
     */
    public function lowerThan($value = null) {
        return $this->compare('lt', $value);
    }

    /**
     * @param * $value
     * @return DataQueryable
     * @throws Exception
     */
    public function lowerOrEqual($value = null) {
        return $this->compare('le', $value);
    }

    /**
     * @param * $value
     * @return DataQueryable
     * @throws Exception
     */
    public function endsWith($value = null) {
        if (is_null($this->left))
            throw new Exception(self::EXCEPTION_INVALID_RIGHT_OP);
        $left = $this->left;
        $escapedValue = $this->escape($value);
        $this->left = "endswith($left,$escapedValue)";
        return $this;
    }

    /**
     * @param * $value
     * @return DataQueryable
     * @throws Exception
     */
    public function startsWith($value = null) {
        if (is_null($this->left))
            throw new Exception(self::EXCEPTION_INVALID_RIGHT_OP);
        $left = $this->left;
        $escapedValue = $this->escape($value);
        $this->left = "startswith($left,$escapedValue)";
        return $this;
    }

    /**
     * @return DataQueryable
     * @throws Exception
     */
    public function toLowerCase() {
        Args::notNull($this->left,"Left operand");
        $field = $this->left;
        $this->left = "tolower($field)";
        return $this;
    }

    /**
     * @return DataQueryable
     * @throws Exception
     */
    public function toUpperCase() {
        Args::notNull($this->left,"Left operand");
        $field = $this->left;
        $this->left = "toupper($field)";
        return $this;
    }

    /**
     * @return DataQueryable
     * @throws Exception
     */
    public function trim() {
        Args::notNull($this->left,"Left operand");
        $field = $this->left;
        $this->left = "toupper($field)";
        return $this;
    }

    /**
     * @return DataQueryable
     * @throws Exception
     */
    public function round() {
        Args::notNull($this->left,"Left operand");
        $field = $this->left;
        $this->left = "round($field)";
        return $this;
    }

    /**
     * @return DataQueryable
     * @throws Exception
     */
    public function floor() {
        Args::notNull($this->left,"Left operand");
        $field = $this->left;
        $this->left = "floor($field)";
        return $this;
    }

    /**
     * @return DataQueryable
     * @throws Exception
     */
    public function ceil() {
        Args::notNull($this->left,"Left operand");
        $field = $this->left;
        $this->left = "ceiling($field)";
        return $this;
    }

    /**
     * @param int $pos
     * @param int $length
     * @return $this
     * @throws Exception
     */
    public function substring($pos=0, $length=0) {
        if ($length<=0)
            throw new Exception('Invalid argument. Length must be greater than zero.');
        if ($pos<0)
            throw new Exception('Invalid argument. Position must be greater or equal to zero.');
        $field = $this->left;
        $this->left = "substring($field,$pos,$length)";
        return $this;
    }

    /**
     * @param int $pos
     * @param int $length
     * @return $this
     * @throws Exception
     */
    public function substr($pos=0, $length=0) {
        return $this->substring($pos,$length);
    }

    /**
     * @param string $s
     * @return DataQueryable
     */
    public function indexOf($s) {
        Args::notNull($this->left,"Left operand");
        Args::notNull($s,"Value");
        $str = $this->escape($s);
        $field = $this->left;
        $this->left = "indexof($field,$str)";
        return $this;
    }

    /**
     * @param string $value
     * @return DataQueryable
     * @throws Exception
     */
    public function contains($value) {
        Args::notNull($value,"Value");
        //escape value
        $str = $this->escape($value);
        //get left operand
        $left = $this->left;
        //format left operand
        $this->left = "contains($left,$str)";
        //and finally append comparison
        return $this->compare('ge', 0);
    }

    /**
     * @return DataQueryable
     * @throws Exception
     */
    public function length() {
        Args::notNull($this->left,"Left operand");
        $field = $this->left;
        $this->left = "length($field)";
        return $this;
    }

    /**
     * @return DataQueryable
     * @throws Exception
     */
    public function getDate() {
        Args::notNull($this->left,"Left operand");
        $field = $this->left;
        $this->left = "date($field)";
        return $this;
    }
    
    /**
     * @return DataQueryable
     * @throws Exception
     */
    public function getYear() {
        Args::notNull($this->left,"Left operand");
        $field = $this->left;
        $this->left = "year($field)";
        return $this;
    }

    /**
     * @return DataQueryable
     * @throws Exception
     */
    public function getFullYear() {
        return $this->getYear();
    }

    /**
     * @return DataQueryable
     * @throws Exception
     */
    public function getMonth() {
        Args::notNull($this->left,"Left operand");
        $field = $this->left;
        $this->left = "month($field)";
        return $this;
    }

    /**
     * @return DataQueryable
     * @throws Exception
     */
    public function getDay() {
        Args::notNull($this->left,"Left operand");
        $field = $this->left;
        $this->left = "day($field)";
        return $this;
    }

    /**
     * @return DataQueryable
     * @throws Exception
     */
    public function getHours() {
        Args::notNull($this->left,"Left operand");
        $field = $this->left;
        $this->left = "hour($field)";
        return $this;
    }

    /**
     * @return DataQueryable
     * @throws Exception
     */
    public function getMinutes() {
        Args::notNull($this->left,"Left operand");
        $field = $this->left;
        $this->left = "minute($field)";
        return $this;
    }

    /**
     * @return DataQueryable
     * @throws Exception
     */
    public function getSeconds() {
        Args::notNull($this->left,"Left operand");
        $field = $this->left;
        $this->left = "second($field)";
        return $this;
    }

    /**
     * @param string $op
     * @param * $value
     * @return DataQueryable
     * @throws Exception
     */
    private function compare($op = null, $value = null) {
        if (is_null($this->left))
            throw new Exception(EXCEPTION_INVALID_RIGHT_OP);
        $this->op = $op;
        $this->right = $value;
        $this->append();
        return $this;
    }

    protected function append() {
        try {
            $expr = $this->left . ' ' . $this->op . ' ' . $this->escape($this->right);
            if (is_null($this->lop)) {
                $this->lop = 'and';
            }
            if (isset($this->options->filter))
                $this->options->filter = '(' . $this->options->filter . ') '. $this->lop .' (' . $expr . ')';
            else
                $this->options->filter = $expr;
                    //clear expression parameters
            $this->left = null; $this->op = null; $this->right = null; $this->lop = null;
        }
        catch(Exception $e) {
            throw $e;
        }
    }

    /**
     * @param null $value
     * @return string
     */
    public function escape($value = null) {
        //0. null
        if (is_null($value))
            return 'null';
        //1. array
        if (is_array($value)) {
            $array = array();
             foreach ($value as $val) {
                 array_push($array,$this->escape($val));
             }
            return '['. implode(",", $array) . ']';
        }
        //2. datetime
        else if (is_a($value, 'DateTime')) {
            $str = $value->format('c');
            return "'$str'";
        }
        //3. boolean
        else if (is_bool($value)) {
            return $value==true ? 'true': 'false';
        }
        //4. numeric
        else if (is_float($value) || is_double($value) || is_int($value)) {
            return json_encode($value);
        }
        //5. string
        else if (is_string($value)) {
            return "'$value'";
        }
        //6. filter expression
        else if (is_a($value, 'FilterExpression')) {
            return (string)$value;
        }
        //7. other
        else {
            $str = (string)$value;
            return "'$str'";
        }
    }

}