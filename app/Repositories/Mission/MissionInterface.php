<?php
namespace App\Repositories\Mission;

use Illuminate\Http\Request;
use App\Models\MissionRating;
use App\Models\Mission;
use Illuminate\Database\Eloquent\Collection;

interface MissionInterface
{
    /**
     * Store a new resource.
     *
     * @param  Illuminate\Http\Request $request
     * @return void
     */
    public function store(Request $request);
    
    /**
     * Update resource.
     *
     * @param  Illuminate\Http\Request $request
     * @param  int $id
     * @return void
     */
    public function update(Request $request, int $id);
  
    /**
     * Find a specified resource.
     *
     * @param  int $id
     * @return void
     */
    public function find(int $id);
    
    /**
     * Delete specified resource.
     *
     * @param  int $id
     * @return void
     */
    public function delete(int $id);

    /**
     * Add/remove mission to favourite.
     *
     * @param int $userId
     * @param int $missionId
     * @return \Illuminate\Http\Response
     */
    public function missionFavourite(int $userId, int $missionId);
    
    /*
     * Get mission name.
     *
     * @param int $missionId
     * @param int $languageId
     * @return string
     */
    public function getMissionName(int $missionId, $languageId): string;

    /**
     * Add/update mission rating.
     *
     * @param int $userId
     * @param array $request
     * @return App\Models\MissionRating
     */
    public function storeMissionRating(int $userId, array $request): MissionRating;

    /**
     * Get mission detail.
     *
     * @param Illuminate\Http\Request $request
     * @param int $languageId
     * @param int $missionId
     * @return \App\Models\Mission
     */
    public function getMissionDetail(Request $request, int $languageId, int $missionId): Mission;

    /**
     * Get mission comments.
     *
     * @param int $missionId
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getComment(int $missionId): Collection;
}
