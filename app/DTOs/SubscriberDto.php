<?php

namespace WBU\DTOs;

/**
 * Class SubscriberDto
 * @package WBU\DTOs
 */
class SubscriberDto
{
    public $mailChimpEmailAddress;
    public $mailChimpInterests;
    public $mailChimpLastChanged;
    public $mailChimpListId;
    public $mailChimpListMemberRating;
    public $mailChimpListAverageClickRate;
    public $mailChimpListAverageOpenRate;
    public $mailChimpMergeFields;
    public $mailChimpSignUpTimestamp;
    public $mailChimpSubscriptionStatus;
    public $mailChimpUniqueId;

    public function __construct(array $subscriberArray = null) {
        if (empty($subscriberArray)) {
            return;
        }
        $this->mailChimpEmailAddress = isset( $subscriberArray['email_address'] ) ? $subscriberArray['email_address'] : '';
        $this->mailChimpInterests = isset( $subscriberArray['interests'] ) ? $subscriberArray['interests'] : [];
        $this->mailChimpLastChanged = isset( $subscriberArray['last_changed'] ) ? $subscriberArray['last_changed'] : '';
        $this->mailChimpListId = isset( $subscriberArray['list_id'] ) ? $subscriberArray['list_id'] : '';
        $this->mailChimpListMemberRating = isset( $subscriberArray['member_rating'] ) ? (int) $subscriberArray['member_rating'] : 0;
        $this->mailChimpListAverageClickRate = isset( $subscriberArray['stats'] ) ? (int) $subscriberArray['stats']['avg_open_rate'] : 0;
        $this->mailChimpListAverageOpenRate = isset( $subscriberArray['stats'] ) ? (int) $subscriberArray['stats']['avg_click_rate'] : 0;
        $this->mailChimpMergeFields = isset( $subscriberArray['merge_fields'] ) ? $subscriberArray['merge_fields'] : [];
        $this->mailChimpSignUpTimestamp = isset( $subscriberArray['timestamp_signup'] ) ? $subscriberArray['timestamp_signup'] : '';
        $this->mailChimpSubscriptionStatus = isset( $subscriberArray['status'] ) ? $subscriberArray['status'] : '';
        $this->mailChimpUniqueId = isset( $subscriberArray['unique_email_id'] ) ? $subscriberArray['unique_email_id'] : null;
    }
}
