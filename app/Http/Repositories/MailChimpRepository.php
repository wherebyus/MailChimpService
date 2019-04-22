<?php

namespace WBU\Http\Repositories;

use DrewM\MailChimp\MailChimp;
use WBU\DTOs\SegmentDto;
use WBU\DTOs\SubscriberDto;
use Rollbar\Rollbar;
use Rollbar\Payload\Level;

class MailChimpRepository implements MailChimpRepositoryInterface
{
    const OPTION_KEY_MAILCHIMP_API = 'options_mcapi_key';
    const OPTION_MAILCHIMP_LISTID = 'options_mclist_id';
    const OPTION_MAILCHIMP_SIGNUP_LOCATION_ID = 'mcsignup_location';
    const OPTION_MAILCHIMP_ANNUALID = 'options_mcannual_id';

    private $mailChimp;

    public function __construct(MailChimp $mailChimp)
    {
        $this->mailChimp = $mailChimp;
    }

    public function createCampaign(
        string $fromName,
        string $imageUrl,
        string $listId,
        int $postId,
        string $replyToEmailAddress,
        string $seoDescription,
        string $seoTitle,
        string $subject
    ) {
        $campaignOptions = [
            'recipients' => [
                'list_id' => $listId,
            ],
            'settings' => [
                'from_name' => $fromName,
                'reply_to' => $replyToEmailAddress,
                'subject_line' => $subject,
                'use_conversation' => false,
            ],
            'social_card' => [
                'description' => $this->removeEmojisFromDescription($seoDescription),
                'image_url' => $imageUrl,
                'title' => $seoTitle,
            ],
            'type' => 'regular',
        ];

        try {
            return $this->post("campaigns", $campaignOptions);
        } catch (\Exception $e) {
            return false;
        }
    }

    private function get(string $method, array $arguments)
    {
        $apiResults = $this->mailChimp->get($method, $arguments);

        if ($apiResults === false) {
            $errorMessage = $this->mailChimp->getLastError();
            throw new \Exception($errorMessage);
        }

        if (isset($apiResults['status']) && $apiResults['status'] === 400 ||
            isset($apiResults['status']) && $apiResults['status'] === 404
        ) {
            $errorMessage = $apiResults['details'] ?? '';
            throw new \Exception($errorMessage);
        }

        return $apiResults;
    }

