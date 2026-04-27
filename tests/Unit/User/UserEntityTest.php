<?php

namespace App\Tests\Unit\User;

use App\Entity\User\User;
use PHPUnit\Framework\TestCase;

final class UserEntityTest extends TestCase
{
    public function testBasicSettersAndRoleMapping(): void
    {
        $user = new User();
        $user->setCin(12345678)
            ->setNom('Doe')
            ->setPrenom('Jane')
            ->setEmail('jane@example.com')
            ->setMdp('secret')
            ->setRole(2)
            ->setImg('avatar.png')
            ->setTwoFactorEnabled(1);

        $this->assertSame(12345678, $user->getCin());
        $this->assertSame('Doe', $user->getNom());
        $this->assertSame('Jane', $user->getPrenom());
        $this->assertSame('jane@example.com', $user->getEmail());
        $this->assertSame('secret', $user->getMdp());
        $this->assertSame('avatar.png', $user->getImg());
        $this->assertSame(['ROLE_AGRICULTEUR'], $user->getRoles());
        $this->assertTrue($user->isTwoFactorEnabled());
    }

    public function testBackupCodesLifecycle(): void
    {
        $user = new User();
        $user->setBackupCodes(['abc', 'def']);

        $this->assertTrue($user->isBackupCode('abc'));
        $this->assertFalse($user->isBackupCode('zzz'));

        $user->invalidateBackupCode('abc');
        $this->assertFalse($user->isBackupCode('abc'));
        $this->assertSame(['def'], array_values($user->getBackupCodes()));
    }
}
