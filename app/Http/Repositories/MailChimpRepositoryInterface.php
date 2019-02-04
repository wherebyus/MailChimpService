<?php

namespace WBU\Http\Repositories;

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
    public function getSubscriberByEmail(string $listId, string $email);
    public function getSubscriberByUniqueId(string $listId, string $uniqueId);
    public function getRootInformationFromApi() : array;
    public function getSignupLocationInterestId() : string;
    public function getSignupLocationsFromApi(string $listId) : array;
    public function mailFromWordPress(string $email, string $subject, string $message) : bool;
    public function sendCampaign(string $campaignId) : bool;
    public function subscribeMember(string $listId, string $email, array $interests, array $mergeFields);
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
    public function updateSubscriber(string $email, array $interests, string $listId, array $mergeFields) : bool;
    public function updateSubscriberMergeTag(string $email, string $listId, string $mergeTag, $mergeTagValue) : bool;
}
