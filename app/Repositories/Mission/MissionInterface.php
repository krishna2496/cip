<?php
namespace App\Repositories\Mission;

use Illuminate\Http\Request;

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
}
