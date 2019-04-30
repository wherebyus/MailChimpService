<?php

namespace WBU\Tests;

use DrewM\MailChimp\MailChimp;
use Mockery;
use PHPUnit\Framework\TestCase;
use WBU\DTOs\SegmentDto;
use WBU\DTOs\SubscriberDto;
use WBU\Http\Repositories\MailChimpRepository;

use WP_Mock;

require __DIR__ . '/../../../../bootstrap/autoload.php';

class MailChimpRepositoryTest extends TestCase
{
    private $apiKey;
    private $dto;
    private $referralObject;
    private $mailchimp;
    private $repository;
    private $subscriber;

    public function setUp() : void
    {
        \WP_Mock::setUp();
        \WP_Mock::setUsePatchwork(true);
        \WP_Mock::bootstrap();

        $this->apiKey = 'maidkmasodmasd92gibberish-us7';

        $this->mailchimp = $this->createMock('DrewM\MailChimp\MailChimp');
        $this->mailchimp->verify_ssl = false;

        $this->repository = new MailChimpRepository($this->mailchimp);
        $this->subscriber = array();
        $this->subscriber['mailChimpEmailAddress'] = 'al@whereby.us';
        $this->subscriber['mailChimpInterests'] = null;
        $this->subscriber['mailChimpLastChanged'] = null;
        $this->subscriber['mailChimpListId'] = '123456';
        $this->subscriber['mailChimpListMemberRating'] = 4;
        $this->subscriber['mailChimpListAverageClickRate'] = 55;
        $this->subscriber['mailChimpListAverageOpenRate'] = 80;
        $this->subscriber['mailChimpMergeFields'] = null;
        $this->subscriber['mailChimpSignUpTimestamp'] = null;
        $this->subscriber['mailChimpSubscriptionStatus'] = 'active';
        $this->subscriber['mailChimpUniqueId'] = '20';
        $this->subscriber['id'] = '223923k2k23923k';
    }

    public function tearDown() : void
    {
        \WP_Mock::tearDown();
    }

    public function testGetRootInformationFromApi()
    {
        $apiResults = [];

        $this->mailchimp->expects($this->once())
            ->method('get')
            ->with('')
            ->willReturn($apiResults);

        $actualResults = $this->repository->getRootInformationFromApi();

        $this->assertEquals($apiResults, $actualResults);
    }

    public function testGetSubscriberByEmail()
    {
        $email = 'a@b.com';
        $listId = '292j2j28';

        $this->mailchimp
            ->expects($this->once())
            ->method('get')
            ->willReturn($this->subscriber);

        $actual = $this->repository->getSubscriberByEmail($listId, $email);

        $this->assertInstanceOf('WBU\DTOs\SubscriberDto', $actual);
    }

    public function testGetSubscriberByUniqueId()
    {
        $uniqueId = 'dweAF2341';
        $listId = '292j2j28';

        $this->mailchimp
            ->expects($this->once())
            ->method('get')
            ->willReturn($this->subscriber);

        $actual = $this->repository->getSubscriberByUniqueId($listId, $uniqueId);

        $this->assertInstanceOf('WBU\DTOs\SubscriberDto', $actual);
    }

    public function testUpdateMailChimpSettingsInWordPress()
    {
        $expected = true;
        \WP_Mock::userFunction('update_option', [
            'times' => 1,
            'args' => [MailChimpRepository::OPTION_KEY_MAILCHIMP_API, 'XYZ'],
            'return' => true,
        ]);
        $actual = $this->repository->updateMailChimpSettingsInWordPress('XYZ');
        $this->assertEquals($expected, $actual);
    }

    public function testCannotUpdateMailChimpSettingsInWordPress_exceptionThrown()
    {
        \WP_Mock::userFunction('update_option')->andThrows(new \Exception());
        $actual = $this->repository->updateMailChimpSettingsInWordPress('XYZ');
        $this->assertFalse($actual);
    }

