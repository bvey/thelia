<?php

/*
 * This file is part of the Thelia package.
 * http://www.thelia.net
 *
 * (c) OpenStudio <info@thelia.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Thelia\Tests\Action;

use Thelia\Action\Profile;
use Thelia\Core\Event\Profile\ProfileEvent;
use Thelia\Core\Security\AccessManager;
use Thelia\Model\Profile as ProfileModel;
use Thelia\Model\ProfileQuery;

/**
 * Class ProfileTest.
 *
 * @author Manuel Raynaud <manu@raynaud.io>
 */
class ProfileTest extends BaseAction
{
    public static function setUpBeforeClass(): void
    {
        ProfileQuery::create()
            ->filterByCode('Test')
            ->delete();
    }

    public function testCreate()
    {
        $event = new ProfileEvent();

        $event
            ->setCode('Test')
            ->setLocale('en_US')
            ->setTitle('test profile')
            ->setChapo('test chapo')
            ->setDescription('test description')
            ->setPostscriptum('test postscriptum');

        $action = new Profile($this->getMockEventDispatcher());
        $action->create($event, null, $this->getMockEventDispatcher());

        $createdProfile = $event->getProfile();

        $this->assertInstanceOf('Thelia\Model\Profile', $createdProfile);
        $this->assertFalse($createdProfile->isNew());

        $this->assertEquals('Test', $createdProfile->getCode());
        $this->assertEquals('en_US', $createdProfile->getLocale());
        $this->assertEquals('test profile', $createdProfile->getTitle());
        $this->assertEquals('test chapo', $createdProfile->getChapo());
        $this->assertEquals('test description', $createdProfile->getDescription());
        $this->assertEquals('test postscriptum', $createdProfile->getPostscriptum());

        return $createdProfile;
    }

    /**
     * @depends testCreate
     *
     * @return ProfileModel
     */
    public function testUpdate(ProfileModel $profile)
    {
        $event = new ProfileEvent();

        $event
            ->setId($profile->getId())
            ->setLocale('en_US')
            ->setTitle('test update title')
            ->setChapo('test update chapo')
            ->setDescription('test update description')
            ->setPostscriptum('test update postscriptum');

        $action = new Profile($this->getMockEventDispatcher());
        $action->update($event, null, $this->getMockEventDispatcher());

        $updatedProfile = $event->getProfile();

        $this->assertInstanceOf('Thelia\Model\Profile', $updatedProfile);

        $this->assertEquals($profile->getCode(), $updatedProfile->getCode());
        $this->assertEquals('en_US', $updatedProfile->getLocale());
        $this->assertEquals('test update title', $updatedProfile->getTitle());
        $this->assertEquals('test update chapo', $updatedProfile->getChapo());
        $this->assertEquals('test update description', $updatedProfile->getDescription());
        $this->assertEquals('test update postscriptum', $updatedProfile->getPostscriptum());

        return $updatedProfile;
    }

    /**
     * @depends testUpdate
     */
    public function testUpdateResourceAccess(ProfileModel $profile)
    {
        $event = new ProfileEvent();
        $event
            ->setId($profile->getId())
            ->setResourceAccess([
                    'admin.address' => [AccessManager::CREATE],
                ])
        ;

        $action = new Profile($this->getMockEventDispatcher());
        $action->updateResourceAccess($event);

        $updatedProfile = $event->getProfile();

        $this->assertInstanceOf('Thelia\Model\Profile', $updatedProfile);

        $resources = $updatedProfile->getResources();

        $this->assertCount(1, $resources);

        $resource = $resources->getFirst();
        $this->assertEquals('admin.address', $resource->getCode());

        $profileResource = $updatedProfile->getProfileResources()->getFirst();
        $accessManager = new AccessManager($profileResource->getAccess());

        $this->assertTrue($accessManager->can(AccessManager::CREATE));
    }
}
