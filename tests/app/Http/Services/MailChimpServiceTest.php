<?php

namespace WBU\Tests;

use PHPUnit\Framework\TestCase;
use WBU\DTOs\SegmentDto;
use WBU\DTOs\SubscriberDto;
use WBU\Http\Repositories\MailChimpRepository;
use WBU\Http\Services\MailChimpService;
use WBU\Models\Segment;
use WBU\Models\Subscriber;

require __DIR__ . '/../../../../bootstrap/autoload.php';

class MailChimpServiceTest extends TestCase
{
    private $repository;
    private $service;

    public function setUp() : void
    {
        $this->repository = $this->createMock(MailChimpRepository::class);
        $this->service = new MailChimpService($this->repository);
    }

    public function testGetSubscriberByEmail()
    {
        $dto = new SubscriberDto();
        $listId = '123456';
        $email = 'a@b.com';
        $member = new Subscriber($dto);

        $this->repository
            ->expects($this->once())
            ->method('getSubscriberByEmail')
            ->with($listId, $email)
            ->willReturn($dto);

         $actualResults = $this->service->getSubscriberByEmail($listId, $email);
    }

    public function testCanGetListMembersFromApi_returnsArrayOfModels()
    {
        $dto = new SubscriberDto();
        $listId = '123456';
        $member = new Subscriber($dto);
        $offset = 10;

        $this->repository
            ->expects($this->once())
            ->method('getListMembersFromApi')
            ->with($listId)
            ->willReturn([$dto]);

        $actualResults = $this->service->getListMembersFromApi($listId, $offset);

        $this->assertEquals([$member], $actualResults);
    }

    public function testCanGetEmptyListFromApi_returnsEmptyArray()
    {
        $offset = 10;
        $emptyArray = [];
        $listId = '1235323';
        $this->repository
            ->expects($this->once())
            ->method('getListMembersFromApi')
            ->with($listId)
            ->willReturn($emptyArray);

        $actualResults = $this->service->getListMembersFromApi($listId, $offset);

        $this->assertEmpty($actualResults);
    }

    public function testCanGetListsFromApi_returnsArray()
    {
        $lists = ['TESTING' => ['subscribers' => []]];

        $this->repository
            ->expects($this->once())
            ->method('getListsFromApi')
            ->willReturn($lists);

        $actualResults = $this->service->getListsFromApi();

        $this->assertEquals($lists, $actualResults);
    }

    public function testCanGetRootInformationFromApi_returnsArray()
    {
        $rootInformation = [];

        $this->repository
            ->expects($this->once())
            ->method('getRootInformationFromApi')
            ->willReturn($rootInformation);

        $actualResults = $this->service->getRootInformationFromApi();

        $this->assertEquals($rootInformation, $actualResults);
    }

    public function testCanUpdateSettingsInWordPress_returnsBoolean()
    {
        $key = 'hay';

        $this->repository
            ->expects($this->once())
            ->method('updateMailChimpSettingsInWordPress')
            ->with($key)
            ->willReturn(true);

        $actualResults = $this->service->updateMailChimpSettingsInWordPress($key);

        $this->assertTrue($actualResults);
    }

    public function testCanGetListSubscriberCount_returnsInteger()
    {
        $expectedResult = 20;
        $listId = '123456';

        $this->repository
            ->expects($this->once())
            ->method('getListSubscriberCount')
            ->with($listId)
            ->willReturn(20);

        $actualResults = $this->service->getListSubscriberCount($listId);

        $this->assertEquals($expectedResult, $actualResults);
    }

    public function testCanSubscribeMember_returnsMember()
    {
        $listId = '191929j4j4';
        $dto = new SubscriberDto();
        $email = 'test@whereby.us';
        $tagsArray = [];
        $mergeFields = [];
        $member = new Subscriber($dto);

        $this->repository->expects($this->once())
            ->method('subscribeMember')
            ->with($listId, $email, $tagsArray, $mergeFields)
            ->willReturn($dto);

        $actualResults = $this->service->subscribeMember($listId, $email, $tagsArray, $mergeFields);

        $this->assertEquals($member, $actualResults);
    }