    public function testCanGetListMembersFromApi_returnsArray()
    {
        $arrayOfMembers = [];

        $offset = 10;
        $listId = '292j2j28';
        $this->mailchimp
            ->expects($this->any())
            ->method('get')
            ->willReturn($arrayOfMembers);

        $actualResults = $this->repository->getListMembersFromApi($listId, $offset);

        $this->assertEquals([], $actualResults);
    }

    public function testCanGetListsFromApi_returnsArray()
    {
        $this->mailchimp
            ->expects($this->any())
            ->method('get')
            ->with('lists')
            ->willReturn([]);

        $actualResults = $this->repository->getListsFromApi();

        $this->assertEquals([], $actualResults);
    }

    public function testCanUpdateMailChimpSettingsInWordPress_returnsBoolean()
    {
        \WP_Mock::userFunction(
            'update_option',
            array(
                'args' => array(
                    'options_mcapi_key',
                ),
                'return' => true,
            )
        );

        \WP_Mock::passthruFunction('update_option', array('times' => 1));

        $actualResults = $this->repository->updateMailChimpSettingsInWordPress('wee');

        $this->assertEquals(true, $actualResults);
    }

    public function testCanGetListSubscriberCount_returnsInteger()
    {
        $expectedResult = 0;
        $listId = '1234';
        $this->mailchimp
            ->expects($this->any())
            ->method('get')
            ->with('lists/1234');

        $actualResults = $this->repository->getListSubscriberCount($listId);

        $this->assertEquals($expectedResult, $actualResults);
    }

    public function testSubscribeMember_returnsArray()
    {
        $dto = new SubscriberDto($this->subscriber);
        $tagsArray = [];
        $mergeFields = [];
        $email = 'al@whereby.us';
        $listId = '1234';

        $this->mailchimp
            ->expects($this->once())
            ->method('post')
            ->willReturn($this->subscriber);

        $actualResults = $this->repository->subscribeMember($listId, $email, $tagsArray, $mergeFields);

        $this->assertEquals($dto, $actualResults);
    }

    public function testSubscribeMemberWithMergeTag_returnsArray()
    {
        $dto = new SubscriberDto($this->subscriber);
        $tagsArray = [];
        $mergeFields = ['FNAME' => 'Amy'];
        $email = 'al@whereby.us';
        $listId = '1234';

        $this->mailchimp
            ->expects($this->once())
            ->method('post')
            ->willReturn($this->subscriber);

        $actualResults = $this->repository->subscribeMember($listId, $email, $tagsArray, $mergeFields);

        $this->assertEquals($dto, $actualResults);
    }


    public function testSubscribeMemberThrowsException_returnsNull()
    {
        $listId = '393j3j3';
        $email = 'test@whereby.us';
        $tagsArray = [];
        $mergeFields = [];
        $this->mailchimp
            ->expects($this->once())
            ->method('post')
            ->will($this->throwException(new \Exception()));

        $actualResults = $this->repository->subscribeMember($listId, $email, $tagsArray, $mergeFields);

        $this->assertNull($actualResults);
    }

    public function testCannotSubscribeMember_apiReturnsFalse()
    {
        $listId = '29292j2j';
        $email = 'test@whereby.us';
        $tagsArray = [];
        $mergeFields = [];

        $this->mailchimp
            ->expects($this->once())
            ->method('post')
            ->willReturn(false);

        $actualResults = $this->repository->subscribeMember($listId, $email, $tagsArray, $mergeFields);

        $this->assertNull($actualResults);
    }
    public function testCannotSubscribeMember_apiReturnsErrorMessage_methodReturnsNull()
    {
        $listId = '29292j2j';
        $email = 'test@whereby.us';
        $tagsArray = [];
        $mergeFields = [];
        $apiResults = [
            'details' => 'I am an error message',
            'status' => 400
        ];

        $this->mailchimp
            ->expects($this->once())
            ->method('post')
            ->willReturn($apiResults);

        $actualResults = $this->repository->subscribeMember($listId, $email, $tagsArray, $mergeFields);

        $this->assertNull($actualResults);
    }


