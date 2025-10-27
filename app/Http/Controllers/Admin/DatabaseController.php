<?php

namespace App\Http\Controllers\Admin;

use Spatie\DbDumper\Databases\MySql;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DatabaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function downloadDatabase()
        {
            $fileName = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $filePath = storage_path('app/' . $fileName);

            try {
                MySql::create()
                    ->setDbName(env('DB_DATABASE'))
                    ->setUserName(env('DB_USERNAME'))
                    ->setPassword(env('DB_PASSWORD'))
                    ->setHost(env('DB_HOST'))
                    ->dumpToFile($filePath);

                return response()->download($filePath)->deleteFileAfterSend(true);

            } catch (\Exception $e) {
                return back()->with('error', 'Database backup failed: ' . $e->getMessage());
            }
        }
}