    /**
     * @param string $campaignId
     * @return array|null
     */
    public function getCampaignById(string $campaignId)
    {
        try {
            return $this->get("campaigns/{$campaignId}", []);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getOption(string $optionName, $defaultValue = null)
    {
        $option = null;

        try {
            $option = get_option($optionName, $defaultValue);
        } catch (\Exception $e) {
            $option = null;
        }

        return $option;
    }

    public function getLastError() : string
    {
        return $this->mailChimp->getLastError();
    }

    public function getListID() : string
    {
        return $this->getOption(self::OPTION_MAILCHIMP_LISTID) ?: getenv('MC_LIST_ID');
    }

    /**
     * @see https://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/#read-get_lists_list_id_members
     */
    public function getListMembersFromApi(string $listId, int $offset) : array
    {
        $membersApiResponse = $this->mailChimp->get("lists/{$listId}/members?offset={$offset}");
        $membersArray = $membersApiResponse['members'] ?? [];

        return $this->toSubscriberDtoArray($membersArray);
    }

    /**
     * @see https://developer.mailchimp.com/documentation/mailchimp/reference/lists/segments/#read-get_lists_list_id_segments_segment_id
     */
    public function getSegmentById(string $listId, string $segmentId) : ?SegmentDto
    {
        $arguments = [];

        try {
            $segment = $this->get("lists/{$listId}/segments/{$segmentId}", $arguments);
            return new SegmentDto($segment);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @see https://developer.mailchimp.com/documentation/mailchimp/reference/lists/segments/#read-get_lists_list_id_segments
     */
    public function getSegments(string $listId) :  array
    {
        $arguments = [
            'count' => 20,
        ];

        try {
            $apiResponse = $this->get("lists/{$listId}/segments", $arguments);
            $segments = isset($apiResponse['segments']) ? $apiResponse['segments'] : [];

            return $this->toSegmentDtoArray($segments);
        } catch (\Exception $e) {
            return [];
        }
    }

    // Previously called getMemberFromApi()
    public function getSubscriberByEmail(string $listId, string $email) : ?SubscriberDto
    {
        $subscriber_hash = $this->getSubscriberHashFromEmail($email);
        $membersApiResponse = $this->mailChimp->get("lists/{$listId}/members/{$subscriber_hash}");

        $subscriber = isset($membersApiResponse['id']) ? $membersApiResponse : null;

        return $subscriber ? new SubscriberDto($subscriber) : null;
    }

    public function getSubscriberByUniqueId(string $listId, string $uniqueId) : ?SubscriberDto
    {
        $membersApiResponse = $this->mailChimp->get(
            "lists/{$listId}/members?unique_email_id={$uniqueId}&fields=members.email_address,members.unique_email_id"
        );

        $subscriber = isset($membersApiResponse['id']) ? $membersApiResponse : null;

        return $subscriber ? new SubscriberDto($subscriber) : null;
    }

    private function getSubscriberHashFromEmail(string $email) : string
    {
        return md5(strtolower($email));
    }

    /**
     * @see https://developer.mailchimp.com/documentation/mailchimp/reference/lists/
     */
    public function getListSubscriberCount(string $listId) : int
    {
        $list = $this->mailChimp->get("lists/{$listId}");
        $listSubscriberCount = (int) $list['stats']['member_count'] ?? 0;

        return $listSubscriberCount;
    }

    /**
     * @see https://developer.mailchimp.com/documentation/mailchimp/reference/lists/
     */
    public function getListsFromApi() : array
    {
        $listsApiResponse = $this->mailChimp->get('lists');

        return $listsApiResponse['lists'] ?? [];
    }

    /**
     * @see https://developer.mailchimp.com/documentation/mailchimp/reference/root/#read-get_root
     */
    public function getRootInformationFromApi() : array
    {
        $rootInformation = $this->mailChimp->get('');
        return $rootInformation ?: [];
    }

    public function getSignupLocationInterestId() : string
    {
        return $this->getOption(self::OPTION_MAILCHIMP_SIGNUP_LOCATION_ID, 'af44a7a082');
    }

    /**
     * @see https://developer.mailchimp.com/documentation/mailchimp/reference/lists/interest-categories/interests
     */
    public function getSignupLocationsFromApi(string $listId) : array
    {
        $arguments = [];
        $signupLocationInterestId = $this->getSignupLocationInterestId();
        $signupLocations = null;

        try {
            $result = $this->get(
                "lists/{$listId}/interest-categories/{$signupLocationInterestId}/interests",
                $arguments
            );
            $signupLocations = $result['interests'];
        } catch (\Exception $e) {
            $signupLocations = [];
        }

        return $signupLocations;
    }

    public function mailFromWordPress(string $email, string $subject, string $message) : bool
    {
        $mailSuccessfullyProcessed = false;

        try {
            $mailSuccessfullyProcessed = wp_mail($email, $subject, $message);
        } catch (\Exception $e) {
            return $mailSuccessfullyProcessed;
        }

        return $mailSuccessfullyProcessed;
    }

    private function parseApiResults($apiResults)
    {
        if ($apiResults === false) {
            $errorMessage = $this->mailChimp->getLastError();
            throw new \Exception($errorMessage);
        }

        if (isset($apiResults['status']) && $apiResults['status'] >= 400) {
            $errorMessage = $apiResults['details'] ?? '';
            throw new \Exception($errorMessage);
        }

        return $apiResults;
    }

    /**
     * Use `patch` to update an existing resource without creating a new one if
     * it doesn't exist.
     */
    private function patch(string $method, array $arguments)
    {
        return $this->parseApiResults($this->mailChimp->patch($method, $arguments));
    }

    /**
     * Use `post` to create a new resource
     */
    private function post(string $method, array $arguments)
    {
        return $this->parseApiResults($this->mailChimp->post($method, $arguments));
    }

    /**
     * Use `post` to update a new resource
     */
    private function put(string $method, array $arguments)
    {
        return $this->parseApiResults($this->mailChimp->put($method, $arguments));
    }

    private function removeEmojisFromDescription(string $description) : string
    {
        return preg_replace('/([0-9#][\x{20E3}])|[\x{00ae}\x{00a9}\x{203C}\x{2047}\x{2048}\x{2049}\x{3030}\x{303D}\x{2139}\x{2122}\x{3297}\x{3299}][\x{FE00}-\x{FEFF}]?|[\x{2190}-\x{21FF}][\x{FE00}-\x{FEFF}]?|[\x{2300}-\x{23FF}][\x{FE00}-\x{FEFF}]?|[\x{2460}-\x{24FF}][\x{FE00}-\x{FEFF}]?|[\x{25A0}-\x{25FF}][\x{FE00}-\x{FEFF}]?|[\x{2600}-\x{27BF}][\x{FE00}-\x{FEFF}]?|[\x{2900}-\x{297F}][\x{FE00}-\x{FEFF}]?|[\x{2B00}-\x{2BF0}][\x{FE00}-\x{FEFF}]?|[\x{1F000}-\x{1F6FF}][\x{FE00}-\x{FEFF}]?/u', '', $description);
    }
    /**
     * @see https://developer.mailchimp.com/documentation/mailchimp/reference/campaigns/#action-post_campaigns_campaign_id_actions_send
     */
    public function sendCampaign(string $campaignId) : bool
    {
        try {
            return $this->post("campaigns/{$campaignId}/actions/send", []);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @see https://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/#create-post_lists_list_id_members
     */
    public function subscribeMember(string $listId, string $email, array $interests, array $mergeFields) : ?SubscriberDto
    {
        /**
         * @todo Don't forget the Interest ID
         * Used when person A refers B, B accepts, and we need to subscribe B
         * to the mailing list.
         */
        $arguments = [
            'email_address' => $email,
            'status' => 'subscribed'
        ];

        if (!empty($mergeFields)) {
            $arguments['merge_fields'] = $mergeFields;
        }

        try {
            $result = $this->post("lists/{$listId}/members", $arguments);
            $response = new SubscriberDto($result);
        } catch (\Exception $e) {
            $response = null;
        }

        return $response;
    }

    private function toSegmentDtoArray(array $resultSet) : array
    {
        if (empty($resultSet)) {
            return [];
        }

        $dtoArray = [];

        foreach ($resultSet as $object) {
            $dtoArray[] = new SegmentDto($object);
        }

        return $dtoArray;
    }

    private function toSubscriberDtoArray(array $resultSet) : array
    {
        if (empty($resultSet)) {
            return [];
        }

        $dtoArray = [];

        foreach ($resultSet as $object) {
            $dtoArray[] = new SubscriberDto($object);
        }

        return $dtoArray;
    }

    /**
     * @see https://developer.mailchimp.com/documentation/mailchimp/reference/campaigns/content/#edit-put_campaigns_campaign_id_content
     */
    public function updateCampaignContentById(
        string $campaignId,
        string $content
    ) : bool {
        $contentOptions = [
            'html' => $content
        ];

        try {
            $this->put("campaigns/{$campaignId}/content", $contentOptions);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @return array|bool
     * @see https://developer.mailchimp.com/documentation/mailchimp/reference/campaigns/#%20
     */
    public function updateCampaignSettingsById(
        string $campaignId,
        string $emailSubject,
        string $fromName,
        string $replyToEmailAddress
    ) {
        $campaignSettings = [
            'settings' => [
                'subject_line' => $emailSubject,
                'reply_to' => $replyToEmailAddress,
                'from_name' => $fromName,
            ],
        ];
        try {
            return $this->patch("campaigns/{$campaignId}", $campaignSettings);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function updateMailChimpSettingsInWordPress(string $key) : bool
    {
        $result = null;

        try {
            $result = update_option(self::OPTION_KEY_MAILCHIMP_API, $key);
        } catch (\Exception $e) {
            $result = false;
        }

        return $result;
    }

    public function updateSubscriber(string $email, array $tags, string $listId, array $mergeFields) : bool
    {
        $arguments = [
            'email_address' => $email,
            'tags' => $tags,
            'merge_fields' => $mergeFields
        ];
        $subscriberHash = $this->getSubscriberHashFromEmail($email);

        try {
            $this->patch("lists/{$listId}/members/{$subscriberHash}", $arguments);
            $response = true;
        } catch (\Exception $e) {
            $errorMessage = "We're unable to update {$email} on {$listId}.";
            $details = [
                'exception' => $e,
                'arguments' => $arguments
            ];
            Rollbar::log(Level::WARNING, $errorMessage, $details);
            $response = false;
        }

        return $response;
    }

    public function updateSubscriberMergeTag(string $email, string $listId, string $mergeTag, $mergeTagValue) : bool
    {
        $mailChimpApiArguments = [
            'email_address' => $email,
            'merge_fields' => [
                $mergeTag => $mergeTagValue
            ]
        ];
        $subscriberHash = $this->getSubscriberHashFromEmail($email);

        try {
            $this->patch("lists/{$listId}/members/{$subscriberHash}", $mailChimpApiArguments);
            return true;
        } catch (\Exception $e) {
            $details = [
                'exception' => $e,
                'arguments' => $mailChimpApiArguments
            ];
            $errorMessage = "We were unable to update the tag {$mergeTag} for {$email} on {$listId} to the value {$mergeTagValue}";
            Rollbar::log(Level::WARNING, $errorMessage, $details);
            return false;
        }
    }
}