    public function testCanGetListId_returnsListId()
    {
        $listId = '292929292';

        \WP_Mock::userFunction(
            'get_option',
            array(
                'return' => $listId,
            )
        );

        $actualResults = $this->repository->getListID();

        $this->assertEquals($listId, $actualResults);
    }

    public function testCannotGetListId_getOptionThrowsException()
    {
        \WP_Mock::userFunction(
            'get_option'
        )->andThrows(new \Exception());

        $actualResults = $this->repository->getListID();

        $this->assertEmpty($actualResults);
    }



    public function testgetSubscriberByEmail_returnsMember()
    {
        $listId = "1234";
        $email = "michael@whereby.us";

        $results = array();
        $results['mailChimpEmailAddress'] = 'al@whereby.us';
        $results['mailChimpInterests'] = null;
        $results['mailChimpLastChanged'] = null;
        $results['mailChimpListId'] = '123456';
        $results['mailChimpListMemberRating'] = 4;
        $results['mailChimpListAverageClickRate'] = 55;
        $results['mailChimpListAverageOpenRate'] = 80;
        $results['mailChimpMergeFields'] = null;
        $results['mailChimpSignUpTimestamp'] = null;
        $results['mailChimpSubscriptionStatus'] = 'active';
        $results['mailChimpUniqueId'] = '20';
        $results['id'] = '223923k2k23923k';

        $this->dto = new SubscriberDto($results);

        $this->mailchimp
            ->expects($this->any())
            ->method('get')
            ->willReturn($results);

        $actualResults = $this->repository->getSubscriberByEmail($listId, $email);
        $this->assertEquals($this->dto, $actualResults);
    }

    public function testCanGetListMembersFromApi_returnsArrayOfMembers()
    {
        $membersArray = [
            'members' => [
                $this->subscriber
            ]
        ];
        $listId = '1923j32j32';
        $offset = 10;
        $dto = new SubscriberDto($this->subscriber);
        $this->mailchimp
            ->expects($this->once())
            ->method('get')
            ->willReturn($membersArray);

        $actualResults = $this->repository->getListMembersFromApi($listId, $offset);

        $this->assertEquals([$dto], $actualResults);
    }

    public function testCanGetLastError_returnsString()
    {
        $errorMessage = 'Weeeee';

        $this->mailchimp
            ->expects($this->once())
            ->method('getLastError')
            ->willReturn($errorMessage);

        $actualResults = $this->repository->getLastError();

        $this->assertEquals($errorMessage, $actualResults);
    }

    public function testCanUpdateSubscriber_returnsTrue()
    {
        $apiResults = [];
        $email = 'test@whereby.us';
        $mergeFields = [
            'FNAME' => 'Mickey'
        ];
        $listId = '123344kk2k';

        $this->mailchimp
            ->expects($this->once())
            ->method('patch')
            ->willReturn($apiResults);

        $actualResults = $this->repository->updateSubscriber(
            $email,
            $listId,
            $mergeFields
        );

        $this->assertTrue($actualResults);
    }

    public function testCannotUpdateSubscriber_returnsFalse()
    {
        $apiResults = false;
        $email = 'test@whereby.us';
        $mergeFields = [
            'FNAME' => 'Yeezy'
        ];
        $listId = '123344kk2k';
        $subscriberStatus = 'pending';

        $this->mailchimp
            ->expects($this->once())
            ->method('patch')
            ->willReturn($apiResults);

        $actualResults = $this->repository->updateSubscriber(
            $email,
            $listId,
            $mergeFields
        );

        $this->assertFalse($actualResults);
    }

    public function testCanMailFromWordPress_returnsBoolean()
    {
        $email = 'test@whereby.us';
        $message = 'Well this should work';
        $subject = 'Hey friends';

        \WP_Mock::userFunction('wp_mail', [
            'times' => 1,
            'args' => [$email, $subject, $message],
            'return' => true,
        ]);

        $actualResults = $this->repository->mailFromWordPress($email, $subject, $message);

        $this->assertEquals(true, $actualResults);
    }

