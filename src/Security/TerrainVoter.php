<?php
// src/Security/TerrainVoter.php
namespace App\Security;

use App\Entity\Terrain\Terrain;
use App\Entity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Terrain>
 */
class TerrainVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === 'TERRAIN_OWNER' && $subject instanceof Terrain;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) return false;

        /** @var Terrain $subject */
        return $subject->getCin() === $user->getCin();
    }
}