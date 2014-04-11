<?php

namespace Oneup\AclBundle\Tests\Security\Authorization\Acl;

use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Core\User\User;
use Oneup\AclBundle\Tests\Model\SomeOtherObject;

use Oneup\AclBundle\Tests\Model\AbstractSecurityTest;

/**
 * AclProviderTest
 *
 * @uses AbstractSecurityTest
 * @author Julien Deniau <julien.deniau@mapado.com>
 */
class AclProviderTest extends AbstractSecurityTest
{

    /**
     * testFindObjectIdentitiesForToken
     *
     * @access public
     * @return void
     */
    public function testFindObjectIdentitiesForToken()
    {
        $aclProvider = $this->container->get('security.acl.provider');
        $this->assertInstanceOf('Oneup\AclBundle\Security\Authorization\Acl\AclProvider', $aclProvider);

        // empty object identity
        $ret = $aclProvider->findObjectIdentitiesForUser(
            $this->token,
            MaskBuilder::MASK_VIEW
        );
        $this->assertEmpty($ret);

        // add one
        $this->manager->addObjectPermission($this->object1, $this->mask1, $this->token);
        $ret = $aclProvider->findObjectIdentitiesForUser(
            $this->token,
            MaskBuilder::MASK_VIEW
        );

        $this->assertCount(1, $ret);

        // add another one
        $this->manager->addObjectPermission($this->object2, $this->mask1, $this->token);
        $ret = $aclProvider->findObjectIdentitiesForUser(
            $this->token,
            MaskBuilder::MASK_VIEW
        );

        $this->assertCount(2, $ret);

        $ret = $aclProvider->findObjectIdentitiesForUser(
            $this->token,
            MaskBuilder::MASK_DELETE
        );
        $this->assertEmpty($ret);
    }

    /**
     * testFindObjectIdentitiesForUser
     *
     * @access public
     * @return void
     */
    public function testFindObjectIdentitiesForUser()
    {
        $aclProvider = $this->container->get('security.acl.provider');
        $user = new User('usertest', 'pwd');

        $ret = $aclProvider->findObjectIdentitiesForUser(
            $user,
            MaskBuilder::MASK_EDIT
        );
        $this->assertEmpty($ret);

        // add right on object2 (instanceof SomeObject)
        $this->manager->addObjectPermission($this->object2, $this->mask1, $user);
        $ret = $aclProvider->findObjectIdentitiesForUser($user, MaskBuilder::MASK_EDIT);
        $this->assertCount(1, $ret);

        // add another object type
        $tmp = new SomeOtherObject(1);
        $this->manager->addObjectPermission($tmp, $this->mask1, $user);
        $ret = $aclProvider->findObjectIdentitiesForUser($user, MaskBuilder::MASK_EDIT);
        $this->assertCount(2, $ret);

        // filter by type
        $ret = $aclProvider->findObjectIdentitiesForUser($user, MaskBuilder::MASK_EDIT, get_class($tmp));
        $this->assertCount(1, $ret);
    }
}
