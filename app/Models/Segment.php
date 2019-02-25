<?php

namespace WBU\Models;

use WBU\DTOs\SegmentDto;

class Segment
{
    private $id;
    private $memberCount;
    private $name;

    public function __construct(SegmentDto $dto = null)
    {
        if (empty($dto)) {
            return;
        }

        $this->id = $dto->id;
        $this->memberCount = $dto->memberCount;
        $this->name = $dto->name;
    }

    public function convertToArray() : array
    {
        return get_object_vars($this);
    }

    public function getName() : string
    {
        return $this->name;
    }
}
