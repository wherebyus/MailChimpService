<?php

namespace WBU\Http\Services;

use WBU\Models\Segment;
use WBU\Models\Subscriber;

interface MailChimpServiceInterface
{
    public function getLastError() : string;
    public function getListId() : string;
    public function getListMembersFromApi(string $listId, int $offset) : array;

    /**
     * @param string $listId
     * @param string $email
     * @return null|Subscriber
     */
    public function getSubscriberByEmail(string $listId, string $email) : ?Subscriber;
    public function getSubscriberByUniqueId(string $listId, string $uniqueId) : ?Subscriber;
    public function getListSubscriberCount(string $listId) : int;
    public function getListsFromApi() : array;
    public function getRootInformationFromApi() : array;
    public function getSegmentById(string $listId, string $segmentId) : ?Segment;
    public function getSegments(string $listId) : array;
    public function getSignupLocationsFromApi(string $listId) : array;
    public function mailFromWordPress(string $email, string $subject, string $message) : bool;
    public function subscribeMember(string $listId, string $email, array $interests, array $mergeFields) : ?Subscriber;
    public function updateMailChimpSettingsInWordPress(string $key) : bool;
    public function updateSubscriber(string $email, array $interests, string $listId, array $mergeFields) : bool;
    public function updateSubscriberMergeTag(string $email, string $listId, string $mergeTag, $mergeTagValue) : bool;
}
