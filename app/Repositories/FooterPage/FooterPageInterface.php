<?php

namespace App\Repositories\FooterPage;

use Illuminate\Http\Request;

interface FooterPageInterface {

	// public function save(array $data);
    
    public function store(Request $request);
	
	public function update(Request $request, int $id);
	
	public function footerPageList(Request $request);

    public function find(int $id);
	
    public function delete(int $id);
	
}