    public function testMailerThrowsException_returnsFalse()
    {
        $email = 'test@whereby.us';
        $message = 'Well this should work';
        $subject = 'Hey friends';

        \WP_Mock::userFunction('wp_mail', [
            'times' => 1,
            'args' => [$email, $subject, $message],
        ])->andThrows(new \Exception());

        $actualResults = $this->repository->mailFromWordPress($email, $subject, $message);

        $this->assertEquals(false, $actualResults);
    }

    public function testCanUpdateSubscriberMergeTag_returnsTrue()
    {
        $apiResults = [];
        $email = 'test@whereby.us';
        $listId = '123344kk2k';

        $this->mailchimp
            ->expects($this->once())
            ->method('patch')
            ->willReturn($apiResults);

        $actualResults = $this->repository->updateSubscriberMergeTag(
            $email,
            $listId,
            'FNAME',
            'Mickey'
        );

        $this->assertTrue($actualResults);
    }

    public function testCanUpdateSubscriberMergeTag_returnsFalse()
    {
        $apiResults = false;
        $email = 'test@whereby.us';
        $listId = '123344kk2k';

        $this->mailchimp
            ->expects($this->once())
            ->method('patch')
            ->willReturn($apiResults);

        $actualResults = $this->repository->updateSubscriberMergeTag(
            $email,
            $listId,
            'FNAME',
            'Mickey'
        );

        $this->assertFalse($actualResults);
    }

    public function testCanSendCampaign_returnsTrue()
    {
        $campaignId = '12j2j2j2j2j2';
        $this->mailchimp
            ->expects($this->once())
            ->method('post')
            ->willReturn(true);

        $actualResults = $this->repository->sendCampaign($campaignId);

        $this->assertTrue($actualResults);
    }

    public function testCannotSendCampaign_throwsException_returnsFalse()
    {
        $campaignId = '22j2j2j2j2j';
        $this->mailchimp
            ->expects($this->once())
            ->method('post')
            ->will($this->throwException(new \Exception()));

        $actualResults = $this->repository->sendCampaign($campaignId);

        $this->assertFalse($actualResults);
    }

    public function testCanUpdateCampaignContent_returnsTrue()
    {
        $campaignId = '22j2j2j2jj2';
        $content = 'Weeeee';
        $this->mailchimp
            ->expects($this->once())
            ->method('put')
            ->willReturn(true);

        $actualResults = $this->repository->updateCampaignContentById($campaignId, $content);

        $this->assertTrue($actualResults);
    }

    public function testCannotUpdateCampaignContent_returnsFalse()
    {
        $campaignId = '2j2j2j2jj2';
        $contnet = 'weeeeeee';
        $this->mailchimp
            ->expects($this->once())
            ->method('put')
            ->will($this->throwException(new \Exception()));

        $actualResults = $this->repository->updateCampaignContentById($campaignId, $contnet);

        $this->assertFalse($actualResults);
    }

    public function testCanUpdateCampaignSettingsById_returnsArray()
    {
        $campaignArray = [
            'id' => '3k33j3j3j3'
        ];
        $campaignId = '3k33j3j3j3';
        $subject = 'yay';
        $from = 'Jack Ripper';
        $replyTo = 'jack@whitechapel.com';

        $this->mailchimp
            ->expects($this->once())
            ->method('patch')
            ->willReturn($campaignArray);

        $actualResults = $this->repository->updateCampaignSettingsById(
            $campaignId,
            $subject,
            $from,
            $replyTo
        );

        $this->assertEquals($actualResults, $campaignArray);
    }

    public function testCannotUpdateSettings_returnsFalse()
    {
        $campaignId = '3j3j3j3j3';
        $subject = 'yay';
        $from = 'Jack Ripper';
        $replyTo = 'jack@whitechapel.com';
        $this->mailchimp
            ->expects($this->once())
            ->method('patch')
            ->will($this->throwException(new \Exception()));

        $actualResults = $this->repository->updateCampaignSettingsById(
            $campaignId,
            $subject,
            $from,
            $replyTo
        );

        $this->assertFalse($actualResults);
    }

