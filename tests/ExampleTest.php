<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ExampleTest extends TestCase
{
    use DatabaseMigrations;
    public function testCreateState()
    {
        $factory = new App\Classes\StateFactory();
        $init = $factory->create('App\Classes\State\Init');
        $this->assertEquals(get_class($init), 'App\Classes\State\Init');
    }

    public function providerUser()
    {
        $qqUser = new \App\Classes\Logic\qqUser();
        $qqUser->createIfNotExist('123','','','','test');
        $qqUser->rememberMe();
    }

    public function testInvitationInit()
    {
        $this->providerUser();
        $user = new App\Classes\Logic\qqUser();
        $invitation = $user->getInvitation();
        $this->assertEquals($invitation->state, 'NotInit');
        $this->assertTrue($invitation->canSave());
        $this->assertEquals(get_class($invitation->getState()), 'App\Classes\State\NotInit');
    }

    public function testSyncImages()
    {
        $this->providerUser();
        $invitation = (new \App\Classes\Logic\qqUser())->getInvitation();
        $dir = $invitation->getImagesRelativeDir();
        Storage::deleteDirectory($dir);
        $images = ['1.jpg', '0.jpg', '2.jpg', '5.jpg'];
        array_walk($images, function($image) use ($dir) {
            Storage::put($dir.$image, '');
        });
        $invitation->syncImages();
        $this->assertTrue(count(array_diff($images, $invitation->images)) == 0);
        $this->assertTrue($invitation->getCurrentImageNum() == 4);
        $this->assertTrue($invitation->reachLimitImageNum(10) == ($invitation::MAX_IMAGE_NUM < 14));
        Storage::deleteDirectory($dir);
        $invitation->syncImages();
        $this->assertTrue($invitation->getCurrentImageNum() == 0);
    }

    public function testDeleteImage()
    {
        $this->providerUser();
        $invitation = (new \App\Classes\Logic\qqUser())->getInvitation();
        $dir = $invitation->getImagesRelativeDir();
        Storage::deleteDirectory($dir);
        $images = ['1.jpg', '0.jpg', '2.jpg', '5.jpg'];
        array_walk($images, function($image) use ($dir) {
            Storage::put($dir.$image, '');
        });
        $ret = $invitation->delImage('1.jpg');
        array_splice($images, 0, 1);
        $this->assertTrue(array_diff($invitation->images, $images) == []);
        $ret = $invitation->delImage('4.jpg');
        $this->assertFalse($ret);
        Storage::deleteDirectory($dir);
    }

    public function testTransformState()
    {
        $this->providerUser();
        $invitation = (new \App\Classes\Logic\qqUser())->getInvitation();
        $this->assertEquals($invitation->getState()->getName(), 'NotInit');
        $invitation->changeState('Frozen', $invitation);
        $this->assertEquals($invitation->getState()->getName(), 'NotInit');
        $invitation->changeState('Init', $invitation);
        $this->assertEquals($invitation->getState()->getName(), 'Init');
        $invitation->changeState('Frozen', $invitation);
        $this->assertEquals($invitation->getState()->getName(), 'Frozen');
        $invitation->changeState('Init', $invitation);
        $this->assertEquals($invitation->getState()->getName(), 'Init');

        $invitation->changeState('WaitPublish', $invitation);
        $this->assertEquals($invitation->getState()->getName(), 'WaitPublish');
        $invitation->changeState('Init', $invitation);
        $this->assertEquals($invitation->getState()->getName(), 'Init');
        $invitation->changeState('WaitPublish', $invitation);
        $this->assertEquals($invitation->getState()->getName(), 'WaitPublish');

        $invitation->changeState('Published', $invitation);
        $this->assertEquals($invitation->getState()->getName(), 'Published');
        $invitation->changeState('WaitPublish', $invitation);
        $this->assertEquals($invitation->getState()->getName(), 'Published');

        $invitation->changeState('Init', $invitation);
        $this->assertEquals($invitation->getState()->getName(), 'Init');
    }

    public function testSavePublishCancelPublish()
    {
        $this->providerUser();
        $invitation = (new \App\Classes\Logic\qqUser())->getInvitation();
        $invitation->save('groomname', 'bridename', '2017年10月01日 12点00分', '13238482834', '[]', '北京朝阳区25号', '北京大酒店', '1楼大厅', '8663766', 'i_wannna_be_with_you.mp3', 'style1');
        $this->assertFalse($invitation->isFullFill());
        $this->assertFalse($invitation->publish());

        $invitation->save('groomname', 'bridename', '2017年10月01日 12点00分', '13238482834', '["a.jpg","b.jpg"]', '北京朝阳区25号', '北京大酒店', '1楼大厅', '8663766', 'i_wannna_be_with_you.mp3', 'style1');
        $this->assertEquals($invitation->getState()->getName(), 'Init');
        $this->assertTrue($invitation->isFullFill());

        $this->assertTrue($invitation->publish());
        $this->assertEquals($invitation->getState()->getName(), 'WaitPublish');
        $this->assertFalse($invitation->save('groomname', 'bridename', '2017年10月01日 12点00分', '13238482834', '[]', '北京朝阳区25号', '北京大酒店', '1楼大厅', '8663766', 'i_wannna_be_with_you.mp3', 'style1'));

        $this->assertTrue($invitation->cancelPublish());
        $this->assertEquals($invitation->getState()->getName(), 'Init');
    }

    public function testInitInvitationById()
    {
        $this->providerUser();
        $invitation = (new \App\Classes\Logic\qqUser())->getInvitation();
        $id = $invitation->id;
        $byId = new \App\Classes\Logic\Invitation($id);
        $this->assertEquals($byId->getUser()->public_id, '123' );
    }

    /**
     * @expectedException Exception
     */
    public function testInitInvitationByIdException()
    {
        new \App\Classes\Logic\Invitation(1);
    }

    /**
     * @expectedException \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function testInitInvitationByIdNonUser()
    {
        $this->providerUser();
        $invitation = (new \App\Classes\Logic\qqUser())->getInvitation();
        \App\qqUser::first()->delete();
        new \App\Classes\Logic\Invitation(1);
    }

    public function testSendMessageToAdmin()
    {
        $this->providerUser();
        $user = new \App\Classes\Logic\qqUser();
        $user->sendMessageToAdmin('hello1');
        $receiveMessages = (new \App\Classes\Logic\Admin())->getReceiveMessages();
        $this->assertEquals(count($receiveMessages), 1);
        $user->sendMessageToAdmin('hello2');
        $receiveMessages = (new \App\Classes\Logic\Admin())->getReceiveMessages();
        $this->assertEquals(count($receiveMessages), 2);
        $this->assertEquals($receiveMessages[1]->message, 'hello2');
    }

    public function testAdminDeleteMessage()
    {
        $this->providerUser();
        $user = new \App\Classes\Logic\qqUser();
        $user->sendMessageToAdmin('hello1');
        $msg2 = $user->sendMessageToAdmin('hello2');
        $receiveMessages = (new \App\Classes\Logic\Admin())->getReceiveMessages();
        $this->assertEquals(count($receiveMessages), 2);
        $msg2->delete();
        $receiveMessages = (new \App\Classes\Logic\Admin())->getReceiveMessages();
        $this->assertEquals(count($receiveMessages), 1);
        $this->assertEquals(\App\Message::withTrashed()->get()->count(), 2);
    }

    public function testAdminSendMessage()
    {
        $this->providerUser();
        $user = new \App\Classes\Logic\qqUser();
        $admin = new \App\Classes\Logic\Admin();
        $admin->sendMessageToUser($user,'hello');
        $receiveMessages = $user->getReceiveMessages();
        $this->assertEquals(count($receiveMessages), 1);
        $this->assertEquals($receiveMessages[0]->message, 'hello');
    }

}
