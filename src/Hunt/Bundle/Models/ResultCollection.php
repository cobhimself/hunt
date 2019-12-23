<?php


namespace Hunt\Bundle\Models;


use Symfony\Component\HttpFoundation\ParameterBag;

class ResultCollection extends ParameterBag
{
    public function getLongestFilenameLength()
    {
        return max(array_map('strlen', $this->keys()));
    }

    public function getLongestLineNumInResults()
    {
        return max(
            array_map(static function ($arr) {
                    return $arr->getLongestLineNumLength();
                },
                $this->all()
            )
        );
    }
}