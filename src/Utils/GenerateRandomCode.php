<?php

namespace App\Utils;

use App\Entity\User;

class GenerateRandomCode 
{
    /**
     * Generate random code register during the creation of group
     * 
     * @param User $user
     */
    public function generate(User $user)
    {
        for($i = 0; $i < 4; $i++) {
            $chaine[] = random_int(1,1000);
        }

        return hash("sha256", implode("", $chaine) . $user->getEmail());
    }
}