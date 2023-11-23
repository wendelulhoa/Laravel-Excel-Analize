<?php

namespace Analize\Excel\Concerns;

interface WithValidation
{
    /**
     * @return array
     */
    public function rules(): array;
}
