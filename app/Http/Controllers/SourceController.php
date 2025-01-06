<?php

namespace App\Http\Controllers;

use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use App\Models\Source;


class SourceController extends Controller
{
    use HttpResponses;
    public function index()
    {
        $sources =  Source::all();
        return $this->successResponse(['data' => $sources], 'Sources retrieved successfully');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
        ]);

        $source = Source::create($validated);
        return $this->successResponse(['data' => $source], 'Source added successfully');
    }
}
