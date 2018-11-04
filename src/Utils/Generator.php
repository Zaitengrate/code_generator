<?php

namespace App\Utils;

class Generator
{
    public function generateCode($nb)
    {
        $letters_array = ['A', 'B', 'C', 'D', 'E', 'F'];
        $numbers_array = [2, 3, 4, 6, 7, 8, 9];

        $codes_array = [];

        for ($i = 1; $i <= $nb; $i++) {
            $new_array = [];
            for ($j = 0; $j < 6; $j++) {
                $new_array[] = $letters_array[random_int(0, 5)];
            }
            for ($k = 0; $k < 4; $k++) {
                $new_array[] = $numbers_array[random_int(0, 6)];
            }
            shuffle($new_array);
            $string = implode('', $new_array);
            $codes_array[] = $string;
        }

        return $codes_array;
    }
}