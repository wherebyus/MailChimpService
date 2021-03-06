<?php

namespace WBU\Http\Services;

use WBU\DTOs\SubscriberDto;
use WBU\Http\Repositories\MailChimpRepository;
use WBU\Models\Segment;
use WBU\Models\Subscriber;

class MailChimpService implements MailChimpServiceInterface
{
    private $repository;

    public function __construct(MailChimpRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getListId() : string
    {
        return $this->repository->getListID();
    }

    public function getListMembersFromApi(string $listId, int $offset) : array
    {
        return $this->toSubscriberModelArray($this->repository->getListMembersFromApi($listId, $offset));
    }

    /**
     * @param string $listId
     * @param string $email
     * @return null|Subscriber
     */
    public function getSubscriberByEmail(string $listId, string $email) : ?Subscriber
    {
        $dto = $this->repository->getSubscriberByEmail($listId, $email);

        return empty($dto) ? null : new Subscriber($dto);
    }

    public function getSubscriberByUniqueId(string $listId, string $uniqueId) : ?Subscriber
    {
        $dto = $this->repository->getSubscriberByUniqueId($listId, $uniqueId);

        return empty($dto) ? null : new Subscriber($dto);
    }

    public function getLastError() : string
    {
        return $this->repository->getLastError();
    }

    public function getListSubscriberCount(string $listId) : int
    {
        return $this->repository->getListSubscriberCount($listId);
    }

    public function getListsFromApi(): array
    {
        return $this->repository->getListsFromApi();
    }

    public function getRootInformationFromApi() : array
    {
        return $this->repository->getRootInformationFromApi();
    }

    public function getSegmentById(string $listId, string $segmentId) : ?Segment
    {
        $dto = $this->repository->getSegmentById($listId, $segmentId);

        if (empty($dto)) {
            return null;
        }

        return new Segment($dto);
    }

    public function getSegments(string $listId) : array
    {
        return $this->toSegmentModelArray($this->repository->getSegments($listId));
    }

    public function mailFromWordPress(string $email, string $subject, string $message) : bool
    {
        return $this->repository->mailFromWordPress($email, $subject, $message);
    }

    public function removeTagFromSubscriberByEmail(string $listId, string $email, string $tagName) : bool
    {
        return $this->repository->removeTagFromSubscriberByEmail($listId, $email, $tagName);
    }

    public function sendTestNewsletter(string $email, string $campaignId) : bool
    {
      return $this->repository->sendTestNewsletter($email, $campaignId);
    }

    public function subscribeMember(string $listId, string $email, array $tags, array $mergeFields) : ?Subscriber
    {
        $subscriberDto = $this->repository->subscribeMember($listId, $email, $tags, $mergeFields);

        if (empty($subscriberDto)) {
            return null;
        }

        return new Subscriber($subscriberDto);
    }

    public function tagSubscriberByEmail(string $listId, string $email, string $tagName) : bool
    {
        return $this->repository->tagSubscriberByEmail($listId, $email, $tagName);
    }

    private function toSegmentModelArray(array $dtos): array
    {
        if (empty($dtos)) {
            return [];
        }

        $modelArray = [];

        foreach ($dtos as $dto) {
            $modelArray[] = new Segment($dto);
        }

        return $modelArray;
    }

    private function toSubscriberModelArray(array $dtos): array
    {
        if (empty($dtos)) {
            return [];
        }

        $modelArray = [];

        foreach ($dtos as $dto) {
            $modelArray[] = new Subscriber($dto);
        }

        return $modelArray;
    }

    public function unsubscribe(Subscriber $subscriber, string $listId) : bool
    {
        $updatedSubscriber = $this->updateSubscriptionPreference($subscriber, $listId);

        if (empty($updatedSubscriber)) {
            return false;
        }

        return $updatedSubscriber->getSubscriptionStatus() === 'unsubscribed';
    }

    public function updateMailChimpSettingsInWordPress(string $key) : bool
    {
        return $this->repository->updateMailChimpSettingsInWordPress($key);
    }

    public function updateSubscriber(
        string $email,
        string $listId,
        array $mergeFields
    ) : bool {
        return $this->repository->updateSubscriber($email, $listId, $mergeFields);
    }

    public function updateSubscriberMergeTag(string $email, string $listId, string $mergeTag, $mergeTagValue) : bool
    {
        return $this->repository->updateSubscriberMergeTag($email, $listId, $mergeTag, $mergeTagValue);
    }

    public function updateSubscriptionPreference(Subscriber $subscriber, string $listId) : ?Subscriber
    {
        $dto = $this->repository->updateSubscriptionPreference($subscriber->convertToDto(), $listId);

        return empty($dto) ? null : new Subscriber($dto);
    }
}
