<?php

namespace WBU\Models;

use WBU\DTOs\SubscriberDto;

class Subscriber
{
    const MEMBERSHIP_STATUS_BASIC_MEMBER = 1;
    const MERGE_FIELD_FIRST_NAME = 'FNAME';
    const MERGE_FIELD_LAST_NAME = 'LNAME';
    const MERGE_FIELD_IS_ACTIVE = 'ISACTIVE';
    const MERGE_FIELD_IS_MEMBER = 'ISMEMBER';
    const MERGE_FIELD_IS_TESTER = 'ISTESTER';

    private $mailChimpEmailAddress;
    private $mailChimpInterests;
    private $mailChimpLastChanged;
    private $mailChimpListId;
    private $mailChimpListMemberRating;
    private $mailChimpListAverageClickRate;
    private $mailChimpListAverageOpenRate;
    private $mailChimpMergeFields;
    private $mailChimpSignUpTimestamp;
    private $mailChimpSubscriptionStatus;
    private $mailChimpUniqueId;

    public function __construct(SubscriberDto $dto = null)
    {
        if (empty($dto)) {
            return;
        }

        $this->mailChimpEmailAddress = $dto->mailChimpEmailAddress;
        $this->mailChimpInterests = $dto->mailChimpInterests;
        $this->mailChimpLastChanged = $dto->mailChimpLastChanged;
        $this->mailChimpListId = $dto->mailChimpListId;
        $this->mailChimpListMemberRating = $dto->mailChimpListMemberRating;
        $this->mailChimpListAverageClickRate = $dto->mailChimpListAverageClickRate;
        $this->mailChimpListAverageOpenRate = $dto->mailChimpListAverageOpenRate;
        $this->mailChimpMergeFields = $dto->mailChimpMergeFields;
        $this->mailChimpSignUpTimestamp = $dto->mailChimpSignUpTimestamp;
        $this->mailChimpSubscriptionStatus = $dto->mailChimpSubscriptionStatus;
        $this->mailChimpUniqueId = $dto->mailChimpUniqueId;
    }

    public function convertToArray() : array
    {
        return get_object_vars($this);
    }

    public function convertToDto() : SubscriberDto
    {
        $dto = new SubscriberDto();

        $dto->mailChimpEmailAddress = $this->mailChimpEmailAddress;
        $dto->mailChimpInterests = $this->mailChimpInterests;
        $dto->mailChimpLastChanged = $this->mailChimpLastChanged;
        $dto->mailChimpListId = $this->mailChimpListId;
        $dto->mailChimpListMemberRating = $this->mailChimpListMemberRating;
        $dto->mailChimpListAverageClickRate = $this->mailChimpListAverageClickRate;
        $dto->mailChimpListAverageOpenRate = $this->mailChimpListAverageOpenRate;
        $dto->mailChimpMergeFields = $this->mailChimpMergeFields;
        $dto->mailChimpSignUpTimestamp = $this->mailChimpSignUpTimestamp;
        $dto->mailChimpSubscriptionStatus = $this->mailChimpSubscriptionStatus;
        $dto->mailChimpUniqueId = $this->mailChimpUniqueId;

        return $dto;
    }

    public function getClickRate() : int
    {
        return $this->mailChimpListAverageClickRate;
    }

    public function getMailChimpEmailAddress() : string
    {
        return $this->mailChimpEmailAddress;
    }

    public function getMailChimpInterests() : array
    {
        return $this->mailChimpInterests;
    }

    public function getMailChimpLastChanged() : string
    {
        return $this->mailChimpLastChanged;
    }

    public function getMailChimpListId() : string
    {
        return $this->mailChimpListId;
    }

    public function getMailChimpMergeFields() : array
    {
        return $this->mailChimpMergeFields;
    }

    public function getMailChimpFirstName() : string
    {
        $member = $this->getMailChimpMergeFields();
        return (isset($member[self::MERGE_FIELD_FIRST_NAME]) && strtolower( $member[self::MERGE_FIELD_FIRST_NAME] ) !== 'null' ) ? $member[self::MERGE_FIELD_FIRST_NAME] : '';
    }

    public function getMailChimpLastName() : string
    {
        $member = $this->getMailChimpMergeFields();
        return (isset($member[self::MERGE_FIELD_LAST_NAME]) && strtolower( $member[self::MERGE_FIELD_LAST_NAME] ) !== 'null' ) ? $member[self::MERGE_FIELD_LAST_NAME] : '';
    }

    public function getMailChimpSignUpTimestamp() : string
    {
        return $this->mailChimpSignUpTimestamp;
    }

