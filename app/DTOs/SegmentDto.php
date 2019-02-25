<?php

namespace WBU\DTOs;

/**
 * Class SegmentDto
 * @package WBU\DTOs
 * @see https://developer.mailchimp.com/documentation/mailchimp/reference/lists/segments/#read-get_lists_list_id_segments
 */
class SegmentDto
{
    public $id;
    public $memberCount;
    public $name;

    public function __construct(array $segment = null) {
        if (empty($segment)) {
            return;
        }

        $this->id = isset($segment['id']) ? $segment['id'] : '';
        $this->memberCount = isset($segment['member_count']) ? (int) $segment['member_count'] : 0;
        $this->name = isset($segment['name']) ? $segment['name'] : '';
    }
}
