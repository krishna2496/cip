<?php

namespace App\Repositories\MissionTheme;

use Illuminate\Http\Request;

interface MissionThemeInterface
{
    // public function save(array $data);
    public function missionThemeList(Request $request);
}