    public function testCannotSubsribeMember_returnsNull()
    {
        $listId = '1ij2j22';
        $email = 'test@whereby.us';
        $tagsArray = [];
        $mergeFields = [];

        $this->repository->expects($this->once())
            ->method('subscribeMember')
            ->with($listId, $email, $tagsArray)
            ->willReturn(null);

        $actualResults = $this->service->subscribeMember($listId, $email, $tagsArray, $mergeFields);

        $this->assertNull($actualResults);
    }

    public function testCanGetListId_returnsString()
    {
        $listId = '2282j2j2j82';

        $this->repository->expects($this->once())
            ->method('getListId')
            ->willReturn($listId);

        $actualResults = $this->service->getListId();

        $this->assertEquals($listId, $actualResults);
    }

    public function testgetSubscriberByEmail_returnsMember()
    {
        $email = 'todd@whereby.us';
        $listId = 'sdfmiw123';
        $dto = new SubscriberDto();
        $member = new Subscriber($dto);

        $this->repository
            ->expects($this->once())
            ->method('getSubscriberByEmail')
            ->with($listId, $email)
            ->willReturn($dto);

        $actualResults = $this->service->getSubscriberByEmail($listId, $email);
        $this->assertEquals($member, $actualResults);
    }

    public function testgetSubscriberByUniqueId_returnsMember()
    {
        $uniqueId = 'daskf12322';
        $listId = 'sdfmiw123';
        $dto = new SubscriberDto();
        $member = new Subscriber($dto);

        $this->repository
            ->expects($this->once())
            ->method('getSubscriberByUniqueId')
            ->with($listId, $uniqueId)
            ->willReturn($dto);

        $actualResults = $this->service->getSubscriberByUniqueId($listId, $uniqueId);
        $this->assertEquals($member, $actualResults);
    }

    public function testCanGetLastError_returnsString()
    {
        $errorMessage = 'Oh no!';

        $this->repository
            ->expects($this->once())
            ->method('getLastError')
            ->willReturn($errorMessage);

        $actualResults = $this->service->getLastError();

        $this->assertEquals($errorMessage, $actualResults);
    }

    public function testCanMailFromWordPress_returnsBoolean()
    {
        $email = 'test@whereby.us';
        $message = 'Hey friends';
        $subject = 'Test email';
        $expectsResults = true;

        $this->repository
            ->expects($this->once())
            ->method('mailFromWordPress')
            ->with($email, $subject, $message)
            ->willReturn(true);

        $actualResults = $this->service->mailFromWordPress($email, $subject, $message);

        $this->assertEquals($expectsResults, $actualResults);
    }

    public function testCanUpdateSubscriber_returnsTrue()
    {
        $email = 'test@whereby.us';
        $listId = '29292992j2j';
        $mergeFields = [];
        $this->repository
            ->expects($this->once())
            ->method('updateSubscriber')
            ->with($email, $listId, $mergeFields)
            ->willReturn(true);

        $actualResults = $this->service->updateSubscriber(
            $email,
            $listId,
            $mergeFields
        );

        $this->assertTrue($actualResults);
    }

    public function testCanUpdateSubscriberMergeTag_returnsBoolean()
    {
        $email = 'test@whereby.us';
        $listId = '4949494994';
        $mergeTag = 'wee';
        $mergeTagValue = 5;

        $this->repository
            ->expects($this->once())
            ->method('updateSubscriberMergeTag')
            ->with($email, $listId, $mergeTag, $mergeTagValue)
            ->willReturn(true);

        $actualResults = $this->service->updateSubscriberMergeTag($email, $listId, $mergeTag, $mergeTagValue);

        $this->assertTrue($actualResults);
    }

    public function testCanGetSegmentById_returnsArray()
    {
        $expectedResults = [
            'id' => '9d',
            'member_count' => 9,
            'name' => 'Wee',
        ];
        $dto = new SegmentDto($expectedResults);
        $listId = '232323';
        $segmentId = '22323232';
        $segment = new Segment($dto);

        $this->repository
            ->expects($this->once())
            ->method('getSegmentById')
            ->with($listId, $segmentId)
            ->willReturn($dto);

        $actualResults = $this->service->getSegmentById($listId, $segmentId);

        $this->assertEquals($segment, $actualResults);
    }

    public function testCannotGetSegmentById_returnsNull()
    {
        $listId = '232323';
        $segmentId = '22323232';

        $this->repository
            ->expects($this->once())
            ->method('getSegmentById')
            ->with($listId, $segmentId)
            ->willReturn(null);

        $actualResults = $this->service->getSegmentById($listId, $segmentId);

        $this->assertNull($actualResults);
    }

