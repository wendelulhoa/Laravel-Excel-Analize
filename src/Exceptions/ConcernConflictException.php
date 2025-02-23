<?php

namespace Analize\Excel\Exceptions;

use LogicException;

class ConcernConflictException extends LogicException implements LaravelExcelException
{
    /**
     * @return ConcernConflictException
     */
    public static function queryOrCollectionAndView()
    {
        return new static('Cannot use FromQuery, FromArray or FromCollection and FromView on the same sheet.');
    }
}