    public function testCanGetCampaignById_returnsArray()
    {
        $campaign = [
            'weee' => 'whoaaa'
        ];
        $campaignId = '2j2j2j2j2j2j2';
        $this->mailchimp
            ->expects($this->once())
            ->method('get')
            ->willReturn($campaign);

        $actualResults = $this->repository->getCampaignById($campaignId);

        $this->assertEquals($campaign, $actualResults);
    }

    public function testCannotGetCampaignById_returnsNull()
    {
        $campaignId = 'ejejejejejje';
        $this->mailchimp
            ->expects($this->once())
            ->method('get')
            ->will($this->throwException(new \Exception()));

        $actualResults = $this->repository->getCampaignById($campaignId);

        $this->assertNull($actualResults);
    }

    public function testCanCreateCampaign_returnsCampaign()
    {
        $campaign = [
            'test' => 'yayyy'
        ];
        $fromName = 'Jack Ripper';
        $imageUrl = 'whitechapel.jpg';
        $listId = 'j2j2j2j2j2j2';
        $postId = 5;
        $replyToEmailAddress = 'test@test.org';
        $seoDescription = 'Great newsletter';
        $seoTitle = 'Lalalala weee';
        $subject = 'Does this work?';

        $this->mailchimp
            ->expects($this->once())
            ->method('post')
            ->willReturn($campaign);

        $actualResults = $this->repository->createCampaign(
            $fromName,
            $imageUrl,
            $listId,
            $postId,
            $replyToEmailAddress,
            $seoDescription,
            $seoTitle,
            $subject
        );

        $this->assertEquals($campaign, $actualResults);
    }

    public function testCannotCreateCampaign_returnsFalse()
    {
        $fromName = 'Jack Ripper';
        $imageUrl = 'whitechapel.jpg';
        $listId = 'j2j2j2j2j2j2';
        $postId = 5;
        $replyToEmailAddress = 'test@test.org';
        $seoDescription = 'Great newsletter';
        $seoTitle = 'Lalalala weee';
        $subject = 'Does this work?';

        $this->mailchimp
            ->expects($this->once())
            ->method('post')
            ->will($this->throwException(new \Exception()));

        $actualResults = $this->repository->createCampaign(
            $fromName,
            $imageUrl,
            $listId,
            $postId,
            $replyToEmailAddress,
            $seoDescription,
            $seoTitle,
            $subject
        );

        $this->assertFalse($actualResults);
    }

    public function testCanGetSegmentById_returnsSegmentDto()
    {
        $arguments = [];
        $expectedResult['id'] = '9d';
        $expectedResult['member_count'] = 9;
        $expectedResult['name'] = 'Wee';
        $dto = new SegmentDto($expectedResult);
        $listId = '1234';
        $segmentId = '292929';

        $this->mailchimp
            ->expects($this->once())
            ->method('get')
            ->with("lists/{$listId}/segments/{$segmentId}", $arguments)
            ->willReturn($expectedResult);

        $actualResults = $this->repository->getSegmentById($listId, $segmentId);

        $this->assertEquals($dto, $actualResults);
    }

    public function testCannotGetSegmentById_returnsEmptyArray()
    {
        $listId = '1234';
        $segmentId = '232323';
        $this->mailchimp
            ->expects($this->once())
            ->method('get')
            ->will($this->throwException(new \Exception()));

        $actualResults = $this->repository->getSegmentById($listId, $segmentId);

        $this->assertEmpty($actualResults);
    }

    public function testCanGetArrayOfSegmentDtos_returnsArray()
    {
        $segmentArray = [
            'id' => '9d',
            'member_count' => 5,
            'name' => 'Lala',
        ];
        $expectedResult = [
          'segments' => [
              $segmentArray,
          ],
        ];
        $arrayOfDtos = [
            new SegmentDto($segmentArray)
        ];

        $listId = '22j2j2';

        $this->mailchimp
            ->expects($this->once())
            ->method('get')
            ->with("lists/{$listId}/segments", [
                'count' => 20,
            ])
            ->willReturn($expectedResult);

        $actualResults = $this->repository->getSegments($listId);

        $this->assertEquals($arrayOfDtos, $actualResults);
    }

