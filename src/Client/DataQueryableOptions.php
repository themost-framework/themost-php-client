<?php

namespace MostWebFramework\Client;

class DataQueryableOptions
{
    /**
     * Gets or set a string that contains an open data formatted filter statement, if any.
     * @var string
     */
    public $filter;
    /**
     * Gets or sets a comma delimited string that contains the fields to be retrieved.
     * @var string
     */
    public $select;
    /**
     * Gets or sets a comma delimited string that contains the fields to be used for ordering the result set.
     * @var string
     */
    public $order;
    /**
     * Gets or sets a number that indicates the number of records to retrieve.
     * @var int
     */
    public $top;
    /**
     * Gets or sets a number that indicates the number of records to be skipped.
     * @var int
     */
    public $skip;
    /**
     * Gets or sets a comma delimited string that contains the fields to be used for grouping the result set.
     * @var string
     */
    public $group;
    /**
     * Gets or sets a comma delimited string that contains the models to be expanded.
     * @var string
     */
    public $expand;
    /**
     * Gets or sets a boolean that indicates whether paging parameters will be included in the result set.
     * @var boolean
     */
    public $inlinecount;
    /**
     * Gets or sets a boolean which indicates whether the result will contain only the first item of the result set.
     * @var boolean
     */
    public $first;
    /**
     *  Gets or set a string that contains an open data formatted filter statement that is going to be joined with the underlying filter statement, if any.
     * @var string
     */
    public $prepared;
}