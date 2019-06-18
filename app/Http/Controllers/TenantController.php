<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Tenant\TenantRepository;

class TenantController extends Controller
{
    /**
     * @var App\Repositories\Tenant\TenantRepository
     */
    private $tenant;

    /**
     * Create a new Tenant controller instance.
     *
     * @param  App\Repositories\Tenant\TenantRepository $tenant 
     * @return void
     */    
    public function __construct(TenantRepository $tenant)
    {
         $this->tenant = $tenant;
    }
    
    /**
     * Display a listing of the tenants.
     *
     * Illuminate\Http\Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        return  $this->tenant->tenantList($request);
    }

    /**
     * Store a newly created tenant into database
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        return  $this->tenant->store($request);
    }

    /**
     * Show tenant details
     *
     * @param int $id
     * @return mixed
     */
    public function show(int $tenantId)
    {
        return  $this->tenant->find($tenantId);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $id)
    {
        return  $this->tenant->update($request, $id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        return  $this->tenant->delete($id);
    }
}