    public function getMailChimpUniqueId()
    {
        return $this->mailChimpUniqueId;
    }

    public function getMemberRating() : int
    {
        return $this->mailChimpListMemberRating;
    }

    public function getOpenRate() : int
    {
        return $this->mailChimpListAverageOpenRate;
    }

    public function getSubscriptionStatus() : string
    {
        return $this->mailChimpSubscriptionStatus;
    }

    public function setIsActive(int $isActive)
    {
        $mergeFields = $this->getMailChimpMergeFields();
        $mergeFields[self::MERGE_FIELD_IS_ACTIVE] = $isActive;

        $this->mailChimpMergeFields = $mergeFields;
    }

    public function setIsMember(bool $isMember) : void
    {
        $mergeFields = $this->getMailChimpMergeFields();
        $mergeFields[self::MERGE_FIELD_IS_MEMBER] = $isMember;

        $this->mailChimpMergeFields = $mergeFields;
    }

    public function setMailChimpEmailAddress(string $emailAddress)
    {
        $this->mailChimpEmailAddress = $emailAddress;
    }

    public function setMailChimpFirstName(string $name)
    {
        $mergeFields = $this->getMailChimpMergeFields();
        $mergeFields[self::MERGE_FIELD_FIRST_NAME] = $name;

        $this->mailChimpMergeFields = $mergeFields;
    }

    public function setMailChimpInterests(array $interests)
    {
        $this->mailChimpInterests = $interests;
    }

    public function setMailChimpLastChanged(string $lastChanged)
    {
        $this->mailChimpLastChanged = $lastChanged;
    }

    public function setMailChimpLastName(string $surname)
    {
        $mergeFields = $this->getMailChimpMergeFields();
        $mergeFields[self::MERGE_FIELD_FIRST_NAME] = $surname;

        $this->mailChimpMergeFields = $mergeFields;
    }

    public function setMailChimpListId(string $mcListId)
    {
        $this->mailChimpListId = $mcListId;
    }

    public function setMailChimpListMemberRating(int $mcListMemberRating)
    {
        $this->mailChimpListMemberRating = $mcListMemberRating;
    }

    public function setMailChimpListAverageClickRate(int $averageClickRate)
    {
        $this->mailChimpListAverageClickRate = $averageClickRate;
    }

    public function setMailChimpListAverageOpenRate(int $averageOpenRate)
    {
        $this->mailChimpListAverageOpenRate = $averageOpenRate;
    }

    public function setMailChimpMergeFields(array $mergeFields)
    {
        $this->mailChimpMergeFields = $mergeFields;
    }

    public function setMailChimpSignUpTimestamp(string $signUpTimestamp)
    {
        $this->mailChimpSignUpTimestamp = $signUpTimestamp;
    }

    public function setMailChimpSubscriptionStatus(string $subscriptionStatus)
    {
        $this->mailChimpSubscriptionStatus = $subscriptionStatus;
    }

    public function setMailChimpUniqueId(string $uniqueId)
    {
        $this->mailChimpUniqueId = $uniqueId;
    }

    public function setUniqueId(string $uniqueId)
    {
        $this->uniqueId = $uniqueId;
    }

    public function setWordPressUserEmail(string $email)
    {
        $this->wordPressUserEmail = $email;
    }

    public function setWordPressUserFirstName(string $firstName)
    {
        $this->wordPressUserFirstName = $firstName;
    }

    public function setWordPressUserId(int $userId)
    {
        $this->wordPressUserId = $userId;
    }

    public function setWordPressUserLastName(string $lastName)
    {
        $this->wordPressUserLastName = $lastName;
    }

    public function setReferralCount(int $referralCount)
    {
        $this->referralCount = $referralCount;
    }

    public function setLastSyncedTimestamp(string $lastSyncedTimestamp)
    {
        $this->lastSyncedTimestamp = $lastSyncedTimestamp;
    }

  	public function setIsActiveMember(bool $isActiveMember) : void
    {
        $this->isActiveMember = $isActiveMember;
    }

    public function setIsActiveSubscriber(bool $isActiveSubscriber)
    {
        $this->isActiveSubscriber = $isActiveSubscriber;
    }

    public function setHasLoggedInOnce(bool $hasLoggedInOnce)
    {
        $this->hasLoggedInOnce = $hasLoggedInOnce;
    }

    public function setHasReceivedSwag(bool $hasReceivedSwag)
    {
        $this->hasReceivedSwag = $hasReceivedSwag;
    }

    /**
     * Sets the both the isUserTester property and corresponding merge fields.
     */
    public function setIsUserTester(bool $isUserTester) : void
    {
        $this->isUserTester = $isUserTester;
    }
}
