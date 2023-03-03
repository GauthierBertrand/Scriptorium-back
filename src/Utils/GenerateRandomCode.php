<?php

namespace App\Utils;

class GenerateRandomCode 
{
    public function generate($user)
    {
        for($i = 0; $i < 4; $i++) {
            $chaine[] = random_int(1,1000);
        }
        return hash("sha256", implode("-", $chaine) . $user->getEmail());

    }
}