    public function testCanGetSegments_returnsArrayOfSegments()
    {
        $arrayOfDtos = [
            new SegmentDto()
        ];

        $arrayOfModels = [
            new Segment()
        ];

        $listId = '3j3j3';

        $this->repository
            ->expects($this->once())
            ->method('getSegments')
            ->with($listId)
            ->willReturn($arrayOfDtos);

        $actualResults = $this->service->getSegments($listId);

        $this->assertEquals($arrayOfModels, $actualResults);
    }

    public function testCannotGetSegments_returnsEmptyArray()
    {
        $emptyArrayOfDtos = [];

        $listId = '3j3j3';

        $this->repository
            ->expects($this->once())
            ->method('getSegments')
            ->with($listId)
            ->willReturn($emptyArrayOfDtos);

        $actualResults = $this->service->getSegments($listId);

        $this->assertEmpty($actualResults);
    }

    public function testCanUpdateSubscriptionPreferences_returnsSubscriber()
    {
        $subscriber = new Subscriber(new SubscriberDto());
        $subscriber->setMailChimpEmailAddress('test@whereby.us');
        $subscriber->setMailChimpSubscriptionStatus('unsubscribed');
        $listId = '2j2j2j2j2';
        $dto = $subscriber->convertToDto();

        $this->repository
            ->expects($this->once())
            ->method('updateSubscriptionPreference')
            ->with($dto, $listId)
            ->willReturn($dto);

        $actualResults = $this->service->updateSubscriptionPreference($subscriber, $listId);

        $this->assertEquals($subscriber, $actualResults);
    }

    public function testCanUnsubscribeUser_returnsBoolean()
    {
        $subscriber = new Subscriber(new SubscriberDto());
        $subscriber->setMailChimpEmailAddress('test@whereby.us');
        $subscriber->setMailChimpSubscriptionStatus('unsubscribed');
        $listId = '2j2j2j2j2';
        $dto = $subscriber->convertToDto();

        $this->repository
            ->expects($this->once())
            ->method('updateSubscriptionPreference')
            ->with($dto, $listId)
            ->willReturn($dto);

        $actualResults = $this->service->unsubscribe($subscriber, $listId);

        $this->assertTrue($actualResults);
    }

    public function testCannotUnsubscribeUser_returnsBoolean()
    {
        $subscriber = new Subscriber(new SubscriberDto());
        $subscriber->setMailChimpEmailAddress('test@whereby.us');
        $subscriber->setMailChimpSubscriptionStatus('unsubscribed');
        $listId = '2j2j2j2j2';
        $dto = $subscriber->convertToDto();

        $this->repository
            ->expects($this->once())
            ->method('updateSubscriptionPreference')
            ->with($dto, $listId)
            ->willReturn(null);

        $actualResults = $this->service->unsubscribe($subscriber, $listId);

        $this->assertFalse($actualResults);
    }

    public function testCanSendTestNewsletter_returnsBoolean() {
        $email = "c.d.villard@gmail.com";
        $campaignId = "ABCDE12345";

        $this->repository
          ->expects($this->once())
          ->method("sendTestNewsletter")
          ->willReturn(true);

        $actualResults = $this->service->sendTestNewsletter($email, $campaignId);

        $this->assertTrue($actualResults);
    }

    public function testCanTagSubscriberByEmail_returnsBoolean()
    {
        $listId = '2j2j2j2j2j2';
        $email = 'test@whereby.us';
        $tagName = 'cool';

        $this->repository
            ->expects($this->once())
            ->method('tagSubscriberByEmail')
            ->with($listId, $email, $tagName)
            ->willReturn(true);

        $actualResults = $this->service->tagSubscriberByEmail($listId, $email, $tagName);

        $this->assertTrue($actualResults);
    }

    public function testCanRemoveTagrByEmail_returnsBoolean()
    {
        $listId = '2j2j2j2j2j2';
        $email = 'test@whereby.us';
        $tagName = 'cool';

        $this->repository
            ->expects($this->once())
            ->method('removeTagFromSubscriberByEmail')
            ->with($listId, $email, $tagName)
            ->willReturn(true);

        $actualResults = $this->service->removeTagFromSubscriberByEmail($listId, $email, $tagName);

        $this->assertTrue($actualResults);
    }

}
