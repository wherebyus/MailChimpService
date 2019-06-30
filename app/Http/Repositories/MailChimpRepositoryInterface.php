<?php

namespace WBU\Http\Repositories;

use WBU\DTOs\SegmentDto;
use WBU\DTOs\SubscriberDto;

interface MailChimpRepositoryInterface
{
    /**
     * @return array|null
     */
    public function createCampaign(
        string $fromName,
        string $imageUrl,
        string $listId,
        int $postId,
        string $replyToEmailAddress,
        string $seoDescription,
        string $seoTitle,
        string $subject
    );
    /**
     * @param string $campaignId
     * @return array|null
     */
    public function getCampaignById(string $campaignId);
    public function getLastError() : string;
    public function getListMembersFromApi(string $listId, int $offset) : array;
    public function getListID() : string;
    public function getListsFromApi() : array;
    public function getListSubscriberCount(string $listId) : int;
    public function getSegmentById(string $listId, string $segmentId) : ?SegmentDto;
    public function getSegments(string $listId) : array;
    public function getSubscriberByEmail(string $listId, string $email) : ?SubscriberDto;
    public function getSubscriberByUniqueId(string $listId, string $uniqueId) : ?SubscriberDto;
    public function getRootInformationFromApi() : array;
    public function mailFromWordPress(string $email, string $subject, string $message) : bool;
    public function removeTagFromSubscriberByEmail(string $listId, string $email, string $tagName) : bool;
    public function sendCampaign(string $campaignId) : bool;
    public function sendTestNewsletter(string $newsletterId, string $campaignId) : string;
    public function subscribeMember(string $listId, string $email, array $tags, array $mergeFields) : ?SubscriberDto;
    public function tagSubscriberByEmail(string $listId, string $email, string $tagName) : bool;
    public function updateCampaignContentById(
        string $campaignId,
        string $content
    ) : bool;

    /**
     * @return array|bool
     */
    public function updateCampaignSettingsById(
        string $campaignId,
        string $emailSubject,
        string $fromName,
        string $replyToEmailAddress
    );
    public function updateMailChimpSettingsInWordPress(string $key) : bool;
    public function updateSubscriber(string $email, string $listId, array $mergeFields) : bool;
    public function updateSubscriberMergeTag(string $email, string $listId, string $mergeTag, $mergeTagValue) : bool;
    public function updateSubscriptionPreference(SubscriberDto $dto, string $listId) : ?SubscriberDto;
}