    public function testCannotGetArrayOfSegments_returnsEmptyArray()
    {
        $listId = '3j3j3';
        $this->mailchimp
            ->expects($this->once())
            ->method('get')
            ->will($this->throwException(new \Exception()));

        $actualResults = $this->repository->getSegments($listId);

        $this->assertEmpty($actualResults);
    }

    public function testCanUpdateSubscriptionPreference_returnsDto()
    {
        $email = 'test@whereby.us';
        $status = 'cleaned';
        $listId = '2j2j2jj2j2j';
        $apiResults = [
            'email_address' => $email,
            'status' => $status,
            'interests' => [],
            'last_changed' => '',
            'list_id' => '',
            'member_rating' => 0,
            'stats' => [
                'avg_open_rate' => 0,
                'avg_click_rate' => 0
            ],
            'merge_fields' => [],
            'timestamp_signup' => '',
            'unique_email_id' => null
        ];
        $dto = new SubscriberDto($apiResults);

        $this->mailchimp
            ->expects($this->once())
            ->method('patch')
            ->willReturn($apiResults);

        $actualResults = $this->repository->updateSubscriptionPreference($dto, $listId);

        $this->assertEquals($dto, $actualResults);
    }

    public function testCannotUpdateSubscriptionPreference_returnsDto()
    {
        $email = 'test@whereby.us';
        $status = 'cleaned';
        $listId = '2j2j2jj2j2j';
        $apiResults = [
            'email_address' => $email,
            'status' => $status,
            'interests' => [],
            'last_changed' => '',
            'list_id' => '',
            'member_rating' => 0,
            'stats' => [
                'avg_open_rate' => 0,
                'avg_click_rate' => 0
            ],
            'merge_fields' => [],
            'timestamp_signup' => '',
            'unique_email_id' => null
        ];
        $dto = new SubscriberDto($apiResults);

        $this->mailchimp
            ->expects($this->once())
            ->method('patch')
            ->will($this->throwException(new \Exception()));

        $actualResults = $this->repository->updateSubscriptionPreference($dto, $listId);

        $this->assertNull($actualResults);
    }

    public function testCanTagSubscriberByEmail_returnsTrue()
    {
        $apiResults = [];
        $email = 'test@whereby.us';
        $tagName = 'cool';
        $listId = '123344kk2k';

        $this->mailchimp
            ->expects($this->once())
            ->method('post')
            ->willReturn($apiResults);

        $actualResults = $this->repository->tagSubscriberByEmail($listId, $email, $tagName);

        $this->assertTrue($actualResults);
    }

    public function testCannotTagSubscriberByEmail_returnsFalse()
    {
        $apiResults = false;
        $email = 'test@whereby.us';
        $tagName = 'cool';
        $listId = '123344kk2k';

        $this->mailchimp
            ->expects($this->once())
            ->method('post')
            ->willReturn($apiResults);

        $actualResults = $this->repository->tagSubscriberByEmail($listId, $email, $tagName);

        $this->assertFalse($actualResults);
    }

    public function testCanRemoveTagFromSubscriberByEmail_returnsTrue()
    {
        $apiResults = [];
        $email = 'test@whereby.us';
        $tagName = 'cool';
        $listId = '123344kk2k';

        $this->mailchimp
            ->expects($this->once())
            ->method('post')
            ->willReturn($apiResults);

        $actualResults = $this->repository->removeTagFromSubscriberByEmail($listId, $email, $tagName);

        $this->assertTrue($actualResults);
    }

    public function testCannotRemoveTagFromSubscriberByEmail_returnsFalse()
    {
        $apiResults = false;
        $email = 'test@whereby.us';
        $tagName = 'cool';
        $listId = '123344kk2k';

        $this->mailchimp
            ->expects($this->once())
            ->method('post')
            ->willReturn($apiResults);

        $actualResults = $this->repository->removeTagFromSubscriberByEmail($listId, $email, $tagName);

        $this->assertFalse($actualResults);
    }
}
